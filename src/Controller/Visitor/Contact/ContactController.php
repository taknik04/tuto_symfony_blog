<?php

namespace App\Controller\Visitor\Contact;

use App\Entity\Contact;
use App\Form\ContactFormType;
use App\Repository\SettingRepository;
use App\Service\SendEmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'visitor.contact.create', methods:['GET', 'POST'])]
    public function index(
        SettingRepository $settingRepository, 
        Request $request, 
        EntityManagerInterface $em,
        SendEmailService $sendEmailService
    ): Response
    {

        $contact = new Contact();

        $form = $this->createForm(ContactFormType::class, $contact);

        $form->handleRequest($request);

        if ( $form->isSubmitted() && $form->isValid() ) 
        {
            $em->persist($contact);
            $em->flush();

            // Envoi de l'email
            $sendEmailService->send([
                "sender_email" => "japap-dafrique@gmail.com",
                "sender_name"  => "Marie NANA",
                "recipient_email" => "japap-dafrique@gmail.com",
                "subject" => "Un message reçu sur votre blog",
                "html_template" => "emails/contact.html.twig",
                "context"   => [
                    "contact_first_name"    => $contact->getFirstName(),
                    "contact_last_name"     => $contact->getLastName(),
                    "contact_email"         => $contact->getEmail(),
                    "contact_phone"         => $contact->getPhone(),
                    "contact_message"       => $contact->getMessage(),
                ]
            ]);

            $this->addFlash("success", "Votre message a bien été envoyé. Nous vous recontacterons dans les plus brefs délais.");

            return $this->redirectToRoute("visitor.contact.create");
        }

        return $this->render('pages/visitor/contact/index.html.twig', [
            "form"    => $form->createView(),
            "setting" => $settingRepository->find(1)
        ]);
    }
}
