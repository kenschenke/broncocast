<?php

namespace App\Controller;

use App\Model\ContactsModel;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class ContactsController extends Controller
{
    /**
     * @Route("/api/contacts/{id}", name="contacts_delete", methods="DELETE")
     * @param $id
     * @param ContactsModel $model
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteContact($id, ContactsModel $model)
    {
        return $this->json($model->DeleteContact($id));
    }

    /**
     * @Route("/api/contacts", name="contacts_get", methods="GET")
     * @param ContactsModel $model
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getContacts(ContactsModel $model)
    {
        return $this->json($model->GetContacts());
    }

    /**
     * @Route("/api/contacts", name="contacts_post", methods="POST")
     * @param ContactsModel $model
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function newContactAction(ContactsModel $model)
    {
        return $this->json($model->AddContact());
    }

    /**
     * @Route("/api/contacts/{id}", name="contacts_put", methods={"PUT","POST"})
     * @param $id
     * @param ContactsModel $model
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function putContactAction($id, ContactsModel $model)
    {
        return $this->json($model->UpdateContact($id));
    }

    /**
     * @Route("/api/contacts/test/{id}", name="contacts_test", methods="PUT")
     * @param $id
     * @param ContactsModel $model
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function testContact($id, ContactsModel $model)
    {
        return $this->json($model->TestContact($id));
    }
}
