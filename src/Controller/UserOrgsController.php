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
}
