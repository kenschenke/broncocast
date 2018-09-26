<?php

namespace App\Model;

use App\Entity\Contacts;
use App\Entity\Users;
use App\Util\MessageUtil;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ContactsModel
{
    private $em;
    private $tokenStorage;
    private $requestStack;
    private $messageUtil;

    public function __construct(EntityManagerInterface $em, TokenStorageInterface $tokenStorage,
                                RequestStack $requestStack, MessageUtil $messageUtil)
    {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
        $this->requestStack = $requestStack;
        $this->messageUtil = $messageUtil;
    }

    public function AddContact()
    {
        $request = $this->requestStack->getCurrentRequest();

        try {
            if (!$request->request->has('Key')) {
                throw new \Exception('Key parameter missing');
            }

            $Key = trim($request->request->get('Key'));
            if (strlen($Key) > 50) {
                throw new \Exception('Key parameter too long');
            }

            $CarId = (int)($request->request->get('CarId', 0));
            if (!$CarId) {
                // Verify the input resembles an email address

                if (!preg_match("/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}\b/", $Key))
                    throw new \Exception("Invalid email address");
                $Carrier = null;
            } else {
                // Drop everything from the the input except digits

                $Key = preg_replace("/[^0-9]/", "", $Key);
                if (strlen($Key) != 10)
                    throw new \Exception("Mobile numbers must be 10 digits");

                // Make sure the CarId is valid

                $carrRepo = $this->em->getRepository('App:Carriers');
                $Carrier = $carrRepo->find($CarId);
                if (is_null($Carrier)) {
                    throw new \Exception('Carrier Id is not valid');
                }
            }

            // Make sure this contact record doesn't already exist

            $contactRepo = $this->em->getRepository('App:Contacts');
            if (!is_null($contactRepo->findOneBy(['contact' => $Key]))) {
                throw new \Exception('This email or phone number is already in use');
            }

            // Add the contact record

            $contact = new Contacts();
            $user = $this->tokenStorage->getToken()->getUser();
            $contact->setUser($user);
            $contact->setContact($Key);
            if (!is_null($Carrier)) {
                $contact->setCarrierId($CarId);
            }
            $this->em->persist($contact);
            $this->em->flush();

            return ['Success' => true, 'ContactId' => $contact->getId()];
        } catch (\Exception $e) {
            return ['Success' => false, 'Error' => $e->getMessage()];
        }
    }

    public function DeleteContact($id)
    {
        try {
            $repo = $this->em->getRepository('App:Contacts');
            $Contact = $repo->find($id);
            if (is_null($Contact)) {
                throw new \Exception('Contact record not found');
            }

            $user = $this->tokenStorage->getToken()->getUser();
            if ($user->getId() !== $Contact->getUserId()) {
                throw new \Exception('This contact record belongs to a different user');
            }

            $this->em->remove($Contact);
            $this->em->flush();

            return ['Success' => true];
        } catch (\Exception $e) {
            return ['Success' => false, 'Error' => $e->getMessage()];
        }
    }

    public function GetContacts()
    {
        try {
            $user = $this->tokenStorage->getToken()->getUser();

            return ['Success' => true, 'Contacts' => $this->GetContactsForUser($user)];
        } catch (\Exception $e) {
            return ['Success' => false, 'Error' => $e->getMessage()];
        }
    }

    public function GetContactsForUser(Users $user)
    {
        $carrRepo = $this->em->getRepository('App:Carriers');

        $results = [];
        foreach ($user->getContacts() as $contact) {
            $CarName = '';
            if ($contact->getCarrierId() !== null) {
                $carrier = $carrRepo->find($contact->getCarrierId());
                if ($carrier !== null) {
                    $CarName = $carrier->getName();
                }
            }

            $results[] = [
                'ContactId' => $contact->getId(),
                'Contact' => $contact->getContact(),
                'CarId' => $contact->getCarrierId(),
                'CarName' => $CarName,
            ];
        }

        return $results;
    }

    public function TestContact($id)
    {
        try {
            $UserId = $this->tokenStorage->getToken()->getUser()->getId();

            $Contact = $this->em->getRepository('App:Contacts')->find($id);
            if (is_null($Contact)) {
                throw new \Exception('Contact record not found');
            }
            if ($Contact->getUserId() !== $UserId) {
                throw new \Exception('Unauthorized for contact record');
            }

            $TestMessage = 'This is a test messages from BroncoCast';
            $ContactStr = $Contact->getContact();
            if ($this->messageUtil->IsEmail($ContactStr)) {
                $this->messageUtil->SendEmail([$ContactStr], $TestMessage, null, null, null);
            } elseif ($this->messageUtil->IsPhone($ContactStr)) {
                $this->messageUtil->SendSMS([[
                    'ContactId' => $id,
                    'Phone' => $ContactStr,
                ]], $TestMessage);
            } else {
                throw new \Exception('Unrecognized contact type');
            }

            return ['Success' => true];
        } catch (\Exception $e) {
            return ['Success' => false, 'Error' => $e->getMessage()];
        }
    }

    public function UpdateContact($id)
    {
        $request = $this->requestStack->getCurrentRequest();

        try {
            if (!$request->request->has('Key')) {
                throw new \Exception('Key parameter missing');
            }

            $Key = trim($request->request->get('Key'));
            if (strlen($Key) > 50) {
                throw new \Exception('Key parameter too long');
            }

            $contactRepo = $this->em->getRepository('App:Contacts');
            $Contact = $contactRepo->find($id);
            if (is_null($Contact)) {
                throw new \Exception('Contact record not found');
            }

            $user = $this->tokenStorage->getToken()->getUser();
            if ($user->getId() !== $Contact->getUserId()) {
                throw new \Exception('This contact record belongs to a different user');
            }

            $CarId = (int)($request->request->get('CarId', 0));
            if (!$CarId) {
                // Verify the input resembles a valid email address.

                if (!preg_match("/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}\b/", $Key))
                    throw new \Exception("Invalid email address");
                $Carrier = null;
            } else {
                // Drop everything from the the input except digits

                $Key = preg_replace("/[^0-9]/", "", $Key);
                if (strlen($Key) != 10)
                    throw new \Exception("Mobile numbers must be 10 digits");

                // Make sure the CarId is valid

                $carrRepo = $this->em->getRepository('App:Carriers');
                $Carrier = $carrRepo->find($CarId);
                if (is_null($Carrier)) {
                    throw new \Exception('Carrier Id is not valid');
                }
            }

            $Contact->setContact($Key);
            $Contact->setCarrierId($CarId);
            $this->em->persist($Contact);
            $this->em->flush();

            return ['Success' => true];
        } catch (\Exception $e) {
            return ['Success' => false, 'Error' => $e->getMessage()];
        }
    }
}
