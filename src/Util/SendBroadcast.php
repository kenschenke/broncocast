<?php

namespace App\Util;

use App\Entity\Attachments;
use App\Entity\Broadcasts;
use App\Entity\Contacts;
use App\Entity\Orgs;
use Doctrine\ORM\EntityManagerInterface;

class SendBroadcast
{
    private $em;
    private $messageUtil;
    private $pushNotifications;

    public function __construct(EntityManagerInterface $em, MessageUtil $messageUtil, PushNotifications $pushNotifications)
    {
        $this->em = $em;
        $this->messageUtil = $messageUtil;
        $this->pushNotifications = $pushNotifications;
    }

    private function SendBroadcast(Broadcasts $Broadcast)
    {
        $Attachments = $Broadcast->getAttachments();

        $PhoneRecips = [];  // array of array of phone numbers and contact IDs
        $TextContent = '';  // content of SMS message
        $EmailRecips = []; // array of email addresses
        $EmailContent = '';  // content of email message body
        $AppleRecips = [];  // array of Apple device tokens and contact IDs

        $ShortMsg = $Broadcast->getShortMsg();
        $LongMsg = $Broadcast->getLongMsg();

        // There's always a short message and that will always go to text recipients
        $TextContent = $ShortMsg;

        // If LongMsg is empty, email recipients get the short message.  Otherwise the long message
        $EmailContent = empty($LongMsg) ? $ShortMsg : $LongMsg;

        foreach ($Broadcast->getRecipients() as $Recipient) {
            $User = $Recipient->getUser();

            $Force = 'none';
            // If this user only wants to receive one message per broadcast, it will be an email if either:
            // (1) there is a long message or (2) if there is an attachment
            if ($User->getSingleMsg()) {
                $Force = empty($LongMsg) && $Attachments->isEmpty() ? 'short' : 'long';
            }

            // Keep track of the user's phone numbers and the number of
            // device tokens in their account.  This is to avoid sending
            // them both an SMS text and a push notification.

            $UserPhones = [];
            $UserAppleDevices = 0;

            foreach ($User->getContacts() as $Contact) {
                $ContactStr = $Contact->getContact();
                $ContactType = $Contact->getContactType();
                if ($ContactType === Contacts::TYPE_EMAIL) {
                    if ($Force === 'none' || $Force === 'long') {
                        $EmailRecips[] = $ContactStr;
                        $UserEmails[] = $ContactStr;
                    }
                } elseif ($ContactType === Contacts::TYPE_PHONE) {
                    if ($Force === 'none' || $Force === 'short') {
                        $UserPhones[] = [
                            'ContactId' => $Contact->getId(),
                            'Phone' => $ContactStr,
                        ];
                    }
                } elseif ($ContactType === Contacts::TYPE_APPLE) {
                    if ($Force === 'none' || $Force === 'short') {
                        $AppleRecips[] = [
                            'ContactId' => $Contact->getId(),
                            'DeviceToken' => $ContactStr,
                        ];
                        $UserAppleDevices++;
                    }
                }
            }

            // If the user has no Apple device tokens in their account,
            // send the broadcast as SMS texts.

            if ($UserAppleDevices === 0) {
                foreach ($UserPhones as $phone) {
                    $PhoneRecips[] = $phone;
                }
            }
        }

        // Prepare the attachment

        $friendlyName = null;
        $localName = null;
        $mimeType = null;
        if ($Attachments->count() === 1) {
            $attach = $Attachments[0];
            $friendlyName = $attach->getFriendlyName();
            $localName = $attach->getLocalName();
            $mimeType = $attach->getMimeType();
        }

        // Send the emails

        if (!empty($EmailRecips)) {
            $this->messageUtil->SendEmail($EmailRecips, $EmailContent, $localName, $friendlyName, $mimeType);
        }

        // Send the SMS texts

        if (!empty($PhoneRecips)) {
            $this->messageUtil->SendSMS($PhoneRecips, $TextContent);
        }

        // Send the Apple push notifications

        if (!empty($AppleRecips)) {
            $deviceTokens = [];
            foreach ($AppleRecips as $recip) {
                $deviceTokens[] = $recip['DeviceToken'];
            }

            $this->pushNotifications->SendApplePushNotifications($deviceTokens, $TextContent, $Broadcast->getId());
        }

        // Done

        $Broadcast->setDelivered(new \DateTime());
        $this->em->persist($Broadcast);
        $this->em->flush();
    }

    public function SendBroadcasts()
    {
        // Look up the APPREVIEW org
        /** @var Orgs $appReview */
        $appReview = $this->em->getRepository('App:Orgs')->findOneBy(['tag' => 'APPREVIEW']);
        if (is_null($appReview)) {
            $appReviewOrgId = 0;
        } else {
            $appReviewOrgId = $appReview->getId();
        }

        // Get the current date/time in UTC since scheduled dates for
        // broadcasts are stored in UTC
        $Now = new \DateTime();
        $Now->setTimezone(new \DateTimeZone('UTC'));
        $Broadcasts = $this->em->getRepository('App:Broadcasts')->findBy(['delivered' => null, 'cancelled' => false]);
        /** @var Broadcasts $Broadcast */
        foreach ($Broadcasts as $Broadcast) {
            if ($Broadcast->getOrgId() === $appReviewOrgId) {
                $Broadcast->setDelivered(new \DateTime());
                $this->em->persist($Broadcast);
                $this->em->flush();
                continue;
            }

            $Scheduled = $Broadcast->getScheduled();
            if (!is_null($Scheduled)) {
                // Convert the scheduled time to UTC
                $ScheduledTimestamp = $Scheduled->getTimestamp() + $Scheduled->getOffset();
                $Scheduled->setTimezone(new \DateTimeZone('UTC'));
                $Scheduled->setTimestamp($ScheduledTimestamp);
            }
            if (is_null($Scheduled) || $Now > $Scheduled) {
                $this->SendBroadcast($Broadcast);
            }
        }
    }
}
