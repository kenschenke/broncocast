<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;

/*
 * TODO: find a way to force rememberme for app logins
 */

class AppAuthenticator extends AbstractFormLoginAuthenticator
{
    private $security;
    private $encoder;
    private $router;
    private $pwdHelper;

    public function __construct(PwdHelper $pwdHelper, Security $security,
                                UserPasswordEncoderInterface $encoder, RouterInterface $router)
    {
        $this->pwdHelper = $pwdHelper;
        $this->security = $security;
        $this->encoder = $encoder;
        $this->router = $router;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        // Check to see if the password needs to be migrated
        $password = $user->getPassword();
        if (empty($password)) {
            $this->pwdHelper->MigratePassword($user, $credentials['password']);
        }

        return $this->encoder->isPasswordValid($user, $credentials['password']);
    }

    public function getCredentials(Request $request)
    {
        return [
            'username' => $request->request->get('_username'),
            'password' => $request->request->get('_password'),
        ];
    }

    public function getLoginUrl()
    {
        return $this->router->generate('auth_login');
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        try {
            $user = $userProvider->loadUserByUsername($credentials['username']);
        } catch (UsernameNotFoundException $e) {
            throw new AuthenticationException('Invalid username or password');
        }

        return $user;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new JsonResponse(['Success' => false, 'Error' => $exception->getMessage()]);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return new JsonResponse(['Success' => true]);
    }

    public function supports(Request $request)
    {
        // If the user is already authenticated, don't need to do it again
        if ($this->security->getUser()) {
            return false;
        }

        // The user is not authenticated, so the authenticator should continue
        return $request->request->has('applogin');
    }
}
