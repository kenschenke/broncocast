<?php

namespace App\Controller;

use App\Model\AdminBroadcastsModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AdminBroadcastsController extends AbstractController
{
    /**
     * @Route("/api/admin/broadcasts/cancel/{BroadcastId}", name="admin_broadcasts_cancel", METHODS="POST")
     * @param $BroadcastId
     * @param AdminBroadcastsModel $model
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function cancelBroadcast($BroadcastId, AdminBroadcastsModel $model)
    {
        return $this->json($model->CancelBroadcast($BroadcastId));
    }

    /**
     * @Route("/api/admin/broadcasts/{OrgId}", name="admin_broadcasts_get", methods="GET")
     * @param $OrgId
     * @param AdminBroadcastsModel $model
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getBroadcasts($OrgId, AdminBroadcastsModel $model)
    {
        return $this->json($model->GetBroadcasts($OrgId));
    }

    /**
     * @Route("/api/admin/broadcasts/groups/{OrgId}", name="admin_broadcasts_groups", methods="GET")
     * @param $OrgId
     * @param AdminBroadcastsModel $model
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getGroupMemberships($OrgId, AdminBroadcastsModel $model)
    {
        return $this->json($model->GetGroupMemberships($OrgId));
    }

    /**
     * @Route("/api/admin/broadcasts/new/{OrgId}", name="admin_broadcasts_new", methods="POST")
     * @param $OrgId
     * @param AdminBroadcastsModel $model
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function saveNewBroadcast($OrgId, AdminBroadcastsModel $model)
    {
        return $this->json($model->SaveNewBroadcast($OrgId));
    }

    /**
     * @Route("/api/admin/broadcasts/attachment", name="admin_broadcasts_attachment", methods="POST")
     * @param AdminBroadcastsModel $model
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function uploadAttachment(AdminBroadcastsModel $model)
    {
        return $this->json($model->HandleUpload());
    }
}
