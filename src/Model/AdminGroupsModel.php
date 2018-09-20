<?php

namespace App\Model;

use App\Entity\Groups;
use App\Entity\GrpMembers;
use App\Util\AdminChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class AdminGroupsModel
{
    private $em;
    private $adminChecker;
    private $requestStack;

    public function __construct(EntityManagerInterface $em, AdminChecker $adminChecker, RequestStack $requestStack)
    {
        $this->em = $em;
        $this->adminChecker = $adminChecker;
        $this->requestStack = $requestStack;
    }

    public function AddGroupMember($GroupId)
    {
        try {
            $request = $this->requestStack->getCurrentRequest();
            if (!$request->request->has('UserId')) {
                throw new \Exception('UserId parameter missing');
            }
            $UserId = (int)$request->request->get('UserId');

            $Group = $this->em->getRepository('App:Groups')->find($GroupId);
            if (is_null($Group)) {
                throw new \Exception('Group record not found');
            }

            $User = $this->em->getRepository('App:Users')->find($UserId);
            if (is_null($User)) {
                throw new \Exception('User record not found');
            }

            // Make sure the user has admin privileges

            if (!$this->adminChecker->IsAdminUser($Group->getOrgId())) {
                throw new \Exception('Admin privileges required');
            }

            // Make sure the new group member belongs to the organization

            $OrgMember = $this->em->getRepository('App:OrgMembers')->findOneBy(['orgId' => $Group->getOrgId(), 'userId' => $UserId]);
            if (is_null($OrgMember)) {
                throw new \Exception('User is not a member of this organization');
            }

            // Create the new record

            $GrpMember = new GrpMembers();
            $GrpMember->setGroup($Group);
            $GrpMember->setUser($User);
            $this->em->persist($GrpMember);
            $this->em->flush();

            return ['Success' => true, 'MemberId' => $GrpMember->getId()];
        } catch (\Exception $e) {
            return ['Success' => false, 'Error' => $e->getMessage()];
        }
    }

    public function ChangeName($GroupId)
    {
        try {
            $request = $this->requestStack->getCurrentRequest();
            if (!$request->request->has('Name')) {
                throw new \Exception('Name parameter missing');
            }
            $Name = trim($request->request->get('Name'));

            $Group = $this->em->getRepository('App:Groups')->find($GroupId);
            if (is_null($Group)) {
                throw new \Exception('Group record not found');
            }

            if (!$this->adminChecker->IsAdminUser($Group->getOrgId())) {
                throw new \Exception('Administrative privileges required');
            }

            $Group->setGrpName($Name);
            $this->em->persist($Group);
            $this->em->flush();

            return ['Success' => true];
        } catch (\Exception $e) {
            return ['Success' => false, 'Error' => $e->getMessage()];
        }
    }

    public function GetGroupMembers($GroupId)
    {
        try {
            $Group = $this->em->getRepository('App:Groups')->find($GroupId);
            if (is_null($Group)) {
                throw new \Exception('Group record not found');
            }

            if (!$this->adminChecker->IsAdminUser($Group->getOrgId())) {
                throw new \Exception('Administrative privileges required');
            }

            $Members = [];
            $UserRepo = $this->em->getRepository('App:Users');
            $OrgMemberRepo = $this->em->getRepository('App:OrgMembers');
            $grpMembers = $this->em->getRepository('App:GrpMembers')->findBy(['grpId' => $GroupId]);
            foreach ($grpMembers as $member) {
                $User = $UserRepo->find($member->getUserId());
                if (is_null($User)) {
                    throw new \Exception('User record not found');
                }
                $OrgMember = $OrgMemberRepo->findOneBy(['orgId' => $Group->getOrgId(), 'userId' => $User->getId()]);
                if (is_null($OrgMember)) {
                    throw new \Exception('Organization member record not found');
                }

                $UserName = $User->getFullname();
                $OrgMemberName = $OrgMember->getAltUsrName();
                if (!is_null($OrgMemberName) && !empty($OrgMemberName)) {
                    $UserName = $OrgMemberName;
                }

                $Members[] = [
                    'MemberId' => $member->getId(),
                    'UserId' => $User->getId(),
                    'UserName' => $UserName,
                ];
            }

            return ['Success' => true, 'Members' => $Members];
        } catch (\Exception $e) {
            return ['Success' => false, 'Error' => $e->getMessage()];
        }
    }

    public function GetGroupNonMembers($GroupId)
    {
        try {
            $Group = $this->em->getRepository('App:Groups')->find($GroupId);
            if (is_null($Group)) {
                throw new \Exception('Group record not found');
            }

            if (!$this->adminChecker->IsAdminUser($Group->getOrgId())) {
                throw new \Exception('Administrative privileges required');
            }

            $NonMembers = [];
            $UserRepo = $this->em->getRepository('App:Users');
            $GrpMembersRepo = $this->em->getRepository('App:GrpMembers');
            $OrgMembers = $this->em->getRepository('App:OrgMembers')->findBy(['orgId' => $Group->getOrgId()]);
            foreach ($OrgMembers as $OrgMember) {
                // Look for a GrpMember record
                $GrpMember = $GrpMembersRepo->findOneBy(['grpId' => $GroupId, 'userId' => $OrgMember->getUserId()]);
                if (!is_null($GrpMember)) {
                    continue;
                }

                $User = $UserRepo->find($OrgMember->getUserId());
                if (is_null($User)) {
                    throw new \Exception('User record not found');
                }

                $UserName = $User->getFullname();
                $OrgMemberName = $OrgMember->getAltUsrName();
                if (!is_null($OrgMemberName) && !empty($OrgMemberName)) {
                    $UserName = $OrgMemberName;
                }

                $NonMembers[] = [
                    'UserId' => $OrgMember->getUserId(),
                    'UserName' => $UserName,
                    'Hidden' => $OrgMember->getIsHidden(),
                ];
            }

            return ['Success' => true, 'NonMembers' => $NonMembers];
        } catch (\Exception $e) {
            return ['Success' => false, 'Error' => $e->getMessage()];
        }
    }

    public function GetGroups($OrgId)
    {
        try {
            if (!$this->adminChecker->IsAdminUser($OrgId)) {
                throw new \Exception('Administrative privileges required');
            }

            $Groups = [];
            $recs = $this->em->getRepository('App:Groups')->findBy(['orgId' => $OrgId]);
            foreach ($recs as $group) {
                $Groups[] = [
                    'GroupId' => $group->getId(),
                    'GroupName' => $group->getGrpName(),
                ];
            }

            return ['Success' => true, 'Groups' => $Groups];
        } catch (\Exception $e) {
            return ['Success' => false, 'Error' => $e->getMessage()];
        }
    }

    public function NewGroup($OrgId)
    {
        try {
            if (!$this->adminChecker->IsAdminUser($OrgId)) {
                throw new \Exception('Administrative privileges required');
            }

            $Org = $this->em->getRepository('App:Orgs')->find($OrgId);
            if (is_null($Org)) {
                throw new \Exception('Organization Record Not Found');
            }

            $request = $this->requestStack->getCurrentRequest();
            if (!$request->request->has('Name')) {
                throw new \Exception('Name parameter missing');
            }
            $Name = trim($request->request->get('Name'));

            $Group = new Groups();
            $Group->setOrg($Org);
            $Group->setGrpName($Name);
            $this->em->persist($Group);
            $this->em->flush();

            return ['Success' => true, 'GroupId' => $Group->getId()];
        } catch (\Exception $e) {
            return ['Success' => false, 'Error' => $e->getMessage()];
        }
    }

    public function RemoveGroup($GroupId)
    {
        try {
            $Group = $this->em->getRepository('App:Groups')->find($GroupId);
            if (is_null($Group)) {
                throw new \Exception('Group record not found');
            }

            if (!$this->adminChecker->IsAdminUser($Group->getOrgId())) {
                throw new \Exception('Administrative privileges required');
            }

            // Drop group membership records

            $this->em->getConnection()->executeQuery(
                "DELETE FROM grp_members WHERE grp_id = ?",
                [$GroupId]);

            $this->em->remove($Group);
            $this->em->flush();

            return ['Success' => true];
        } catch (\Exception $e) {
            return ['Success' => false, 'Error' => $e->getMessage()];
        }
    }

    public function RemoveGroupMember($MemberId)
    {
        try {
            $GrpMember = $this->em->getRepository('App:GrpMembers')->find($MemberId);
            if (is_null($GrpMember)) {
                throw new \Exception('Group member record not found');
            }

            $Group = $this->em->getRepository('App:Groups')->find($GrpMember->getGrpId());
            if (is_null($Group)) {
                throw new \Exception('Group record not found');
            }

            if (!$this->adminChecker->IsAdminUser($Group->getOrgId())) {
                throw new \Exception('Administrative privileges required');
            }

            $this->em->remove($GrpMember);
            $this->em->flush();

            return ['Success' => true];
        } catch (\Exception $e) {
            return ['Success' => false, 'Error' => $e->getMessage()];
        }
    }
}
