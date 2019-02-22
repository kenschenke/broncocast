<?php

namespace App\Security;

use App\Entity\Contacts;
use App\Model\AppModel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class AppAuthenticator extends AbstractGuardAuthenticator
{
    private $tokenStorage;
    private $encoder;
    private $router;
    private $pwdHelper;
    private $appModel;

    public function __construct(PwdHelper $pwdHelper, TokenStorageInterface $tokenStorage,
                                UserPasswordEncoderInterface $encoder, RouterInterface $router,
                                AppModel $appModel)
    {
        $this->pwdHelper = $pwdHelper;
        $this->tokenStorage = $tokenStorage;
        $this->encoder = $encoder;
        $this->router = $router;
        $this->appModel = $appModel;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        // Check to see if the password needs to be migrated
        $password = $user->getPassword();
        if (empty($password)) {
            $this->pwdHelper->MigratePassword($user, $credentials['password']);
        }

        if (!$this->encoder->isPasswordValid($user, $credentials['password'])) {
            throw new AuthenticationException('Invalid username or password');
        }

        return true;
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

    /*
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new JsonResponse(['Success' => false, 'Error' => $exception->getMessage()]);
    }
    */

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $params = $this->appModel->GetAppParams(false);

        if ($request->request->has('DeviceToken')) {
            $Token = $request->request->get('DeviceToken');
            $Type = $request->request->get('DeviceType', Contacts::TYPE_APPLE);
            $this->appModel->SaveDeviceToken($Token, $Type);
        }

        return new JsonResponse([
            'Success' => true,
            'AdminOrgs' => $params['AdminOrgs'],
            'IsSystemAdmin' => $params['IsSystemAdmin'],
        ]);
    }

    public function supports(Request $request)
    {
        // If the user is already authenticated, don't need to do it again
        $token = $this->tokenStorage->getToken();
        if ($token && $token->getUser()) {
            return false;
        }

        // The user is not authenticated, so the authenticator should continue
        return $request->request->has('applogin');
    }

    public function supportsRememberMe()
    {
        return true;
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        $url = $this->getLoginUrl();

        return new RedirectResponse($url);
    }
}
