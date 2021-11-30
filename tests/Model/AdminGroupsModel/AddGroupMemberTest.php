<?php

namespace App\Tests\Model\AdminGroupsModel;

use App\Model\AdminGroupsModel;
use PHPUnit\Framework\TestCase;

class AddGroupMemberTest extends TestCase {
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

    public function testMissingUserId() {
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
            ->with($this->equalTo('UserId'))
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
        $response = $model->AddGroupMember($groupId);
        $this->assertEquals([
            'Success' => false,
            'Error' => 'UserId parameter missing',
        ], $response);
    }

    public function testInvalidGroupId() {
        $groupId = 5;
        $userId = 10;

        $em = $this->setUpEntityManager();
        $adminChecker = $this->setUpAdminChecker();
        $requestStack = $this->setUpRequestStack();

        $params = $this->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();
        $params
            ->expects($this->once())
            ->method('has')
            ->with($this->equalTo('UserId'))
            ->will($this->returnValue(true));
        $params
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo('UserId'))
            ->will($this->returnValue($userId));

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

        $repoMap = [
            ['App:UserGrps', $groupsRepo],
        ];

        $em->method('getRepository')->will($this->returnValueMap($repoMap));

        $model = new AdminGroupsModel($em, $adminChecker, $requestStack);
        $response = $model->AddGroupMember($groupId);
        $this->assertEquals([
            'Success' => false,
            'Error' => 'Group record not found',
        ], $response);
    }

    public function testInvalidUserId() {
        $groupId = 5;
        $userId = 10;

        $em = $this->setUpEntityManager();
        $adminChecker = $this->setUpAdminChecker();
        $requestStack = $this->setUpRequestStack();

        $params = $this->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();
        $params
            ->expects($this->once())
            ->method('has')
            ->with($this->equalTo('UserId'))
            ->will($this->returnValue(true));
        $params
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo('UserId'))
            ->will($this->returnValue($userId));

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

        $groupsRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $groupsRepo
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($groupId))
            ->will($this->returnValue($group));

        $usersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $usersRepo
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($userId))
            ->will($this->returnValue(null));

        $repoMap = [
            ['App:UserGrps', $groupsRepo],
            ['App:Users', $usersRepo],
        ];

        $em->method('getRepository')->will($this->returnValueMap($repoMap));

        $model = new AdminGroupsModel($em, $adminChecker, $requestStack);
        $response = $model->AddGroupMember($groupId);
        $this->assertEquals([
            'Success' => false,
            'Error' => 'User record not found',
        ], $response);
    }

    public function testNonAdminUser() {
        $groupId = 5;
        $userId = 10;
        $orgId = 6;

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
            ->with($this->equalTo('UserId'))
            ->will($this->returnValue(true));
        $params
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo('UserId'))
            ->will($this->returnValue($userId));

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
        $group->method('getOrgId')->will($this->returnValue($orgId));

        $groupsRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $groupsRepo
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($groupId))
            ->will($this->returnValue($group));

        $user = $this->getMockBuilder('App\Entity\Users')
            ->disableOriginalConstructor()
            ->getMock();

        $usersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $usersRepo
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($userId))
            ->will($this->returnValue($user));

        $repoMap = [
            ['App:UserGrps', $groupsRepo],
            ['App:Users', $usersRepo],
        ];

        $em->method('getRepository')->will($this->returnValueMap($repoMap));

        $model = new AdminGroupsModel($em, $adminChecker, $requestStack);
        $response = $model->AddGroupMember($groupId);
        $this->assertEquals([
            'Success' => false,
            'Error' => 'Admin privileges required',
        ], $response);
    }

    public function testUserNotOrgMember() {
        $groupId = 5;
        $userId = 10;
        $orgId = 6;

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
            ->with($this->equalTo('UserId'))
            ->will($this->returnValue(true));
        $params
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo('UserId'))
            ->will($this->returnValue($userId));

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
        $group->method('getOrgId')->will($this->returnValue($orgId));

        $groupsRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $groupsRepo
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($groupId))
            ->will($this->returnValue($group));

        $user = $this->getMockBuilder('App\Entity\Users')
            ->disableOriginalConstructor()
            ->getMock();

        $usersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $usersRepo
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($userId))
            ->will($this->returnValue($user));

        $orgMembersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgMembersRepo
            ->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(['orgId' => $orgId, 'userId' => $userId]))
            ->will($this->returnValue(null));

        $repoMap = [
            ['App:UserGrps', $groupsRepo],
            ['App:Users', $usersRepo],
            ['App:OrgMembers', $orgMembersRepo],
        ];

        $em->method('getRepository')->will($this->returnValueMap($repoMap));

        $model = new AdminGroupsModel($em, $adminChecker, $requestStack);
        $response = $model->AddGroupMember($groupId);
        $this->assertEquals([
            'Success' => false,
            'Error' => 'User is not a member of this organization',
        ], $response);
    }
}
