<?php

namespace App\Model;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ProfileModel
{
    private $em;
    private $tokenStorage;
    private $requestStack;
    private $encoder;

    public function __construct(EntityManagerInterface $em, TokenStorageInterface $tokenStorage,
                                RequestStack $requestStack, UserPasswordEncoderInterface $encoder)
    {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
        $this->requestStack = $requestStack;
        $this->encoder = $encoder;
    }

    public function ChangePassword()
    {
        $request = $this->requestStack->getCurrentRequest();

        try {
            if (!$request->request->has('Password')) {
                throw new \Exception('Password parameter missing');
            }
            $password = trim($request->request->get('Password'));

            $user = $this->tokenStorage->getToken()->getUser();
            $encoded = $this->encoder->encodePassword($user, $password);
            $user->setPassword($encoded);
            $this->em->persist($user);
            $this->em->flush();

            return ['Success' => true];
        } catch (\Exception $e) {
            return ['Success' => false, 'Error' => $e->getMessage()];
        }
    }

    public function CheckCurrentPassword()
    {
        $request = $this->requestStack->getCurrentRequest();

        try {
            if (!$request->request->has('Password')) {
                throw new \Exception('Password parameter missing');
            }
            $password = trim($request->request->get('Password'));

            $user = $this->tokenStorage->getToken()->getUser();

            return ['Success' => true, 'Valid' => $this->encoder->isPasswordValid($user, $password)];
        } catch (\Exception $e) {
            return ['Success' => false, 'Error' => $e->getMessage()];
        }
    }

    public function GetProfile()
    {
        $user = $this->tokenStorage->getToken()->getUser();

        return [
            'Success' => true,
            'UsrName' => $user->getFullname(),
            'SingleMsg' => $user->getSingleMsg(),
        ];
    }

    public function SaveProfile()
    {
        $request = $this->requestStack->getCurrentRequest();

        try {
            if (!$request->request->has('UsrName')) {
                throw new \Exception('Missing UsrName Parameter');
            }
            if (!$request->request->has('SingleMsg')) {
                throw new \Exception('Missing SingleMsg Parameter');
            }

            $UsrName = trim($request->request->get('UsrName'));
            if (strlen($UsrName) > 30) {
                throw new \Exception('UsrName Parameter Too Long');
            }

            $SingleMsg = $request->request->get('SingleMsg') === 'true';

            $user = $this->tokenStorage->getToken()->getUser();
            $user->setFullname($UsrName);
            $user->setSingleMsg($SingleMsg);
            $this->em->persist($user);
            $this->em->flush();

            return ['Success' => true];
        } catch (\Exception $e) {
            return ['Success' => false, 'Error' => $e->getMessage()];
        }
    }
}
