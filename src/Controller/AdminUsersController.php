<?php

namespace App\Controller;

use App\Model\AdminUsersModel;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class AdminUsersController extends Controller
{
    /**
     * @Route("/api/admin/users/approve/{MemberId}", name="admin_users_approve", methods="PUT")
     * @param $MemberId
     * @param AdminUsersModel $model
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function approveUser($MemberId, AdminUsersModel $model)
    {
        return $this->json($model->ApproveUser($MemberId));
    }

    /**
     * @Route("/api/admin/users/name/{MemberId}", name="admin_users_name", methods={"PUT","POST"})
     * @param $MemberId
     * @param AdminUsersModel $model
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function changeName($MemberId, AdminUsersModel $model)
    {
        return $this->json($model->ChangeName($MemberId));
    }

    /**
     * @Route("/api/admin/users/admin/{MemberId}", name="admin_users_admin_drop", methods="DELETE")
     * @param $MemberId
     * @param AdminUsersModel $model
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function dropAdmin($MemberId, AdminUsersModel $model)
    {
        return $this->json($model->SetUserAdmin($MemberId, false));
    }

    /**
     * @Route("/api/admin/users/{OrgId}", name="admin_users_getusers", methods="GET")
     * @param $OrgId
     * @param AdminUsersModel $model
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getUsers($OrgId, AdminUsersModel $model)
    {
        return $this->json($model->GetUsers($OrgId));
    }

    /**
     * @Route("/api/admin/users/hide/{MemberId}", name="admin_users_hide", methods="PUT")
     * @param $MemberId
     * @param AdminUsersModel $model
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function hideUser($MemberId, AdminUsersModel $model)
    {
        return $this->json($model->SetUserHidden($MemberId, true));
    }

    /**
     * @Route("/api/admin/users/remove/{MemberId}", name="admin_users_remove", methods="DELETE")
     * @param $MemberId
     * @param AdminUsersModel $model
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function removeUser($MemberId, AdminUsersModel $model)
    {
        return $this->json($model->RemoveUser($MemberId));
    }

    /**
     * @Route("/api/admin/users/admin/{MemberId}", name="admin_users_admin_set", methods="PUT")
     * @param $MemberId
     * @param AdminUsersModel $model
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function setAdmin($MemberId, AdminUsersModel $model)
    {
        return $this->json($model->SetUserAdmin($MemberId, true));
    }

    /**
     * @Route("/api/admin/users/unhide/{MemberId}", name="admin_users_unhide", methods="PUT")
     * @param $MemberId
     * @param AdminUsersModel $model
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function unhideUser($MemberId, AdminUsersModel $model)
    {
        return $this->json($model->SetUserHidden($MemberId, false));
    }
}
