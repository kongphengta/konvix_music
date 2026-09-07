<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        return $this->render('security/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error'         => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route('/forgot-password', name: 'app_forgot_password')]
    public function forgotPassword(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        UrlGeneratorInterface $urlGenerator
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        if ($request->isMethod('POST')) {
            $email = trim((string) $request->request->get('email', ''));

            if ($email !== '') {
                $user = $userRepository->findOneBy(['email' => $email]);

                if ($user) {
                    $token = bin2hex(random_bytes(32));
                    $expiresAt = new \DateTimeImmutable('+1 hour');
                    $user->setResetToken($token);
                    $user->setResetTokenExpiresAt($expiresAt);
                    $entityManager->flush();

                    $resetUrl = $urlGenerator->generate('app_reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);
                    $fromEmail = $_ENV['APP_MAILER_FROM'] ?? 'kong.vixay@gmail.com';

                    $message = (new Email())
                        ->from(new Address($fromEmail, 'Konvix Music'))
                        ->to(new Address($user->getEmail()))
                        ->subject('Réinitialisation de votre mot de passe')
                        ->html(sprintf(
                            '<p>Bonjour,</p><p>Vous avez demandé la réinitialisation de votre mot de passe.</p><p><a href="%s">Réinitialiser mon mot de passe</a></p><p>Ce lien expirera dans 1 heure.</p>',
                            htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8')
                        ));

                    $mailer->send($message);
                }
            }

            $this->addFlash('success', 'Si un compte existe pour cette adresse, un lien de réinitialisation vous sera envoyé.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/forgot_password.html.twig');
    }

    #[Route('/reset-password/{token}', name: 'app_reset_password')]
    public function resetPassword(
        string $token,
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = $userRepository->findOneBy(['resetToken' => $token]);

        if (!$user || !$user->getResetTokenExpiresAt() || $user->getResetTokenExpiresAt() < new \DateTimeImmutable()) {
            throw $this->createNotFoundException('Ce lien de réinitialisation est invalide ou expiré.');
        }

        if ($request->isMethod('POST')) {
            $password = (string) $request->request->get('password', '');
            $confirmPassword = (string) $request->request->get('confirm_password', '');

            if ($password !== '' && $password === $confirmPassword) {
                $user->setPassword($passwordHasher->hashPassword($user, $password));
                $user->setResetToken(null);
                $user->setResetTokenExpiresAt(null);
                $entityManager->flush();

                $this->addFlash('success', 'Votre mot de passe a été réinitialisé avec succès.');

                return $this->redirectToRoute('app_login');
            }

            $this->addFlash('error', 'Les deux mots de passe doivent correspondre.');
        }

        return $this->render('security/reset_password.html.twig', ['token' => $token]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): never
    {
        throw new \LogicException('Intercepté par le firewall Symfony.');
    }
}
