<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function choose(): Response
    {
        return $this->render('registration/choose.html.twig');
    }

    #[Route('/register/{accountType}', name: 'app_register_role', requirements: ['accountType' => 'artist|auditeur|admin'])]
    public function register(Request $request, ManagerRegistry $doctrine, UserPasswordHasherInterface $passwordHasher, MailerInterface $mailer, string $accountType): Response
    {
        $user = new User();
        $user->setAccountType($accountType);
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = trim((string) $form->get('email')->getData());
            $existingUser = $doctrine->getRepository(User::class)->findOneBy(['email' => $email]);

            if ($existingUser) {
                $this->addFlash('error', 'Un compte existe déjà avec cette adresse email.');

                return $this->redirectToRoute('app_register_role', ['accountType' => $accountType]);
            }

            $plainPassword = $form->get('plainPassword')->getData();

            if (null !== $plainPassword) {
                $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
            }

            $verificationCode = (string) random_int(100000, 999999);
            $user->setRoles($accountType === 'artist' ? ['ROLE_USER', 'ROLE_ARTISTE'] : ['ROLE_USER']);
            $user->setIsVerified(false);
            $user->setEmailVerificationCode($verificationCode);

            $entityManager = $doctrine->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $message = (new Email())
                ->from(new Address('noreply@konvix.music', 'Konvix Music'))
                ->to(new Address($user->getEmail()))
                ->subject('Votre code de vérification Konvix Music')
                ->text(sprintf(
                    "Bonjour %s,\n\nVotre code de vérification Konvix Music est : %s\n\nEntrez ce code pour valider votre compte.",
                    $user->getFirstName() ?: 'utilisateur',
                    $verificationCode,
                ));

            $mailer->send($message);

            $this->addFlash('success', sprintf('Un code de vérification a été envoyé à %s. Merci de le vérifier pour valider votre compte.', $email));

            return $this->redirect($this->generateUrl('app_verify_email', ['email' => $user->getEmail()]));
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
            'accountType' => $accountType,
            'accountLabel' => match ($accountType) {
                'artist' => 'artiste',
                'auditeur' => 'auditeur',
                'admin' => 'administrateur',
                default => 'utilisateur',
            },
        ]);
    }

    #[Route('/verify-email/resend', name: 'app_verify_email_resend', methods: ['POST'])]
    public function resendVerificationCode(Request $request, ManagerRegistry $doctrine, MailerInterface $mailer): Response
    {
        $email = trim((string) $request->request->get('email', ''));
        $user = $doctrine->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            $this->addFlash('error', 'Aucun compte trouvé pour cette adresse email.');

            return $this->redirectToRoute('app_register');
        }

        $verificationCode = (string) random_int(100000, 999999);
        $user->setEmailVerificationCode($verificationCode);
        $doctrine->getManager()->flush();

        $message = (new Email())
            ->from(new Address('noreply@konvix.music', 'Konvix Music'))
            ->to(new Address($user->getEmail()))
            ->subject('Votre nouveau code de vérification Konvix Music')
            ->text(sprintf(
                "Bonjour %s,\n\nVotre nouveau code de vérification Konvix Music est : %s\n\nEntrez ce code pour valider votre compte.",
                $user->getFirstName() ?: 'utilisateur',
                $verificationCode,
            ));

        $mailer->send($message);

        $this->addFlash('success', 'Un nouveau code de vérification a été envoyé.');

        return $this->redirect($this->generateUrl('app_verify_email', ['email' => $user->getEmail()]));
    }

    #[Route('/verify-email', name: 'app_verify_email')]
    public function verifyEmail(Request $request, ManagerRegistry $doctrine): Response
    {
        $email = (string) ($request->query->get('email') ?? $request->request->get('email', ''));
        $user = $doctrine->getRepository(User::class)->findOneBy(['email' => $email]);

        if ($request->isMethod('POST')) {
            $submittedCode = (string) $request->request->get('code', '');

            if (!$user) {
                $this->addFlash('error', 'Aucun compte trouvé pour cette adresse email.');

                return $this->redirectToRoute('app_register');
            }

            if ($user->getEmailVerificationCode() === $submittedCode) {
                $user->setIsVerified(true);
                $user->setEmailVerificationCode(null);
                $doctrine->getManager()->flush();

                $this->addFlash('success', 'Votre email a bien été vérifié. Vous pouvez maintenant vous connecter.');

                return $this->redirectToRoute('app_login');
            }

            $this->addFlash('error', 'Le code de vérification est incorrect.');
        }

        return $this->render('registration/verify_email.html.twig', [
            'email' => $email,
            'user' => $user,
        ]);
    }
}
