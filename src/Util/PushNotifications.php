<?php

namespace App\Util;

use Doctrine\ORM\EntityManagerInterface;
use Sly\NotificationPusher\Adapter\Apns;
use Sly\NotificationPusher\ApnsPushService;
use Sly\NotificationPusher\PushManager;

class PushNotifications
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function SendApplePushNotifications($deviceIds, $message, $broadcastId)
    {
        $certFile = getenv('APNS_CERTFILE');

        // Filter out device Ids that the APNS adapter knows it can't handle
        $apnsAdapter = new Apns(['certificate' => $certFile, 'passPhrase' => '']);
        $validDeviceIds = [];
        foreach ($deviceIds as $id) {
            if ($apnsAdapter->supports($id))
                $validDeviceIds[] = $id;
        }

        $service = new ApnsPushService($certFile, '', PushManager::ENVIRONMENT_PROD);
        $response = $service->push($validDeviceIds, [$message], ['message' => [
            'sound' => 'default',
            'custom' => ['broadcastId' => $broadcastId],
        ]]);

        $responses = $response->getParsedResponses();
        foreach ($responses as $token => $result) {
            if ($result['token'] === 8) {
                $this->PurgeToken($token);
            }
        }

        $pushManager = new PushManager(PushManager::ENVIRONMENT_PROD);
        $feedback = $pushManager->getFeedback($apnsAdapter);
        foreach ($feedback as $token => $data) {
            $this->PurgeToken($token);
        }
    }

    protected function PurgeToken($token)
    {
        $contact = $this->em->getRepository('App:Contacts')->findOneBy(['contact' => $token]);
        if (!is_null($contact)) {
            $this->em->remove($contact);
            $this->em->flush();
        }
    }
}
