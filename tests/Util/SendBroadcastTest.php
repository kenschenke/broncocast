<?php

namespace App\Tests\Util;

use App\Util\SendBroadcast;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SendBroadcastTest extends TestCase
{
    public function testNoBroadcasts()
    {
        $Broadcasts = new ArrayCollection();
        $repo = $this->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repo->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['isSent' => false]))
            ->will($this->returnValue($Broadcasts));

        $em = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $em->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('App:Broadcasts'))
            ->will($this->returnValue($repo));

        $MessageUtil = $this->getMockBuilder('\App\Util\MessageUtil')
            ->disableOriginalConstructor()
            ->getMock();
        $MessageUtil->expects($this->never())->method('SendEmail');
        $MessageUtil->expects($this->never())->method('SendSMS');

        $SendBroadcast = new SendBroadcast($em, $MessageUtil);
        $SendBroadcast->SendBroadcasts();
    }

    public function testTwoFutureBroadcasts()
    {
        $Broadcast1 = $this->getMockBuilder('\App\Entity\Broadcasts')
            ->disableOriginalConstructor()
            ->getMock();
        $Tomorrow = new \DateTime();
        $Tomorrow->add(new \DateInterval('P1D'));
        $Broadcast1->expects($this->once())
            ->method('getScheduled')
            ->will($this->returnValue($Tomorrow));

        $Broadcast2 = $this->getMockBuilder('\App\Entity\Broadcasts')
            ->disableOriginalConstructor()
            ->getMock();
        $LaterToday = new \DateTime();
        $LaterToday->add(new \DateInterval('PT3H'));
        $Broadcast2->expects($this->once())
            ->method('getScheduled')
            ->will($this->returnValue($LaterToday));

        $Broadcasts = new ArrayCollection([$Broadcast1, $Broadcast2]);

        $repo = $this->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repo->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['isSent' => false]))
            ->will($this->returnValue($Broadcasts));

        $em = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $em->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('App:Broadcasts'))
            ->will($this->returnValue($repo));

        $MessageUtil = $this->getMockBuilder('\App\Util\MessageUtil')
            ->disableOriginalConstructor()
            ->getMock();
        $MessageUtil->expects($this->never())->method('SendEmail');
        $MessageUtil->expects($this->never())->method('SendSMS');

        $SendBroadcast = new SendBroadcast($em, $MessageUtil);
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

        $repo = $this->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repo->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['isSent' => false]))
            ->will($this->returnValue($Broadcasts));

        $em = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $em->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('App:Broadcasts'))
            ->will($this->returnValue($repo));

        $MessageUtil = $this->getMockBuilder('\App\Util\MessageUtil')
            ->disableOriginalConstructor()
            ->getMock();
        $MessageUtil->expects($this->never())->method('SendEmail');
        $MessageUtil->expects($this->never())->method('SendSMS');

        $SendBroadcast = new SendBroadcast($em, $MessageUtil);
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

        $repo = $this->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repo->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['isSent' => false]))
            ->will($this->returnValue($Broadcasts));

        $em = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $em->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('App:Broadcasts'))
            ->will($this->returnValue($repo));

        $MessageUtil = $this->getMockBuilder('\App\Util\MessageUtil')
            ->disableOriginalConstructor()
            ->getMock();
        $MessageUtil->expects($this->never())->method('SendEmail');
        $MessageUtil->expects($this->never())->method('SendSMS');

        $SendBroadcast = new SendBroadcast($em, $MessageUtil);
        $SendBroadcast->SendBroadcasts();
    }

    public function testShortAndLongMessage()
    {
        $Recipients = [];
        $EmailMap = [];
        $EmailRecips = [];
        $PhoneMap = [];
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

            $EmailMap[] = [$Email, true];
            $EmailMap[] = [$Phone, false];
            $PhoneMap[] = [$Email, false];
            $PhoneMap[] = [$Phone, true];
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

        $repo = $this->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repo->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['isSent' => false]))
            ->will($this->returnValue($Broadcasts));

        $em = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $em->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('App:Broadcasts'))
            ->will($this->returnValue($repo));

        $MessageUtil = $this->getMockBuilder('\App\Util\MessageUtil')
            ->disableOriginalConstructor()
            ->getMock();
        $MessageUtil->method('IsEmail')->will($this->returnValueMap($EmailMap));
        $MessageUtil->method('IsPhone')->will($this->returnValueMap($PhoneMap));
        $MessageUtil
            ->expects($this->once())
            ->method('SendEmail')
            ->with($this->equalTo($EmailRecips), $this->equalTo($LongMsg));
        $MessageUtil
            ->expects($this->once())
            ->method('SendSMS')
            ->with($this->equalTo($PhoneRecips), $this->equalTo($ShortMsg));

        $SendBroadcast = new SendBroadcast($em, $MessageUtil);
        $SendBroadcast->SendBroadcasts();
    }

    public function testShortMessageOnly()
    {
        $Recipients = [];
        $EmailMap = [];
        $EmailRecips = [];
        $PhoneMap = [];
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

            $EmailMap[] = [$Email, true];
            $EmailMap[] = [$Phone, false];
            $PhoneMap[] = [$Email, false];
            $PhoneMap[] = [$Phone, true];
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

        $repo = $this->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repo->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['isSent' => false]))
            ->will($this->returnValue($Broadcasts));

        $em = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $em->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('App:Broadcasts'))
            ->will($this->returnValue($repo));

        $MessageUtil = $this->getMockBuilder('\App\Util\MessageUtil')
            ->disableOriginalConstructor()
            ->getMock();
        $MessageUtil->method('IsEmail')->will($this->returnValueMap($EmailMap));
        $MessageUtil->method('IsPhone')->will($this->returnValueMap($PhoneMap));
        $MessageUtil
            ->expects($this->once())
            ->method('SendEmail')
            ->with($this->equalTo($EmailRecips), $this->equalTo($ShortMsg));
        $MessageUtil
            ->expects($this->once())
            ->method('SendSMS')
            ->with($this->equalTo($PhoneRecips), $this->equalTo($ShortMsg));

        $SendBroadcast = new SendBroadcast($em, $MessageUtil);
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

        $PhoneContact = $this->getMockBuilder('\App\Entity\Contacts')
            ->disableOriginalConstructor()
            ->getMock();
        $PhoneContact
            ->expects($this->once())
            ->method('getContact')
            ->will($this->returnValue($Phone));

        $EmailMap = [
            [$Email, true],
            [$Phone, false],
        ];
        $PhoneMap = [
            [$Phone, true],
            [$Email, false],
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

        $repo = $this->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repo->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['isSent' => false]))
            ->will($this->returnValue($Broadcasts));

        $em = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $em->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('App:Broadcasts'))
            ->will($this->returnValue($repo));

        $MessageUtil = $this->getMockBuilder('\App\Util\MessageUtil')
            ->disableOriginalConstructor()
            ->getMock();
        $MessageUtil->method('IsEmail')->will($this->returnValueMap($EmailMap));
        $MessageUtil->method('IsPhone')->will($this->returnValueMap($PhoneMap));
        $MessageUtil
            ->expects($this->once())
            ->method('SendEmail')
            ->with($this->equalTo([$Email]), $this->equalTo($LongMsg));
        $MessageUtil
            ->expects($this->never())
            ->method('SendSMS');

        $SendBroadcast = new SendBroadcast($em, $MessageUtil);
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

        $PhoneContact = $this->getMockBuilder('\App\Entity\Contacts')
            ->disableOriginalConstructor()
            ->getMock();
        $PhoneContact
            ->expects($this->once())
            ->method('getContact')
            ->will($this->returnValue($Phone));
        $PhoneContact
            ->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($PhoneContactId));

        $EmailMap = [
            [$Email, true],
            [$Phone, false],
        ];
        $PhoneMap = [
            [$Phone, true],
            [$Email, false],
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

        $repo = $this->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repo->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['isSent' => false]))
            ->will($this->returnValue($Broadcasts));

        $em = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $em->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('App:Broadcasts'))
            ->will($this->returnValue($repo));

        $MessageUtil = $this->getMockBuilder('\App\Util\MessageUtil')
            ->disableOriginalConstructor()
            ->getMock();
        $MessageUtil->method('IsEmail')->will($this->returnValueMap($EmailMap));
        $MessageUtil->method('IsPhone')->will($this->returnValueMap($PhoneMap));
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

        $SendBroadcast = new SendBroadcast($em, $MessageUtil);
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

        $PhoneContact = $this->getMockBuilder('\App\Entity\Contacts')
            ->disableOriginalConstructor()
            ->getMock();
        $PhoneContact
            ->expects($this->once())
            ->method('getContact')
            ->will($this->returnValue($Phone));
        $PhoneContact
            ->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($ContactId));

        $EmailMap = [
            [$Email, true],
            [$Phone, false],
        ];
        $PhoneMap = [
            [$Phone, true],
            [$Email, false],
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

        $repo = $this->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repo->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['isSent' => false]))
            ->will($this->returnValue($Broadcasts));

        $em = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $em->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('App:Broadcasts'))
            ->will($this->returnValue($repo));

        $MessageUtil = $this->getMockBuilder('\App\Util\MessageUtil')
            ->disableOriginalConstructor()
            ->getMock();
        $MessageUtil->method('IsEmail')->will($this->returnValueMap($EmailMap));
        $MessageUtil->method('IsPhone')->will($this->returnValueMap($PhoneMap));
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

        $SendBroadcast = new SendBroadcast($em, $MessageUtil);
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

        $PhoneContact = $this->getMockBuilder('\App\Entity\Contacts')
            ->disableOriginalConstructor()
            ->getMock();
        $PhoneContact
            ->expects($this->once())
            ->method('getContact')
            ->will($this->returnValue($Phone));

        $EmailMap = [
            [$Email, true],
            [$Phone, false],
        ];
        $PhoneMap = [
            [$Phone, true],
            [$Email, false],
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

        $repo = $this->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repo->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['isSent' => false]))
            ->will($this->returnValue($Broadcasts));

        $em = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $em->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('App:Broadcasts'))
            ->will($this->returnValue($repo));

        $MessageUtil = $this->getMockBuilder('\App\Util\MessageUtil')
            ->disableOriginalConstructor()
            ->getMock();
        $MessageUtil->method('IsEmail')->will($this->returnValueMap($EmailMap));
        $MessageUtil->method('IsPhone')->will($this->returnValueMap($PhoneMap));
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

        $SendBroadcast = new SendBroadcast($em, $MessageUtil);
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

        $PhoneContact = $this->getMockBuilder('\App\Entity\Contacts')
            ->disableOriginalConstructor()
            ->getMock();
        $PhoneContact
            ->expects($this->once())
            ->method('getContact')
            ->will($this->returnValue($Phone));

        $EmailMap = [
            [$Email, true],
            [$Phone, false],
        ];
        $PhoneMap = [
            [$Phone, true],
            [$Email, false],
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

        $repo = $this->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repo->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['isSent' => false]))
            ->will($this->returnValue($Broadcasts));

        $em = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $em->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('App:Broadcasts'))
            ->will($this->returnValue($repo));

        $MessageUtil = $this->getMockBuilder('\App\Util\MessageUtil')
            ->disableOriginalConstructor()
            ->getMock();
        $MessageUtil->method('IsEmail')->will($this->returnValueMap($EmailMap));
        $MessageUtil->method('IsPhone')->will($this->returnValueMap($PhoneMap));
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

        $SendBroadcast = new SendBroadcast($em, $MessageUtil);
        $SendBroadcast->SendBroadcasts();
    }
}
