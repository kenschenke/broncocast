<?php

namespace App\Model;

use App\Util\AdminChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AdminUsersModel
{
    private $tokenStorage;
    private $adminChecker;
    private $em;
    private $requestStack;

    public function __construct(TokenStorageInterface $tokenStorage, AdminChecker $adminChecker,
                                EntityManagerInterface $entityManager, RequestStack $requestStack)
    {
        $this->tokenStorage = $tokenStorage;
        $this->adminChecker = $adminChecker;
        $this->em = $entityManager;
        $this->requestStack = $requestStack;
    }

    public function ApproveUser($MemberId)
    {
        try {
            $OrgMember = $this->em->getRepository('App:OrgMembers')->find($MemberId);
            if (is_null($OrgMember)) {
                throw new \Exception('Member record not found');
            }

            if (!$this->adminChecker->IsAdminUser($OrgMember->getOrgId())) {
                throw new \Exception('Administrative privileges required');
            }

            $OrgMember->setIsApproved(true);
            $this->em->persist($OrgMember);
            $this->em->flush();

            return ['Success' => true];
        } catch (\Exception $e) {
            return ['Success' => false, 'Error' => $e->getMessage()];
        }
    }

    public function ChangeName($MemberId)
    {
        try {
            $request = $this->requestStack->getCurrentRequest();
            if (!$request->request->has('Name')) {
                throw new \Exception('Name parameter missing');
            }
            $Name = trim($request->request->get('Name'));

            $OrgMember = $this->em->getRepository('App:OrgMembers')->find($MemberId);
            if (is_null($OrgMember)) {
                throw new \Exception('Member record not found');
            }

            if (!$this->adminChecker->IsAdminUser($OrgMember->getOrgId())) {
                throw new \Exception('Administrative privileges required');
            }

            $OrgMember->setAltUsrName($Name);
            $this->em->persist($OrgMember);
            $this->em->flush();

            return ['Success' => true];
        } catch (\Exception $e) {
            return ['Success' => false, 'Error' => $e->getMessage()];
        }
    }

    public function GetUsers($OrgId)
    {
        try {
            if (!$this->adminChecker->IsAdminUser($OrgId)) {
                throw new \Exception('Administrative privileges required');
            }

            $userRepo = $this->em->getRepository('App:Users');
            $groupRepo = $this->em->getRepository('App:Groups');

            $org = $this->em->getRepository('App:Orgs')->find($OrgId);
            if (is_null($org)) {
                throw new \Exception('Organization record not found');
            }

            $Users = [];
            foreach ($org->getMembers() as $member) {
                $user = $userRepo->find($member->getUserId());
                if (is_null($user)) {
                    throw new \Exception('User record not found');
                }
                $UserName = $user->getFullname();
                $AltUsrName = $member->getAltUsrName();
                if (!is_null($AltUsrName) && !empty($AltUsrName))
                    $UserName = $AltUsrName;

                $Groups = [];
                foreach ($user->getGroups() as $grpMember) {
                    $group = $groupRepo->find($grpMember->getGrpId());
                    if (is_null($group)) {
                        throw new \Exception('Group record not found');
                    }
                    $Groups[] = $group->getGrpName();
                }
                $GroupNames = implode(', ', $Groups);

                $Contacts = [];
                foreach ($user->getContacts() as $contact) {
                    $CarrierId = $contact->getCarrierId();
                    if (is_null($CarrierId)) {
                        $CarId = 0;
                    }
                    $Contacts[] = [
                        'Contact' => $contact->getContact(),
                        'CarrierId' => $CarrierId,
                    ];
                }

                $Users[] = [
                    'MemberId' => $member->getId(),
                    'UsrId' => $user->getId(),
                    'UsrName' => $UserName,
                    'IsAdmin' => $member->getIsAdmin(),
                    'Approved' => $member->getIsApproved(),
                    'Hidden' => $member->getIsHidden(),
                    'Groups' => $GroupNames,
                    'Contacts' => $Contacts
                ];
            }

            return ['Success' => true, 'Users' => $Users];
        } catch (\Exception $e) {
            return ['Success' => false, 'Error' => $e->getMessage()];
        }
    }

    public function RemoveUser($MemberId)
    {
        try {
            $OrgMember = $this->em->getRepository('App:OrgMembers')->find($MemberId);
            if (is_null($OrgMember)) {
                throw new \Exception('Member record not found');
            }

            if (!$this->adminChecker->IsAdminUser($OrgMember->getOrgId())) {
                throw new \Exception('Administrative privileges required');
            }

            // Drop group membership records

            $this->em->getConnection()->executeQuery(
                "DELETE FROM grp_members WHERE user_id = ? AND " .
                "grp_id IN (SELECT id FROM groups WHERE org_id = ?)",
                [$OrgMember->getUserId(), $OrgMember->getOrgId()]);

            $this->em->remove($OrgMember);
            $this->em->flush();

            return ['Success' => true];
        } catch (\Exception $e) {
            return ['Success' => false, 'Error' => $e->getMessage()];
        }
    }

    public function SetUserHidden($MemberId, $Hidden)
    {
        try {
            $OrgMember = $this->em->getRepository('App:OrgMembers')->find($MemberId);
            if (is_null($OrgMember)) {
                throw new \Exception('Member record not found');
            }

            if (!$this->adminChecker->IsAdminUser($OrgMember->getOrgId())) {
                throw new \Exception('Administrative privileges required');
            }

            $OrgMember->setIsHidden($Hidden);
            $this->em->persist($OrgMember);
            $this->em->flush();

            return ['Success' => true];
        } catch (\Exception $e) {
            return ['Success' => false, 'Error' => $e->getMessage()];
        }
    }
}
