<?php

namespace App\Model;

use App\Util\AdminChecker;
use Doctrine\ORM\EntityManagerInterface;

class SystemUsersModel
{
    private $em;
    private $adminChecker;
    private $adminUsersModel;

    public function __construct(EntityManagerInterface $em, AdminChecker $adminChecker, AdminUsersModel $adminUsersModel)
    {
        $this->em = $em;
        $this->adminChecker = $adminChecker;
        $this->adminUsersModel = $adminUsersModel;
    }

    public function DeleteUser($UserId)
    {
        try {
            if (!$this->adminChecker->IsSystemAdmin()) {
                throw new \Exception('System admin privileges required');
            }

            $User = $this->em->getRepository('App:Users')->find($UserId);
            if (is_null($User)) {
                throw new \Exception('User record not found');
            }

            foreach ($User->getOrgs() as $orgMember) {
                $result = $this->adminUsersModel->RemoveUser($orgMember->getId());
                if (!$result['Success']) {
                    throw new \Exception($result['Error']);
                }
            }

            $conn = $this->em->getConnection();
            $conn->executeQuery('DELETE FROM recipients WHERE user_id = :UserId', ['UserId' => $UserId]);
            $conn->executeQuery(
                'DELETE FROM sms_logs WHERE contact_id IN ' .
                '(SELECT id FROM contacts WHERE user_id = :UserId)',
                ['UserId' => $UserId]);
            $conn->executeQuery('DELETE FROM contacts WHERE user_id = :UserId', ['UserId' => $UserId]);

            $this->em->remove($User);
            $this->em->flush();

            return ['Success' => true];
        } catch (\Exception $e) {
            return ['Success' => false, 'Error' => $e->getMessage()];
        }
    }

    public function FillUserNameFromMemberRecord($MemberId)
    {
        try {
            if (!$this->adminChecker->IsSystemAdmin()) {
                throw new \Exception('System admin privileges required');
            }

            $OrgMember = $this->em->getRepository('App:OrgMembers')->find($MemberId);
            if (is_null($OrgMember)) {
                throw new \Exception('Member record not found');
            }
            $AltUsrName = $OrgMember->getAltUsrName();
            if (is_null($AltUsrName) || empty($AltUsrName)) {
                throw new \Exception('User name in organization member record cannot be empty');
            }

            $User = $this->em->getRepository('App:Users')->find($OrgMember->getUserId());
            if (is_null($User)) {
                throw new \Exception('User record not found');
            }

            $User->setFullname($AltUsrName);
            $this->em->persist($User);
            $OrgMember->setAltUsrName(null);
            $this->em->persist($OrgMember);
            $this->em->flush();

            return ['Success' => true, 'UserName' => $AltUsrName];
        } catch (\Exception $e) {
            return ['Success' => false, 'Error' => $e->getMessage()];
        }
    }

    public function GetUsers()
    {
        try {
            if (!$this->adminChecker->IsSystemAdmin()) {
                throw new \Exception('System admin privileges required');
            }

            $Users = [];
            $recs = $this->em->getRepository('App:Users')->findAll();
            foreach ($recs as $User) {
                $orgs = $User->getOrgs();
                $OrgNames = [];
                $UserName = $User->getFullName();
                $FillMemberId = 0;

                foreach ($orgs as $OrgMember) {
                    $Org = $OrgMember->getOrg();
                    $OrgNames[] = $Org->getOrgName();

                    $AltUsrName = $OrgMember->getAltUsrName();
                    if (!is_null($AltUsrName) && empty($UserName)) {
                        $OrgName = $Org->getOrgName();
                        $UserName = "(Blank) $AltUsrName (From $OrgName)";
                        $FillMemberId = $OrgMember->getId();
                    }
                }
                sort($OrgNames);

                if (empty($UserName)) {
                    $UserName = '(Blank)';
                }
                $Users[] = [
                    'UserId' => $User->getId(),
                    'UserName' => $UserName,
                    'OrgNames' => empty($OrgNames) ? '(None)' : implode(', ', $OrgNames),
                    'FillMemberId' => $FillMemberId,
                ];
            }

            return ['Success' => true, 'Users' => $Users];
        } catch (\Exception $e) {
            return ['Success' => false, 'Error' => $e->getMessage()];
        }
    }
}
