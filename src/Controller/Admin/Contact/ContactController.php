<?php

namespace App\Controller\Admin\Contact;

use App\Entity\Contact;
use App\Repository\ContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ContactController extends AbstractController
{
    #[Route('/admin/contact/list', name: 'admin.contact.index', methods:['GET'])]
    public function index(ContactRepository $contactRepository): Response
    {
        $contacts = $contactRepository->findAll();
        return $this->render('pages/admin/contact/index.html.twig', [
            "contacts" => $contacts
        ]);
    }

    #[Route('/admin/contact/{id}/delete', name: 'admin.contact.delete', methods:['DELETE'])]
    public function delete(Contact $contact, Request $request, EntityManagerInterface $em): Response
    {
        if ( $this->isCsrfTokenValid('delete_contact_'.$contact->getId(), $request->request->get('csrf_token') ) )
        {
            $em->remove($contact);

            $em->flush();

            $this->addFlash('success', "Ce contact a été supprimé.");
        }

        return $this->redirectToRoute('admin.contact.index');
    }
}
