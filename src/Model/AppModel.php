<?php

namespace App\Model;

use App\Util\AdminChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AppModel
{
    private $adminChecker;
    private $em;
    private $tokenStorage;

    public function __construct(AdminChecker $adminChecker, EntityManagerInterface $em,
                                TokenStorageInterface $tokenStorage)
    {
        $this->adminChecker = $adminChecker;
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
    }

    public function GetAppParams()
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
                    'AdminDefault' => $AdminDefault ? 'true' : 'false',
                    'DefaultTZ' => $org->getDefaultTz(),
                ];
            }
        }

        return [
            'IsSystemAdmin' => $IsSystemAdmin ? 'true' : 'false',
            'AdminOrgs' => $AdminOrgs,
        ];
    }
}
