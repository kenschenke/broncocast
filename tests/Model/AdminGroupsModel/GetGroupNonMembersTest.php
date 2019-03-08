<?php

namespace App\Tests\Model\AdminGroupsModel;

use App\Model\AdminGroupsModel;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class GetGroupNonMembersTest extends TestCase {
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
        $response = $model->GetGroupNonMembers($groupId);
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
        $response = $model->GetGroupNonMembers($groupId);
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

        $grpMembersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $grpMembersRepo
            ->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(['grpId' => $groupId, 'userId' => $userId]))
            ->will($this->returnValue(null));

        $orgMember = $this->getMockBuilder('App\Entity\OrgMembers')
            ->disableOriginalConstructor()
            ->getMock();
        $orgMember->method('getUserId')->will($this->returnValue($userId));

        $orgMembersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgMembersRepo
            ->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['orgId' => $orgId]))
            ->will($this->returnValue(new ArrayCollection([$orgMember])));

        $repoMap = [
            ['App:Groups', $groupsRepo],
            ['App:Users', $usersRepo],
            ['App:GrpMembers', $grpMembersRepo],
            ['App:OrgMembers', $orgMembersRepo],
        ];

        $em->method('getRepository')->will($this->returnValueMap($repoMap));

        $model = new AdminGroupsModel($em, $adminChecker, $requestStack);
        $response = $model->GetGroupNonMembers($groupId);
        $this->assertEquals([
            'Success' => false,
            'Error' => 'User record not found',
        ], $response);
    }

    public function testNullAltUsrName() {
        $groupId = 5;
        $orgId = 6;
        $userId = 7;
        $usrName = 'User Name';

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
        $user->method('getFullname')->will($this->returnValue($usrName));

        $usersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $usersRepo
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($userId))
            ->will($this->returnValue($user));

        $grpMembersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $grpMembersRepo
            ->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(['grpId' => $groupId, 'userId' => $userId]))
            ->will($this->returnValue(null));

        $orgMember = $this->getMockBuilder('App\Entity\OrgMembers')
            ->disableOriginalConstructor()
            ->getMock();
        $orgMember->method('getUserId')->will($this->returnValue($userId));
        $orgMember->method('getAltUsrName')->will($this->returnValue(null));
        $orgMember->method('getIsHidden')->will($this->returnValue(false));

        $orgMembersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgMembersRepo
            ->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['orgId' => $orgId]))
            ->will($this->returnValue(new ArrayCollection([$orgMember])));

        $repoMap = [
            ['App:Groups', $groupsRepo],
            ['App:Users', $usersRepo],
            ['App:GrpMembers', $grpMembersRepo],
            ['App:OrgMembers', $orgMembersRepo],
        ];

        $em->method('getRepository')->will($this->returnValueMap($repoMap));

        $model = new AdminGroupsModel($em, $adminChecker, $requestStack);
        $response = $model->GetGroupNonMembers($groupId);
        $this->assertEquals([
            'Success' => true,
            'NonMembers' => [
                [
                    'UserId' => $userId,
                    'UserName' => $usrName,
                    'Hidden' => false,
                ]
            ]
        ], $response);
    }

    public function testEmptyAltUsrName() {
        $groupId = 5;
        $orgId = 6;
        $userId = 7;
        $usrName = 'User Name';

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
        $user->method('getFullname')->will($this->returnValue($usrName));

        $usersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $usersRepo
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($userId))
            ->will($this->returnValue($user));

        $grpMembersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $grpMembersRepo
            ->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(['grpId' => $groupId, 'userId' => $userId]))
            ->will($this->returnValue(null));

        $orgMember = $this->getMockBuilder('App\Entity\OrgMembers')
            ->disableOriginalConstructor()
            ->getMock();
        $orgMember->method('getUserId')->will($this->returnValue($userId));
        $orgMember->method('getAltUsrName')->will($this->returnValue(''));
        $orgMember->method('getIsHidden')->will($this->returnValue(false));

        $orgMembersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgMembersRepo
            ->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['orgId' => $orgId]))
            ->will($this->returnValue(new ArrayCollection([$orgMember])));

        $repoMap = [
            ['App:Groups', $groupsRepo],
            ['App:Users', $usersRepo],
            ['App:GrpMembers', $grpMembersRepo],
            ['App:OrgMembers', $orgMembersRepo],
        ];

        $em->method('getRepository')->will($this->returnValueMap($repoMap));

        $model = new AdminGroupsModel($em, $adminChecker, $requestStack);
        $response = $model->GetGroupNonMembers($groupId);
        $this->assertEquals([
            'Success' => true,
            'NonMembers' => [
                [
                    'UserId' => $userId,
                    'UserName' => $usrName,
                    'Hidden' => false,
                ]
            ]
        ], $response);
    }

    public function testNonEmptyAltUsrName() {
        $groupId = 5;
        $orgId = 6;
        $userId = 7;
        $usrName = 'User Name';
        $altUsrName = 'Alt User Name';

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
        $user->method('getFullname')->will($this->returnValue($usrName));

        $usersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $usersRepo
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($userId))
            ->will($this->returnValue($user));

        $grpMembersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $grpMembersRepo
            ->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(['grpId' => $groupId, 'userId' => $userId]))
            ->will($this->returnValue(null));

        $orgMember = $this->getMockBuilder('App\Entity\OrgMembers')
            ->disableOriginalConstructor()
            ->getMock();
        $orgMember->method('getUserId')->will($this->returnValue($userId));
        $orgMember->method('getAltUsrName')->will($this->returnValue($altUsrName));
        $orgMember->method('getIsHidden')->will($this->returnValue(false));

        $orgMembersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgMembersRepo
            ->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['orgId' => $orgId]))
            ->will($this->returnValue(new ArrayCollection([$orgMember])));

        $repoMap = [
            ['App:Groups', $groupsRepo],
            ['App:Users', $usersRepo],
            ['App:GrpMembers', $grpMembersRepo],
            ['App:OrgMembers', $orgMembersRepo],
        ];

        $em->method('getRepository')->will($this->returnValueMap($repoMap));

        $model = new AdminGroupsModel($em, $adminChecker, $requestStack);
        $response = $model->GetGroupNonMembers($groupId);
        $this->assertEquals([
            'Success' => true,
            'NonMembers' => [
                [
                    'UserId' => $userId,
                    'UserName' => $altUsrName,
                    'Hidden' => false,
                ]
            ]
        ], $response);
    }

    public function testHiddenUser() {
        $groupId = 5;
        $orgId = 6;
        $userId = 7;
        $usrName = 'User Name';

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
        $user->method('getFullname')->will($this->returnValue($usrName));

        $usersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $usersRepo
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($userId))
            ->will($this->returnValue($user));

        $grpMembersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $grpMembersRepo
            ->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(['grpId' => $groupId, 'userId' => $userId]))
            ->will($this->returnValue(null));

        $orgMember = $this->getMockBuilder('App\Entity\OrgMembers')
            ->disableOriginalConstructor()
            ->getMock();
        $orgMember->method('getUserId')->will($this->returnValue($userId));
        $orgMember->method('getAltUsrName')->will($this->returnValue(null));
        $orgMember->method('getIsHidden')->will($this->returnValue(true));

        $orgMembersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgMembersRepo
            ->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['orgId' => $orgId]))
            ->will($this->returnValue(new ArrayCollection([$orgMember])));

        $repoMap = [
            ['App:Groups', $groupsRepo],
            ['App:Users', $usersRepo],
            ['App:GrpMembers', $grpMembersRepo],
            ['App:OrgMembers', $orgMembersRepo],
        ];

        $em->method('getRepository')->will($this->returnValueMap($repoMap));

        $model = new AdminGroupsModel($em, $adminChecker, $requestStack);
        $response = $model->GetGroupNonMembers($groupId);
        $this->assertEquals([
            'Success' => true,
            'NonMembers' => [
                [
                    'UserId' => $userId,
                    'UserName' => $usrName,
                    'Hidden' => true,
                ]
            ]
        ], $response);
    }

    public function testMembersAndNonMembers() {
        $groupId = 5;
        $orgId = 6;
        $memberUserId1 = 7;
        $memberUserId2 = 8;
        $memberUsrName1 = 'Member User Name 1';
        $memberUsrName2 = 'Member User Name 2';
        $nonMemberUserId1 = 9;
        $nonMemberUserId2 = 10;
        $nonMemberUsrName1 = 'Non Member User Name 1';
        $nonMemberUsrName2 = 'Non Member User Name 2';

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

        $memberUser1 = $this->getMockBuilder('App\Entity\Users')
            ->disableOriginalConstructor()
            ->getMock();
        $memberUser1->method('getFullname')->will($this->returnValue($memberUsrName1));

        $memberUser2 = $this->getMockBuilder('App\Entity\Users')
            ->disableOriginalConstructor()
            ->getMock();
        $memberUser2->method('getFullname')->will($this->returnValue($memberUsrName2));

        $nonMemberUser1 = $this->getMockBuilder('App\Entity\Users')
            ->disableOriginalConstructor()
            ->getMock();
        $nonMemberUser1->method('getFullname')->will($this->returnValue($nonMemberUsrName1));

        $nonMemberUser2 = $this->getMockBuilder('App\Entity\Users')
            ->disableOriginalConstructor()
            ->getMock();
        $nonMemberUser2->method('getFullname')->will($this->returnValue($nonMemberUsrName2));

        $usersMap = [
            [$memberUserId1, null, null, $memberUser1],
            [$memberUserId2, null, null, $memberUser2],
            [$nonMemberUserId1, null, null, $nonMemberUser1],
            [$nonMemberUserId2, null, null, $nonMemberUser2],
        ];

        $usersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $usersRepo->method('find')->will($this->returnValueMap($usersMap));

        $grpMember1 = $this->getMockBuilder('App\Entity\GrpMember')
            ->disableOriginalConstructor()
            ->getMock();

        $grpMember2 = $this->getMockBuilder('App\Entity\GrpMember')
            ->disableOriginalConstructor()
            ->getMock();

        $grpMembersMap = [
            [['grpId' => $groupId, 'userId' => $nonMemberUserId1], null, null],
            [['grpId' => $groupId, 'userId' => $nonMemberUserId2], null, null],
            [['grpId' => $groupId, 'userId' => $memberUserId1], null, $grpMember1],
            [['grpId' => $groupId, 'userId' => $memberUserId2], null, $grpMember2],
        ];

        $grpMembersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $grpMembersRepo->method('findOneBy')->will($this->returnValueMap($grpMembersMap));

        $orgMember1 = $this->getMockBuilder('App\Entity\OrgMembers')
            ->disableOriginalConstructor()
            ->getMock();
        $orgMember1->method('getUserId')->will($this->returnValue($nonMemberUserId1));
        $orgMember1->method('getAltUsrName')->will($this->returnValue(null));
        $orgMember1->method('getIsHidden')->will($this->returnValue(false));

        $orgMember2 = $this->getMockBuilder('App\Entity\OrgMembers')
            ->disableOriginalConstructor()
            ->getMock();
        $orgMember2->method('getUserId')->will($this->returnValue($nonMemberUserId2));
        $orgMember2->method('getAltUsrName')->will($this->returnValue(null));
        $orgMember2->method('getIsHidden')->will($this->returnValue(false));

        $orgMembersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgMembersRepo
            ->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['orgId' => $orgId]))
            ->will($this->returnValue(new ArrayCollection([$orgMember1, $orgMember2])));

        $repoMap = [
            ['App:Groups', $groupsRepo],
            ['App:Users', $usersRepo],
            ['App:GrpMembers', $grpMembersRepo],
            ['App:OrgMembers', $orgMembersRepo],
        ];

        $em->method('getRepository')->will($this->returnValueMap($repoMap));

        $model = new AdminGroupsModel($em, $adminChecker, $requestStack);
        $response = $model->GetGroupNonMembers($groupId);
        $this->assertEquals([
            'Success' => true,
            'NonMembers' => [
                [
                    'UserId' => $nonMemberUserId1,
                    'UserName' => $nonMemberUsrName1,
                    'Hidden' => false,
                ],
                [
                    'UserId' => $nonMemberUserId2,
                    'UserName' => $nonMemberUsrName2,
                    'Hidden' => false,
                ],
            ]
        ], $response);
    }
}
