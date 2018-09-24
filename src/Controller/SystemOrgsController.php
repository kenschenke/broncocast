<?php

namespace App\Controller;

use App\Model\SystemOrgsModel;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class SystemOrgsController extends Controller
{
    /**
     * @Route("/api/system/orgs/{OrgId}", name="system_orgs_delete", methods="DELETE")
     * @param $OrgId
     * @param SystemOrgsModel $model
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteOrg($OrgId, SystemOrgsModel $model)
    {
        return $this->json($model->DeleteOrg($OrgId));
    }

    /**
     * @Route("/api/system/orgs/{OrgId}", name="system_orgs_get_org", methods="GET")
     * @param $OrgId
     * @param SystemOrgsModel $model
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getOrg($OrgId, SystemOrgsModel $model)
    {
        return $this->json($model->GetOrg($OrgId));
    }

    /**
     * @Route("/api/system/orgs", name="system_orgs_get", methods="GET")
     * @param SystemOrgsModel $model
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getOrgs(SystemOrgsModel $model)
    {
        return $this->json($model->GetOrgs());
    }

    /**
     * @Route("/api/system/orgs", name="system_orgs_new", methods="POST")
     * @param SystemOrgsModel $model
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function newOrg(SystemOrgsModel $model)
    {
        return $this->json($model->NewOrg());
    }

    /**
     * @Route("/api/system/orgs/{OrgId}", name="system_orgs_update", methods="PUT")
     * @param $OrgId
     * @param SystemOrgsModel $model
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function updateOrg($OrgId, SystemOrgsModel $model)
    {
        return $this->json($model->UpdateOrg($OrgId));
    }
}
