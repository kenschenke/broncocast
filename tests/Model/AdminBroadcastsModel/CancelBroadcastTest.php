<?php

namespace App\Tests\Model\AdminBroadcastsModel;

use App\Model\AdminBroadcastsModel;
use PHPUnit\Framework\TestCase;

class CancelBroadcastTest extends TestCase
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

    public function testBroadcastNotFound()
    {
        $broadcastId = 5;

        $em = $this->setUpEntityManager();
        $adminChecker = $this->setUpAdminChecker();
        $uploadFile = $this->setUpUploadFile();
        $requestStack = $this->setUpRequestStack();
        $tokenStorage = $this->setUpTokenStorage();
        $userUtil = $this->setUpUserUtil();

        $repo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repo->expects($this->once())
            ->method('find')
            ->with($this->equalTo($broadcastId))
            ->will($this->returnValue(null));
        $em->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('App:Broadcasts'))
            ->will($this->returnValue($repo));

        $model = new AdminBroadcastsModel($em, $adminChecker, $uploadFile, $requestStack, $tokenStorage, $userUtil);
        $result = $model->CancelBroadcast($broadcastId);
        $this->assertEquals(['Success' => false, 'Error' => 'Broadcast record not found'], $result);
    }

    public function testUserNotAdmin()
    {
        $broadcastId = 5;
        $orgId = 10;

        $em = $this->setUpEntityManager();
        $adminChecker = $this->setUpAdminChecker();
        $uploadFile = $this->setUpUploadFile();
        $requestStack = $this->setUpRequestStack();
        $tokenStorage = $this->setUpTokenStorage();
        $userUtil = $this->setUpUserUtil();

        $broadcast = $this->getMockBuilder('App\Entity\Broadcasts')
            ->disableOriginalConstructor()
            ->getMock();
        $broadcast->expects($this->once())
            ->method('getOrgId')
            ->will($this->returnValue($orgId));

        $repo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repo->expects($this->once())
            ->method('find')
            ->with($this->equalTo($broadcastId))
            ->will($this->returnValue($broadcast));

        $em->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('App:Broadcasts'))
            ->will($this->returnValue($repo));

        $adminChecker->expects($this->once())
            ->method('IsAdminUser')
            ->with($this->equalTo($orgId))
            ->will($this->returnValue(false));

        $model = new AdminBroadcastsModel($em, $adminChecker, $uploadFile, $requestStack, $tokenStorage, $userUtil);
        $result = $model->CancelBroadcast($broadcastId);
        $this->assertEquals(['Success' => false, 'Error' => 'Administrative privileges required'], $result);
    }

    public function testBroadcastAlreadyDelivered()
    {
        $broadcastId = 5;
        $orgId = 10;

        $em = $this->setUpEntityManager();
        $adminChecker = $this->setUpAdminChecker();
        $uploadFile = $this->setUpUploadFile();
        $requestStack = $this->setUpRequestStack();
        $tokenStorage = $this->setUpTokenStorage();
        $userUtil = $this->setUpUserUtil();

        $broadcast = $this->getMockBuilder('App\Entity\Broadcasts')
            ->disableOriginalConstructor()
            ->getMock();
        $broadcast->expects($this->once())
            ->method('getOrgId')
            ->will($this->returnValue($orgId));
        $broadcast->expects($this->once())
            ->method('getDelivered')
            ->will($this->returnValue(new \DateTime()));

        $repo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repo->expects($this->once())
            ->method('find')
            ->with($this->equalTo($broadcastId))
            ->will($this->returnValue($broadcast));

        $em->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('App:Broadcasts'))
            ->will($this->returnValue($repo));

        $adminChecker->expects($this->once())
            ->method('IsAdminUser')
            ->with($this->equalTo($orgId))
            ->will($this->returnValue(true));

        $model = new AdminBroadcastsModel($em, $adminChecker, $uploadFile, $requestStack, $tokenStorage, $userUtil);
        $result = $model->CancelBroadcast($broadcastId);
        $this->assertEquals(['Success' => false, 'Error' => 'Broadcast already delivered'], $result);
    }

    public function testCancelSuccessful()
    {
        $broadcastId = 5;
        $orgId = 10;

        $em = $this->setUpEntityManager();
        $adminChecker = $this->setUpAdminChecker();
        $uploadFile = $this->setUpUploadFile();
        $requestStack = $this->setUpRequestStack();
        $tokenStorage = $this->setUpTokenStorage();
        $userUtil = $this->setUpUserUtil();

        $broadcast = $this->getMockBuilder('App\Entity\Broadcasts')
            ->disableOriginalConstructor()
            ->getMock();
        $broadcast->expects($this->once())
            ->method('getOrgId')
            ->will($this->returnValue($orgId));
        $broadcast->expects($this->once())
            ->method('getDelivered')
            ->will($this->returnValue(null));
        $broadcast->expects($this->once())
            ->method('setCancelled')
            ->with($this->equalTo(true));

        $repo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repo->expects($this->once())
            ->method('find')
            ->with($this->equalTo($broadcastId))
            ->will($this->returnValue($broadcast));

        $em->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('App:Broadcasts'))
            ->will($this->returnValue($repo));
        $em->expects($this->once())
            ->method('persist')
            ->with($this->equalTo($broadcast));
        $em->expects($this->once())
            ->method('flush');

        $adminChecker->expects($this->once())
            ->method('IsAdminUser')
            ->with($this->equalTo($orgId))
            ->will($this->returnValue(true));

        $model = new AdminBroadcastsModel($em, $adminChecker, $uploadFile, $requestStack, $tokenStorage, $userUtil);
        $result = $model->CancelBroadcast($broadcastId);
        $this->assertEquals(['Success' => true], $result);
    }
}
