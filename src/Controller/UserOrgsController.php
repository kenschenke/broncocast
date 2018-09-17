<?php

namespace App\Controller;

use App\Model\UserOrgsModel;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class UserOrgsController extends Controller
{
    /**
     * @Route("/api/orgs", name="orgs_get", methods="GET")
     * @param UserOrgsModel $model
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getOrgs(UserOrgsModel $model)
    {
        return $this->json($model->GetOrgs());
    }

    /**
     * @Route("/api/orgs", name="orgs_add_member", methods="POST")
     * @param UserOrgsModel $model
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function addOrgMember(UserOrgsModel $model)
    {
        return $this->json($model->AddOrgMember());
    }

    /**
     * @Route("/api/orgs/{MemberId}", name="orgs_delete_member", methods="DELETE")
     * @param $MemberId
     * @param UserOrgsModel $model
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteOrgMember($MemberId, UserOrgsModel $model)
    {
        return $this->json($model->DeleteOrgMember($MemberId));
    }
}
