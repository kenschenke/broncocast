<?php

namespace App\Util;

use Doctrine\ORM\EntityManagerInterface;
use Kreait\Firebase;

class FcmHandler
{
    /**
     * @var Firebase
     */
    private $firebase;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(Firebase $firebase, EntityManagerInterface $em)
    {
        $this->firebase = $firebase;
        $this->em = $em;
    }

    public function SendMessages($tokens, $shortMsg)
    {
        $messaging = $this->firebase->getMessaging();
        $message = Firebase\Messaging\CloudMessage::new()
            ->withNotification([
            'title' => 'BroncoCast',
            'body' => $shortMsg,
        ]);
        $responseData = $messaging->sendMulticast($message, $tokens);

        $failedSends = $responseData->failures();
        foreach ($failedSends->getItems() as $failure) {
            $this->PurgeToken($failure->target()->value());
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