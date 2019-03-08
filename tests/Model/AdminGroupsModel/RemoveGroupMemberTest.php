<?php

namespace App\Tests\Model\AdminGroupsModel;

use App\Model\AdminGroupsModel;
use PHPUnit\Framework\TestCase;

class RemoveGroupMemberTest extends TestCase {
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

    public function testMissingGrpMemberRecord() {
        $memberId = 6;

        $em = $this->setUpEntityManager();
        $adminChecker = $this->setUpAdminChecker();
        $requestStack = $this->setUpRequestStack();

        $grpMembersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $grpMembersRepo
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($memberId))
            ->will($this->returnValue(null));

        $em
            ->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('App:GrpMembers'))
            ->will($this->returnValue($grpMembersRepo));

        $model = new AdminGroupsModel($em, $adminChecker, $requestStack);
        $response = $model->RemoveGroupMember($memberId);
        $this->assertEquals([
            'Success' => false,
            'Error' => 'Group member record not found',
        ], $response);
    }

    public function testMissingGroupRecord() {
        $memberId = 6;
        $groupId = 7;

        $em = $this->setUpEntityManager();
        $adminChecker = $this->setUpAdminChecker();
        $requestStack = $this->setUpRequestStack();

        $grpMember = $this->getMockBuilder('App\Entity\GrpMembers')
            ->disableOriginalConstructor()
            ->getMock();
        $grpMember->method('getGrpId')->will($this->returnValue($groupId));

        $grpMembersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $grpMembersRepo
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($memberId))
            ->will($this->returnValue($grpMember));

        $groupsRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $groupsRepo
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($groupId))
            ->will($this->returnValue(null));

        $reposMap = [
            ['App:GrpMembers', $grpMembersRepo],
            ['App:Groups', $groupsRepo],
        ];

        $em->method('getRepository')->will($this->returnValueMap($reposMap));

        $model = new AdminGroupsModel($em, $adminChecker, $requestStack);
        $response = $model->RemoveGroupMember($memberId);
        $this->assertEquals([
            'Success' => false,
            'Error' => 'Group record not found',
        ], $response);
    }

    public function testNonAdminUser() {
        $memberId = 6;
        $groupId = 7;
        $orgId = 8;

        $em = $this->setUpEntityManager();
        $adminChecker = $this->setUpAdminChecker();
        $requestStack = $this->setUpRequestStack();

        $adminChecker
            ->expects($this->once())
            ->method('IsAdminUser')
            ->with($this->equalTo($orgId))
            ->will($this->returnValue(false));

        $grpMember = $this->getMockBuilder('App\Entity\GrpMembers')
            ->disableOriginalConstructor()
            ->getMock();
        $grpMember->method('getGrpId')->will($this->returnValue($groupId));

        $grpMembersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $grpMembersRepo
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($memberId))
            ->will($this->returnValue($grpMember));

        $group = $this->getMockBuilder('App\Entity\Groups')
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

        $reposMap = [
            ['App:GrpMembers', $grpMembersRepo],
            ['App:Groups', $groupsRepo],
        ];

        $em->method('getRepository')->will($this->returnValueMap($reposMap));

        $model = new AdminGroupsModel($em, $adminChecker, $requestStack);
        $response = $model->RemoveGroupMember($memberId);
        $this->assertEquals([
            'Success' => false,
            'Error' => 'Administrative privileges required',
        ], $response);
    }

    public function testSuccessful() {
        $memberId = 6;
        $groupId = 7;
        $orgId = 8;

        $em = $this->setUpEntityManager();
        $adminChecker = $this->setUpAdminChecker();
        $requestStack = $this->setUpRequestStack();

        $adminChecker
            ->expects($this->once())
            ->method('IsAdminUser')
            ->with($this->equalTo($orgId))
            ->will($this->returnValue(true));

        $grpMember = $this->getMockBuilder('App\Entity\GrpMembers')
            ->disableOriginalConstructor()
            ->getMock();
        $grpMember->method('getGrpId')->will($this->returnValue($groupId));

        $grpMembersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $grpMembersRepo
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($memberId))
            ->will($this->returnValue($grpMember));

        $group = $this->getMockBuilder('App\Entity\Groups')
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

        $reposMap = [
            ['App:GrpMembers', $grpMembersRepo],
            ['App:Groups', $groupsRepo],
        ];

        $em->method('getRepository')->will($this->returnValueMap($reposMap));
        $em
            ->expects($this->once())
            ->method('remove')
            ->with($this->equalTo($grpMember));
        $em->expects($this->once())->method('flush');

        $model = new AdminGroupsModel($em, $adminChecker, $requestStack);
        $response = $model->RemoveGroupMember($memberId);
        $this->assertEquals(['Success' => true], $response);
    }
}
