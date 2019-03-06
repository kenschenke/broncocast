<?php

namespace App\Tests\Util;

use App\Entity\Contacts;
use App\Util\SendBroadcast;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class SendBroadcastTest extends TestCase
{
    public function testNoBroadcasts()
    {
        $Broadcasts = new ArrayCollection();
        $broadcastsRepo = $this->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $broadcastsRepo->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['delivered' => null, 'cancelled' => false]))
            ->will($this->returnValue($Broadcasts));

        $orgsRepo = $this->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgsRepo->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(['tag' => 'APPREVIEW']))
            ->will($this->returnValue(null));

        $emMap = [
            ['App:Broadcasts', $broadcastsRepo],
            ['App:Orgs', $orgsRepo],
        ];

        $em = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $em->method('getRepository')->will($this->returnValueMap($emMap));

        $MessageUtil = $this->getMockBuilder('\App\Util\MessageUtil')
            ->disableOriginalConstructor()
            ->getMock();
        $MessageUtil->expects($this->never())->method('SendEmail');
        $MessageUtil->expects($this->never())->method('SendSMS');

        $pushNotifications = $this->getMockBuilder('\App\Util\PushNotifications')
            ->disableOriginalConstructor()
            ->getMock();
        $pushNotifications->expects($this->never())->method('SendApplePushNotifications');

        $SendBroadcast = new SendBroadcast($em, $MessageUtil, $pushNotifications);
        $SendBroadcast->SendBroadcasts();
    }

    public function testTwoFutureBroadcasts()
    {
        $Broadcast1 = $this->getMockBuilder('\App\Entity\Broadcasts')
            ->disableOriginalConstructor()
            ->getMock();
        $Tomorrow = new \DateTime();
        $Tomorrow->setTimezone(new \DateTimeZone('UTC'));
        $Tomorrow->add(new \DateInterval('P1D'));
        $Broadcast1->expects($this->once())
            ->method('getScheduled')
            ->will($this->returnValue($Tomorrow));

        $Broadcast2 = $this->getMockBuilder('\App\Entity\Broadcasts')
            ->disableOriginalConstructor()
            ->getMock();
        $LaterToday = new \DateTime();
        $LaterToday->setTimezone(new \DateTimeZone('UTC'));
        $LaterToday->add(new \DateInterval('PT3H'));
        $Broadcast2->expects($this->once())
            ->method('getScheduled')
            ->will($this->returnValue($LaterToday));

        $Broadcasts = new ArrayCollection([$Broadcast1, $Broadcast2]);

        $broadcastsRepo = $this->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $broadcastsRepo->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['delivered' => null, 'cancelled' => false]))
            ->will($this->returnValue($Broadcasts));

        $orgsRepo = $this->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgsRepo->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(['tag' => 'APPREVIEW']))
            ->will($this->returnValue(null));

        $emMap = [
            ['App:Broadcasts', $broadcastsRepo],
            ['App:Orgs', $orgsRepo],
        ];

        $em = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $em->method('getRepository')->will($this->returnValueMap($emMap));

        $MessageUtil = $this->getMockBuilder('\App\Util\MessageUtil')
            ->disableOriginalConstructor()
            ->getMock();
        $MessageUtil->expects($this->never())->method('SendEmail');
        $MessageUtil->expects($this->never())->method('SendSMS');

        $pushNotifications = $this->getMockBuilder('\App\Util\PushNotifications')
            ->disableOriginalConstructor()
            ->getMock();
        $pushNotifications->expects($this->never())->method('SendApplePushNotifications');

        $SendBroadcast = new SendBroadcast($em, $MessageUtil, $pushNotifications);
        $SendBroadcast->SendBroadcasts();
    }

    public function testReadyScheduledBroadcast()
    {
        $Broadcast = $this->getMockBuilder('\App\Entity\Broadcasts')
            ->disableOriginalConstructor()
            ->getMock();
        $FewMinutesAgo = new \DateTime();
        $FewMinutesAgo->sub(new \DateInterval('PT2M'));
        $Broadcast->expects($this->once())
            ->method('getScheduled')
            ->will($this->returnValue($FewMinutesAgo));
        $Broadcast->expects($this->once())
            ->method('getRecipients')
            ->will($this->returnValue(new ArrayCollection()));
        $Broadcast->expects($this->once())
            ->method('getAttachments')
            ->will($this->returnValue(new ArrayCollection()));

        $Broadcasts = new ArrayCollection([$Broadcast]);

        $broadcastsRepo = $this->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $broadcastsRepo->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['delivered' => null, 'cancelled' => false]))
            ->will($this->returnValue($Broadcasts));

        $orgsRepo = $this->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgsRepo->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(['tag' => 'APPREVIEW']))
            ->will($this->returnValue(null));

        $emMap = [
            ['App:Broadcasts', $broadcastsRepo],
            ['App:Orgs', $orgsRepo],
        ];

        $em = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $em->method('getRepository')->will($this->returnValueMap($emMap));

        $MessageUtil = $this->getMockBuilder('\App\Util\MessageUtil')
            ->disableOriginalConstructor()
            ->getMock();
        $MessageUtil->expects($this->never())->method('SendEmail');
        $MessageUtil->expects($this->never())->method('SendSMS');

        $pushNotifications = $this->getMockBuilder('\App\Util\PushNotifications')
            ->disableOriginalConstructor()
            ->getMock();
        $pushNotifications->expects($this->never())->method('SendApplePushNotifications');

        $SendBroadcast = new SendBroadcast($em, $MessageUtil, $pushNotifications);
        $SendBroadcast->SendBroadcasts();
    }

    public function testNonScheduledBroadcast()
    {
        $Broadcast = $this->getMockBuilder('\App\Entity\Broadcasts')
            ->disableOriginalConstructor()
            ->getMock();
        $Broadcast->expects($this->once())
            ->method('getRecipients')
            ->will($this->returnValue(new ArrayCollection()));
        $Broadcast->expects($this->once())
            ->method('getAttachments')
            ->will($this->returnValue(new ArrayCollection()));

        $Broadcasts = new ArrayCollection([$Broadcast]);

        $broadcastsRepo = $this->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $broadcastsRepo->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['delivered' => null, 'cancelled' => false]))
            ->will($this->returnValue($Broadcasts));

        $orgsRepo = $this->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgsRepo->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(['tag' => 'APPREVIEW']))
            ->will($this->returnValue(null));

        $emMap = [
            ['App:Broadcasts', $broadcastsRepo],
            ['App:Orgs', $orgsRepo],
        ];

        $em = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $em->method('getRepository')->will($this->returnValueMap($emMap));

        $MessageUtil = $this->getMockBuilder('\App\Util\MessageUtil')
            ->disableOriginalConstructor()
            ->getMock();
        $MessageUtil->expects($this->never())->method('SendEmail');
        $MessageUtil->expects($this->never())->method('SendSMS');

        $pushNotifications = $this->getMockBuilder('\App\Util\PushNotifications')
            ->disableOriginalConstructor()
            ->getMock();
        $pushNotifications->expects($this->never())->method('SendApplePushNotifications');

        $SendBroadcast = new SendBroadcast($em, $MessageUtil, $pushNotifications);
        $SendBroadcast->SendBroadcasts();
    }

    public function testShortAndLongMessage()
    {
        $Recipients = [];
        $EmailRecips = [];
        $PhoneRecips = [];
        $ShortMsg = 'Short Message';
        $LongMsg = 'Long Message';

        for ($i = 1; $i < 5; $i++) {
            $Email = "email$i@example.com";
            $EmailContact = $this->getMockBuilder('\App\Entity\Contacts')
                ->disableOriginalConstructor()
                ->getMock();
            $EmailContact
                ->expects($this->once())
                ->method('getContact')
                ->will($this->returnValue($Email));
            $EmailContact
                ->expects($this->once())
                ->method('getContactType')
                ->will($this->returnValue(Contacts::TYPE_EMAIL));
            $EmailRecips[] = $Email;

            $Phone = "816555121$i";
            $PhoneContact = $this->getMockBuilder('\App\Entity\Contacts')
                ->disableOriginalConstructor()
                ->getMock();
            $PhoneContact
                ->expects($this->once())
                ->method('getContact')
                ->will($this->returnValue($Phone));
            $PhoneContact
                ->expects($this->once())
                ->method('getContactType')
                ->will($this->returnValue(Contacts::TYPE_PHONE));
            $PhoneContact
                ->expects($this->once())
                ->method('getId')
                ->will($this->returnValue($i + 5));
            $PhoneRecips[] = [
                'ContactId' => $i + 5,
                'Phone' => $Phone,
            ];

            $User = $this->getMockBuilder('\App\Entity\Users')
                ->disableOriginalConstructor()
                ->getMock();
            $User
                ->expects($this->once())
                ->method('getContacts')
                ->will($this->returnValue(new ArrayCollection([$EmailContact, $PhoneContact])));
            $User
                ->expects($this->once())
                ->method('getSingleMsg')
                ->will($this->returnValue(false));

            $Recipient = $this->getMockBuilder('\App\Entity\Recipients')
                ->disableOriginalConstructor()
                ->getMock();
            $Recipient
                ->expects($this->once())
                ->method('getUser')
                ->will($this->returnValue($User));
            $Recipients[] = $Recipient;
        }

        $Broadcast = $this->getMockBuilder('\App\Entity\Broadcasts')
            ->disableOriginalConstructor()
            ->getMock();
        $Broadcast->expects($this->once())
            ->method('getRecipients')
            ->will($this->returnValue(new ArrayCollection($Recipients)));
        $Broadcast->expects($this->once())
            ->method('getAttachments')
            ->will($this->returnValue(new ArrayCollection()));
        $Broadcast->method('getShortMsg')->will($this->returnValue($ShortMsg));
        $Broadcast->method('getLongMsg')->will($this->returnValue($LongMsg));

        $Broadcasts = new ArrayCollection([$Broadcast]);

        $broadcastsRepo = $this->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $broadcastsRepo->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['delivered' => null, 'cancelled' => false]))
            ->will($this->returnValue($Broadcasts));

        $orgsRepo = $this->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgsRepo->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(['tag' => 'APPREVIEW']))
            ->will($this->returnValue(null));

        $emMap = [
            ['App:Broadcasts', $broadcastsRepo],
            ['App:Orgs', $orgsRepo],
        ];

        $em = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $em->method('getRepository')->will($this->returnValueMap($emMap));

        $MessageUtil = $this->getMockBuilder('\App\Util\MessageUtil')
            ->disableOriginalConstructor()
            ->getMock();
        $MessageUtil
            ->expects($this->once())
            ->method('SendEmail')
            ->with($this->equalTo($EmailRecips), $this->equalTo($LongMsg));
        $MessageUtil
            ->expects($this->once())
            ->method('SendSMS')
            ->with($this->equalTo($PhoneRecips), $this->equalTo($ShortMsg));

        $pushNotifications = $this->getMockBuilder('\App\Util\PushNotifications')
            ->disableOriginalConstructor()
            ->getMock();
        $pushNotifications->expects($this->never())->method('SendApplePushNotifications');

        $SendBroadcast = new SendBroadcast($em, $MessageUtil, $pushNotifications);
        $SendBroadcast->SendBroadcasts();
    }

    public function testShortMessageOnly()
    {
        $Recipients = [];
        $EmailRecips = [];
        $PhoneRecips = [];
        $ShortMsg = 'Short Message';

        for ($i = 1; $i < 5; $i++) {
            $Email = "email$i@example.com";
            $EmailContact = $this->getMockBuilder('\App\Entity\Contacts')
                ->disableOriginalConstructor()
                ->getMock();
            $EmailContact
                ->expects($this->once())
                ->method('getContact')
                ->will($this->returnValue($Email));
            $EmailContact
                ->expects($this->once())
                ->method('getContactType')
                ->will($this->returnValue(Contacts::TYPE_EMAIL));
            $EmailRecips[] = $Email;

            $Phone = "816555121$i";
            $PhoneContact = $this->getMockBuilder('\App\Entity\Contacts')
                ->disableOriginalConstructor()
                ->getMock();
            $PhoneContact
                ->expects($this->once())
                ->method('getContact')
                ->will($this->returnValue($Phone));
            $PhoneContact
                ->expects($this->once())
                ->method('getContactType')
                ->will($this->returnValue(Contacts::TYPE_PHONE));
            $PhoneContact
                ->expects($this->once())
                ->method('getId')
                ->will($this->returnValue($i+5));
            $PhoneRecips[] = [
                'ContactId' => $i + 5,
                'Phone' => $Phone,
            ];

            $User = $this->getMockBuilder('\App\Entity\Users')
                ->disableOriginalConstructor()
                ->getMock();
            $User
                ->expects($this->once())
                ->method('getContacts')
                ->will($this->returnValue(new ArrayCollection([$EmailContact, $PhoneContact])));
            $User
                ->expects($this->once())
                ->method('getSingleMsg')
                ->will($this->returnValue(false));

            $Recipient = $this->getMockBuilder('\App\Entity\Recipients')
                ->disableOriginalConstructor()
                ->getMock();
            $Recipient
                ->expects($this->once())
                ->method('getUser')
                ->will($this->returnValue($User));
            $Recipients[] = $Recipient;
        }

        $Broadcast = $this->getMockBuilder('\App\Entity\Broadcasts')
            ->disableOriginalConstructor()
            ->getMock();
        $Broadcast->expects($this->once())
            ->method('getRecipients')
            ->will($this->returnValue(new ArrayCollection($Recipients)));
        $Broadcast->expects($this->once())
            ->method('getAttachments')
            ->will($this->returnValue(new ArrayCollection()));
        $Broadcast->method('getShortMsg')->will($this->returnValue($ShortMsg));

        $Broadcasts = new ArrayCollection([$Broadcast]);

        $broadcastsRepo = $this->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $broadcastsRepo->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['delivered' => null, 'cancelled' => false]))
            ->will($this->returnValue($Broadcasts));

        $orgsRepo = $this->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgsRepo->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(['tag' => 'APPREVIEW']))
            ->will($this->returnValue(null));

        $emMap = [
            ['App:Broadcasts', $broadcastsRepo],
            ['App:Orgs', $orgsRepo],
        ];

        $em = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $em->method('getRepository')->will($this->returnValueMap($emMap));

        $MessageUtil = $this->getMockBuilder('\App\Util\MessageUtil')
            ->disableOriginalConstructor()
            ->getMock();
        $MessageUtil
            ->expects($this->once())
            ->method('SendEmail')
            ->with($this->equalTo($EmailRecips), $this->equalTo($ShortMsg));
        $MessageUtil
            ->expects($this->once())
            ->method('SendSMS')
            ->with($this->equalTo($PhoneRecips), $this->equalTo($ShortMsg));

        $pushNotifications = $this->getMockBuilder('\App\Util\PushNotifications')
            ->disableOriginalConstructor()
            ->getMock();
        $pushNotifications->expects($this->never())->method('SendApplePushNotifications');

        $SendBroadcast = new SendBroadcast($em, $MessageUtil, $pushNotifications);
        $SendBroadcast->SendBroadcasts();
    }

    public function testSingleMsgShortAndLong()
    {
        $Email = 'email@example.com';
        $Phone = '8165551212';
        $ShortMsg = 'Short Message';
        $LongMsg = 'Long Message';

        $EmailContact = $this->getMockBuilder('\App\Entity\Contacts')
            ->disableOriginalConstructor()
            ->getMock();
        $EmailContact
            ->expects($this->once())
            ->method('getContact')
            ->will($this->returnValue($Email));
        $EmailContact
            ->expects($this->once())
            ->method('getContactType')
            ->will($this->returnValue(Contacts::TYPE_EMAIL));

        $PhoneContact = $this->getMockBuilder('\App\Entity\Contacts')
            ->disableOriginalConstructor()
            ->getMock();
        $PhoneContact
            ->expects($this->once())
            ->method('getContact')
            ->will($this->returnValue($Phone));
        $PhoneContact
            ->expects($this->once())
            ->method('getContactType')
            ->will($this->returnValue(Contacts::TYPE_PHONE));

        $User = $this->getMockBuilder('\App\Entity\Users')
            ->disableOriginalConstructor()
            ->getMock();
        $User
            ->expects($this->once())
            ->method('getContacts')
            ->will($this->returnValue(new ArrayCollection([$EmailContact, $PhoneContact])));
        $User
            ->expects($this->once())
            ->method('getSingleMsg')
            ->will($this->returnValue(true));

        $Recipient = $this->getMockBuilder('\App\Entity\Recipients')
            ->disableOriginalConstructor()
            ->getMock();
        $Recipient
            ->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue($User));

        $Broadcast = $this->getMockBuilder('\App\Entity\Broadcasts')
            ->disableOriginalConstructor()
            ->getMock();
        $Broadcast->expects($this->once())
            ->method('getRecipients')
            ->will($this->returnValue(new ArrayCollection([$Recipient])));
        $Broadcast->expects($this->once())
            ->method('getAttachments')
            ->will($this->returnValue(new ArrayCollection()));
        $Broadcast->method('getShortMsg')->will($this->returnValue($ShortMsg));
        $Broadcast->method('getLongMsg')->will($this->returnValue($LongMsg));

        $Broadcasts = new ArrayCollection([$Broadcast]);

        $broadcastsRepo = $this->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $broadcastsRepo->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['delivered' => null, 'cancelled' => false]))
            ->will($this->returnValue($Broadcasts));

        $orgsRepo = $this->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgsRepo->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(['tag' => 'APPREVIEW']))
            ->will($this->returnValue(null));

        $emMap = [
            ['App:Broadcasts', $broadcastsRepo],
            ['App:Orgs', $orgsRepo],
        ];

        $em = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $em->method('getRepository')->will($this->returnValueMap($emMap));

        $MessageUtil = $this->getMockBuilder('\App\Util\MessageUtil')
            ->disableOriginalConstructor()
            ->getMock();
        $MessageUtil
            ->expects($this->once())
            ->method('SendEmail')
            ->with($this->equalTo([$Email]), $this->equalTo($LongMsg));
        $MessageUtil
            ->expects($this->never())
            ->method('SendSMS');

        $pushNotifications = $this->getMockBuilder('\App\Util\PushNotifications')
            ->disableOriginalConstructor()
            ->getMock();
        $pushNotifications->expects($this->never())->method('SendApplePushNotifications');

        $SendBroadcast = new SendBroadcast($em, $MessageUtil, $pushNotifications);
        $SendBroadcast->SendBroadcasts();
    }

    public function testSingleMsgShortOnly()
    {
        $Email = 'email@example.com';
        $Phone = '8165551212';
        $PhoneContactId = 5;
        $ShortMsg = 'Short Message';

        $EmailContact = $this->getMockBuilder('\App\Entity\Contacts')
            ->disableOriginalConstructor()
            ->getMock();
        $EmailContact
            ->expects($this->once())
            ->method('getContact')
            ->will($this->returnValue($Email));
        $EmailContact
            ->expects($this->once())
            ->method('getContactType')
            ->will($this->returnValue(Contacts::TYPE_EMAIL));

        $PhoneContact = $this->getMockBuilder('\App\Entity\Contacts')
            ->disableOriginalConstructor()
            ->getMock();
        $PhoneContact
            ->expects($this->once())
            ->method('getContact')
            ->will($this->returnValue($Phone));
        $PhoneContact
            ->expects($this->once())
            ->method('getContactType')
            ->will($this->returnValue(Contacts::TYPE_PHONE));
        $PhoneContact
            ->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($PhoneContactId));

        $User = $this->getMockBuilder('\App\Entity\Users')
            ->disableOriginalConstructor()
            ->getMock();
        $User
            ->expects($this->once())
            ->method('getContacts')
            ->will($this->returnValue(new ArrayCollection([$EmailContact, $PhoneContact])));
        $User
            ->expects($this->once())
            ->method('getSingleMsg')
            ->will($this->returnValue(true));

        $Recipient = $this->getMockBuilder('\App\Entity\Recipients')
            ->disableOriginalConstructor()
            ->getMock();
        $Recipient
            ->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue($User));

        $Broadcast = $this->getMockBuilder('\App\Entity\Broadcasts')
            ->disableOriginalConstructor()
            ->getMock();
        $Broadcast->expects($this->once())
            ->method('getRecipients')
            ->will($this->returnValue(new ArrayCollection([$Recipient])));
        $Broadcast->expects($this->once())
            ->method('getAttachments')
            ->will($this->returnValue(new ArrayCollection()));
        $Broadcast->method('getShortMsg')->will($this->returnValue($ShortMsg));

        $Broadcasts = new ArrayCollection([$Broadcast]);

        $broadcastsRepo = $this->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $broadcastsRepo->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['delivered' => null, 'cancelled' => false]))
            ->will($this->returnValue($Broadcasts));

        $orgsRepo = $this->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgsRepo->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(['tag' => 'APPREVIEW']))
            ->will($this->returnValue(null));

        $emMap = [
            ['App:Broadcasts', $broadcastsRepo],
            ['App:Orgs', $orgsRepo],
        ];

        $em = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $em->method('getRepository')->will($this->returnValueMap($emMap));

        $MessageUtil = $this->getMockBuilder('\App\Util\MessageUtil')
            ->disableOriginalConstructor()
            ->getMock();
        $MessageUtil
            ->expects($this->never())
            ->method('SendEmail');
        $MessageUtil
            ->expects($this->once())
            ->method('SendSMS')
            ->with($this->equalTo([
                [
                    'ContactId' => $PhoneContactId,
                    'Phone' => $Phone,
                ]
            ]), $this->equalTo($ShortMsg));

        $pushNotifications = $this->getMockBuilder('\App\Util\PushNotifications')
            ->disableOriginalConstructor()
            ->getMock();
        $pushNotifications->expects($this->never())->method('SendApplePushNotifications');

        $SendBroadcast = new SendBroadcast($em, $MessageUtil, $pushNotifications);
        $SendBroadcast->SendBroadcasts();
    }

    public function testShortAndLongMessageWithAttachment()
    {
        $Email = 'email@example.com';
        $Phone = '8165551212';
        $ContactId = 5;
        $ShortMsg = 'Short Message';
        $LongMsg = 'Long Message';
        $LocalName = 'Local Name';
        $FriendlyName = 'Friendly Name';
        $MimeType = 'Mime Type';

        $EmailContact = $this->getMockBuilder('\App\Entity\Contacts')
            ->disableOriginalConstructor()
            ->getMock();
        $EmailContact
            ->expects($this->once())
            ->method('getContact')
            ->will($this->returnValue($Email));
        $EmailContact
            ->expects($this->once())
            ->method('getContactType')
            ->will($this->returnValue(Contacts::TYPE_EMAIL));

        $PhoneContact = $this->getMockBuilder('\App\Entity\Contacts')
            ->disableOriginalConstructor()
            ->getMock();
        $PhoneContact
            ->expects($this->once())
            ->method('getContact')
            ->will($this->returnValue($Phone));
        $PhoneContact
            ->expects($this->once())
            ->method('getContactType')
            ->will($this->returnValue(Contacts::TYPE_PHONE));
        $PhoneContact
            ->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($ContactId));

        $User = $this->getMockBuilder('\App\Entity\Users')
            ->disableOriginalConstructor()
            ->getMock();
        $User
            ->expects($this->once())
            ->method('getContacts')
            ->will($this->returnValue(new ArrayCollection([$EmailContact, $PhoneContact])));
        $User
            ->expects($this->once())
            ->method('getSingleMsg')
            ->will($this->returnValue(false));

        $Recipient = $this->getMockBuilder('\App\Entity\Recipients')
            ->disableOriginalConstructor()
            ->getMock();
        $Recipient
            ->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue($User));

        $Attachment = $this->getMockBuilder('\App\Entity\Attachments')
            ->disableOriginalConstructor()
            ->getMock();
        $Attachment->method('getLocalName')->will($this->returnValue($LocalName));
        $Attachment->method('getFriendlyName')->will($this->returnValue($FriendlyName));
        $Attachment->method('getMimeType')->will($this->returnValue($MimeType));

        $Broadcast = $this->getMockBuilder('\App\Entity\Broadcasts')
            ->disableOriginalConstructor()
            ->getMock();
        $Broadcast->expects($this->once())
            ->method('getRecipients')
            ->will($this->returnValue(new ArrayCollection([$Recipient])));
        $Broadcast->expects($this->once())
            ->method('getAttachments')
            ->will($this->returnValue(new ArrayCollection([$Attachment])));
        $Broadcast->method('getShortMsg')->will($this->returnValue($ShortMsg));
        $Broadcast->method('getLongMsg')->will($this->returnValue($LongMsg));

        $Broadcasts = new ArrayCollection([$Broadcast]);

        $broadcastsRepo = $this->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $broadcastsRepo->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['delivered' => null, 'cancelled' => false]))
            ->will($this->returnValue($Broadcasts));

        $orgsRepo = $this->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgsRepo->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(['tag' => 'APPREVIEW']))
            ->will($this->returnValue(null));

        $emMap = [
            ['App:Broadcasts', $broadcastsRepo],
            ['App:Orgs', $orgsRepo],
        ];

        $em = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $em->method('getRepository')->will($this->returnValueMap($emMap));

        $MessageUtil = $this->getMockBuilder('\App\Util\MessageUtil')
            ->disableOriginalConstructor()
            ->getMock();
        $MessageUtil
            ->expects($this->once())
            ->method('SendEmail')
            ->with($this->equalTo([$Email]),
                $this->equalTo($LongMsg),
                $this->equalTo($LocalName),
                $this->equalTo($FriendlyName),
                $this->equalTo($MimeType)
            );
        $MessageUtil
            ->expects($this->once())
            ->method('SendSMS')
            ->with($this->equalTo([
                [
                    'ContactId' => $ContactId,
                    'Phone' => $Phone,
                ]
            ]));

        $pushNotifications = $this->getMockBuilder('\App\Util\PushNotifications')
            ->disableOriginalConstructor()
            ->getMock();
        $pushNotifications->expects($this->never())->method('SendApplePushNotifications');

        $SendBroadcast = new SendBroadcast($em, $MessageUtil, $pushNotifications);
        $SendBroadcast->SendBroadcasts();
    }

    public function testSingleMessageWithAttachment()
    {
        $Email = 'email@example.com';
        $Phone = '8165551212';
        $ShortMsg = 'Short Message';
        $LongMsg = 'Long Message';
        $LocalName = 'Local Name';
        $FriendlyName = 'Friendly Name';
        $MimeType = 'Mime Type';

        $EmailContact = $this->getMockBuilder('\App\Entity\Contacts')
            ->disableOriginalConstructor()
            ->getMock();
        $EmailContact
            ->expects($this->once())
            ->method('getContact')
            ->will($this->returnValue($Email));
        $EmailContact
            ->expects($this->once())
            ->method('getContactType')
            ->will($this->returnValue(Contacts::TYPE_EMAIL));

        $PhoneContact = $this->getMockBuilder('\App\Entity\Contacts')
            ->disableOriginalConstructor()
            ->getMock();
        $PhoneContact
            ->expects($this->once())
            ->method('getContact')
            ->will($this->returnValue($Phone));
        $PhoneContact
            ->expects($this->once())
            ->method('getContactType')
            ->will($this->returnValue(Contacts::TYPE_PHONE));

        $User = $this->getMockBuilder('\App\Entity\Users')
            ->disableOriginalConstructor()
            ->getMock();
        $User
            ->expects($this->once())
            ->method('getContacts')
            ->will($this->returnValue(new ArrayCollection([$EmailContact, $PhoneContact])));
        $User
            ->expects($this->once())
            ->method('getSingleMsg')
            ->will($this->returnValue(true));

        $Recipient = $this->getMockBuilder('\App\Entity\Recipients')
            ->disableOriginalConstructor()
            ->getMock();
        $Recipient
            ->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue($User));

        $Attachment = $this->getMockBuilder('\App\Entity\Attachments')
            ->disableOriginalConstructor()
            ->getMock();
        $Attachment->method('getLocalName')->will($this->returnValue($LocalName));
        $Attachment->method('getFriendlyName')->will($this->returnValue($FriendlyName));
        $Attachment->method('getMimeType')->will($this->returnValue($MimeType));

        $Broadcast = $this->getMockBuilder('\App\Entity\Broadcasts')
            ->disableOriginalConstructor()
            ->getMock();
        $Broadcast->expects($this->once())
            ->method('getRecipients')
            ->will($this->returnValue(new ArrayCollection([$Recipient])));
        $Broadcast->expects($this->once())
            ->method('getAttachments')
            ->will($this->returnValue(new ArrayCollection([$Attachment])));
        $Broadcast->method('getShortMsg')->will($this->returnValue($ShortMsg));
        $Broadcast->method('getLongMsg')->will($this->returnValue($LongMsg));

        $Broadcasts = new ArrayCollection([$Broadcast]);

        $broadcastsRepo = $this->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $broadcastsRepo->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['delivered' => null, 'cancelled' => false]))
            ->will($this->returnValue($Broadcasts));

        $orgsRepo = $this->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgsRepo->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(['tag' => 'APPREVIEW']))
            ->will($this->returnValue(null));

        $emMap = [
            ['App:Broadcasts', $broadcastsRepo],
            ['App:Orgs', $orgsRepo],
        ];

        $em = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $em->method('getRepository')->will($this->returnValueMap($emMap));

        $MessageUtil = $this->getMockBuilder('\App\Util\MessageUtil')
            ->disableOriginalConstructor()
            ->getMock();
        $MessageUtil
            ->expects($this->once())
            ->method('SendEmail')
            ->with($this->equalTo([$Email]),
                $this->equalTo($LongMsg),
                $this->equalTo($LocalName),
                $this->equalTo($FriendlyName),
                $this->equalTo($MimeType)
            );
        $MessageUtil
            ->expects($this->never())
            ->method('SendSMS');

        $pushNotifications = $this->getMockBuilder('\App\Util\PushNotifications')
            ->disableOriginalConstructor()
            ->getMock();
        $pushNotifications->expects($this->never())->method('SendApplePushNotifications');

        $SendBroadcast = new SendBroadcast($em, $MessageUtil, $pushNotifications);
        $SendBroadcast->SendBroadcasts();
    }

    public function testSingleShortMessageWithAttachment()
    {
        $Email = 'email@example.com';
        $Phone = '8165551212';
        $ShortMsg = 'Short Message';
        $LocalName = 'Local Name';
        $FriendlyName = 'Friendly Name';
        $MimeType = 'Mime Type';

        $EmailContact = $this->getMockBuilder('\App\Entity\Contacts')
            ->disableOriginalConstructor()
            ->getMock();
        $EmailContact
            ->expects($this->once())
            ->method('getContact')
            ->will($this->returnValue($Email));
        $EmailContact
            ->expects($this->once())
            ->method('getContactType')
            ->will($this->returnValue(Contacts::TYPE_EMAIL));

        $PhoneContact = $this->getMockBuilder('\App\Entity\Contacts')
            ->disableOriginalConstructor()
            ->getMock();
        $PhoneContact
            ->expects($this->once())
            ->method('getContact')
            ->will($this->returnValue($Phone));
        $PhoneContact
            ->expects($this->once())
            ->method('getContactType')
            ->will($this->returnValue(Contacts::TYPE_PHONE));

        $User = $this->getMockBuilder('\App\Entity\Users')
            ->disableOriginalConstructor()
            ->getMock();
        $User
            ->expects($this->once())
            ->method('getContacts')
            ->will($this->returnValue(new ArrayCollection([$EmailContact, $PhoneContact])));
        $User
            ->expects($this->once())
            ->method('getSingleMsg')
            ->will($this->returnValue(true));

        $Recipient = $this->getMockBuilder('\App\Entity\Recipients')
            ->disableOriginalConstructor()
            ->getMock();
        $Recipient
            ->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue($User));

        $Attachment = $this->getMockBuilder('\App\Entity\Attachments')
            ->disableOriginalConstructor()
            ->getMock();
        $Attachment->method('getLocalName')->will($this->returnValue($LocalName));
        $Attachment->method('getFriendlyName')->will($this->returnValue($FriendlyName));
        $Attachment->method('getMimeType')->will($this->returnValue($MimeType));

        $Broadcast = $this->getMockBuilder('\App\Entity\Broadcasts')
            ->disableOriginalConstructor()
            ->getMock();
        $Broadcast->expects($this->once())
            ->method('getRecipients')
            ->will($this->returnValue(new ArrayCollection([$Recipient])));
        $Broadcast->expects($this->once())
            ->method('getAttachments')
            ->will($this->returnValue(new ArrayCollection([$Attachment])));
        $Broadcast->method('getShortMsg')->will($this->returnValue($ShortMsg));

        $Broadcasts = new ArrayCollection([$Broadcast]);

        $broadcastsRepo = $this->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $broadcastsRepo->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['delivered' => null, 'cancelled' => false]))
            ->will($this->returnValue($Broadcasts));

        $orgsRepo = $this->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgsRepo->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(['tag' => 'APPREVIEW']))
            ->will($this->returnValue(null));

        $emMap = [
            ['App:Broadcasts', $broadcastsRepo],
            ['App:Orgs', $orgsRepo],
        ];

        $em = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $em->method('getRepository')->will($this->returnValueMap($emMap));

        $MessageUtil = $this->getMockBuilder('\App\Util\MessageUtil')
            ->disableOriginalConstructor()
            ->getMock();
        $MessageUtil
            ->expects($this->once())
            ->method('SendEmail')
            ->with($this->equalTo([$Email]),
                $this->equalTo($ShortMsg),
                $this->equalTo($LocalName),
                $this->equalTo($FriendlyName),
                $this->equalTo($MimeType)
            );
        $MessageUtil
            ->expects($this->never())
            ->method('SendSMS');

        $pushNotifications = $this->getMockBuilder('\App\Util\PushNotifications')
            ->disableOriginalConstructor()
            ->getMock();
        $pushNotifications->expects($this->never())->method('SendApplePushNotifications');

        $SendBroadcast = new SendBroadcast($em, $MessageUtil, $pushNotifications);
        $SendBroadcast->SendBroadcasts();
    }

    public function testUsersWithAppleDeviceTokens()
    {
        $BroadcastId = 10;
        $Recipients = [];
        $EmailRecips = [];
        $PhoneRecips = [];
        $AppleTokens = [];
        $ShortMsg = 'Short Message';

        for ($i = 1; $i < 5; $i++) {
            $Email = "email$i@example.com";
            $EmailContact = $this->getMockBuilder('\App\Entity\Contacts')
                ->disableOriginalConstructor()
                ->getMock();
            $EmailContact
                ->expects($this->once())
                ->method('getContact')
                ->will($this->returnValue($Email));
            $EmailContact
                ->expects($this->once())
                ->method('getContactType')
                ->will($this->returnValue(Contacts::TYPE_EMAIL));
            $EmailRecips[] = $Email;

            $Contacts = [$EmailContact];

            if (($i % 2) !== 0) {
                $Token = "a1b2c3d4e5f$i";
                $AppleContact = $this->getMockBuilder('\App\Entity\Contacts')
                    ->disableOriginalConstructor()
                    ->getMock();
                $AppleContact
                    ->expects($this->once())
                    ->method('getContact')
                    ->will($this->returnValue($Token));
                $AppleContact
                    ->expects($this->once())
                    ->method('getContactType')
                    ->will($this->returnValue(Contacts::TYPE_APPLE));
                $AppleContact
                    ->expects($this->once())
                    ->method('getId')
                    ->will($this->returnValue($i+5));
                $Contacts[] = $AppleContact;
                $AppleTokens[] = $Token;
            } else {
                $Phone = "816555121$i";
                $PhoneContact = $this->getMockBuilder('\App\Entity\Contacts')
                    ->disableOriginalConstructor()
                    ->getMock();
                $PhoneContact
                    ->expects($this->once())
                    ->method('getContact')
                    ->will($this->returnValue($Phone));
                $PhoneContact
                    ->expects($this->once())
                    ->method('getContactType')
                    ->will($this->returnValue(Contacts::TYPE_PHONE));
                $PhoneContact
                    ->expects($this->once())
                    ->method('getId')
                    ->will($this->returnValue($i+5));
                $PhoneRecips[] = [
                    'ContactId' => $i + 5,
                    'Phone' => $Phone,
                ];
                $Contacts[] = $PhoneContact;
            }

            $User = $this->getMockBuilder('\App\Entity\Users')
                ->disableOriginalConstructor()
                ->getMock();
            $User
                ->expects($this->once())
                ->method('getContacts')
                ->will($this->returnValue(new ArrayCollection($Contacts)));
            $User
                ->expects($this->once())
                ->method('getSingleMsg')
                ->will($this->returnValue(false));

            $Recipient = $this->getMockBuilder('\App\Entity\Recipients')
                ->disableOriginalConstructor()
                ->getMock();
            $Recipient
                ->expects($this->once())
                ->method('getUser')
                ->will($this->returnValue($User));
            $Recipients[] = $Recipient;
        }

        $Broadcast = $this->getMockBuilder('\App\Entity\Broadcasts')
            ->disableOriginalConstructor()
            ->getMock();
        $Broadcast->expects($this->once())
            ->method('getRecipients')
            ->will($this->returnValue(new ArrayCollection($Recipients)));
        $Broadcast->expects($this->once())
            ->method('getAttachments')
            ->will($this->returnValue(new ArrayCollection()));
        $Broadcast->method('getShortMsg')->will($this->returnValue($ShortMsg));
        $Broadcast->method('getId')->will($this->returnValue($BroadcastId));

        $Broadcasts = new ArrayCollection([$Broadcast]);

        $broadcastsRepo = $this->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $broadcastsRepo->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['delivered' => null, 'cancelled' => false]))
            ->will($this->returnValue($Broadcasts));

        $orgsRepo = $this->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgsRepo->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(['tag' => 'APPREVIEW']))
            ->will($this->returnValue(null));

        $emMap = [
            ['App:Broadcasts', $broadcastsRepo],
            ['App:Orgs', $orgsRepo],
        ];

        $em = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $em->method('getRepository')->will($this->returnValueMap($emMap));

        $MessageUtil = $this->getMockBuilder('\App\Util\MessageUtil')
            ->disableOriginalConstructor()
            ->getMock();
        $MessageUtil
            ->expects($this->once())
            ->method('SendEmail')
            ->with($this->equalTo($EmailRecips), $this->equalTo($ShortMsg));
        $MessageUtil
            ->expects($this->once())
            ->method('SendSMS')
            ->with($this->equalTo($PhoneRecips), $this->equalTo($ShortMsg));

        $pushNotifications = $this->getMockBuilder('\App\Util\PushNotifications')
            ->disableOriginalConstructor()
            ->getMock();
        $pushNotifications
            ->expects($this->once())
            ->method('SendApplePushNotifications')
            ->with($this->equalTo($AppleTokens), $this->equalTo($ShortMsg), $this->equalTo($BroadcastId));

        $SendBroadcast = new SendBroadcast($em, $MessageUtil, $pushNotifications);
        $SendBroadcast->SendBroadcasts();
    }

    public function testUsersWithMultipleAppleDeviceTokens()
    {
        $BroadcastId = 10;
        $Recipients = [];
        $EmailRecips = [];
        $PhoneRecips = [];
        $AppleTokens = [];
        $ShortMsg = 'Short Message';

        for ($i = 1; $i < 5; $i++) {
            $Email = "email$i@example.com";
            $EmailContact = $this->getMockBuilder('\App\Entity\Contacts')
                ->disableOriginalConstructor()
                ->getMock();
            $EmailContact
                ->expects($this->once())
                ->method('getContact')
                ->will($this->returnValue($Email));
            $EmailContact
                ->expects($this->once())
                ->method('getContactType')
                ->will($this->returnValue(Contacts::TYPE_EMAIL));
            $EmailRecips[] = $Email;

            $Contacts = [$EmailContact];

            if (($i % 2) !== 0) {
                $Token = "a1b2c3d4e5f$i";
                $AppleContact = $this->getMockBuilder('\App\Entity\Contacts')
                    ->disableOriginalConstructor()
                    ->getMock();
                $AppleContact
                    ->expects($this->once())
                    ->method('getContact')
                    ->will($this->returnValue($Token));
                $AppleContact
                    ->expects($this->once())
                    ->method('getContactType')
                    ->will($this->returnValue(Contacts::TYPE_APPLE));
                $AppleContact
                    ->expects($this->once())
                    ->method('getId')
                    ->will($this->returnValue($i+5));
                $Contacts[] = $AppleContact;
                $AppleTokens[] = $Token;
            } else {
                $Phone = "816555121$i";
                $PhoneContact = $this->getMockBuilder('\App\Entity\Contacts')
                    ->disableOriginalConstructor()
                    ->getMock();
                $PhoneContact
                    ->expects($this->once())
                    ->method('getContact')
                    ->will($this->returnValue($Phone));
                $PhoneContact
                    ->expects($this->once())
                    ->method('getContactType')
                    ->will($this->returnValue(Contacts::TYPE_PHONE));
                $PhoneContact
                    ->expects($this->once())
                    ->method('getId')
                    ->will($this->returnValue($i+5));
                $PhoneRecips[] = [
                    'ContactId' => $i + 5,
                    'Phone' => $Phone,
                ];
                $Contacts[] = $PhoneContact;
            }

            if ($i === 3) {
                $Token = "a1b2c3d4e5f2$i";
                $AppleContact = $this->getMockBuilder('\App\Entity\Contacts')
                    ->disableOriginalConstructor()
                    ->getMock();
                $AppleContact
                    ->expects($this->once())
                    ->method('getContact')
                    ->will($this->returnValue($Token));
                $AppleContact
                    ->expects($this->once())
                    ->method('getContactType')
                    ->will($this->returnValue(Contacts::TYPE_APPLE));
                $AppleContact
                    ->expects($this->once())
                    ->method('getId')
                    ->will($this->returnValue($i+5));
                $Contacts[] = $AppleContact;
                $AppleTokens[] = $Token;
            }

            $User = $this->getMockBuilder('\App\Entity\Users')
                ->disableOriginalConstructor()
                ->getMock();
            $User
                ->expects($this->once())
                ->method('getContacts')
                ->will($this->returnValue(new ArrayCollection($Contacts)));
            $User
                ->expects($this->once())
                ->method('getSingleMsg')
                ->will($this->returnValue(false));

            $Recipient = $this->getMockBuilder('\App\Entity\Recipients')
                ->disableOriginalConstructor()
                ->getMock();
            $Recipient
                ->expects($this->once())
                ->method('getUser')
                ->will($this->returnValue($User));
            $Recipients[] = $Recipient;
        }

        $Broadcast = $this->getMockBuilder('\App\Entity\Broadcasts')
            ->disableOriginalConstructor()
            ->getMock();
        $Broadcast->expects($this->once())
            ->method('getRecipients')
            ->will($this->returnValue(new ArrayCollection($Recipients)));
        $Broadcast->expects($this->once())
            ->method('getAttachments')
            ->will($this->returnValue(new ArrayCollection()));
        $Broadcast->method('getShortMsg')->will($this->returnValue($ShortMsg));
        $Broadcast->method('getId')->will($this->returnValue($BroadcastId));

        $Broadcasts = new ArrayCollection([$Broadcast]);

        $broadcastsRepo = $this->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $broadcastsRepo->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['delivered' => null, 'cancelled' => false]))
            ->will($this->returnValue($Broadcasts));

        $orgsRepo = $this->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgsRepo->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(['tag' => 'APPREVIEW']))
            ->will($this->returnValue(null));

        $emMap = [
            ['App:Broadcasts', $broadcastsRepo],
            ['App:Orgs', $orgsRepo],
        ];

        $em = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $em->method('getRepository')->will($this->returnValueMap($emMap));

        $MessageUtil = $this->getMockBuilder('\App\Util\MessageUtil')
            ->disableOriginalConstructor()
            ->getMock();
        $MessageUtil
            ->expects($this->once())
            ->method('SendEmail')
            ->with($this->equalTo($EmailRecips), $this->equalTo($ShortMsg));
        $MessageUtil
            ->expects($this->once())
            ->method('SendSMS')
            ->with($this->equalTo($PhoneRecips), $this->equalTo($ShortMsg));

        $pushNotifications = $this->getMockBuilder('\App\Util\PushNotifications')
            ->disableOriginalConstructor()
            ->getMock();
        $pushNotifications
            ->expects($this->once())
            ->method('SendApplePushNotifications')
            ->with($this->equalTo($AppleTokens), $this->equalTo($ShortMsg), $this->equalTo($BroadcastId));

        $SendBroadcast = new SendBroadcast($em, $MessageUtil, $pushNotifications);
        $SendBroadcast->SendBroadcasts();
    }
}
