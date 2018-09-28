<?php

namespace App\Util;

use App\Entity\SmsLogs;
use Doctrine\ORM\EntityManagerInterface;
use Mailgun\Mailgun;
use Twilio\Rest\Client;

/*
 * Purge broadcasts and old smslog records in Periodic
 * Purge orphaned files in the attachment folder
 * Update Periodic to use GMT when comparing scheduled date
 * Write lock file in Periodic and send email when lock file is stale for 3 runs in a row
 * Allow inspection of smslog records in Users list
 * Highlight users in Admin with smslog records
 * Highlight users in Admin with blacklisted numbers
 * Highlight unapproved users in admin
 */

class MessageUtil
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function IsEmail($Email)
    {
        return preg_match('/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}\b/', $Email) === 1;
    }

    public function IsPhone($Phone)
    {
        return strlen($Phone) === 10 && preg_match('/[^0-9]/', $Phone) === 0;
    }

    public function SendEmail($Recips, $Content, $AttachLocal, $AttachFriendly, $AttachMimeType)
    {
        $msgClient = new Mailgun(getenv('MAILGUN_API_KEY'));
        $domain = 'teambroncobots.com';

        $RecipVars = [];
        $i = 1;
        foreach ($Recips as $Recip) {
            $RecipVars[$Recip] = ['id' => $i++];
        }

        $params = [
            'from' => getenv('EMAIL_FROM_ADDR'),
            'to' => implode(',', $Recips),
            'subject' => 'BroncoCast',
            'text' => $Content,
            'recipient-variables' => json_encode($RecipVars)
        ];
        $files = [];
        $attach_params = [];
        if (!is_null($AttachLocal) && !is_null($AttachFriendly) && !is_null($AttachMimeType)) {
            $files[] = getenv('BRONCOCAST_ATTACHMENTS_DIR') . '/' . $AttachLocal;
            $attach_params = ['attachment' => $files];
        }

        $result = $msgClient->sendMessage($domain, $params, $attach_params);
    }

    public function SendSMS($PhoneContacts, $Content)
    {
        $TwilioSID = getenv('TWILIO_SID');
        $TwilioAuthToken = getenv('TWILIO_AUTHTOKEN');
        $client = new Client($TwilioSID, $TwilioAuthToken);
        $FromNumber = getenv('TWILIO_FROM_NUMBER');

        foreach ($PhoneContacts as $PhoneContact) {
            try {
                $client->messages->create("+1{$PhoneContact['Phone']}", [
                    'from' => $FromNumber,
                    'body' => $Content,
                ]);
            } catch (\Exception $e) {
                $Contact = $this->em->getRepository('App:Contacts')->find($PhoneContact['ContactId']);
                if (!is_null($Contact)) {
                    $log = new SmsLogs();
                    $log->setContact($Contact);
                    $log->setCode((string)$e->getCode());
                    $log->setMessage($e->getMessage());
                    $this->em->persist($log);
                    $this->em->flush();
                }
            }
        }
    }
}
