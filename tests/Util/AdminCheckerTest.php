<?php

namespace App\Tests\Util;

use App\Util\AdminChecker;
use PHPUnit\Framework\TestCase;

class AdminCheckerTest extends TestCase
{
    private function setUpOrgMember($orgId, $isAdmin)
    {
        $orgMember = $this->getMockBuilder('App\Entity\OrgMembers')
            ->disableOriginalConstructor()
            ->getMock();
        $orgMember->method('getOrgId')->will($this->returnValue($orgId));
        $orgMember->method('getIsAdmin')->will($this->returnValue($isAdmin));

        return $orgMember;
    }

    private function setupTokenStorage($user)
    {
        $token = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\TokenInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $token->method('getUser')->will($this->returnValue($user));

        $tokenStorage = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $tokenStorage->method('getToken')->will($this->returnValue($token));

        return $tokenStorage;
    }

    public function testIsAdminUserIsSystemAdmin()
    {
        $orgIdParam = '2';
        $roles = ['ROLE_ROLE1', 'ROLE_SYSTEM_ADMIN', 'ROLE_ROLE2'];

        $user = $this->getMockBuilder('App\Entity\Users')
            ->disableOriginalConstructor()
            ->getMock();
        $user->method('getRoles')->will($this->returnValue($roles));

        $tokenStorage = $this->setupTokenStorage($user);
        $adminChecker = new AdminChecker($tokenStorage);
        $this->assertTrue($adminChecker->IsAdminUser($orgIdParam));
    }

    public function testIsAdminUserIsOrgAdmin()
    {
        $orgIdParam = '3';
        $roles = ['ROLE_ROLE1', 'ROLE_ROLE2'];

        $orgNonAdmin = $this->setUpOrgMember(2, false);
        $orgAdmin = $this->setUpOrgMember(3, true);

        $user = $this->getMockBuilder('App\Entity\Users')
            ->disableOriginalConstructor()
            ->getMock();
        $user->method('getRoles')->will($this->returnValue($roles));
        $user->method('getOrgs')->will($this->returnValue([$orgNonAdmin, $orgAdmin]));

        $tokenStorage = $this->setupTokenStorage($user);
        $adminChecker = new AdminChecker($tokenStorage);
        $this->assertTrue($adminChecker->IsAdminUser($orgIdParam));
    }

    public function testIsAdminUserIsNotOrgAdmin()
    {
        $orgIdParam = '3';
        $roles = ['ROLE_ROLE1', 'ROLE_ROLE2'];

        $orgNonAdmin = $this->setUpOrgMember(2, false);
        $orgAdmin = $this->setUpOrgMember(3, false);

        $user = $this->getMockBuilder('App\Entity\Users')
            ->disableOriginalConstructor()
            ->getMock();
        $user->method('getRoles')->will($this->returnValue($roles));
        $user->method('getOrgs')->will($this->returnValue([$orgNonAdmin, $orgAdmin]));

        $tokenStorage = $this->setupTokenStorage($user);
        $adminChecker = new AdminChecker($tokenStorage);
        $this->assertFalse($adminChecker->IsAdminUser($orgIdParam));
    }

    public function testIsSystemAdminFalse()
    {
        $roles = ['ROLE_ROLE1', 'ROLE_ROLE2'];

        $user = $this->getMockBuilder('App\Entity\Users')
            ->disableOriginalConstructor()
            ->getMock();
        $user->method('getRoles')->will($this->returnValue($roles));

        $tokenStorage = $this->setupTokenStorage($user);
        $adminChecker = new AdminChecker($tokenStorage);
        $this->assertFalse($adminChecker->IsSystemAdmin());
    }

    public function testIsSystemAdminTrue()
    {
        $roles = ['ROLE_ROLE1', 'ROLE_SYSTEM_ADMIN', 'ROLE_ROLE2'];

        $user = $this->getMockBuilder('App\Entity\Users')
            ->disableOriginalConstructor()
            ->getMock();
        $user->method('getRoles')->will($this->returnValue($roles));

        $tokenStorage = $this->setupTokenStorage($user);
        $adminChecker = new AdminChecker($tokenStorage);
        $this->assertTrue($adminChecker->IsSystemAdmin());
    }
}
