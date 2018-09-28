<?php

namespace App\Model;

use App\Entity\Orgs;
use App\Util\AdminChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class SystemOrgsModel
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

    public function DeleteOrg($OrgId)
    {
        try {
            if (!$this->adminChecker->IsSystemAdmin()) {
                throw new \Exception('System admin privileges required');
            }

            $Org = $this->em->getRepository('App:Orgs')->find($OrgId);
            if (is_null($Org)) {
                throw new \Exception('Organization record not found');
            }

            $res = $this->em->getConnection()->executeQuery(
                'SELECT id FROM attachments WHERE broadcast_id IN ' .
                '(SELECT id FROM broadcasts WHERE org_id = ?)', [$OrgId]
            );
            $repo = $this->em->getRepository('App:Attachments');
            while (($row = $res->fetch(\PDO::FETCH_NUM))) {
                $attach = $repo->find($row[0]);
                if (is_null($attach)) {
                    throw new \Exception('Attachment record not found');
                }
                unlink(getenv('BRONCOCAST_ATTACHMENTS_DIR') . '/' . $attach->getLocalName());
                $this->em->remove($attach);
            }

            $this->em->getConnection()->executeQuery(
                'DELETE FROM recipients WHERE broadcast_id IN ' .
                '(SELECT id FROM broadcasts WHERE org_id = ?)', [$OrgId]
            );
            $this->em->getConnection()->executeQuery(
                'DELETE FROM broadcasts WHERE org_id = ?', [$OrgId]
            );
            $this->em->getConnection()->executeQuery(
                'DELETE FROM grp_members WHERE grp_id IN ' .
                '(SELECT id FROM groups WHERE org_id = ?)', [$OrgId]
            );
            $this->em->getConnection()->executeQuery(
                'DELETE FROM groups WHERE org_id = ?', [$OrgId]
            );
            $this->em->getConnection()->executeQuery(
                'DELETE FROM org_members WHERE org_id = ?', [$OrgId]
            );

            $this->em->remove($Org);
            $this->em->flush();

            return ['Success' => true];
        } catch (\Exception $e) {
            return ['Success' => false, 'Error' => $e->getMessage()];
        }
    }

    public function GetOrg($OrgId)
    {
        try {
            if (!$this->adminChecker->IsSystemAdmin()) {
                throw new \Exception('System admin privileges required');
            }

            $Org = $this->em->getRepository('App:Orgs')->find($OrgId);
            if (is_null($Org)) {
                throw new \Exception('Organization record not found');
            }

            return [
                'Success' => true,
                'OrgName' => $Org->getOrgName(),
                'DefaultTZ' => $Org->getDefaultTz(),
                'Tag' => $Org->getTag(),
            ];
        } catch (\Exception $e) {
            return ['Success' => false, 'Error' => $e->getMessage()];
        }
    }

    public function GetOrgs()
    {
        try {
            if (!$this->adminChecker->IsSystemAdmin()) {
                throw new \Exception('System admin privileges required');
            }

            $Orgs = [];
            $recs = $this->em->getRepository('App:Orgs')->findAll();
            foreach ($recs as $Org) {
                $Orgs[] = [
                    'OrgId' => $Org->getId(),
                    'OrgName' => $Org->getOrgName(),
                ];
            }

            return ['Success' => true, 'Orgs' => $Orgs];
        } catch (\Exception $e) {
            return ['Success' => false, 'Error' => $e->getMessage()];
        }
    }

    public function NewOrg()
    {
        try {
            if (!$this->adminChecker->IsSystemAdmin()) {
                throw new \Exception('System admin privileges required');
            }

            $request = $this->requestStack->getCurrentRequest();

            if (!$request->request->has('OrgName')) {
                throw new \Exception('OrgName parameter missing');
            }
            $OrgName = trim($request->request->get('OrgName'));

            if (!$request->request->has('DefaultTZ')) {
                throw new \Exception('DefaultTZ parameter missing');
            }
            $DefaultTZ = trim($request->request->get('DefaultTZ'));

            if (!$request->request->has('Tag')) {
                throw new \Exception('Tag parameter missing');
            }
            $Tag = strtoupper(trim($request->request->get('Tag')));

            $Org = new Orgs();
            $Org->setOrgName($OrgName);
            $Org->setDefaultTz($DefaultTZ);
            $Org->setTag($Tag);
            $Org->setMaxBrcAge(0);
            $this->em->persist($Org);
            $this->em->flush();

            return ['Success' => true, 'OrgId' => $Org->getId()];
        } catch (\Exception $e) {
            return ['Success' => false, 'Error' => $e->getMessage()];
        }
    }

    public function UpdateOrg($OrgId)
    {
        try {
            if (!$this->adminChecker->IsSystemAdmin()) {
                throw new \Exception('System admin privileges required');
            }

            $request = $this->requestStack->getCurrentRequest();

            if (!$request->request->has('OrgName')) {
                throw new \Exception('OrgName parameter missing');
            }
            $OrgName = trim($request->request->get('OrgName'));

            if (!$request->request->has('DefaultTZ')) {
                throw new \Exception('DefaultTZ parameter missing');
            }
            $DefaultTZ = trim($request->request->get('DefaultTZ'));

            if (!$request->request->has('Tag')) {
                throw new \Exception('Tag parameter missing');
            }
            $Tag = strtoupper(trim($request->request->get('Tag')));

            $Org = $this->em->getRepository('App:Orgs')->find($OrgId);
            if (is_null($Org)) {
                throw new \Exception('Organization record not found');
            }

            $Org->setOrgName($OrgName);
            $Org->setDefaultTz($DefaultTZ);
            $Org->setTag($Tag);
            $this->em->persist($Org);
            $this->em->flush();

            return ['Success' => true];
        } catch (\Exception $e) {
            return ['Success' => false, 'Error' => $e->getMessage()];
        }
    }
}
