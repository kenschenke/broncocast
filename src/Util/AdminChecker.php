<?php

namespace App\Util;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AdminChecker
{
    protected $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function IsAdminUser($OrgId)
    {
        if ($this->IsSystemAdmin()) {
            return true;
        }

        $user = $this->tokenStorage->getToken()->getUser();
        foreach ($user->getOrgs() as $org) {
            // getOrgs() returns an array of OrgMember records
            if ($org->getOrgId() === (int)$OrgId) {
                return $org->getIsAdmin();
            }
        }

        return false;
    }

    public function IsSystemAdmin()
    {
        $user = $this->tokenStorage->getToken()->getUser();

        return in_array('ROLE_SYSTEM_ADMIN', $user->getRoles());
    }
}
