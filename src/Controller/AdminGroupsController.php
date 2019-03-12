<?php

namespace App\Controller;

use App\Model\AdminGroupsModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AdminGroupsController extends AbstractController {
    /**
     * @Route("/api/admin/groups/members/{GroupId}", name="admin_groups_add_member", methods="POST")
     * @param $GroupId
     * @param AdminGroupsModel $model
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function addGroupMember($GroupId, AdminGroupsModel $model)
    {
        return $this->json($model->AddGroupMember($GroupId));
    }

    /**
     * @Route("/api/admin/groups/name/{GroupId}", name="admin_groups_name", methods={"PUT","POST"})
     * @param $GroupId
     * @param AdminGroupsModel $model
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function changeName($GroupId, AdminGroupsModel $model)
    {
        return $this->json($model->ChangeName($GroupId));
    }

    /**
     * @Route("/api/admin/groups/members/{GroupId}", name="admin_groups_get_members", methods="GET")
     * @param $GroupId
     * @param AdminGroupsModel $model
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getGroupMembers($GroupId, AdminGroupsModel $model)
    {
        return $this->json($model->GetGroupMembers($GroupId));
    }

    /**
     * @Route("/api/admin/groups/nonmembers/{GroupId}", name="admin_groups_get_nonmembers", methods="GET")
     * @param $GroupId
     * @param AdminGroupsModel $model
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getGroupNonMembers($GroupId, AdminGroupsModel $model)
    {
        return $this->json($model->GetGroupNonMembers($GroupId));
    }

    /**
     * @Route("/api/admin/groups/{OrgId}", name="admin_get_groups", methods="GET")
     * @param $OrgId
     * @param AdminGroupsModel $model
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getGroups($OrgId, AdminGroupsModel $model)
    {
        return $this->json($model->GetGroups($OrgId));
    }

    /**
     * @Route("/api/admin/groups/{OrgId}", name="admin_groups_new", methods="POST")
     * @param $OrgId
     * @param AdminGroupsModel $model
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function newGroup($OrgId, AdminGroupsModel $model)
    {
        return $this->json($model->NewGroup($OrgId));
    }

    /**
     * @Route("/api/admin/groups/remove/{GroupId}", name="admin_groups_remove", methods="DELETE")
     * @param $GroupId
     * @param AdminGroupsModel $model
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function removeGroup($GroupId, AdminGroupsModel $model)
    {
        return $this->json($model->RemoveGroup($GroupId));
    }

    /**
     * @Route("/api/admin/groups/members/{MemberId}", name="admin_groups_remove_member", methods="DELETE")
     * @param $MemberId
     * @param AdminGroupsModel $model
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function removeGroupMember($MemberId, AdminGroupsModel $model)
    {
        return $this->json($model->RemoveGroupMember($MemberId));
    }
}
