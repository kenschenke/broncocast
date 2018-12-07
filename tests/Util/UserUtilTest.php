<?php

namespace App\Tests\Util;

use App\Util\UserUtil;
use PHPUnit\Framework\TestCase;

class UserUtilTest extends TestCase
{
    public function testBadUserId()
    {
        $userId = 5;

        $userRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $userRepo->expects($this->once())
            ->method('find')
            ->with($this->equalTo($userId))
            ->will($this->returnValue(null));

        $em = $this->getMockBuilder('Doctrine\ORM\EntityManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $em->method('getRepository')->will($this->returnValue($userRepo));

        $userUtil = new UserUtil($em);
        $this->assertEmpty($userUtil->GetUserName($userId));
    }

    public function testWithNoOrgId()
    {
        $userId = 5;
        $fullName = 'John Doe';

        $user = $this->getMockBuilder('App\Entity\Users')
            ->disableOriginalConstructor()
            ->getMock();
        $user->expects($this->once())
            ->method('getFullname')
            ->will($this->returnValue($fullName));

        $userRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $userRepo->expects($this->once())
            ->method('find')
            ->with($this->equalTo($userId))
            ->will($this->returnValue($user));

        $em = $this->getMockBuilder('Doctrine\ORM\EntityManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $em->method('getRepository')->will($this->returnValue($userRepo));

        $userUtil = new UserUtil($em);
        $this->assertEquals($fullName, $userUtil->GetUserName($userId));
    }

    public function testUserNotMemberOfOrg()
    {
        $userId = 5;
        $orgId = 10;
        $fullName = 'John Doe';

        $user = $this->getMockBuilder('App\Entity\Users')
            ->disableOriginalConstructor()
            ->getMock();
        $user->expects($this->once())
            ->method('getFullname')
            ->will($this->returnValue($fullName));

        $userRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $userRepo->expects($this->once())
            ->method('find')
            ->with($this->equalTo($userId))
            ->will($this->returnValue($user));

        $orgMemberRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgMemberRepo->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(['userId' => $userId, 'orgId' => $orgId]))
            ->will($this->returnValue(null));

        $repoMap = [
            ['App:OrgMembers', $orgMemberRepo],
            ['App:Users', $userRepo],
        ];

        $em = $this->getMockBuilder('Doctrine\ORM\EntityManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $em->method('getRepository')->will($this->returnValueMap($repoMap));

        $userUtil = new UserUtil($em);
        $this->assertEquals($fullName, $userUtil->GetUserName($userId, $orgId));
    }

    public function testUserHasNoAltName()
    {
        $userId = 5;
        $orgId = 10;
        $fullName = 'John Doe';

        $user = $this->getMockBuilder('App\Entity\Users')
            ->disableOriginalConstructor()
            ->getMock();
        $user->expects($this->once())
            ->method('getFullname')
            ->will($this->returnValue($fullName));

        $userRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $userRepo->expects($this->once())
            ->method('find')
            ->with($this->equalTo($userId))
            ->will($this->returnValue($user));

        $orgMember = $this->getMockBuilder('App\Entity\OrgMembers')
            ->disableOriginalConstructor()
            ->getMock();
        $orgMember->expects($this->once())
            ->method('getAltUsrName')
            ->will($this->returnValue(null));

        $orgMemberRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgMemberRepo->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(['userId' => $userId, 'orgId' => $orgId]))
            ->will($this->returnValue($orgMember));

        $repoMap = [
            ['App:OrgMembers', $orgMemberRepo],
            ['App:Users', $userRepo],
        ];

        $em = $this->getMockBuilder('Doctrine\ORM\EntityManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $em->method('getRepository')->will($this->returnValueMap($repoMap));

        $userUtil = new UserUtil($em);
        $this->assertEquals($fullName, $userUtil->GetUserName($userId, $orgId));
    }

    public function testUserHasEmptyAltName()
    {
        $userId = 5;
        $orgId = 10;
        $fullName = 'John Doe';

        $user = $this->getMockBuilder('App\Entity\Users')
            ->disableOriginalConstructor()
            ->getMock();
        $user->expects($this->once())
            ->method('getFullname')
            ->will($this->returnValue($fullName));

        $userRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $userRepo->expects($this->once())
            ->method('find')
            ->with($this->equalTo($userId))
            ->will($this->returnValue($user));

        $orgMember = $this->getMockBuilder('App\Entity\OrgMembers')
            ->disableOriginalConstructor()
            ->getMock();
        $orgMember->expects($this->once())
            ->method('getAltUsrName')
            ->will($this->returnValue(''));

        $orgMemberRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgMemberRepo->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(['userId' => $userId, 'orgId' => $orgId]))
            ->will($this->returnValue($orgMember));

        $repoMap = [
            ['App:OrgMembers', $orgMemberRepo],
            ['App:Users', $userRepo],
        ];

        $em = $this->getMockBuilder('Doctrine\ORM\EntityManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $em->method('getRepository')->will($this->returnValueMap($repoMap));

        $userUtil = new UserUtil($em);
        $this->assertEquals($fullName, $userUtil->GetUserName($userId, $orgId));
    }

    public function testUserHasAltName()
    {
        $userId = 5;
        $orgId = 10;
        $fullName = 'John Doe';
        $altUserName = 'John Doe Alternate';

        $user = $this->getMockBuilder('App\Entity\Users')
            ->disableOriginalConstructor()
            ->getMock();
        $user->expects($this->never())
            ->method('getFullname')
            ->will($this->returnValue($fullName));

        $userRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $userRepo->expects($this->once())
            ->method('find')
            ->with($this->equalTo($userId))
            ->will($this->returnValue($user));

        $orgMember = $this->getMockBuilder('App\Entity\OrgMembers')
            ->disableOriginalConstructor()
            ->getMock();
        $orgMember->expects($this->once())
            ->method('getAltUsrName')
            ->will($this->returnValue($altUserName));

        $orgMemberRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgMemberRepo->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(['userId' => $userId, 'orgId' => $orgId]))
            ->will($this->returnValue($orgMember));

        $repoMap = [
            ['App:OrgMembers', $orgMemberRepo],
            ['App:Users', $userRepo],
        ];

        $em = $this->getMockBuilder('Doctrine\ORM\EntityManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $em->method('getRepository')->will($this->returnValueMap($repoMap));

        $userUtil = new UserUtil($em);
        $this->assertEquals($altUserName, $userUtil->GetUserName($userId, $orgId));
    }
}
