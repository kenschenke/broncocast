<?php

namespace App\Controller;

use App\Model\AppModel;
use App\Model\AuthenticationModel;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends Controller
{
    /**
     * @Route("/api/recover/send", name="recover_send", METHODS="POST")
     * @param AppModel $appModel
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function appSendRecoveryCode(AppModel $appModel)
    {
        return $this->json($appModel->SendRecoveryCode());
    }

    /**
     * @Route("/api/recover/save", name="recover_save", METHODS="POST")
     * @param AppModel $appModel
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function appRecoverByCode(AppModel $appModel)
    {
        return $this->json($appModel->RecoverUsingCode());
    }

    /**
     * @Route("/auth/isauth", name="auth_isauth", methods="GET")
     * @param AuthenticationModel $model
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function isAuthAction(AuthenticationModel $model)
    {
        return $this->json($model->AppAuthenticate());
    }

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
     * @Route("/auth/register", name="auth_register", methods="GET")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function registerFormAction()
    {
        return $this->render('register.html.twig');
    }

    /**
     * @Route("/auth/register", name="auth_register_save", methods="POST")
     * @return Response
     */
    public function registerSaveAction(AuthenticationModel $model)
    {
        return $model->Register();
    }

    /**
     * @Route("/api/register/welcome", name="register_welcome", methods="PUT")
     * @param AuthenticationModel $model
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function registerWelcome(AuthenticationModel $model)
    {
        return $this->json($model->SendWelcome());
    }

    /**
     * @Route("/auth/recover", name="auth_recover", methods="GET")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function recoverFormAction()
    {
        return $this->render('recover.html.twig',
            ['email' => '', 'err' => '', 'msg' => '']);
    }

    /**
     * @Route("/auth/recover/{Token}", name="auth_recover_link", methods="GET")
     * @param $Token
     * @param AuthenticationModel $model
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function recoverLink($Token, AuthenticationModel $model)
    {
        return $model->Recover($Token);
    }

    /**
     * @Route("/auth/recover", name="auth_recover_post", methods="POST")
     * @param AuthenticationModel $model
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function recoverSendAction(AuthenticationModel $model)
    {
        return $model->SendRecoverEmail();
    }

    /**
     * @Route("/auth/recover/update", name="auth_recover_updatepwd", methods="POST")
     * @param AuthenticationModel $model
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function recoverUpdatePassword(AuthenticationModel $model)
    {
        return $model->UpdatePassword();
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
