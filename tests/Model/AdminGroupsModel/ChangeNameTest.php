<?php

namespace App\Tests\Model\AdminGroupsModel;

use App\Model\AdminGroupsModel;
use PHPUnit\Framework\TestCase;

class ChangeNameTest extends TestCase {
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

    public function testMissingNameParameter() {
        $groupId = 5;

        $em = $this->setUpEntityManager();
        $adminChecker = $this->setUpAdminChecker();
        $requestStack = $this->setUpRequestStack();

        $params = $this->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();
        $params
            ->expects($this->once())
            ->method('has')
            ->with($this->equalTo('Name'))
            ->will($this->returnValue(false));

        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $request->request = $params;

        $requestStack
            ->expects($this->once())
            ->method('getCurrentRequest')
            ->will($this->returnValue($request));

        $model = new AdminGroupsModel($em, $adminChecker, $requestStack);
        $response = $model->ChangeName($groupId);
        $this->assertEquals([
            'Success' => false,
            'Error' => 'Name parameter missing',
        ], $response);
    }

    public function testInvalidGroupId() {
        $groupId = 5;
        $groupName = 'Group Name';

        $em = $this->setUpEntityManager();
        $adminChecker = $this->setUpAdminChecker();
        $requestStack = $this->setUpRequestStack();

        $params = $this->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();
        $params
            ->expects($this->once())
            ->method('has')
            ->with($this->equalTo('Name'))
            ->will($this->returnValue($groupName));
        $params
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo('Name'))
            ->will($this->returnValue($groupName));

        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $request->request = $params;

        $requestStack
            ->expects($this->once())
            ->method('getCurrentRequest')
            ->will($this->returnValue($request));

        $groupsRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $groupsRepo
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($groupId))
            ->will($this->returnValue(null));

        $em
            ->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('App:UserGrps'))
            ->will($this->returnValue($groupsRepo));

        $model = new AdminGroupsModel($em, $adminChecker, $requestStack);
        $response = $model->ChangeName($groupId);
        $this->assertEquals([
            'Success' => false,
            'Error' => 'Group record not found',
        ], $response);
    }

    public function testNonAdminUser() {
        $groupId = 5;
        $groupName = 'Group Name';
        $orgId = 10;

        $em = $this->setUpEntityManager();
        $adminChecker = $this->setUpAdminChecker();
        $requestStack = $this->setUpRequestStack();

        $adminChecker
            ->expects($this->once())
            ->method('IsAdminUser')
            ->with($this->equalTo($orgId))
            ->will($this->returnValue(false));

        $params = $this->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();
        $params
            ->expects($this->once())
            ->method('has')
            ->with($this->equalTo('Name'))
            ->will($this->returnValue($groupName));
        $params
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo('Name'))
            ->will($this->returnValue($groupName));

        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $request->request = $params;

        $requestStack
            ->expects($this->once())
            ->method('getCurrentRequest')
            ->will($this->returnValue($request));

        $group = $this->getMockBuilder('App\Entity\UserGrps')
            ->disableOriginalConstructor()
            ->getMock();
        $group
            ->expects($this->once())
            ->method('getOrgId')
            ->will($this->returnValue($orgId));
        $group
            ->expects($this->never())
            ->method('setGrpName')
            ->with($this->equalTo($groupName));

        $groupsRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $groupsRepo
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($groupId))
            ->will($this->returnValue($group));

        $em
            ->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('App:UserGrps'))
            ->will($this->returnValue($groupsRepo));

        $model = new AdminGroupsModel($em, $adminChecker, $requestStack);
        $response = $model->ChangeName($groupId);
        $this->assertEquals([
            'Success' => false,
            'Error' => 'Administrative privileges required',
        ], $response);
    }

    public function testSuccessful() {
        $groupId = 5;
        $groupName = 'Group Name';
        $orgId = 10;

        $em = $this->setUpEntityManager();
        $adminChecker = $this->setUpAdminChecker();
        $requestStack = $this->setUpRequestStack();

        $adminChecker
            ->expects($this->once())
            ->method('IsAdminUser')
            ->with($this->equalTo($orgId))
            ->will($this->returnValue(true));

        $params = $this->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();
        $params
            ->expects($this->once())
            ->method('has')
            ->with($this->equalTo('Name'))
            ->will($this->returnValue($groupName));
        $params
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo('Name'))
            ->will($this->returnValue($groupName));

        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $request->request = $params;

        $requestStack
            ->expects($this->once())
            ->method('getCurrentRequest')
            ->will($this->returnValue($request));

        $group = $this->getMockBuilder('App\Entity\UserGrps')
            ->disableOriginalConstructor()
            ->getMock();
        $group
            ->expects($this->once())
            ->method('getOrgId')
            ->will($this->returnValue($orgId));
        $group
            ->expects($this->once())
            ->method('setGrpName')
            ->with($this->equalTo($groupName));

        $groupsRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $groupsRepo
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($groupId))
            ->will($this->returnValue($group));

        $em
            ->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('App:UserGrps'))
            ->will($this->returnValue($groupsRepo));
        $em
            ->expects($this->once())
            ->method('persist')
            ->with($this->equalTo($group));
        $em->expects($this->once())->method('flush');

        $model = new AdminGroupsModel($em, $adminChecker, $requestStack);
        $response = $model->ChangeName($groupId);
        $this->assertEquals(['Success' => true], $response);
    }
}
