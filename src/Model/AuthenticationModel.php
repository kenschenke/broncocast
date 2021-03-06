<?php

namespace App\Model;

use App\Entity\Contacts;
use App\Entity\OrgMembers;
use App\Entity\Users;
use App\Security\PwdHelper;
use App\Util\MessageUtil;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AuthenticationModel
{
    protected $twig;
    protected $authenticationUtils;
    protected $requestStack;
    protected $em;
    protected $pwdHelper;
    protected $messageUtil;
    protected $tokenStorage;
    protected $checker;
    protected $appModel;

    public function __construct(\Twig_Environment $twig, AuthenticationUtils $authenticationUtils,
                                RequestStack $requestStack, EntityManagerInterface $em, PwdHelper $pwdHelper,
                                MessageUtil $messageUtil, TokenStorageInterface $tokenStorage,
                                AuthorizationCheckerInterface $checker, AppModel $appModel)
    {
        $this->twig = $twig;
        $this->authenticationUtils = $authenticationUtils;
        $this->requestStack = $requestStack;
        $this->em = $em;
        $this->pwdHelper = $pwdHelper;
        $this->messageUtil = $messageUtil;
        $this->tokenStorage = $tokenStorage;
        $this->checker = $checker;
        $this->appModel = $appModel;
    }

    public function AppAuthenticate()
    {
        $IsAuth = false;
        $AdminOrgs = [];
        $IsSystemAdmin = false;

        if ($this->checker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $request = $this->requestStack->getCurrentRequest();
            if ($request->query->has('DeviceToken')) {
                $Token = $request->query->get('DeviceToken');
                $Type = $request->query->get('DeviceType', Contacts::TYPE_APPLE);
                $this->appModel->SaveDeviceToken($Token, $Type);
            }

            $params = $this->appModel->GetAppParams(false);
            $IsAuth = true;
            $AdminOrgs = $params['AdminOrgs'];
            $IsSystemAdmin = $params['IsSystemAdmin'];
        }

        return [
            'IsAuth' => $IsAuth,
            'AdminOrgs' => $AdminOrgs,
            'IsSystemAdmin' => $IsSystemAdmin,
        ];
    }

    /**
     * @return Response
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function Login()
    {
        // Get the login error if there is one
        $error = $this->authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $this->authenticationUtils->getLastUsername();

        return new Response($this->twig->render('login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]));
    }

    public function Register($OrgTag = null)
    {
        try {
            $request = $this->requestStack->getCurrentRequest();
            if (!$request->request->has('email')) {
                throw new \Exception('email parameter missing');
            }
            if (!$request->request->has('password1')) {
                throw new \Exception('password1 parameter missing');
            }
            if (!$request->request->has('password2')) {
                throw new \Exception('password2 parameter missing');
            }

            $email = strtolower(trim($request->request->get('email')));
            $password1 = trim($request->request->get('password1'));
            $password2 = trim($request->request->get('password2'));

            $Contact = $this->em->getRepository('App:Contacts')->findOneBy(['contact' => $email]);
            if (!is_null($Contact)) {
                throw new \Exception('An account with that email address already exists');
            }

            if ($password1 !== $password2) {
                throw new \Exception('Passwords do not match');
            }

            $User = new Users();
            $User->setFullname('');
            $User->setLegacyPassword('');
            $User->setSalt('');
            $User->setIsActive(true);
            $User->setSingleMsg(false);
            $this->pwdHelper->SaveUserPassword($User, $password1);
            $this->em->persist($User);
            $this->em->flush();

            if (is_null($OrgTag)) {
                $OrgTag = getenv('DEFAULT_ORG_TAG');
            }
            $Org = $this->em->getRepository('App:Orgs')->findOneBy(['tag' => $OrgTag]);
            if (is_null($Org)) {
                throw new \Exception('Unrecognized organization tag');
            }
            $OrgMember = new OrgMembers();
            $OrgMember->setUser($User);
            $OrgMember->setOrg($Org);
            $OrgMember->setIsAdmin(false);
            $OrgMember->setIsApproved(false);
            $OrgMember->setIsHidden(false);
            $this->em->persist($OrgMember);

            $Contact = new Contacts();
            $Contact->setUser($User);
            $Contact->setContact($email);
            $Contact->setContactType(Contacts::TYPE_EMAIL);
            $this->em->persist($Contact);
            $this->em->flush();

            return new Response($this->twig->render('silentlogin.html.twig', [
                'username' => $email,
                'password' => $password1,
                'redirect' => '/register'
            ]));
        } catch (\Exception $e) {
            return new Response($this->twig->render('register.html.twig', [
                'err' => $e->getMessage()
            ]));
        }
    }

    public function Recover($ResetStr)
    {
        $user = $this->em->getRepository('App:Users')->findOneBy(['resetStr' => $ResetStr]);
        if (is_null($user)) {
            return new Response($this->twig->render('accountTokenNotFound.html.twig'));
        }

        $resetExpire = $user->getResetExpire();
        $now = new \DateTime();
        if (is_null($resetExpire) || $resetExpire < $now) {
            return new Response($this->twig->render('recover.html.twig', [
                'email' => '',
                'err' => 'The link has expired. Please enter your email for a new link.',
                'msg' => '',
            ]));
        }

        return new Response($this->twig->render('resetpwd.html.twig', [
            'resetStr' => $ResetStr,
            'err' => '',
        ]));
    }

    public function SendRecoverEmail()
    {
        try {
            $request = $this->requestStack->getCurrentRequest();
            if (!$request->request->has('email')) {
                throw new \Exception('Email address missing.');
            }

            $email = strtolower(trim($request->request->get('email')));
            $contact = $this->em->getRepository('App:Contacts')->findOneBy(['contact' => $email]);
            if (is_null($contact)) {
                throw new \Exception('A user account with that email address could not be located.');
            }
            $user = $contact->getUser();
            $this->pwdHelper->SendResetEmail($user, $email);
            $this->em->persist($user);
            $this->em->flush();
        } catch (\Exception $e) {
            return new Response($this->twig->render('recover.html.twig', [
                'email' => $email,
                'err' => $e->getMessage(),
                'msg' => ''
            ]));
        }

        return new Response($this->twig->render('recover.html.twig', [
            'email' => $email,
            'err' => '',
            'msg' => 'A message was sent to the email address containing a link to recover the account.'
        ]));
    }

    public function SendWelcome()
    {
        try {
            $User = $this->tokenStorage->getToken()->getUser();
            foreach ($User->getContacts() as $Contact) {
                $phone = $Contact->getContact();
                if ($this->messageUtil->IsPhone($phone)) {
                    $recip = [
                        'ContactId' => $Contact->getId(),
                        'Phone' => $phone
                    ];
                    $this->messageUtil->SendSMS([$recip],
                        'Thank you for registering with BroncoCast! ' .
                        'Reply HELP for help. Reply STOP to unsubscribe. ' .
                        'Msg&Data rates may apply.');
                }
            }

            return ['Success' => true];
        } catch (\Exception $e) {
            return ['Success' => false, 'Error' => $e->getMessage()];
        }
    }

    public function UpdatePassword()
    {
        $request = $this->requestStack->getCurrentRequest();
        $ResetStr = $request->request->get('resetstr', '');

        try {
            if (!$request->request->has('resetstr')) {
                throw new \Exception('resetstr parameter missing');
            }
            if (!$request->request->has('password1')) {
                throw new \Exception('password1 parameter missing');
            }
            if (!$request->request->has('password2')) {
                throw new \Exception('password2 parameter missing');
            }

            $ResetStr = $request->request->get('resetstr');
            $Password1 = trim($request->request->get('password1'));
            $Password2 = trim($request->request->get('password2'));

            if (empty($Password1) || empty($Password2)) {
                throw new \Exception('Password cannot be empty');
            }
            if ($Password1 !== $Password2) {
                throw new \Exception('Passwords do not match');
            }

            $User = $this->em->getRepository('App:Users')->findOneBy(['resetStr' => $ResetStr]);
            if (is_null($User)) {
                throw new \Exception('Account not found.');
            }

            $this->pwdHelper->SaveUserPassword($User, $Password1);
            $User->setResetStr(null);
            $User->setResetExpire(null);
            $this->em->persist($User);
            $this->em->flush();

            $Contacts = $User->getContacts();
            if (!$Contacts->isEmpty()) {
                return new Response($this->twig->render('silentlogin.html.twig', [
                    'username' => $Contacts[0]->getContact(),
                    'password' => $Password1,
                ]));
            } else {
                return new Response($this->twig->render('login.html.twig'));
            }
        } catch (\Exception $e) {
            return new Response($this->twig->render('resetpwd.html.twig', [
                'resetStr' => $ResetStr,
                'err' => '',
            ]));
        }
    }
}
