<?php

namespace App\Controller;

use App\Model\MyBroadcastsModel;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class MyBroadcastsController extends Controller
{
    /**
     * @Route("/api/broadcasts", name="broadcasts_get", methods="GET")
     * @param MyBroadcastsModel $model
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getBroadcasts(MyBroadcastsModel $model)
    {
        return $this->json($model->GetBroadcasts());
    }

    /**
     * @Route("/api/broadcasts/attachments/{Id}", name="broadcasts_view_attachment", METHODS="GET")
     * @param $Id
     * @param MyBroadcastsModel $model
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function viewAttachment($Id, MyBroadcastsModel $model)
    {
        return $model->ViewAttachment($Id);
    }
}
