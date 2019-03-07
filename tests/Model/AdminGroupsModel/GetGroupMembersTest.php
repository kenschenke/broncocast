<?php

namespace App\Tests\Model\AdminGroupsModel;

use App\Model\AdminGroupsModel;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class GetGroupMembersTest extends TestCase {
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

    public function testInvalidGroupId() {
        $groupId = 5;

        $em = $this->setUpEntityManager();
        $adminChecker = $this->setUpAdminChecker();
        $requestStack = $this->setUpRequestStack();

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
            ->with($this->equalTo('App:Groups'))
            ->will($this->returnValue($groupsRepo));

        $model = new AdminGroupsModel($em, $adminChecker, $requestStack);
        $response = $model->GetGroupMembers($groupId);
        $this->assertEquals([
            'Success' => false,
            'Error' => 'Group record not found',
        ], $response);
    }

    public function testNonAdminUser() {
        $groupId = 5;
        $orgId = 6;

        $em = $this->setUpEntityManager();
        $adminChecker = $this->setUpAdminChecker();
        $requestStack = $this->setUpRequestStack();

        $adminChecker
            ->expects($this->once())
            ->method('IsAdminUser')
            ->with($this->equalTo($orgId))
            ->will($this->returnValue(false));

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

        $em
            ->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('App:Groups'))
            ->will($this->returnValue($groupsRepo));

        $model = new AdminGroupsModel($em, $adminChecker, $requestStack);
        $response = $model->GetGroupMembers($groupId);
        $this->assertEquals([
            'Success' => false,
            'Error' => 'Administrative privileges required',
        ], $response);
    }

    public function testMissingUserRecord() {
        $groupId = 5;
        $orgId = 6;
        $userId = 7;

        $em = $this->setUpEntityManager();
        $adminChecker = $this->setUpAdminChecker();
        $requestStack = $this->setUpRequestStack();

        $adminChecker
            ->expects($this->once())
            ->method('IsAdminUser')
            ->with($this->equalTo($orgId))
            ->will($this->returnValue(true));

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

        $usersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $usersRepo
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($userId))
            ->will($this->returnValue(null));

        $orgMembersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $grpMember = $this->getMockBuilder('App\Entity\GrpMembers')
            ->disableOriginalConstructor()
            ->getMock();
        $grpMember
            ->expects($this->once())
            ->method('getUserId')
            ->will($this->returnValue($userId));

        $grpMembersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $grpMembersRepo
            ->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['grpId' => $groupId]))
            ->will($this->returnValue(new ArrayCollection([$grpMember])));

        $repoMap = [
            ['App:Groups', $groupsRepo],
            ['App:Users', $usersRepo],
            ['App:OrgMembers', $orgMembersRepo],
            ['App:GrpMembers', $grpMembersRepo],
        ];

        $em->method('getRepository')->will($this->returnValueMap($repoMap));

        $model = new AdminGroupsModel($em, $adminChecker, $requestStack);
        $response = $model->GetGroupMembers($groupId);
        $this->assertEquals([
            'Success' => false,
            'Error' => 'User record not found',
        ], $response);
    }

    public function testMissingOrgMemberRecord() {
        $groupId = 5;
        $orgId = 6;
        $userId = 7;

        $em = $this->setUpEntityManager();
        $adminChecker = $this->setUpAdminChecker();
        $requestStack = $this->setUpRequestStack();

        $adminChecker
            ->expects($this->once())
            ->method('IsAdminUser')
            ->with($this->equalTo($orgId))
            ->will($this->returnValue(true));

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

        $user = $this->getMockBuilder('App\Entity\Users')
            ->disableOriginalConstructor()
            ->getMock();
        $user->method('getId')->will($this->returnValue($userId));

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

        $grpMember = $this->getMockBuilder('App\Entity\GrpMembers')
            ->disableOriginalConstructor()
            ->getMock();
        $grpMember
            ->expects($this->once())
            ->method('getUserId')
            ->will($this->returnValue($userId));

        $grpMembersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $grpMembersRepo
            ->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['grpId' => $groupId]))
            ->will($this->returnValue(new ArrayCollection([$grpMember])));

        $repoMap = [
            ['App:Groups', $groupsRepo],
            ['App:Users', $usersRepo],
            ['App:OrgMembers', $orgMembersRepo],
            ['App:GrpMembers', $grpMembersRepo],
        ];

        $em->method('getRepository')->will($this->returnValueMap($repoMap));

        $model = new AdminGroupsModel($em, $adminChecker, $requestStack);
        $response = $model->GetGroupMembers($groupId);
        $this->assertEquals([
            'Success' => false,
            'Error' => 'Organization member record not found',
        ], $response);
    }

    public function testNullAltUsrName() {
        $groupId = 5;
        $orgId = 6;
        $userId = 7;
        $usrName = 'User Name';
        $memberId = 8;

        $em = $this->setUpEntityManager();
        $adminChecker = $this->setUpAdminChecker();
        $requestStack = $this->setUpRequestStack();

        $adminChecker
            ->expects($this->once())
            ->method('IsAdminUser')
            ->with($this->equalTo($orgId))
            ->will($this->returnValue(true));

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

        $user = $this->getMockBuilder('App\Entity\Users')
            ->disableOriginalConstructor()
            ->getMock();
        $user->method('getId')->will($this->returnValue($userId));
        $user->method('getFullname')->will($this->returnValue($usrName));

        $usersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $usersRepo
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($userId))
            ->will($this->returnValue($user));

        $orgMember = $this->getMockBuilder('App\Entity\OrgMembers')
            ->disableOriginalConstructor()
            ->getMock();
        $orgMember
            ->expects($this->once())
            ->method('getAltUsrName')
            ->will($this->returnValue(null));

        $orgMembersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgMembersRepo
            ->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(['orgId' => $orgId, 'userId' => $userId]))
            ->will($this->returnValue($orgMember));

        $grpMember = $this->getMockBuilder('App\Entity\GrpMembers')
            ->disableOriginalConstructor()
            ->getMock();
        $grpMember
            ->expects($this->once())
            ->method('getUserId')
            ->will($this->returnValue($userId));
        $grpMember->method('getId')->will($this->returnValue($memberId));

        $grpMembersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $grpMembersRepo
            ->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['grpId' => $groupId]))
            ->will($this->returnValue(new ArrayCollection([$grpMember])));

        $repoMap = [
            ['App:Groups', $groupsRepo],
            ['App:Users', $usersRepo],
            ['App:OrgMembers', $orgMembersRepo],
            ['App:GrpMembers', $grpMembersRepo],
        ];

        $em->method('getRepository')->will($this->returnValueMap($repoMap));

        $model = new AdminGroupsModel($em, $adminChecker, $requestStack);
        $response = $model->GetGroupMembers($groupId);
        $this->assertEquals([
            'Success' => true,
            'Members' => [
                [
                    'MemberId' => $memberId,
                    'UserId' => $userId,
                    'UserName' => $usrName,
                ]
            ],
        ], $response);
    }

    public function testEmptyAltUsrName() {
        $groupId = 5;
        $orgId = 6;
        $userId = 7;
        $usrName = 'User Name';
        $memberId = 8;

        $em = $this->setUpEntityManager();
        $adminChecker = $this->setUpAdminChecker();
        $requestStack = $this->setUpRequestStack();

        $adminChecker
            ->expects($this->once())
            ->method('IsAdminUser')
            ->with($this->equalTo($orgId))
            ->will($this->returnValue(true));

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

        $user = $this->getMockBuilder('App\Entity\Users')
            ->disableOriginalConstructor()
            ->getMock();
        $user->method('getId')->will($this->returnValue($userId));
        $user->method('getFullname')->will($this->returnValue($usrName));

        $usersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $usersRepo
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($userId))
            ->will($this->returnValue($user));

        $orgMember = $this->getMockBuilder('App\Entity\OrgMembers')
            ->disableOriginalConstructor()
            ->getMock();
        $orgMember
            ->expects($this->once())
            ->method('getAltUsrName')
            ->will($this->returnValue(''));

        $orgMembersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgMembersRepo
            ->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(['orgId' => $orgId, 'userId' => $userId]))
            ->will($this->returnValue($orgMember));

        $grpMember = $this->getMockBuilder('App\Entity\GrpMembers')
            ->disableOriginalConstructor()
            ->getMock();
        $grpMember
            ->expects($this->once())
            ->method('getUserId')
            ->will($this->returnValue($userId));
        $grpMember->method('getId')->will($this->returnValue($memberId));

        $grpMembersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $grpMembersRepo
            ->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['grpId' => $groupId]))
            ->will($this->returnValue(new ArrayCollection([$grpMember])));

        $repoMap = [
            ['App:Groups', $groupsRepo],
            ['App:Users', $usersRepo],
            ['App:OrgMembers', $orgMembersRepo],
            ['App:GrpMembers', $grpMembersRepo],
        ];

        $em->method('getRepository')->will($this->returnValueMap($repoMap));

        $model = new AdminGroupsModel($em, $adminChecker, $requestStack);
        $response = $model->GetGroupMembers($groupId);
        $this->assertEquals([
            'Success' => true,
            'Members' => [
                [
                    'MemberId' => $memberId,
                    'UserId' => $userId,
                    'UserName' => $usrName,
                ]
            ],
        ], $response);
    }

    public function testAltUsrName() {
        $groupId = 5;
        $orgId = 6;
        $userId = 7;
        $usrName = 'User Name';
        $altUsrName = 'Alt User Name';
        $memberId = 8;

        $em = $this->setUpEntityManager();
        $adminChecker = $this->setUpAdminChecker();
        $requestStack = $this->setUpRequestStack();

        $adminChecker
            ->expects($this->once())
            ->method('IsAdminUser')
            ->with($this->equalTo($orgId))
            ->will($this->returnValue(true));

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

        $user = $this->getMockBuilder('App\Entity\Users')
            ->disableOriginalConstructor()
            ->getMock();
        $user->method('getId')->will($this->returnValue($userId));
        $user->method('getFullname')->will($this->returnValue($usrName));

        $usersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $usersRepo
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($userId))
            ->will($this->returnValue($user));

        $orgMember = $this->getMockBuilder('App\Entity\OrgMembers')
            ->disableOriginalConstructor()
            ->getMock();
        $orgMember
            ->expects($this->once())
            ->method('getAltUsrName')
            ->will($this->returnValue($altUsrName));

        $orgMembersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgMembersRepo
            ->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(['orgId' => $orgId, 'userId' => $userId]))
            ->will($this->returnValue($orgMember));

        $grpMember = $this->getMockBuilder('App\Entity\GrpMembers')
            ->disableOriginalConstructor()
            ->getMock();
        $grpMember
            ->expects($this->once())
            ->method('getUserId')
            ->will($this->returnValue($userId));
        $grpMember->method('getId')->will($this->returnValue($memberId));

        $grpMembersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $grpMembersRepo
            ->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['grpId' => $groupId]))
            ->will($this->returnValue(new ArrayCollection([$grpMember])));

        $repoMap = [
            ['App:Groups', $groupsRepo],
            ['App:Users', $usersRepo],
            ['App:OrgMembers', $orgMembersRepo],
            ['App:GrpMembers', $grpMembersRepo],
        ];

        $em->method('getRepository')->will($this->returnValueMap($repoMap));

        $model = new AdminGroupsModel($em, $adminChecker, $requestStack);
        $response = $model->GetGroupMembers($groupId);
        $this->assertEquals([
            'Success' => true,
            'Members' => [
                [
                    'MemberId' => $memberId,
                    'UserId' => $userId,
                    'UserName' => $altUsrName,
                ]
            ],
        ], $response);
    }

    public function testMultipleGroupMembers() {
        $groupId = 5;
        $orgId = 6;

        $em = $this->setUpEntityManager();
        $adminChecker = $this->setUpAdminChecker();
        $requestStack = $this->setUpRequestStack();

        $adminChecker
            ->expects($this->once())
            ->method('IsAdminUser')
            ->with($this->equalTo($orgId))
            ->will($this->returnValue(true));

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

        $usersMap = [];
        $orgMembersMap = [];
        $grpMembers = [];
        $members = [];
        for ($i = 10; $i <= 15; $i++) {
            $usrName = "User Name $i";
            $user = $this->getMockBuilder('App\Entity\Users')
                ->disableOriginalConstructor()
                ->getMock();
            $user->method('getId')->will($this->returnValue($i));
            $user->method('getFullname')->will($this->returnValue($usrName));

            $usersMap[] = [$i, null, null, $user];

            $orgMember = $this->getMockBuilder('App\Entity\OrgMembers')
                ->disableOriginalConstructor()
                ->getMock();
            $orgMember
                ->expects($this->once())
                ->method('getAltUsrName')
                ->will($this->returnValue(null));

            $orgMembersMap[] = [
                ['orgId' => $orgId, 'userId' => $i], null, $orgMember
            ];

            $memberId = $i * 10;
            $grpMember = $this->getMockBuilder('App\Entity\GrpMembers')
                ->disableOriginalConstructor()
                ->getMock();
            $grpMember
                ->expects($this->once())
                ->method('getUserId')
                ->will($this->returnValue($i));
            $grpMember->method('getId')->will($this->returnValue($memberId));

            $grpMembers[] = $grpMember;

            $members[] = [
                'MemberId' => $memberId,
                'UserId' => $i,
                'UserName' => $usrName,
            ];
        }

        $usersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $usersRepo->method('find')->will($this->returnValueMap($usersMap));

        $orgMembersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgMembersRepo->method('findOneBy')->will($this->returnValueMap($orgMembersMap));

        $grpMembersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $grpMembersRepo
            ->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['grpId' => $groupId]))
            ->will($this->returnValue(new ArrayCollection($grpMembers)));

        $repoMap = [
            ['App:Groups', $groupsRepo],
            ['App:Users', $usersRepo],
            ['App:OrgMembers', $orgMembersRepo],
            ['App:GrpMembers', $grpMembersRepo],
        ];

        $em->method('getRepository')->will($this->returnValueMap($repoMap));

        $model = new AdminGroupsModel($em, $adminChecker, $requestStack);
        $response = $model->GetGroupMembers($groupId);
        $this->assertEquals([
            'Success' => true,
            'Members' => $members,
        ], $response);
    }
}
