<?php

namespace App\Model;

use App\Entity\Contacts;
use App\Entity\OrgMembers;
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

            $Key = strtolower(trim($request->request->get('Key')));
            if (strlen($Key) > 150) {
                throw new \Exception('Key parameter too long');
            }

            if ($this->messageUtil->IsPhone($Key)) {
                $contactType = Contacts::TYPE_PHONE;
            } elseif ($this->messageUtil->IsEmail($Key)) {
                $contactType = Contacts::TYPE_EMAIL;
            } else {
                throw new \Exception('Unrecognized Contact Type');
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
            $contact->setContactType($contactType);
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
        $results = [];
        foreach ($user->getContacts() as $contact) {
            $contactType = $contact->getContactType();
            if ($contactType !== Contacts::TYPE_EMAIL && $contactType !== Contacts::TYPE_PHONE) {
                continue;
            }

            $results[] = [
                'ContactId' => $contact->getId(),
                'Contact' => $contact->getContact(),
            ];
        }

        return $results;
    }

    public function TestContact($id)
    {
        try {
            /** @var Users $User */
            $User = $this->tokenStorage->getToken()->getUser();

            // Don't bother sending a test if the user is a member of the APPREVIEW org
            if ($User->getOrgs()->count() === 1) {
                /** @var OrgMembers $OrgMember */
                $OrgMember = $User->getOrgs()[0];
                if ($OrgMember->getOrg()->getTag() === 'APPREVIEW') {
                    return ['Success' => false, 'Error' => 'Tests cannot be sent from this account'];
                }
            }

            $UserId = $User->getId();

            $Contact = $this->em->getRepository('App:Contacts')->find($id);
            if (is_null($Contact)) {
                throw new \Exception('Contact record not found');
            }
            if ($Contact->getUserId() !== $UserId) {
                throw new \Exception('Unauthorized for contact record');
            }

            $TestMessage = 'This is a test message from BroncoCast';
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

            $Key = strtolower(trim($request->request->get('Key')));
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

            if (!$this->messageUtil->IsPhone($Key) && !$this->messageUtil->IsEmail($Key)) {
                throw new \Exception('The contact information does not resemble an email or phone number');
            }

            // See if another contact record with the new key already exists

            $OtherContact = $this->em->getRepository('App:Contacts')->findOneBy(['contact' => $Key]);
            if (!is_null($OtherContact) && $OtherContact->getId() !== $Contact->getId()) {
                throw new \Exception('This email or phone number is already in use');
            }

            $Contact->setContact($Key);
            $this->em->persist($Contact);
            $this->em->flush();

            return ['Success' => true];
        } catch (\Exception $e) {
            return ['Success' => false, 'Error' => $e->getMessage()];
        }
    }
}
