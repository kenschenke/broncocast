<?php

namespace App\Tests\Model\AdminBroadcastsModel;

use App\Model\AdminBroadcastsModel;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class GetGroupMembershipsTest extends TestCase {
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
        $response = $model->GetGroupMemberships($OrgId);
        $this->assertEquals([
            'Success' => false,
            'Error' => 'Administrative privileges required',
        ], $response);
    }

    public function testMultipleGroups()
    {
        $orgId = 5;
        $groupId = 10;
        $groupNames = ['Students', 'Mentors', 'Parents'];
        $userNames = ['ZZZ User Name', 'TTT User Name', 'MMM User Name', 'GGG User Name', 'AAA User Name'];

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

        $groupRecs = [];
        for ($i = 0; $i < count($groupNames); $i++) {
            $group = $this->getMockBuilder('App\Entity\UserGrps')
                ->disableOriginalConstructor()
                ->getMock();
            $group
                ->expects($this->once())
                ->method('getId')
                ->will($this->returnValue($groupId + $i));
            $group
                ->expects($this->once())
                ->method('getGrpName')
                ->will($this->returnValue($groupNames[$i]));
            $groupRecs[] = $group;
        }

        $grpMembersMap = [];
        $usersMap = [];
        $orgMembersMap = [];
        $groups = [];
        for ($i = 0; $i < count($groupNames); $i++) {
            $grpMembers = [];

            $members = [];
            for ($u = 0; $u < 5; $u++) {
                $grpMember = $this->getMockBuilder('App\Entity\GrpMembers')
                    ->disableOriginalConstructor()
                    ->getMock();

                $userId = ($groupId + $i) * 10 + $u;
                $grpMember
                    ->expects($this->once())
                    ->method('getUserId')
                    ->will($this->returnValue($userId));

                $grpMembers[] = $grpMember;

                $orgMember = $this->getMockBuilder('App\Entity\OrgMembers')
                    ->disableOriginalConstructor()
                    ->getMock();
                $orgMember->method('getIsHidden')->will($this->returnValue(false));

                $orgMembersMap[] = [
                    ['orgId' => $orgId, 'userId' => $userId], null, $orgMember
                ];

                $user = $this->getMockBuilder('App\Entity\Users')
                    ->disableOriginalConstructor()
                    ->getMock();
                $user->method('getId')->will($this->returnValue($userId));
                $fullname = "{$groupNames[$i]} " . $userNames[count($userNames) - $u - 1];
                $user->method('getFullname')->will($this->returnValue($fullname));

                $usersMap[] = [$userId, null, null, $user];

                $members[] = [
                    'UserId' => $userId,
                    'UserName' => $fullname,
                ];
            }

            $grpMembersMap[] = [
                ['grpId' => $groupId + $i], null, null, null, new ArrayCollection($grpMembers)
            ];

            $groups[$groupNames[$i]] = $members;
        }

        $groupsRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $groupsRepo
            ->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['orgId' => $orgId]))
            ->will($this->returnValue(new ArrayCollection($groupRecs)));

        $grpMembersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $grpMembersRepo->method('findBy')->will($this->returnValueMap($grpMembersMap));

        $usersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $usersRepo->method('find')->will($this->returnValueMap($usersMap));

        $orgMembersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgMembersRepo->method('findOneBy')->will($this->returnValueMap($orgMembersMap));

        $repoMap = [
            ['App:UserGrps', $groupsRepo],
            ['App:GrpMembers', $grpMembersRepo],
            ['App:Users', $usersRepo],
            ['App:OrgMembers', $orgMembersRepo],
        ];

        $em->method('getRepository')->will($this->returnValueMap($repoMap));

        $model = new AdminBroadcastsModel($em, $adminChecker, $uploadFile, $requestStack, $tokenStorage, $userUtil);
        $response = $model->GetGroupMemberships($orgId);
        $this->assertEquals([
            'Success' => true,
            'Groups' => $groups,
        ], $response);
    }

    public function testHiddenUser()
    {
        $orgId = 5;
        $groupId = 10;
        $groupName = 'Group Name';

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

        $group = $this->getMockBuilder('App\Entity\UserGrps')
            ->disableOriginalConstructor()
            ->getMock();
        $group
            ->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($groupId));
        $group
            ->expects($this->once())
            ->method('getGrpName')
            ->will($this->returnValue($groupName));

        $usersMap = [];
        $orgMembersMap = [];
        $groups = [];
        $grpMembers = [];

        $members = [];
        for ($u = 0; $u < 5; $u++) {
            $grpMember = $this->getMockBuilder('App\Entity\GrpMembers')
                ->disableOriginalConstructor()
                ->getMock();

            $userId = $groupId * 10 + $u;
            $grpMember
                ->expects($this->once())
                ->method('getUserId')
                ->will($this->returnValue($userId));

            $grpMembers[] = $grpMember;

            $orgMember = $this->getMockBuilder('App\Entity\OrgMembers')
                ->disableOriginalConstructor()
                ->getMock();
            $isHidden = ($u % 2) === 0;
            $orgMember->method('getIsHidden')->will($this->returnValue($isHidden));

            $orgMembersMap[] = [
                ['orgId' => $orgId, 'userId' => $userId], null, $orgMember
            ];

            $user = $this->getMockBuilder('App\Entity\Users')
                ->disableOriginalConstructor()
                ->getMock();
            $user->method('getId')->will($this->returnValue($userId));
            $fullname = "$groupName User $userId";
            $user->method('getFullname')->will($this->returnValue($fullname));

            $usersMap[] = [$userId, null, null, $user];

            if (!$isHidden) {
                $members[] = [
                    'UserId' => $userId,
                    'UserName' => $fullname,
                ];
            }
        }

        $groups[$groupName] = $members;

        $groupsRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $groupsRepo
            ->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['orgId' => $orgId]))
            ->will($this->returnValue(new ArrayCollection([$group])));

        $grpMembersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $grpMembersRepo
            ->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['grpId' => $groupId]))
            ->will($this->returnValue($grpMembers));

        $usersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $usersRepo->method('find')->will($this->returnValueMap($usersMap));

        $orgMembersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgMembersRepo->method('findOneBy')->will($this->returnValueMap($orgMembersMap));

        $repoMap = [
            ['App:UserGrps', $groupsRepo],
            ['App:GrpMembers', $grpMembersRepo],
            ['App:Users', $usersRepo],
            ['App:OrgMembers', $orgMembersRepo],
        ];

        $em->method('getRepository')->will($this->returnValueMap($repoMap));

        $model = new AdminBroadcastsModel($em, $adminChecker, $uploadFile, $requestStack, $tokenStorage, $userUtil);
        $response = $model->GetGroupMemberships($orgId);
        $this->assertEquals([
            'Success' => true,
            'Groups' => $groups,
        ], $response);
    }

    public function testEmptyAltUsrName()
    {
        $orgId = 5;
        $groupId = 10;
        $groupName = 'Group Name';
        $userId = 15;
        $usrName = "User Name";

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

        $group = $this->getMockBuilder('App\Entity\UserGrps')
            ->disableOriginalConstructor()
            ->getMock();
        $group
            ->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($groupId));
        $group
            ->expects($this->once())
            ->method('getGrpName')
            ->will($this->returnValue($groupName));

        $grpMember = $this->getMockBuilder('App\Entity\GrpMembers')
            ->disableOriginalConstructor()
            ->getMock();
        $grpMember
            ->expects($this->once())
            ->method('getUserId')
            ->will($this->returnValue($userId));

        $orgMember = $this->getMockBuilder('App\Entity\OrgMembers')
            ->disableOriginalConstructor()
            ->getMock();
        $orgMember->method('getIsHidden')->will($this->returnValue(false));
        $orgMember->method('getAltUsrName')->will($this->returnValue(''));

        $user = $this->getMockBuilder('App\Entity\Users')
            ->disableOriginalConstructor()
            ->getMock();
        $user->method('getId')->will($this->returnValue($userId));
        $user->method('getFullname')->will($this->returnValue($usrName));

        $groups = [];
        $groups[$groupName] = [
            ['UserId' => $userId, 'UserName' => $usrName]
        ];

        $groupsRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $groupsRepo
            ->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['orgId' => $orgId]))
            ->will($this->returnValue(new ArrayCollection([$group])));

        $grpMembersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $grpMembersRepo
            ->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['grpId' => $groupId]))
            ->will($this->returnValue([$grpMember]));

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
            ->will($this->returnValue($orgMember));

        $repoMap = [
            ['App:UserGrps', $groupsRepo],
            ['App:GrpMembers', $grpMembersRepo],
            ['App:Users', $usersRepo],
            ['App:OrgMembers', $orgMembersRepo],
        ];

        $em->method('getRepository')->will($this->returnValueMap($repoMap));

        $model = new AdminBroadcastsModel($em, $adminChecker, $uploadFile, $requestStack, $tokenStorage, $userUtil);
        $response = $model->GetGroupMemberships($orgId);
        $this->assertEquals([
            'Success' => true,
            'Groups' => $groups,
        ], $response);
    }

    public function testNonEmptyAltUsrName()
    {
        $orgId = 5;
        $groupId = 10;
        $groupName = 'Group Name';
        $userId = 15;
        $usrName = "User Name";
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

        $group = $this->getMockBuilder('App\Entity\UserGrps')
            ->disableOriginalConstructor()
            ->getMock();
        $group
            ->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($groupId));
        $group
            ->expects($this->once())
            ->method('getGrpName')
            ->will($this->returnValue($groupName));

        $grpMember = $this->getMockBuilder('App\Entity\GrpMembers')
            ->disableOriginalConstructor()
            ->getMock();
        $grpMember
            ->expects($this->once())
            ->method('getUserId')
            ->will($this->returnValue($userId));

        $orgMember = $this->getMockBuilder('App\Entity\OrgMembers')
            ->disableOriginalConstructor()
            ->getMock();
        $orgMember->method('getIsHidden')->will($this->returnValue(false));
        $orgMember->method('getAltUsrName')->will($this->returnValue($altUsrName));

        $user = $this->getMockBuilder('App\Entity\Users')
            ->disableOriginalConstructor()
            ->getMock();
        $user->method('getId')->will($this->returnValue($userId));
        $user->method('getFullname')->will($this->returnValue($usrName));

        $groups = [];
        $groups[$groupName] = [
            ['UserId' => $userId, 'UserName' => $altUsrName]
        ];

        $groupsRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $groupsRepo
            ->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['orgId' => $orgId]))
            ->will($this->returnValue(new ArrayCollection([$group])));

        $grpMembersRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $grpMembersRepo
            ->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['grpId' => $groupId]))
            ->will($this->returnValue([$grpMember]));

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
            ->will($this->returnValue($orgMember));

        $repoMap = [
            ['App:UserGrps', $groupsRepo],
            ['App:GrpMembers', $grpMembersRepo],
            ['App:Users', $usersRepo],
            ['App:OrgMembers', $orgMembersRepo],
        ];

        $em->method('getRepository')->will($this->returnValueMap($repoMap));

        $model = new AdminBroadcastsModel($em, $adminChecker, $uploadFile, $requestStack, $tokenStorage, $userUtil);
        $response = $model->GetGroupMemberships($orgId);
        $this->assertEquals([
            'Success' => true,
            'Groups' => $groups,
        ], $response);
    }
}
