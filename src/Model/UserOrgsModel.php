<?php

namespace App\Model;

use App\Util\AdminChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserOrgsModel
{
    private $em;
    private $tokenStorage;
    private $adminChecker;

    public function __construct(EntityManagerInterface $em, TokenStorageInterface $tokenStorage,
                                AdminChecker $adminChecker)
    {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
        $this->adminChecker = $adminChecker;
    }

    public function GetOrgs()
    {
        $user = $this->tokenStorage->getToken()->getUser();

        try {
            $Orgs = [];
            $AdminOrgs = [];
            $isSystemAdmin = $this->adminChecker->IsSystemAdmin();
            foreach ($user->getOrgs() as $org) {
                $Orgs[] = [
                    'MemberId' => $org->getId(),
                    'OrgId' => $org->getOrgId(),
                    'OrgName' => $org->getOrg()->getOrgName(),
                    'IsAdmin' => $isSystemAdmin || $org->getIsAdmin(),
                ];

                if ($org->getIsAdmin()) {
                    $AdminOrgs[] = [
                        'MemberId' => $org->getId(),
                        'OrgId' => $org->getOrgId(),
                        'OrgName' => $org->getOrg()->getOrgName(),
                        'IsAdmin' => $isSystemAdmin || $org->getIsAdmin(),
                    ];
                }

                if ($isSystemAdmin) {
                    $AdminOrgs = [];
                    $orgs = $this->em->getRepository('App:Orgs')->findAll();
                    foreach ($orgs as $org) {
                        $AdminOrgs[] = [
                            'OrgId' => $org->getId(),
                            'OrgName' => $org->getOrgName(),
                        ];
                    }
                }
            }

            return [
                'Success' => true,
                'Orgs' => $Orgs,
                'AdminOrgs' => $AdminOrgs,
            ];
        } catch (\Exception $e) {
            return ['Success' => false, 'Error' => $e->getMessage()];
        }
    }
}
