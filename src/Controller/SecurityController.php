<?php

namespace App\Controller;

use App\Model\AuthenticationModel;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends Controller
{
    /**
     * @Route("/auth/login", name="auth_login")
     * @param AuthenticationModel $model
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function loginAction(AuthenticationModel $model)
    {
        return $model->Login();
    }

    /**
     * @Route("/auth/register", name="auth_register")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function registerFormAction()
    {
        return $this->render('register.html.twig');
    }

    /**
     * @Route("/auth/recover", name="auth_recover")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function recoverFormAction()
    {
        return $this->render('recover.html.twig');
    }

    /**
     * @Route("/auth/login_check", name="login_check")
     */
    public function loginCheckAction()
    {
        // this controller will not be executed
        // as this route is handled by the Security system
        throw new \Exception('Should not be here');
    }
}
