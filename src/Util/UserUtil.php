<?php

namespace App\Util;

use Doctrine\ORM\EntityManagerInterface;

class UserUtil
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function GetUserName($UserId, $OrgId = 0)
    {
        $UserRepo = $this->em->getRepository('App:Users');
        $User = $UserRepo->find($UserId);
        if (is_null($User)) {
            return '';
        }

        if ($OrgId) {
            $OrgMembersRepo = $this->em->getRepository('App:OrgMembers');
            $OrgMember = $OrgMembersRepo->findOneBy(['userId' => $UserId, 'orgId' => $OrgId]);
            if (!is_null($OrgMember)) {
                $AltUserName = $OrgMember->getAltUsrName();
                if (!is_null($AltUserName) && !empty($AltUserName)) {
                    return $AltUserName;
                }
            }
        }

        return $User->getFullname();
    }
}
