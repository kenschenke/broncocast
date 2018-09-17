<?php

namespace App\Model;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AuthenticationModel
{
    protected $twig;
    protected $authenticationUtils;

    public function __construct(\Twig_Environment $twig, AuthenticationUtils $authenticationUtils)
    {
        $this->twig = $twig;
        $this->authenticationUtils = $authenticationUtils;
    }

    /**
     * @return Response
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function Login()
    {
        // Get the login error if there is one
        $error = $this->authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $this->authenticationUtils->getLastUsername();

        return new Response($this->twig->render('login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]));
    }
}
