<?php

namespace App\Model;

use App\Entity\OrgMembers;
use App\Util\AdminChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserOrgsModel
{
    private $em;
    private $tokenStorage;
    private $adminChecker;
    private $requestStack;

    public function __construct(EntityManagerInterface $em, TokenStorageInterface $tokenStorage,
                                AdminChecker $adminChecker, RequestStack $requestStack)
    {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
        $this->adminChecker = $adminChecker;
        $this->requestStack = $requestStack;
    }

    public function AddOrgMember()
    {
        try {
            $request = $this->requestStack->getCurrentRequest();
            if (!$request->request->has('Tag')) {
                throw new \Exception('Tag parameter missing');
            }
            $Tag = strtoupper(trim($request->request->get('Tag')));
            $user = $this->tokenStorage->getToken()->getUser();
            $orgRepo = $this->em->getRepository('App:Orgs');
            $org = $orgRepo->findOneBy(['tag' => $Tag]);
            if (is_null($org)) {
                throw new \Exception('Organization not found');
            }

            $orgMemberRepo = $this->em->getRepository('App:OrgMembers');
            $orgMember = $orgMemberRepo->findOneBy(['orgId' => $org->getId(), 'userId' => $user->getId()]);
            if (is_null($orgMember)) {
                $orgMember = new OrgMembers();
                $orgMember->setUser($user);
                $orgMember->setOrg($org);
                $orgMember->setIsAdmin(false);
                $orgMember->setIsApproved(false);
                $orgMember->setIsHidden(false);
                $this->em->persist($orgMember);
                $this->em->flush();
            }

            return [
                'Success' => true,
                'MemberId' => $orgMember->getId(),
                'OrgId' => $org->getId(),
                'OrgName' => $org->getOrgName(),
                'IsSystemAdmin' => $this->adminChecker->IsSystemAdmin(),
            ];
        } catch (\Exception $e) {
            return ['Success' => false, 'Error' => $e->getMessage()];
        }
    }

    public function DeleteOrgMember($MemberId)
    {
        try {
            $user = $this->tokenStorage->getToken()->getUser();
            $userId = $user->getId();
            $orgMember = $this->em->getRepository('App:OrgMembers')->find($MemberId);
            if (is_null($orgMember)) {
                throw new \Exception('Organization member record not found');
            }
            if ($orgMember->getUserId() !== $userId) {
                if (!$this->adminChecker->IsAdminUser($orgMember->getOrgId())) {
                    throw new \Exception('Organization member record belongs to another user');
                }
            }
            $this->em->remove($orgMember);

            // Drop group memberships for this organization

            foreach ($user->getGroups() as $grpMember) {
                if ($grpMember->getGroup()->getOrgId() === $orgMember->getOrgId()) {
                    $this->em->remove($grpMember);
                }
            }

            $this->em->flush();

            return ['Success' => true];
        } catch (\Exception $e) {
            return ['Success' => false, 'Error' => $e->getMessage()];
        }
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
