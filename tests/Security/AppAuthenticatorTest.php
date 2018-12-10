<?php

namespace App\Tests\Security;

use App\Security\AppAuthenticator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class AppAuthenticatorTest extends TestCase
{
    protected function mockAppModel()
    {
        return $this->getMockBuilder('App\Model\AppModel')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function mockEncoder()
    {
        return $this->getMockBuilder('Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function mockPwdHelper()
    {
        return $this->getMockBuilder('App\Security\PwdHelper')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function mockRouter()
    {
        return $this->getMockBuilder('Symfony\Component\Routing\RouterInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function mockTokenStorage()
    {
        return $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function mockUser()
    {
        return $this->getMockBuilder('App\Entity\Users')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testCheckCredentialsWithMigration()
    {
        $password = '';
        $suppliedPassword = 'User Supplied Password';
        $credentials = ['password' => $suppliedPassword];

        $user = $this->mockUser();
        $pwdHelper = $this->mockPwdHelper();
        $tokenStorage = $this->mockTokenStorage();
        $encoder = $this->mockEncoder();
        $router = $this->mockRouter();
        $appModel = $this->mockAppModel();

        $user->expects($this->once())
            ->method('getPassword')
            ->will($this->returnValue($password));
        $pwdHelper->expects($this->once())
            ->method('MigratePassword')
            ->with($this->equalTo($user), $this->equalTo($suppliedPassword));
        $encoder->expects($this->once())
            ->method('isPasswordValid')
            ->with($this->equalTo($user), $this->equalTo($suppliedPassword))
            ->will($this->returnValue(true));

        $appAuthenticator = new AppAuthenticator($pwdHelper, $tokenStorage, $encoder, $router, $appModel);
        $this->assertTrue($appAuthenticator->checkCredentials($credentials, $user));
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     * @expectedExceptionMessage Invalid username or password
     */
    public function testCheckCredentialsWithInvalidPassword()
    {
        $password = 'Stored Password';
        $suppliedPassword = 'User Supplied Password';
        $credentials = ['password' => $suppliedPassword];

        $user = $this->mockUser();
        $pwdHelper = $this->mockPwdHelper();
        $tokenStorage = $this->mockTokenStorage();
        $encoder = $this->mockEncoder();
        $router = $this->mockRouter();
        $appModel = $this->mockAppModel();

        $user->expects($this->once())
            ->method('getPassword')
            ->will($this->returnValue($password));
        $pwdHelper->expects($this->never())
            ->method('MigratePassword')
            ->with($this->equalTo($user), $this->equalTo($suppliedPassword));
        $encoder->expects($this->once())
            ->method('isPasswordValid')
            ->with($this->equalTo($user), $this->equalTo($suppliedPassword))
            ->will($this->returnValue(false));

        $appAuthenticator = new AppAuthenticator($pwdHelper, $tokenStorage, $encoder, $router, $appModel);
        $appAuthenticator->checkCredentials($credentials, $user);
    }

    public function testCheckCredentialsWithValidPassword()
    {
        $password = 'Stored Password';
        $suppliedPassword = 'User Supplied Password';
        $credentials = ['password' => $suppliedPassword];

        $user = $this->mockUser();
        $pwdHelper = $this->mockPwdHelper();
        $tokenStorage = $this->mockTokenStorage();
        $encoder = $this->mockEncoder();
        $router = $this->mockRouter();
        $appModel = $this->mockAppModel();

        $user->expects($this->once())
            ->method('getPassword')
            ->will($this->returnValue($password));
        $pwdHelper->expects($this->never())
            ->method('MigratePassword')
            ->with($this->equalTo($user), $this->equalTo($suppliedPassword));
        $encoder->expects($this->once())
            ->method('isPasswordValid')
            ->with($this->equalTo($user), $this->equalTo($suppliedPassword))
            ->will($this->returnValue(true));

        $appAuthenticator = new AppAuthenticator($pwdHelper, $tokenStorage, $encoder, $router, $appModel);
        $this->assertTrue($appAuthenticator->checkCredentials($credentials, $user));
    }

    public function testGetCredentials()
    {
        $username = 'User Name';
        $password = 'User Password';

        $expectedResult = [
            'username' => $username,
            'password' => $password,
        ];

        $paramsMap = [
            ['_username', null, $username],
            ['_password', null, $password],
        ];

        $params = $this->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();
        $params->method('get')->will($this->returnValueMap($paramsMap));

        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $request->request = $params;

        $pwdHelper = $this->mockPwdHelper();
        $tokenStorage = $this->mockTokenStorage();
        $encoder = $this->mockEncoder();
        $router = $this->mockRouter();
        $appModel = $this->mockAppModel();

        $appAuthenticator = new AppAuthenticator($pwdHelper, $tokenStorage, $encoder, $router, $appModel);
        $this->assertSame($expectedResult, $appAuthenticator->getCredentials($request));
    }

    public function testGetLoginUrl()
    {
        $expectedValue = 'Generated Route';

        $pwdHelper = $this->mockPwdHelper();
        $tokenStorage = $this->mockTokenStorage();
        $encoder = $this->mockEncoder();
        $router = $this->mockRouter();
        $appModel = $this->mockAppModel();

        $router->expects($this->once())
            ->method('generate')
            ->with($this->equalTo('auth_login'))
            ->will($this->returnValue($expectedValue));

        $appAuthenticator = new AppAuthenticator($pwdHelper, $tokenStorage, $encoder, $router, $appModel);
        $this->assertSame($expectedValue, $appAuthenticator->getLoginUrl());
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     * @expectedExceptionMessage Invalid username or password
     */
    public function testGetUserFails()
    {
        $username = 'Supplied User Name';
        $credentials = ['username' => $username];

        $pwdHelper = $this->mockPwdHelper();
        $tokenStorage = $this->mockTokenStorage();
        $encoder = $this->mockEncoder();
        $router = $this->mockRouter();
        $appModel = $this->mockAppModel();

        $userProvider = $this->getMockBuilder('Symfony\Component\Security\Core\User\UserProviderInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $userProvider->expects($this->once())
            ->method('loadUserByUsername')
            ->with($this->equalTo($username))
            ->will($this->throwException(new UsernameNotFoundException()));

        $appAuthenticator = new AppAuthenticator($pwdHelper, $tokenStorage, $encoder, $router, $appModel);
        $appAuthenticator->getUser($credentials, $userProvider);
    }

    public function testGetUserSucceeds()
    {
        $username = 'Supplied User Name';
        $credentials = ['username' => $username];

        $user = $this->mockUser();
        $pwdHelper = $this->mockPwdHelper();
        $tokenStorage = $this->mockTokenStorage();
        $encoder = $this->mockEncoder();
        $router = $this->mockRouter();
        $appModel = $this->mockAppModel();

        $userProvider = $this->getMockBuilder('Symfony\Component\Security\Core\User\UserProviderInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $userProvider->expects($this->once())
            ->method('loadUserByUsername')
            ->with($this->equalTo($username))
            ->will($this->returnValue($user));

        $appAuthenticator = new AppAuthenticator($pwdHelper, $tokenStorage, $encoder, $router, $appModel);
        $this->assertSame($user, $appAuthenticator->getUser($credentials, $userProvider));
    }

    public function testSupportsAlreadyAuthenticated()
    {
        $pwdHelper = $this->mockPwdHelper();
        $tokenStorage = $this->mockTokenStorage();
        $encoder = $this->mockEncoder();
        $router = $this->mockRouter();
        $appModel = $this->mockAppModel();

        $user = $this->mockUser();

        $token = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\TokenInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $token->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue($user));
        $tokenStorage->expects($this->once())
            ->method('getToken')
            ->will($this->returnValue($token));

        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $appAuthenticator = new AppAuthenticator($pwdHelper, $tokenStorage, $encoder, $router, $appModel);
        $this->assertFalse($appAuthenticator->supports($request));
    }

    public function testSupportsNotAuthenticatedAndNoAppLogin()
    {
        $pwdHelper = $this->mockPwdHelper();
        $tokenStorage = $this->mockTokenStorage();
        $encoder = $this->mockEncoder();
        $router = $this->mockRouter();
        $appModel = $this->mockAppModel();

        $token = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\TokenInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $token->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue(null));
        $tokenStorage->expects($this->once())
            ->method('getToken')
            ->will($this->returnValue($token));

        $params = $this->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();
        $params->expects($this->once())
            ->method('has')
            ->with($this->equalTo('applogin'))
            ->will($this->returnValue(false));

        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $request->request = $params;

        $appAuthenticator = new AppAuthenticator($pwdHelper, $tokenStorage, $encoder, $router, $appModel);
        $this->assertFalse($appAuthenticator->supports($request));
    }

    public function testSupportsNotAuthenticatedWithAppLogin()
    {
        $pwdHelper = $this->mockPwdHelper();
        $tokenStorage = $this->mockTokenStorage();
        $encoder = $this->mockEncoder();
        $router = $this->mockRouter();
        $appModel = $this->mockAppModel();

        $token = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\TokenInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $token->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue(null));
        $tokenStorage->expects($this->once())
            ->method('getToken')
            ->will($this->returnValue($token));

        $params = $this->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();
        $params->expects($this->once())
            ->method('has')
            ->with($this->equalTo('applogin'))
            ->will($this->returnValue(true));

        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $request->request = $params;

        $appAuthenticator = new AppAuthenticator($pwdHelper, $tokenStorage, $encoder, $router, $appModel);
        $this->assertTrue($appAuthenticator->supports($request));
    }
}
