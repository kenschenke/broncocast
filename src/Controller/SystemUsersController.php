<?php

namespace App\Controller;

use App\Model\SystemUsersModel;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class SystemUsersController extends Controller
{
    /**
     * @Route("/api/system/users", name="system_users_get", methods="GET")
     * @param SystemUsersModel $model
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getUsers(SystemUsersModel $model)
    {
        return $this->json($model->GetUsers());
    }

    /**
     * @Route("/api/system/users/{UserId}", name="system_users_delete", methods="DELETE")
     * @param $UserId
     * @param SystemUsersModel $model
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteUser($UserId, SystemUsersModel $model)
    {
        return $this->json($model->DeleteUser($UserId));
    }

    /**
     * @Route("/api/system/users/fillname/{MemberId}", name="system_users_fillname", methods="PUT")
     * @param $MemberId
     * @param SystemUsersModel $model
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function fillName($MemberId, SystemUsersModel $model)
    {
        return $this->json($model->FillUserNameFromMemberRecord($MemberId));
    }
}
