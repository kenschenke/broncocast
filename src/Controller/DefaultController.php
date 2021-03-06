<?php

namespace App\Controller;

use App\Model\AppModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class DefaultController extends AbstractController
{
    /**
     * @Route("/main", name="mainpage")
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param AppModel $appModel
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function mainAction(AuthorizationCheckerInterface $authorizationChecker, AppModel $appModel)
    {
        if ($authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $params = $appModel->GetAppParams();
            $params['InitialRoute'] = 'profile';
            return $this->render('app.html.twig', $params);
        } else {
            return $this->redirectToRoute('auth_login');
        }
    }

    /**
     * @Route("/profile", name="profile")
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param AppModel $appModel
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function profileAction(AuthorizationCheckerInterface $authorizationChecker, AppModel $appModel)
    {
        if ($authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $params = $appModel->GetAppParams();
            $params['InitialRoute'] = 'profile';
            return $this->render('app.html.twig', $params);
        } else {
            return $this->redirectToRoute('auth_login');
        }
    }

    /**
     * @Route("/broadcasts", name="broadcasts")
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param AppModel $appModel
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function broadcastsAction(AuthorizationCheckerInterface $authorizationChecker, AppModel $appModel)
    {
        if ($authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $params = $appModel->GetAppParams();
            $params['InitialRoute'] = 'broadcasts';
            return $this->render('app.html.twig', $params);
        } else {
            return $this->redirectToRoute('auth_login');
        }
    }

    /**
     * @Route("/admin/users", name="admin_users")
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param TokenStorageInterface $tokenStorage
     * @param AppModel $appModel
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function adminUsersAction(AuthorizationCheckerInterface $authorizationChecker,
                                     TokenStorageInterface $tokenStorage, AppModel $appModel)
    {
        if ($authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $roles = $tokenStorage->getToken()->getUser()->getRoles();
            if (!in_array('ROLE_ORG_ADMIN', $roles) && !in_array('ROLE_SYSTEM_ADMIN', $roles)) {
                return $this->redirectToRoute('mainpage');
            } else {
                $params = $appModel->GetAppParams();
                $params['InitialRoute'] = 'admin/users';
                return $this->render('app.html.twig', $params);
            }
        } else {
            return $this->redirectToRoute('auth_login');
        }
    }

    /**
     * @Route("/admin/groups", name="admin_groups")
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param TokenStorageInterface $tokenStorage
     * @param AppModel $appModel
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function adminGroupsAction(AuthorizationCheckerInterface $authorizationChecker,
                                      TokenStorageInterface $tokenStorage, AppModel $appModel)
    {
        if ($authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $roles = $tokenStorage->getToken()->getUser()->getRoles();
            if (!in_array('ROLE_ORG_ADMIN', $roles) && !in_array('ROLE_SYSTEM_ADMIN', $roles)) {
                return $this->redirectToRoute('mainpage');
            } else {
                $params = $appModel->GetAppParams();
                $params['InitialRoute'] = 'admin/groups';
                return $this->render('app.html.twig', $params);
            }
        } else {
            return $this->redirectToRoute('auth_login');
        }
    }

    /**
     * @Route("/admin/broadcasts", name="admin_broadcasts")
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param TokenStorageInterface $tokenStorage
     * @param AppModel $appModel
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function adminBroadcastsAction(AuthorizationCheckerInterface $authorizationChecker,
                                          TokenStorageInterface $tokenStorage, AppModel $appModel)
    {
        if ($authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $roles = $tokenStorage->getToken()->getUser()->getRoles();
            if (!in_array('ROLE_ORG_ADMIN', $roles) && !in_array('ROLE_SYSTEM_ADMIN', $roles)) {
                return $this->redirectToRoute('mainpage');
            } else {
                $params = $appModel->GetAppParams();
                $params['InitialRoute'] = 'admin/broadcasts';
                return $this->render('app.html.twig', $params);
            }
        } else {
            return $this->redirectToRoute('auth_login');
        }
    }

    /**
     * @Route("/system/orgs", name="system_orgs")
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param TokenStorageInterface $tokenStorage
     * @param AppModel $appModel
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function systemOrgsAction(AuthorizationCheckerInterface $authorizationChecker,
                                     TokenStorageInterface $tokenStorage, AppModel $appModel)
    {
        if ($authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $roles = $tokenStorage->getToken()->getUser()->getRoles();
            if (!in_array('ROLE_SYSTEM_ADMIN', $roles)) {
                return $this->redirectToRoute('mainpage');
            } else {
                $params = $appModel->GetAppParams();
                $params['InitialRoute'] = 'system/orgs';
                return $this->render('app.html.twig', $params);
            }
        } else {
            return $this->redirectToRoute('auth_login');
        }
    }

    /**
     * @Route("/system/users", name="system_users")
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param TokenStorageInterface $tokenStorage
     * @param AppModel $appModel
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function systemUsersAction(AuthorizationCheckerInterface $authorizationChecker,
                                      TokenStorageInterface $tokenStorage, AppModel $appModel)
    {
        if ($authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $roles = $tokenStorage->getToken()->getUser()->getRoles();
            if (!in_array('ROLE_SYSTEM_ADMIN', $roles)) {
                return $this->redirectToRoute('mainpage');
            } else {
                $params = $appModel->GetAppParams();
                $params['InitialRoute'] = 'system/users';
                return $this->render('app.html.twig', $params);
            }
        } else {
            return $this->redirectToRoute('auth_login');
        }
    }

    /**
     * @Route("/about", name="about")
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param AppModel $appModel
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function aboutAction(AuthorizationCheckerInterface $authorizationChecker, AppModel $appModel)
    {
        if ($authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $params = $appModel->GetAppParams();
            $params['InitialRoute'] = 'about';
            return $this->render('app.html.twig', $params);
        } else {
            return $this->redirectToRoute('auth_login');
        }
    }

    /**
     * @Route("/register", name="register")
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param AppModel $appModel
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function registerAction(AuthorizationCheckerInterface $authorizationChecker, AppModel $appModel)
    {
        if ($authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $params = $appModel->GetAppParams();
            $params['InitialRoute'] = 'register';
            return $this->render('app.html.twig', $params);
        } else {
            return $this->redirectToRoute('auth_login');
        }
    }

    /**
     * @Route("/api/register", name="register_app", methods="POST")
     * @param AppModel $appModel
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function registerAppAction(AppModel $appModel)
    {
        return $this->json($appModel->RegisterUser());
    }
}
