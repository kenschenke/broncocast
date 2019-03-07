<?php

namespace App\Tests\Model\AdminBroadcastsModel;

use App\Model\AdminBroadcastsModel;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class GetBroadcastsTest extends TestCase
{
    protected function setUpAdminChecker()
    {
        $adminChecker = $this->getMockBuilder('App\Util\AdminChecker')
            ->disableOriginalConstructor()
            ->getMock();

        return $adminChecker;
    }

    protected function setUpEntityManager()
    {
        $em = $this->getMockBuilder('Doctrine\ORM\EntityManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        return $em;
    }

    protected function setUpRequestStack()
    {
        $requestStack = $this->getMockBuilder('Symfony\Component\HttpFoundation\RequestStack')
            ->disableOriginalConstructor()
            ->getMock();

        return $requestStack;
    }

    protected function setUpTokenStorage()
    {
        $tokenStorage = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface')
            ->disableOriginalConstructor()
            ->getMock();

        return $tokenStorage;
    }

    protected function setUpUploadFile()
    {
        $uploadFile = $this->getMockBuilder('App\Util\UploadFile')
            ->disableOriginalConstructor()
            ->getMock();

        return $uploadFile;
    }

    protected function setUpUserUtil()
    {
        $userUtil = $this->getMockBuilder('App\Util\UserUtil')
            ->disableOriginalConstructor()
            ->getMock();

        return $userUtil;
    }

    public function testIsNonAdmin()
    {
        $OrgId = 5;

        $em = $this->setUpEntityManager();
        $adminChecker = $this->setUpAdminChecker();
        $uploadFile = $this->setUpUploadFile();
        $requestStack = $this->setUpRequestStack();
        $tokenStorage = $this->setUpTokenStorage();
        $userUtil = $this->setUpUserUtil();

        $adminChecker
            ->expects($this->once())
            ->method('IsAdminUser')
            ->with($this->equalTo($OrgId))
            ->will($this->returnValue(false));

        $model = new AdminBroadcastsModel($em, $adminChecker, $uploadFile, $requestStack, $tokenStorage, $userUtil);
        $response = $model->GetBroadcasts($OrgId);
        $this->assertEquals([
            'Success' => false,
            'Error' => 'Administrative privileges required',
        ], $response);
    }

    public function testInvalidOrgId()
    {
        $OrgId = 5;

        $em = $this->setUpEntityManager();
        $adminChecker = $this->setUpAdminChecker();
        $uploadFile = $this->setUpUploadFile();
        $requestStack = $this->setUpRequestStack();
        $tokenStorage = $this->setUpTokenStorage();
        $userUtil = $this->setUpUserUtil();

        $adminChecker
            ->expects($this->once())
            ->method('IsAdminUser')
            ->with($this->equalTo($OrgId))
            ->will($this->returnValue(true));

        $repo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repo
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($OrgId))
            ->will($this->returnValue(null));
        $em
            ->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('App:Orgs'))
            ->will($this->returnValue($repo));

        $model = new AdminBroadcastsModel($em, $adminChecker, $uploadFile, $requestStack, $tokenStorage, $userUtil);
        $response = $model->GetBroadcasts($OrgId);
        $this->assertEquals([
            'Success' => false,
            'Error' => 'Organization record not found',
        ], $response);
    }

    public function testMultipleBroadcasts()
    {
        $OrgId = 15;

        $em = $this->setUpEntityManager();
        $adminChecker = $this->setUpAdminChecker();
        $uploadFile = $this->setUpUploadFile();
        $requestStack = $this->setUpRequestStack();
        $tokenStorage = $this->setUpTokenStorage();
        $userUtil = $this->setUpUserUtil();

        $adminChecker
            ->expects($this->once())
            ->method('IsAdminUser')
            ->with($this->equalTo($OrgId))
            ->will($this->returnValue(true));

        $org = $this->getMockBuilder('App\Entity\Orgs')
            ->disableOriginalConstructor()
            ->getMock();
        $orgRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgRepo
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($OrgId))
            ->will($this->returnValue($org));

        $Broadcasts = [];
        $Recs = [];
        for ($i = 1; $i <= 5; $i++) {
            $shortMsg = "Short Message $i";
            $longMsg = "Long Message $i";
            $timestamp = $i * 10;
            $usrName = "User Name $i";
            $recipNames = ["ZZZ User $i", "CCC User $i", "AAA User $i"];

            $broadcast = $this->getMockBuilder('\App\Entity\Broadcasts')
                ->disableOriginalConstructor()
                ->getMock();

            $created = $this->getMockBuilder('\DateTime')
                ->disableOriginalConstructor()
                ->getMock();
            $formattedTime = 'Time Format';
            $created
                ->expects($this->once())
                ->method('getTimestamp')
                ->will($this->returnValue($timestamp));
            $created
                ->expects($this->once())
                ->method('format')
                ->with($this->equalTo('D M j, Y g:i a'))
                ->will($this->returnValue($formattedTime));

            $recipients = [];
            foreach ($recipNames as $recipName) {
                $user = $this->getMockBuilder('App\Entity\Users')
                    ->disableOriginalConstructor()
                    ->getMock();
                $user->method('getFullname')->will($this->returnValue($recipName));
                $recipient = $this->getMockBuilder('App\Entity\Recipients')
                    ->disableOriginalConstructor()
                    ->getMock();
                $recipient->method('getUser')->will($this->returnValue($user));
                $recipients[] = $recipient;
            }

            $broadcast->method('getId')->will($this->returnValue($i));
            $broadcast->method('getShortMsg')->will($this->returnValue($shortMsg));
            $broadcast->method('getLongMsg')->will($this->returnValue($longMsg));
            $broadcast->method('getScheduled')->will($this->returnValue(null));
            $broadcast->method('getDelivered')->will($this->returnValue(null));
            $broadcast->method('getCreated')->will($this->returnValue($created));
            $broadcast->method('getCancelled')->will($this->returnValue(false));
            $broadcast->method('getUsrName')->will($this->returnValue($usrName));
            $broadcast->method('getAttachments')->will($this->returnValue(new ArrayCollection()));
            $broadcast->method('getRecipients')->will($this->returnValue(new ArrayCollection($recipients)));

            $Recs[] = $broadcast;

            $Broadcasts[] = [
                'BroadcastId' => $i,
                'ShortMsg' => $shortMsg,
                'LongMsg' => $longMsg,
                'Time' => $formattedTime,
                'Timestamp' => $timestamp,
                'IsDelivered' => false,
                'IsCancelled' => false,
                'UsrName' => $usrName,
                'AttachmentUrl' => '',
                'Recipients' => [$recipNames[2], $recipNames[1], $recipNames[0]],
            ];
        }

        $broadcastRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $broadcastRepo
            ->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['orgId' => $OrgId ]))
            ->will($this->returnValue(new ArrayCollection($Recs)));

        $orgMembersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgMembersRepo->method('findOneBy')->will($this->returnValue(null));

        $repoMap = [
            ['App:Orgs', $orgRepo],
            ['App:Broadcasts', $broadcastRepo],
            ['App:OrgMembers', $orgMembersRepo],
        ];

        $em->method('getRepository')->will($this->returnValueMap($repoMap));

        $model = new AdminBroadcastsModel($em, $adminChecker, $uploadFile, $requestStack, $tokenStorage, $userUtil);
        $response = $model->GetBroadcasts($OrgId);
        $this->assertEquals([
            'Success' => true,
            'Broadcasts' => $Broadcasts,
        ], $response);
    }

    public function testNoLongMsg()
    {
        $broadcastId = 5;
        $orgId = 15;
        $shortMsg = "Short Message";
        $timestamp = 50;
        $usrName = "User Name";
        $recipNames = ["ZZZ User", "CCC User", "AAA User"];

        $em = $this->setUpEntityManager();
        $adminChecker = $this->setUpAdminChecker();
        $uploadFile = $this->setUpUploadFile();
        $requestStack = $this->setUpRequestStack();
        $tokenStorage = $this->setUpTokenStorage();
        $userUtil = $this->setUpUserUtil();

        $adminChecker
            ->expects($this->once())
            ->method('IsAdminUser')
            ->with($this->equalTo($orgId))
            ->will($this->returnValue(true));

        $org = $this->getMockBuilder('App\Entity\Orgs')
            ->disableOriginalConstructor()
            ->getMock();
        $orgRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgRepo
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($orgId))
            ->will($this->returnValue($org));

        $broadcast = $this->getMockBuilder('\App\Entity\Broadcasts')
            ->disableOriginalConstructor()
            ->getMock();

        $created = $this->getMockBuilder('\DateTime')
            ->disableOriginalConstructor()
            ->getMock();
        $formattedTime = 'Time Format';
        $created
            ->expects($this->once())
            ->method('getTimestamp')
            ->will($this->returnValue($timestamp));
        $created
            ->expects($this->once())
            ->method('format')
            ->with($this->equalTo('D M j, Y g:i a'))
            ->will($this->returnValue($formattedTime));

        $recipients = [];
        foreach ($recipNames as $recipName) {
            $user = $this->getMockBuilder('App\Entity\Users')
                ->disableOriginalConstructor()
                ->getMock();
            $user->method('getFullname')->will($this->returnValue($recipName));
            $recipient = $this->getMockBuilder('App\Entity\Recipients')
                ->disableOriginalConstructor()
                ->getMock();
            $recipient->method('getUser')->will($this->returnValue($user));
            $recipients[] = $recipient;
        }

        $broadcast->method('getId')->will($this->returnValue($broadcastId));
        $broadcast->method('getShortMsg')->will($this->returnValue($shortMsg));
        $broadcast->method('getLongMsg')->will($this->returnValue(null));
        $broadcast->method('getScheduled')->will($this->returnValue(null));
        $broadcast->method('getDelivered')->will($this->returnValue(null));
        $broadcast->method('getCreated')->will($this->returnValue($created));
        $broadcast->method('getCancelled')->will($this->returnValue(false));
        $broadcast->method('getUsrName')->will($this->returnValue($usrName));
        $broadcast->method('getAttachments')->will($this->returnValue(new ArrayCollection()));
        $broadcast->method('getRecipients')->will($this->returnValue(new ArrayCollection($recipients)));

        $Broadcasts = [
            [
                'BroadcastId' => $broadcastId,
                'ShortMsg' => $shortMsg,
                'LongMsg' => '',
                'Time' => $formattedTime,
                'Timestamp' => $timestamp,
                'IsDelivered' => false,
                'IsCancelled' => false,
                'UsrName' => $usrName,
                'AttachmentUrl' => '',
                'Recipients' => [$recipNames[2], $recipNames[1], $recipNames[0]],
            ]
        ];

        $broadcastRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $broadcastRepo
            ->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['orgId' => $orgId ]))
            ->will($this->returnValue(new ArrayCollection([$broadcast])));

        $orgMembersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgMembersRepo->method('findOneBy')->will($this->returnValue(null));

        $repoMap = [
            ['App:Orgs', $orgRepo],
            ['App:Broadcasts', $broadcastRepo],
            ['App:OrgMembers', $orgMembersRepo],
        ];

        $em->method('getRepository')->will($this->returnValueMap($repoMap));

        $model = new AdminBroadcastsModel($em, $adminChecker, $uploadFile, $requestStack, $tokenStorage, $userUtil);
        $response = $model->GetBroadcasts($orgId);
        $this->assertEquals([
            'Success' => true,
            'Broadcasts' => $Broadcasts,
        ], $response);
    }

    public function testScheduledBroadcast()
    {
        $broadcastId = 5;
        $orgId = 15;
        $shortMsg = "Short Message";
        $timestamp = 50;
        $usrName = "User Name";
        $recipNames = ["ZZZ User", "CCC User", "AAA User"];

        $em = $this->setUpEntityManager();
        $adminChecker = $this->setUpAdminChecker();
        $uploadFile = $this->setUpUploadFile();
        $requestStack = $this->setUpRequestStack();
        $tokenStorage = $this->setUpTokenStorage();
        $userUtil = $this->setUpUserUtil();

        $adminChecker
            ->expects($this->once())
            ->method('IsAdminUser')
            ->with($this->equalTo($orgId))
            ->will($this->returnValue(true));

        $org = $this->getMockBuilder('App\Entity\Orgs')
            ->disableOriginalConstructor()
            ->getMock();
        $orgRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgRepo
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($orgId))
            ->will($this->returnValue($org));

        $broadcast = $this->getMockBuilder('\App\Entity\Broadcasts')
            ->disableOriginalConstructor()
            ->getMock();

        $created = $this->getMockBuilder('\DateTime')
            ->disableOriginalConstructor()
            ->getMock();
        $created->expects($this->never())->method('getTimestamp');
        $created->expects($this->never())->method('format');

        $scheduled = $this->getMockBuilder('\DateTime')
            ->disableOriginalConstructor()
            ->getMock();
        $formattedTime = 'Time Format';
        $offset = 500;
        $scheduled
            ->expects($this->exactly(2))
            ->method('getTimestamp')
            ->will($this->returnValue($timestamp));
        $scheduled
            ->expects($this->once())
            ->method('format')
            ->with($this->equalTo('D M j, Y g:i a'))
            ->will($this->returnValue($formattedTime));
        $scheduled
            ->expects($this->once())
            ->method('getOffset')
            ->will($this->returnValue($offset));
        $scheduled
            ->expects($this->once())
            ->method('setTimestamp')
            ->with($this->equalTo($timestamp + $offset));

        $recipients = [];
        foreach ($recipNames as $recipName) {
            $user = $this->getMockBuilder('App\Entity\Users')
                ->disableOriginalConstructor()
                ->getMock();
            $user->method('getFullname')->will($this->returnValue($recipName));
            $recipient = $this->getMockBuilder('App\Entity\Recipients')
                ->disableOriginalConstructor()
                ->getMock();
            $recipient->method('getUser')->will($this->returnValue($user));
            $recipients[] = $recipient;
        }

        $broadcast->method('getId')->will($this->returnValue($broadcastId));
        $broadcast->method('getShortMsg')->will($this->returnValue($shortMsg));
        $broadcast->method('getLongMsg')->will($this->returnValue(null));
        $broadcast->method('getScheduled')->will($this->returnValue($scheduled));
        $broadcast->method('getDelivered')->will($this->returnValue(null));
        $broadcast->method('getCreated')->will($this->returnValue($created));
        $broadcast->method('getCancelled')->will($this->returnValue(false));
        $broadcast->method('getUsrName')->will($this->returnValue($usrName));
        $broadcast->method('getAttachments')->will($this->returnValue(new ArrayCollection()));
        $broadcast->method('getRecipients')->will($this->returnValue(new ArrayCollection($recipients)));

        $Broadcasts = [
            [
                'BroadcastId' => $broadcastId,
                'ShortMsg' => $shortMsg,
                'LongMsg' => '',
                'Time' => $formattedTime,
                'Timestamp' => $timestamp,
                'IsDelivered' => false,
                'IsCancelled' => false,
                'UsrName' => $usrName,
                'AttachmentUrl' => '',
                'Recipients' => [$recipNames[2], $recipNames[1], $recipNames[0]],
            ]
        ];

        $broadcastRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $broadcastRepo
            ->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['orgId' => $orgId ]))
            ->will($this->returnValue(new ArrayCollection([$broadcast])));

        $orgMembersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgMembersRepo->method('findOneBy')->will($this->returnValue(null));

        $repoMap = [
            ['App:Orgs', $orgRepo],
            ['App:Broadcasts', $broadcastRepo],
            ['App:OrgMembers', $orgMembersRepo],
        ];

        $em->method('getRepository')->will($this->returnValueMap($repoMap));

        $model = new AdminBroadcastsModel($em, $adminChecker, $uploadFile, $requestStack, $tokenStorage, $userUtil);
        $response = $model->GetBroadcasts($orgId);
        $this->assertEquals([
            'Success' => true,
            'Broadcasts' => $Broadcasts,
        ], $response);
    }

    public function testDeliveredBroadcast()
    {
        $broadcastId = 5;
        $orgId = 15;
        $shortMsg = "Short Message";
        $timestamp = 50;
        $usrName = "User Name";
        $recipNames = ["ZZZ User", "CCC User", "AAA User"];

        $em = $this->setUpEntityManager();
        $adminChecker = $this->setUpAdminChecker();
        $uploadFile = $this->setUpUploadFile();
        $requestStack = $this->setUpRequestStack();
        $tokenStorage = $this->setUpTokenStorage();
        $userUtil = $this->setUpUserUtil();

        $adminChecker
            ->expects($this->once())
            ->method('IsAdminUser')
            ->with($this->equalTo($orgId))
            ->will($this->returnValue(true));

        $org = $this->getMockBuilder('App\Entity\Orgs')
            ->disableOriginalConstructor()
            ->getMock();
        $orgRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgRepo
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($orgId))
            ->will($this->returnValue($org));

        $broadcast = $this->getMockBuilder('\App\Entity\Broadcasts')
            ->disableOriginalConstructor()
            ->getMock();

        $created = $this->getMockBuilder('\DateTime')
            ->disableOriginalConstructor()
            ->getMock();
        $created->expects($this->never())->method('getTimestamp');
        $created->expects($this->never())->method('format');

        $scheduled = $this->getMockBuilder('\DateTime')
            ->disableOriginalConstructor()
            ->getMock();
        $offset = 500;
        $scheduled
            ->expects($this->once())
            ->method('getTimestamp')
            ->will($this->returnValue($timestamp));
        $scheduled->expects($this->never())->method('format');
        $scheduled
            ->expects($this->once())
            ->method('getOffset')
            ->will($this->returnValue($offset));
        $scheduled->expects($this->once())->method('setTimestamp');

        $delivered = $this->getMockBuilder('\DateTime')
            ->disableOriginalConstructor()
            ->getMock();
        $formattedTime = 'Time Format';
        $delivered
            ->expects($this->once())
            ->method('getTimestamp')
            ->will($this->returnValue($timestamp));
        $delivered
            ->expects($this->once())
            ->method('format')
            ->with($this->equalTo('D M j, Y g:i a'))
            ->will($this->returnValue($formattedTime));

        $recipients = [];
        foreach ($recipNames as $recipName) {
            $user = $this->getMockBuilder('App\Entity\Users')
                ->disableOriginalConstructor()
                ->getMock();
            $user->method('getFullname')->will($this->returnValue($recipName));
            $recipient = $this->getMockBuilder('App\Entity\Recipients')
                ->disableOriginalConstructor()
                ->getMock();
            $recipient->method('getUser')->will($this->returnValue($user));
            $recipients[] = $recipient;
        }

        $broadcast->method('getId')->will($this->returnValue($broadcastId));
        $broadcast->method('getShortMsg')->will($this->returnValue($shortMsg));
        $broadcast->method('getLongMsg')->will($this->returnValue(null));
        $broadcast->method('getScheduled')->will($this->returnValue($scheduled));
        $broadcast->method('getDelivered')->will($this->returnValue($delivered));
        $broadcast->method('getCreated')->will($this->returnValue($created));
        $broadcast->method('getCancelled')->will($this->returnValue(false));
        $broadcast->method('getUsrName')->will($this->returnValue($usrName));
        $broadcast->method('getAttachments')->will($this->returnValue(new ArrayCollection()));
        $broadcast->method('getRecipients')->will($this->returnValue(new ArrayCollection($recipients)));

        $Broadcasts = [
            [
                'BroadcastId' => $broadcastId,
                'ShortMsg' => $shortMsg,
                'LongMsg' => '',
                'Time' => $formattedTime,
                'Timestamp' => $timestamp,
                'IsDelivered' => true,
                'IsCancelled' => false,
                'UsrName' => $usrName,
                'AttachmentUrl' => '',
                'Recipients' => [$recipNames[2], $recipNames[1], $recipNames[0]],
            ]
        ];

        $broadcastRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $broadcastRepo
            ->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['orgId' => $orgId ]))
            ->will($this->returnValue(new ArrayCollection([$broadcast])));

        $orgMembersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgMembersRepo->method('findOneBy')->will($this->returnValue(null));

        $repoMap = [
            ['App:Orgs', $orgRepo],
            ['App:Broadcasts', $broadcastRepo],
            ['App:OrgMembers', $orgMembersRepo],
        ];

        $em->method('getRepository')->will($this->returnValueMap($repoMap));

        $model = new AdminBroadcastsModel($em, $adminChecker, $uploadFile, $requestStack, $tokenStorage, $userUtil);
        $response = $model->GetBroadcasts($orgId);
        $this->assertEquals([
            'Success' => true,
            'Broadcasts' => $Broadcasts,
        ], $response);
    }

    public function testAttachment()
    {
        $broadcastId = 5;
        $orgId = 15;
        $attachmentId = 25;
        $shortMsg = "Short Message";
        $timestamp = 50;
        $usrName = "User Name";
        $recipNames = ["ZZZ User", "CCC User", "AAA User"];
        $attachmentUrl = "/api/broadcasts/attachments/{$attachmentId}";

        $em = $this->setUpEntityManager();
        $adminChecker = $this->setUpAdminChecker();
        $uploadFile = $this->setUpUploadFile();
        $requestStack = $this->setUpRequestStack();
        $tokenStorage = $this->setUpTokenStorage();
        $userUtil = $this->setUpUserUtil();

        $adminChecker
            ->expects($this->once())
            ->method('IsAdminUser')
            ->with($this->equalTo($orgId))
            ->will($this->returnValue(true));

        $org = $this->getMockBuilder('App\Entity\Orgs')
            ->disableOriginalConstructor()
            ->getMock();
        $orgRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgRepo
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($orgId))
            ->will($this->returnValue($org));

        $broadcast = $this->getMockBuilder('\App\Entity\Broadcasts')
            ->disableOriginalConstructor()
            ->getMock();

        $created = $this->getMockBuilder('\DateTime')
            ->disableOriginalConstructor()
            ->getMock();
        $formattedTime = 'Time Format';
        $created
            ->expects($this->once())
            ->method('getTimestamp')
            ->will($this->returnValue($timestamp));
        $created
            ->expects($this->once())
            ->method('format')
            ->with($this->equalTo('D M j, Y g:i a'))
            ->will($this->returnValue($formattedTime));

        $recipients = [];
        foreach ($recipNames as $recipName) {
            $user = $this->getMockBuilder('App\Entity\Users')
                ->disableOriginalConstructor()
                ->getMock();
            $user->method('getFullname')->will($this->returnValue($recipName));
            $recipient = $this->getMockBuilder('App\Entity\Recipients')
                ->disableOriginalConstructor()
                ->getMock();
            $recipient->method('getUser')->will($this->returnValue($user));
            $recipients[] = $recipient;
        }

        $attachment1 = $this->getMockBuilder('App\Entity\Attachments')
            ->disableOriginalConstructor()
            ->getMock();
        $attachment1
            ->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($attachmentId));
        $attachment2 = $this->getMockBuilder('App\Entity\Attachments')
            ->disableOriginalConstructor()
            ->getMock();
        $attachment2->expects($this->never())->method('getId');
        $attachments = [$attachment1, $attachment2];

        $broadcast->method('getId')->will($this->returnValue($broadcastId));
        $broadcast->method('getShortMsg')->will($this->returnValue($shortMsg));
        $broadcast->method('getLongMsg')->will($this->returnValue(null));
        $broadcast->method('getScheduled')->will($this->returnValue(null));
        $broadcast->method('getDelivered')->will($this->returnValue(null));
        $broadcast->method('getCreated')->will($this->returnValue($created));
        $broadcast->method('getCancelled')->will($this->returnValue(false));
        $broadcast->method('getUsrName')->will($this->returnValue($usrName));
        $broadcast->method('getAttachments')->will($this->returnValue(new ArrayCollection($attachments)));
        $broadcast->method('getRecipients')->will($this->returnValue(new ArrayCollection($recipients)));

        $Broadcasts = [
            [
                'BroadcastId' => $broadcastId,
                'ShortMsg' => $shortMsg,
                'LongMsg' => '',
                'Time' => $formattedTime,
                'Timestamp' => $timestamp,
                'IsDelivered' => false,
                'IsCancelled' => false,
                'UsrName' => $usrName,
                'AttachmentUrl' => $attachmentUrl,
                'Recipients' => [$recipNames[2], $recipNames[1], $recipNames[0]],
            ]
        ];

        $broadcastRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $broadcastRepo
            ->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['orgId' => $orgId ]))
            ->will($this->returnValue(new ArrayCollection([$broadcast])));

        $orgMembersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgMembersRepo->method('findOneBy')->will($this->returnValue(null));

        $repoMap = [
            ['App:Orgs', $orgRepo],
            ['App:Broadcasts', $broadcastRepo],
            ['App:OrgMembers', $orgMembersRepo],
        ];

        $em->method('getRepository')->will($this->returnValueMap($repoMap));

        $model = new AdminBroadcastsModel($em, $adminChecker, $uploadFile, $requestStack, $tokenStorage, $userUtil);
        $response = $model->GetBroadcasts($orgId);
        $this->assertEquals([
            'Success' => true,
            'Broadcasts' => $Broadcasts,
        ], $response);
    }

    public function testUserWithNullAltName()
    {
        $broadcastId = 5;
        $orgId = 15;
        $userId = 20;
        $shortMsg = "Short Message";
        $timestamp = 50;
        $usrName = "User Name";
        $fullname = "Full Name";

        $em = $this->setUpEntityManager();
        $adminChecker = $this->setUpAdminChecker();
        $uploadFile = $this->setUpUploadFile();
        $requestStack = $this->setUpRequestStack();
        $tokenStorage = $this->setUpTokenStorage();
        $userUtil = $this->setUpUserUtil();

        $adminChecker
            ->expects($this->once())
            ->method('IsAdminUser')
            ->with($this->equalTo($orgId))
            ->will($this->returnValue(true));

        $org = $this->getMockBuilder('App\Entity\Orgs')
            ->disableOriginalConstructor()
            ->getMock();
        $orgRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgRepo
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($orgId))
            ->will($this->returnValue($org));

        $broadcast = $this->getMockBuilder('\App\Entity\Broadcasts')
            ->disableOriginalConstructor()
            ->getMock();

        $created = $this->getMockBuilder('\DateTime')
            ->disableOriginalConstructor()
            ->getMock();
        $formattedTime = 'Time Format';
        $created
            ->expects($this->once())
            ->method('getTimestamp')
            ->will($this->returnValue($timestamp));
        $created
            ->expects($this->once())
            ->method('format')
            ->with($this->equalTo('D M j, Y g:i a'))
            ->will($this->returnValue($formattedTime));

        $orgMember = $this->getMockBuilder('App\Entity\OrgMembers')
            ->disableOriginalConstructor()
            ->getMock();
        $orgMember
            ->expects($this->once())
            ->method('getAltUsrName')
            ->will($this->returnValue(null));

        $user = $this->getMockBuilder('App\Entity\Users')
            ->disableOriginalConstructor()
            ->getMock();
        $user->method('getFullname')->will($this->returnValue($fullname));
        $user->method('getId')->will($this->returnValue($userId));
        $recipient = $this->getMockBuilder('App\Entity\Recipients')
            ->disableOriginalConstructor()
            ->getMock();
        $recipient->method('getUser')->will($this->returnValue($user));

        $broadcast->method('getId')->will($this->returnValue($broadcastId));
        $broadcast->method('getShortMsg')->will($this->returnValue($shortMsg));
        $broadcast->method('getLongMsg')->will($this->returnValue(null));
        $broadcast->method('getScheduled')->will($this->returnValue(null));
        $broadcast->method('getDelivered')->will($this->returnValue(null));
        $broadcast->method('getCreated')->will($this->returnValue($created));
        $broadcast->method('getCancelled')->will($this->returnValue(false));
        $broadcast->method('getUsrName')->will($this->returnValue($usrName));
        $broadcast->method('getAttachments')->will($this->returnValue(new ArrayCollection()));
        $broadcast->method('getRecipients')->will($this->returnValue(new ArrayCollection([$recipient])));

        $Broadcasts = [
            [
                'BroadcastId' => $broadcastId,
                'ShortMsg' => $shortMsg,
                'LongMsg' => '',
                'Time' => $formattedTime,
                'Timestamp' => $timestamp,
                'IsDelivered' => false,
                'IsCancelled' => false,
                'UsrName' => $usrName,
                'AttachmentUrl' => '',
                'Recipients' => [$fullname],
            ]
        ];

        $broadcastRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $broadcastRepo
            ->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['orgId' => $orgId ]))
            ->will($this->returnValue(new ArrayCollection([$broadcast])));

        $orgMembersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgMembersRepo
            ->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(['orgId' => $orgId, 'userId' => $userId]))
            ->will($this->returnValue($orgMember));

        $repoMap = [
            ['App:Orgs', $orgRepo],
            ['App:Broadcasts', $broadcastRepo],
            ['App:OrgMembers', $orgMembersRepo],
        ];

        $em->method('getRepository')->will($this->returnValueMap($repoMap));

        $model = new AdminBroadcastsModel($em, $adminChecker, $uploadFile, $requestStack, $tokenStorage, $userUtil);
        $response = $model->GetBroadcasts($orgId);
        $this->assertEquals([
            'Success' => true,
            'Broadcasts' => $Broadcasts,
        ], $response);
    }

    public function testUserWithEmptyAltName()
    {
        $broadcastId = 5;
        $orgId = 15;
        $userId = 20;
        $shortMsg = "Short Message";
        $timestamp = 50;
        $usrName = "User Name";
        $fullname = "Full Name";

        $em = $this->setUpEntityManager();
        $adminChecker = $this->setUpAdminChecker();
        $uploadFile = $this->setUpUploadFile();
        $requestStack = $this->setUpRequestStack();
        $tokenStorage = $this->setUpTokenStorage();
        $userUtil = $this->setUpUserUtil();

        $adminChecker
            ->expects($this->once())
            ->method('IsAdminUser')
            ->with($this->equalTo($orgId))
            ->will($this->returnValue(true));

        $org = $this->getMockBuilder('App\Entity\Orgs')
            ->disableOriginalConstructor()
            ->getMock();
        $orgRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgRepo
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($orgId))
            ->will($this->returnValue($org));

        $broadcast = $this->getMockBuilder('\App\Entity\Broadcasts')
            ->disableOriginalConstructor()
            ->getMock();

        $created = $this->getMockBuilder('\DateTime')
            ->disableOriginalConstructor()
            ->getMock();
        $formattedTime = 'Time Format';
        $created
            ->expects($this->once())
            ->method('getTimestamp')
            ->will($this->returnValue($timestamp));
        $created
            ->expects($this->once())
            ->method('format')
            ->with($this->equalTo('D M j, Y g:i a'))
            ->will($this->returnValue($formattedTime));

        $orgMember = $this->getMockBuilder('App\Entity\OrgMembers')
            ->disableOriginalConstructor()
            ->getMock();
        $orgMember
            ->expects($this->once())
            ->method('getAltUsrName')
            ->will($this->returnValue(''));

        $user = $this->getMockBuilder('App\Entity\Users')
            ->disableOriginalConstructor()
            ->getMock();
        $user->method('getFullname')->will($this->returnValue($fullname));
        $user->method('getId')->will($this->returnValue($userId));
        $recipient = $this->getMockBuilder('App\Entity\Recipients')
            ->disableOriginalConstructor()
            ->getMock();
        $recipient->method('getUser')->will($this->returnValue($user));

        $broadcast->method('getId')->will($this->returnValue($broadcastId));
        $broadcast->method('getShortMsg')->will($this->returnValue($shortMsg));
        $broadcast->method('getLongMsg')->will($this->returnValue(null));
        $broadcast->method('getScheduled')->will($this->returnValue(null));
        $broadcast->method('getDelivered')->will($this->returnValue(null));
        $broadcast->method('getCreated')->will($this->returnValue($created));
        $broadcast->method('getCancelled')->will($this->returnValue(false));
        $broadcast->method('getUsrName')->will($this->returnValue($usrName));
        $broadcast->method('getAttachments')->will($this->returnValue(new ArrayCollection()));
        $broadcast->method('getRecipients')->will($this->returnValue(new ArrayCollection([$recipient])));

        $Broadcasts = [
            [
                'BroadcastId' => $broadcastId,
                'ShortMsg' => $shortMsg,
                'LongMsg' => '',
                'Time' => $formattedTime,
                'Timestamp' => $timestamp,
                'IsDelivered' => false,
                'IsCancelled' => false,
                'UsrName' => $usrName,
                'AttachmentUrl' => '',
                'Recipients' => [$fullname],
            ]
        ];

        $broadcastRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $broadcastRepo
            ->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['orgId' => $orgId ]))
            ->will($this->returnValue(new ArrayCollection([$broadcast])));

        $orgMembersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgMembersRepo
            ->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(['orgId' => $orgId, 'userId' => $userId]))
            ->will($this->returnValue($orgMember));

        $repoMap = [
            ['App:Orgs', $orgRepo],
            ['App:Broadcasts', $broadcastRepo],
            ['App:OrgMembers', $orgMembersRepo],
        ];

        $em->method('getRepository')->will($this->returnValueMap($repoMap));

        $model = new AdminBroadcastsModel($em, $adminChecker, $uploadFile, $requestStack, $tokenStorage, $userUtil);
        $response = $model->GetBroadcasts($orgId);
        $this->assertEquals([
            'Success' => true,
            'Broadcasts' => $Broadcasts,
        ], $response);
    }

    public function testUserWithAltName()
    {
        $broadcastId = 5;
        $orgId = 15;
        $userId = 20;
        $shortMsg = "Short Message";
        $timestamp = 50;
        $usrName = "User Name";
        $fullname = "Full Name";
        $altUsrName = "Alt User Name";

        $em = $this->setUpEntityManager();
        $adminChecker = $this->setUpAdminChecker();
        $uploadFile = $this->setUpUploadFile();
        $requestStack = $this->setUpRequestStack();
        $tokenStorage = $this->setUpTokenStorage();
        $userUtil = $this->setUpUserUtil();

        $adminChecker
            ->expects($this->once())
            ->method('IsAdminUser')
            ->with($this->equalTo($orgId))
            ->will($this->returnValue(true));

        $org = $this->getMockBuilder('App\Entity\Orgs')
            ->disableOriginalConstructor()
            ->getMock();
        $orgRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgRepo
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($orgId))
            ->will($this->returnValue($org));

        $broadcast = $this->getMockBuilder('\App\Entity\Broadcasts')
            ->disableOriginalConstructor()
            ->getMock();

        $created = $this->getMockBuilder('\DateTime')
            ->disableOriginalConstructor()
            ->getMock();
        $formattedTime = 'Time Format';
        $created
            ->expects($this->once())
            ->method('getTimestamp')
            ->will($this->returnValue($timestamp));
        $created
            ->expects($this->once())
            ->method('format')
            ->with($this->equalTo('D M j, Y g:i a'))
            ->will($this->returnValue($formattedTime));

        $orgMember = $this->getMockBuilder('App\Entity\OrgMembers')
            ->disableOriginalConstructor()
            ->getMock();
        $orgMember
            ->expects($this->once())
            ->method('getAltUsrName')
            ->will($this->returnValue($altUsrName));

        $user = $this->getMockBuilder('App\Entity\Users')
            ->disableOriginalConstructor()
            ->getMock();
        $user->method('getFullname')->will($this->returnValue($fullname));
        $user->method('getId')->will($this->returnValue($userId));
        $recipient = $this->getMockBuilder('App\Entity\Recipients')
            ->disableOriginalConstructor()
            ->getMock();
        $recipient->method('getUser')->will($this->returnValue($user));

        $broadcast->method('getId')->will($this->returnValue($broadcastId));
        $broadcast->method('getShortMsg')->will($this->returnValue($shortMsg));
        $broadcast->method('getLongMsg')->will($this->returnValue(null));
        $broadcast->method('getScheduled')->will($this->returnValue(null));
        $broadcast->method('getDelivered')->will($this->returnValue(null));
        $broadcast->method('getCreated')->will($this->returnValue($created));
        $broadcast->method('getCancelled')->will($this->returnValue(false));
        $broadcast->method('getUsrName')->will($this->returnValue($usrName));
        $broadcast->method('getAttachments')->will($this->returnValue(new ArrayCollection()));
        $broadcast->method('getRecipients')->will($this->returnValue(new ArrayCollection([$recipient])));

        $Broadcasts = [
            [
                'BroadcastId' => $broadcastId,
                'ShortMsg' => $shortMsg,
                'LongMsg' => '',
                'Time' => $formattedTime,
                'Timestamp' => $timestamp,
                'IsDelivered' => false,
                'IsCancelled' => false,
                'UsrName' => $usrName,
                'AttachmentUrl' => '',
                'Recipients' => [$altUsrName],
            ]
        ];

        $broadcastRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $broadcastRepo
            ->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['orgId' => $orgId ]))
            ->will($this->returnValue(new ArrayCollection([$broadcast])));

        $orgMembersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgMembersRepo
            ->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(['orgId' => $orgId, 'userId' => $userId]))
            ->will($this->returnValue($orgMember));

        $repoMap = [
            ['App:Orgs', $orgRepo],
            ['App:Broadcasts', $broadcastRepo],
            ['App:OrgMembers', $orgMembersRepo],
        ];

        $em->method('getRepository')->will($this->returnValueMap($repoMap));

        $model = new AdminBroadcastsModel($em, $adminChecker, $uploadFile, $requestStack, $tokenStorage, $userUtil);
        $response = $model->GetBroadcasts($orgId);
        $this->assertEquals([
            'Success' => true,
            'Broadcasts' => $Broadcasts,
        ], $response);
    }
}
