<?php

namespace App\Controller;

use App\Model\TimezonesModel;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class TimezonesController extends Controller
{
    /**
     * @Route("/api/timezones", name="get_timezones", methods="GET")
     * @param TimezonesModel $model
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getTimezones(TimezonesModel $model)
    {
        return $this->json($model->GetTimezones());
    }
}
