<?php

namespace App\Tests\Model\AdminBroadcastsModel;

use App\Model\AdminBroadcastsModel;
use PHPUnit\Framework\TestCase;

class SaveNewBroadcastTest extends TestCase {
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
        $response = $model->SaveNewBroadcast($OrgId);
        $this->assertEquals([
            'Success' => false,
            'Error' => 'Administrative privileges required',
        ], $response);
    }

    public function testInvalidOrgId()
    {
        $orgId = 5;

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

        $orgsRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgsRepo
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($orgId))
            ->will($this->returnValue(null));

        $em
            ->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('App:Orgs'))
            ->will($this->returnValue($orgsRepo));

        $model = new AdminBroadcastsModel($em, $adminChecker, $uploadFile, $requestStack, $tokenStorage, $userUtil);
        $response = $model->SaveNewBroadcast($orgId);
        $this->assertEquals([
            'Success' => false,
            'Error' => 'Organization record not found',
        ], $response);
    }

    public function testMissingShortMsgParam()
    {
        $orgId = 5;

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

        $orgsRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgsRepo
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($orgId))
            ->will($this->returnValue($org));

        $em
            ->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('App:Orgs'))
            ->will($this->returnValue($orgsRepo));

        $params = $this->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();
        $params
            ->expects($this->once())
            ->method('has')
            ->with($this->equalTo('ShortMsg'))
            ->will($this->returnValue(false));

        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $request->request = $params;

        $requestStack
            ->expects($this->once())
            ->method('getCurrentRequest')
            ->will($this->returnValue($request));

        $model = new AdminBroadcastsModel($em, $adminChecker, $uploadFile, $requestStack, $tokenStorage, $userUtil);
        $response = $model->SaveNewBroadcast($orgId);
        $this->assertEquals([
            'Success' => false,
            'Error' => 'ShortMsg parameter missing',
        ], $response);
    }

    public function testMissingRecipientsParam()
    {
        $orgId = 5;

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

        $orgsRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgsRepo
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($orgId))
            ->will($this->returnValue($org));

        $em
            ->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('App:Orgs'))
            ->will($this->returnValue($orgsRepo));

        $hasParamsMap = [
            ['ShortMsg', true],
            ['Recipients', false],
        ];

        $params = $this->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();
        $params->method('has')->will($this->returnValueMap($hasParamsMap));

        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $request->request = $params;

        $requestStack
            ->expects($this->once())
            ->method('getCurrentRequest')
            ->will($this->returnValue($request));

        $model = new AdminBroadcastsModel($em, $adminChecker, $uploadFile, $requestStack, $tokenStorage, $userUtil);
        $response = $model->SaveNewBroadcast($orgId);
        $this->assertEquals([
            'Success' => false,
            'Error' => 'Recipients parameter missing',
        ], $response);
    }

    public function testShortMsgParameterTooLong()
    {
        $orgId = 5;
        $shortMsg = str_repeat('X', 141);
        $longMsg = 'Long Message';
        $recipients = 'Recipients';
        $scheduled = 'Scheduled';
        $timezone = 'Timezone';
        $attachmentFriendlyName = 'Attachment Friendly Name';
        $attachmentLocalName = 'Attachment Local Name';
        $attachmentMimeType = 'Attachment Mime Type';

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

        $orgsRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgsRepo
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($orgId))
            ->will($this->returnValue($org));

        $em
            ->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('App:Orgs'))
            ->will($this->returnValue($orgsRepo));

        $hasParamsMap = [
            ['ShortMsg', true],
            ['Recipients', true],
        ];

        $getParamsMap = [
            ['ShortMsg', null, $shortMsg],
            ['LongMsg', '', $longMsg],
            ['Recipients', null, $recipients],
            ['Scheduled', '', $scheduled],
            ['TimeZone', '', $timezone],
            ['AttachmentFriendlyName', '', $attachmentFriendlyName],
            ['AttachmentLocalName', '', $attachmentLocalName],
            ['AttachmentMimeType', '', $attachmentMimeType],
        ];

        $params = $this->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();
        $params->method('has')->will($this->returnValueMap($hasParamsMap));
        $params->method('get')->will($this->returnValueMap($getParamsMap));

        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $request->request = $params;

        $requestStack
            ->expects($this->once())
            ->method('getCurrentRequest')
            ->will($this->returnValue($request));

        $model = new AdminBroadcastsModel($em, $adminChecker, $uploadFile, $requestStack, $tokenStorage, $userUtil);
        $response = $model->SaveNewBroadcast($orgId);
        $this->assertEquals([
            'Success' => false,
            'Error' => 'ShortMsg parameter too long',
        ], $response);
    }

    public function testLongMsgParameterTooLong()
    {
        $orgId = 5;
        $shortMsg = 'Short Message';
        $longMsg = str_repeat('X', 2049);
        $recipients = 'Recipients';
        $scheduled = 'Scheduled';
        $timezone = 'Timezone';
        $attachmentFriendlyName = 'Attachment Friendly Name';
        $attachmentLocalName = 'Attachment Local Name';
        $attachmentMimeType = 'Attachment Mime Type';

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

        $orgsRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgsRepo
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($orgId))
            ->will($this->returnValue($org));

        $em
            ->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('App:Orgs'))
            ->will($this->returnValue($orgsRepo));

        $hasParamsMap = [
            ['ShortMsg', true],
            ['Recipients', true],
        ];

        $getParamsMap = [
            ['ShortMsg', null, $shortMsg],
            ['LongMsg', '', $longMsg],
            ['Recipients', null, $recipients],
            ['Scheduled', '', $scheduled],
            ['TimeZone', '', $timezone],
            ['AttachmentFriendlyName', '', $attachmentFriendlyName],
            ['AttachmentLocalName', '', $attachmentLocalName],
            ['AttachmentMimeType', '', $attachmentMimeType],
        ];

        $params = $this->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();
        $params->method('has')->will($this->returnValueMap($hasParamsMap));
        $params->method('get')->will($this->returnValueMap($getParamsMap));

        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $request->request = $params;

        $requestStack
            ->expects($this->once())
            ->method('getCurrentRequest')
            ->will($this->returnValue($request));

        $model = new AdminBroadcastsModel($em, $adminChecker, $uploadFile, $requestStack, $tokenStorage, $userUtil);
        $response = $model->SaveNewBroadcast($orgId);
        $this->assertEquals([
            'Success' => false,
            'Error' => 'LongMsg parameter too long',
        ], $response);
    }

    public function testEmptyTimeZoneParameter()
    {
        $orgId = 5;
        $shortMsg = 'Short Message';
        $longMsg = 'Long Message';
        $recipients = 'Recipients';
        $scheduled = 'Scheduled';
        $timezone = '';
        $attachmentFriendlyName = 'Attachment Friendly Name';
        $attachmentLocalName = 'Attachment Local Name';
        $attachmentMimeType = 'Attachment Mime Type';

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

        $orgsRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgsRepo
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($orgId))
            ->will($this->returnValue($org));

        $em
            ->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('App:Orgs'))
            ->will($this->returnValue($orgsRepo));

        $hasParamsMap = [
            ['ShortMsg', true],
            ['Recipients', true],
        ];

        $getParamsMap = [
            ['ShortMsg', null, $shortMsg],
            ['LongMsg', '', $longMsg],
            ['Recipients', null, $recipients],
            ['Scheduled', '', $scheduled],
            ['TimeZone', '', $timezone],
            ['AttachmentFriendlyName', '', $attachmentFriendlyName],
            ['AttachmentLocalName', '', $attachmentLocalName],
            ['AttachmentMimeType', '', $attachmentMimeType],
        ];

        $params = $this->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();
        $params->method('has')->will($this->returnValueMap($hasParamsMap));
        $params->method('get')->will($this->returnValueMap($getParamsMap));

        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $request->request = $params;

        $requestStack
            ->expects($this->once())
            ->method('getCurrentRequest')
            ->will($this->returnValue($request));

        $model = new AdminBroadcastsModel($em, $adminChecker, $uploadFile, $requestStack, $tokenStorage, $userUtil);
        $response = $model->SaveNewBroadcast($orgId);
        $this->assertEquals([
            'Success' => false,
            'Error' => 'Missing TimeZone parameter',
        ], $response);
    }

    public function testInvalidTimeZoneParameter()
    {
        $orgId = 5;
        $shortMsg = 'Short Message';
        $longMsg = 'Long Message';
        $recipients = 'Recipients';
        $scheduled = 'Scheduled';
        $timezone = 'Invalid Time Zone';
        $attachmentFriendlyName = 'Attachment Friendly Name';
        $attachmentLocalName = 'Attachment Local Name';
        $attachmentMimeType = 'Attachment Mime Type';

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

        $orgsRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgsRepo
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($orgId))
            ->will($this->returnValue($org));

        $em
            ->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('App:Orgs'))
            ->will($this->returnValue($orgsRepo));

        $hasParamsMap = [
            ['ShortMsg', true],
            ['Recipients', true],
        ];

        $getParamsMap = [
            ['ShortMsg', null, $shortMsg],
            ['LongMsg', '', $longMsg],
            ['Recipients', null, $recipients],
            ['Scheduled', '', $scheduled],
            ['TimeZone', '', $timezone],
            ['AttachmentFriendlyName', '', $attachmentFriendlyName],
            ['AttachmentLocalName', '', $attachmentLocalName],
            ['AttachmentMimeType', '', $attachmentMimeType],
        ];

        $params = $this->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();
        $params->method('has')->will($this->returnValueMap($hasParamsMap));
        $params->method('get')->will($this->returnValueMap($getParamsMap));

        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $request->request = $params;

        $requestStack
            ->expects($this->once())
            ->method('getCurrentRequest')
            ->will($this->returnValue($request));

        $model = new AdminBroadcastsModel($em, $adminChecker, $uploadFile, $requestStack, $tokenStorage, $userUtil);
        $response = $model->SaveNewBroadcast($orgId);
        $this->assertEquals([
            'Success' => false,
            'Error' => 'Invalid TimeZone parameter',
        ], $response);
    }
}
