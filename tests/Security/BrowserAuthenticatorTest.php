<?php

namespace App\Tests\Security;

use App\Security\BrowserAuthenticator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class BrowserAuthenticatorTest extends TestCase
{
    protected function setUpEncoder()
    {
        $encoder = $this->getMockBuilder('Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface')
            ->disableOriginalConstructor()
            ->getMock();

        return $encoder;
    }

    protected function setUpPwdHelper()
    {
        $helper = $this->getMockBuilder('App\Security\PwdHelper')
            ->disableOriginalConstructor()
            ->getMock();

        return $helper;
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     * @expectedExceptionMessage Invalid username or password
     */
    public function testAuthenticateTokenInvalidUser()
    {
        $username = 'John Doe';
        $providerKey = 'Key';

        $tokenInterface = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\TokenInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $tokenInterface->expects($this->once())
            ->method('getUsername')
            ->will($this->returnValue($username));

        $userProvider = $this->getMockBuilder('Symfony\Component\Security\Core\User\UserProviderInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $userProvider->expects($this->once())
            ->method('loadUserByUsername')
            ->with($this->equalTo($username))
            ->will($this->throwException(new UsernameNotFoundException()));

        $encoder = $this->setUpEncoder();
        $pwdHelper = $this->setUpPwdHelper();

        $authenticator = new BrowserAuthenticator($encoder, $pwdHelper);
        $authenticator->authenticateToken($tokenInterface, $userProvider, $providerKey);
    }

    public function testAuthenticateTokenMigratePassword()
    {
        $credentials = 'User Credentials';
        $username = 'John Doe';
        $providerKey = 'Key';
        $roles = ['Roles'];

        $tokenInterface = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\TokenInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $tokenInterface->expects($this->once())
            ->method('getUsername')
            ->will($this->returnValue($username));
        $tokenInterface->expects($this->exactly(2))
            ->method('getCredentials')
            ->will($this->returnValue($credentials));

        $user = $this->getMockBuilder('App\Entity\Users')
            ->disableOriginalConstructor()
            ->getMock();
        $user->expects($this->exactly(2))
            ->method('getPassword')
            ->will($this->returnValue(''));
        $user->expects($this->once())
            ->method('getRoles')
            ->will($this->returnValue($roles));

        $userProvider = $this->getMockBuilder('Symfony\Component\Security\Core\User\UserProviderInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $userProvider->expects($this->once())
            ->method('loadUserByUsername')
            ->with($this->equalTo($username))
            ->will($this->returnValue($user));

        $encoder = $this->setUpEncoder();
        $pwdHelper = $this->setUpPwdHelper();

        $pwdHelper->expects($this->once())
            ->method('MigratePassword')
            ->with($this->equalTo($user), $this->equalTo($credentials));

        $encoder->expects($this->once())
            ->method('isPasswordValid')
            ->with($this->equalTo($user), $this->equalTo($credentials))
            ->will($this->returnValue(true));

        $authenticator = new BrowserAuthenticator($encoder, $pwdHelper);
        $authenticator->authenticateToken($tokenInterface, $userProvider, $providerKey);
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     * @expectedExceptionMessage Invalid username or password
     */
    public function testAuthenticateTokenInvalidPassword()
    {
        $credentials = 'User Credentials';
        $username = 'John Doe';
        $providerKey = 'Key';
        $password = 'User Password';

        $tokenInterface = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\TokenInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $tokenInterface->expects($this->once())
            ->method('getUsername')
            ->will($this->returnValue($username));
        $tokenInterface->expects($this->once())
            ->method('getCredentials')
            ->will($this->returnValue($credentials));

        $user = $this->getMockBuilder('App\Entity\Users')
            ->disableOriginalConstructor()
            ->getMock();
        $user->expects($this->once())
            ->method('getPassword')
            ->will($this->returnValue($password));

        $userProvider = $this->getMockBuilder('Symfony\Component\Security\Core\User\UserProviderInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $userProvider->expects($this->once())
            ->method('loadUserByUsername')
            ->with($this->equalTo($username))
            ->will($this->returnValue($user));

        $encoder = $this->setUpEncoder();
        $pwdHelper = $this->setUpPwdHelper();

        $encoder->expects($this->once())
            ->method('isPasswordValid')
            ->with($this->equalTo($user), $this->equalTo($credentials))
            ->will($this->returnValue(false));

        $authenticator = new BrowserAuthenticator($encoder, $pwdHelper);
        $authenticator->authenticateToken($tokenInterface, $userProvider, $providerKey);
    }
}
