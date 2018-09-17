<?php

namespace App\Model;

use App\Util\AdminChecker;
use Doctrine\ORM\EntityManagerInterface;

class AppModel
{
    private $adminChecker;
    private $em;

    public function __construct(AdminChecker $adminChecker, EntityManagerInterface $em)
    {
        $this->adminChecker = $adminChecker;
        $this->em = $em;
    }

    public function GetAppParams()
    {
        $IsSystemAdmin = $this->adminChecker->IsSystemAdmin();

        $AdminOrgs = [];
        $orgs = $this->em->getRepository('App:Orgs')->findBy([], ['orgName' => 'ASC']);
        foreach ($orgs as $org) {
            if ($IsSystemAdmin || $this->adminChecker->IsAdminUser($org->getId())) {
                $AdminOrgs[] = [
                    'OrgId' => $org->getId(),
                    'OrgName' => $org->getOrgName(),
                    'DefaultTZ' => $org->getDefaultTz(),
                ];
            }
        }

        $Carriers = [];
        $carriers = $this->em->getRepository('App:Carriers')->findBy([], ['name' => 'ASC']);
        foreach ($carriers as $carrier) {
            $Carriers[] = [
                'CarrierId' => $carrier->getId(),
                'CarrierName' => str_replace('\'', '\\\'', $carrier->getName())
            ];
        }

        return [
            'IsSystemAdmin' => $IsSystemAdmin,
            'AdminOrgs' => $AdminOrgs,
            'Carriers' => $Carriers,
        ];
    }
}
