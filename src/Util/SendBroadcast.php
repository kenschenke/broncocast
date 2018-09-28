<?php

namespace App\Util;

use App\Entity\Attachments;
use App\Entity\Broadcasts;
use Doctrine\ORM\EntityManagerInterface;

class SendBroadcast
{
    private $em;
    private $messageUtil;

    public function __construct(EntityManagerInterface $em, MessageUtil $messageUtil)
    {
        $this->em = $em;
        $this->messageUtil = $messageUtil;
    }

    private function SendBroadcast(Broadcasts $Broadcast)
    {
        $Attachments = $Broadcast->getAttachments();

        $PhoneRecips = [];  // array of array of phone numbers and contact IDs
        $TextContent = '';  // content of SMS message
        $EmailRecips = []; // array of email addresses
        $EmailContent = '';  // content of email message body

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

            foreach ($User->getContacts() as $Contact) {
                $ContactStr = $Contact->getContact();
                if ($this->messageUtil->IsEmail($ContactStr)) {
                    if ($Force === 'none' || $Force === 'long') {
                        $EmailRecips[] = $ContactStr;
                    }
                } elseif ($this->messageUtil->IsPhone($ContactStr)) {
                    if ($Force === 'none' || $Force === 'short') {
                        $PhoneRecips[] = [
                            'ContactId' => $Contact->getId(),
                            'Phone' => $ContactStr,
                        ];
                    }
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

        // Done

        $Broadcast->setDelivered(new \DateTime());
        $this->em->persist($Broadcast);
        $this->em->flush();
    }

    public function SendBroadcasts()
    {
        // Get the current date/time in UTC since scheduled dates for
        // broadcasts are stored in UTC
        $Now = new \DateTime();
        $Now->setTimezone(new \DateTimeZone('UTC'));
        $Broadcasts = $this->em->getRepository('App:Broadcasts')->findBy(['delivered' => null]);
        foreach ($Broadcasts as $Broadcast) {
            $Scheduled = $Broadcast->getScheduled();
            if (is_null($Scheduled) || $Now > $Scheduled) {
                $this->SendBroadcast($Broadcast);
            }
        }
    }
}
