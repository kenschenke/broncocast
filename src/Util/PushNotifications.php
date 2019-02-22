<?php

namespace App\Util;

use Doctrine\ORM\EntityManagerInterface;
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
        $service = new ApnsPushService(getenv('APNS_CERTFILE'), '', PushManager::ENVIRONMENT_DEV);
        $response = $service->push($deviceIds, [$message], ['message' => [
            'sound' => 'default',
            'custom' => ['broadcastId' => $broadcastId]
        ]]);

        $responses = $response->getParsedResponses();
        $repo = $this->em->getRepository('App:Contacts');
        foreach ($responses as $token => $result) {
            if ($result['token'] == 8) {
                // invalid token - remove it
                $contact = $repo->findOneBy(['contact' => $token]);
                if (!is_null($contact)) {
                    $this->em->remove($contact);
                    $this->em->flush();
                }
            }
        }
    }
}
