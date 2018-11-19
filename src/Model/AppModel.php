<?php

namespace App\Model;

use App\Security\PwdHelper;
use App\Util\AdminChecker;
use App\Util\MessageUtil;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AppModel
{
    private $adminChecker;
    private $em;
    private $tokenStorage;
    private $requestStack;
    private $messageUtil;
    private $pwdHelper;

    public function __construct(AdminChecker $adminChecker, EntityManagerInterface $em,
                                TokenStorageInterface $tokenStorage, RequestStack $requestStack,
                                MessageUtil $messageUtil, PwdHelper $pwdHelper)
    {
        $this->adminChecker = $adminChecker;
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
        $this->requestStack = $requestStack;
        $this->messageUtil = $messageUtil;
        $this->pwdHelper = $pwdHelper;
    }

    public function GetAppParams($ForJavascript = true)
    {
        $IsSystemAdmin = $this->adminChecker->IsSystemAdmin();

        $MemberOrgs = [];
        $UserId = $this->tokenStorage->getToken()->getUser()->getId();
        $OrgMembers = $this->em->getRepository('App:OrgMembers')->findBy(['userId' => $UserId]);
        foreach ($OrgMembers as $member) {
            $MemberOrgs[] = $member->getOrgId();
        }

        $AdminOrgs = [];
        $FoundAdminDefault = false;
        $orgs = $this->em->getRepository('App:Orgs')->findBy([], ['orgName' => 'ASC']);
        foreach ($orgs as $org) {
            if ($IsSystemAdmin || $this->adminChecker->IsAdminUser($org->getId())) {
                $AdminDefault = false;
                if (!$FoundAdminDefault && in_array($org->getId(), $MemberOrgs)) {
                    $AdminDefault = true;
                    $FoundAdminDefault = true;
                }
                $AdminOrgs[] = [
                    'OrgId' => $org->getId(),
                    'OrgName' => $org->getOrgName(),
                    'AdminDefault' => $ForJavascript ? ($AdminDefault ? 'true' : 'false') : $AdminDefault,
                    'DefaultTZ' => $org->getDefaultTz(),
                ];
            }
        }

        return [
            'IsSystemAdmin' => $ForJavascript ? ($IsSystemAdmin ? 'true' : 'false') : $IsSystemAdmin,
            'AdminOrgs' => $AdminOrgs,
        ];
    }

    public function RecoverUsingCode()
    {
        try {
            $request = $this->requestStack->getCurrentRequest();
            if (!$request->request->has('Code')) {
                throw new \Exception('Code parameter missing');
            }
            $Code = (int)$request->request->get('Code');

            if (!$request->request->has('Password')) {
                throw new \Exception('Password parameter missing');
            }
            $Password = trim($request->request->get('Password'));

            if (!$request->request->has('Contact')) {
                throw new \Exception('Contact parameter missing');
            }
            $Contact = strtolower(trim($request->request->get('Contact')));

            $user = $this->em->getRepository('App:Users')->findOneBy(['resetCode' => $Code]);
            if (is_null($user)) {
                throw new \Exception('Unrecognized code');
            }

            $contactRecord = $this->em->getRepository('App:Contacts')->findOneBy(['contact' => $Contact]);
            if (is_null($contactRecord)) {
                throw new \Exception('Unable to verify contact information');
            }
            if ($contactRecord->getUserId() !== $user->getId()) {
                throw new \Exception('Contact information does not belong to the user');
            }

            $this->pwdHelper->SaveUserPassword($user, $Password);
            $user->setResetCode(null);
            $user->setResetExpire(null);
            $this->em->persist($user);
            $this->em->flush();

            return ['Success' => true];
        } catch (\Exception $e) {
            return ['Success' => false, 'Error' => $e->getMessage()];
        }
    }

    public function SendRecoveryCode()
    {
        try {
            $request = $this->requestStack->getCurrentRequest();
            if (!$request->request->has('Contact')) {
                throw new \Exception('Contact parameter missing');
            }

            $Contact = strtolower(trim($request->request->get('Contact')));

            $isPhone = false;
            // See if it's a phone number
            if ($this->messageUtil->IsPhone($Contact)) {
                $isPhone = true;
            } else if (!$this->messageUtil->IsEmail($Contact)) {
                throw new \Exception('Expected valid phone or email');
            }

            // Look for a user with this phone or email

            $contactRecord = $this->em->getRepository('App:Contacts')->findOneBy(['contact' => $Contact]);
            if (is_null($contactRecord)) {
                throw new \Exception('The ' . ($isPhone ? 'phone number' : 'email address') . ' was not found');
            }
            $user = $contactRecord->getUser();

            // See if the user already has a code that isn't yet expired
            $Code = $user->getResetCode();
            $ResetExpire = $user->getResetExpire();
            $now = new \DateTime();
            if (is_null($Code) || is_null($ResetExpire) || $ResetExpire < $now) {
                $Code = random_int(1000, 9999);
                $user->setResetCode($Code);
            }
            $resetExpire = new \DateTime();
            $resetExpire->add(new \DateInterval('P7D'));  // seven days
            $user->setResetExpire($resetExpire);
            $this->em->persist($user);
            $this->em->flush();

            // Send the recovery message
            $Message = "Your Broncocast reset code is $Code";
            if ($isPhone) {
                $this->messageUtil->SendSMS([
                    ['Phone' => $Contact, 'ContactId' => $contactRecord->getId()]
                ], $Message);
            } else {
                $this->messageUtil->SendEmail([$Contact], $Message, null, null, null);
            }

            return ['Success' => true];
        } catch (\Exception $e) {
            return ['Success' => false, 'Error' => $e->getMessage()];
        }
    }
}
