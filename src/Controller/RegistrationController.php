<?php

namespace App\Controller;

use App\Entity\User;
use DateTimeImmutable;
use App\Security\EmailVerifier;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Symfony\Component\Mime\Address;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    #[Route('/register', name: 'visitor.registration.register', methods: ['GET', 'POST'])]
    public function register(Request $request,
     UserPasswordHasherInterface $userPasswordHasher, 
     EntityManagerInterface $entityManager
     ): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('visitor.registration.verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('japapdafrique@gmail.com', 'Marie Nana'))
                    ->to($user->getEmail())
                    ->subject("Validation du compte par email sur le blog Japap d'Afrique")
                    ->htmlTemplate('emails/confirmation_email.html.twig')
            );

            return $this->redirectToRoute('visitor.registration.waiting_for_email_verification');
        }

        return $this->render('pages/visitor/registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/register/waiting-for-email-verification', name: 'visitor.registration.waiting_for_email_verification', methods:['GET'])]
    public function emailVerif(): Response

    {
        return$this->render("pages/visitor/registration/waiting_for_email_verif.html.twig");
    }


    #[Route('/verify/email', name: 'visitor.registration.verify_email')]
    public function verifyUserEmail
    (Request $request,
    TranslatorInterface $translator, 
    UserRepository $userRepository,
    EntityManagerInterface $em
    
    ): Response
    {
        $id = $request->query->get('id');

        if (null === $id) {
            return $this->redirectToRoute('visitor.registration.register');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('visitor.registration.register');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('visitor.registration.register');
        }

        $user->setVerifiedAt(new DateTimeImmutable());
        
        $em->persist($user);
        $em->flush();

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Votre compte a bien été vérifié! Vous pouvez vous connecter.');

        return $this->redirectToRoute('visitor.welcome.index');
    }
}
