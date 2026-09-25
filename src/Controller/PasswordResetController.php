<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class PasswordResetController extends AbstractController
{
    #[Route('/mot-de-passe-oublie', name: 'app_forgot_password', methods: ['GET', 'POST'])]
    public function request(Request $request, ManagerRegistry $doctrine, MailerInterface $mailer, UrlGeneratorInterface $urlGenerator): Response
    {
        if ($request->isMethod('POST')) {
            $email = trim((string) $request->request->get('email', ''));

            if ('' === $email) {
                $this->addFlash('error', 'Veuillez renseigner une adresse email.');

                return $this->redirectToRoute('app_forgot_password');
            }

            $user = $doctrine->getRepository(User::class)->findOneBy(['email' => $email]);

            if ($user) {
                $token = bin2hex(random_bytes(32));
                $user->setPasswordResetToken($token);
                $user->setPasswordResetExpiresAt(new \DateTimeImmutable('+1 hour'));
                $doctrine->getManager()->flush();

                $resetUrl = $urlGenerator->generate('app_reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

                $message = (new Email())
                    ->from(new Address('noreply@konvix.music', 'Konvix Music'))
                    ->to(new Address($user->getEmail()))
                    ->subject('Réinitialisation de votre mot de passe Konvix Music')
                    ->text(sprintf(
                        "Bonjour %s,\n\nPour réinitialiser votre mot de passe, cliquez sur le lien suivant : %s\n\nCe lien expire dans 1 heure.\n",
                        $user->getFirstName() ?: 'utilisateur',
                        $resetUrl,
                    ));

                $mailer->send($message);
            }

            $this->addFlash('success', 'Un lien de réinitialisation a été envoyé si un compte correspond à cette adresse.');

            return $this->redirectToRoute('app_forgot_password');
        }

        return $this->render('security/forgot_password.html.twig');
    }

    #[Route('/reinitialiser-mot-de-passe', name: 'app_reset_password', methods: ['GET', 'POST'])]
    public function reset(Request $request, ManagerRegistry $doctrine, UserPasswordHasherInterface $passwordHasher): Response
    {
        $token = trim((string) ($request->query->get('token') ?? $request->request->get('token', '')));
        $user = '' === $token ? null : $doctrine->getRepository(User::class)->findOneBy(['passwordResetToken' => $token]);

        if (!$user || !$user->getPasswordResetExpiresAt() || $user->getPasswordResetExpiresAt() < new \DateTimeImmutable()) {
            $this->addFlash('error', 'Ce lien de réinitialisation est invalide ou expiré.');

            return $this->redirectToRoute('app_forgot_password');
        }

        if ($request->isMethod('POST')) {
            $newPassword = (string) $request->request->get('newPassword', '');
            $confirmPassword = (string) $request->request->get('confirmPassword', '');

            if ('' === $newPassword || $newPassword !== $confirmPassword) {
                $this->addFlash('error', 'Les mots de passe ne correspondent pas.');

                return $this->render('security/reset_password.html.twig', [
                    'token' => $token,
                ]);
            }

            $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
            $user->setPasswordResetToken(null);
            $user->setPasswordResetExpiresAt(null);
            $doctrine->getManager()->flush();

            $this->addFlash('success', 'Votre mot de passe a été réinitialisé.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password.html.twig', [
            'token' => $token,
        ]);
    }
}
