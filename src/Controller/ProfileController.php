<?php

namespace App\Controller;

use App\Model\ProfileModel;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends Controller
{
    /**
     * @Route("/api/profile", name="profile_get", methods="GET")
     * @param ProfileModel $model
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getProfile(ProfileModel $model)
    {
        return $this->json($model->GetProfile());
    }

    /**
     * @Route("/api/profile", name="profile_put", methods="PUT")
     * @param ProfileModel $model
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function putProfile(ProfileModel $model)
    {
        return $this->json($model->SaveProfile());
    }

    /**
     * @Route("/api/profile/password", name="profile_password", methods="PUT")
     * @param ProfileModel $model
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function changePassword(ProfileModel $model)
    {
        return $this->json($model->ChangePassword());
    }

    /**
     * @Route("/api/profile/currentpwd", name="profile_currentpwd", methods="PUT")
     * @param ProfileModel $model
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function checkCurrentPwd(ProfileModel $model)
    {
        return $this->json($model->CheckCurrentPassword());
    }
}
