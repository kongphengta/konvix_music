<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

final class EmailVerificationService
{
    public function __construct(
        private readonly ManagerRegistry $doctrine,
        private readonly MailerInterface $mailer,
    ) {
    }

    public function sendCode(User $user): void
    {
        $verificationCode = (string) random_int(100000, 999999);
        $user->setEmailVerificationCode($verificationCode);

        $entityManager = $this->doctrine->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        $fromAddress = $this->resolveFromAddress();

        $message = (new Email())
            ->from(new Address($fromAddress, 'Konvix Music'))
            ->to(new Address($user->getEmail()))
            ->subject('Votre code de vérification Konvix Music')
            ->text(sprintf(
                "Bonjour %s,\n\nVotre code de vérification Konvix Music est : %s\n\nEntrez ce code pour valider votre compte.",
                $user->getFirstName() ?: 'utilisateur',
                $verificationCode,
            ));

        try {
            $this->mailer->send($message);
        } catch (TransportExceptionInterface $exception) {
            throw new \RuntimeException(sprintf(
                'Le mail de vérification n’a pas pu être envoyé à %s.',
                $user->getEmail() ?? 'l’utilisateur'
            ), 0, $exception);
        }
    }

    private function resolveFromAddress(): string
    {
        $configuredFrom = trim((string) ($_ENV['MAILER_FROM'] ?? $_SERVER['MAILER_FROM'] ?? ''));
        $dsn = trim((string) ($_ENV['MAILER_DSN'] ?? $_SERVER['MAILER_DSN'] ?? ''));

        if (preg_match('/^smtp:\/\/([^:@\/]+)@/i', $dsn, $matches) === 1) {
            $dsnUser = rawurldecode($matches[1]);

            if ('' === $configuredFrom) {
                return $dsnUser;
            }

            if (str_contains(strtolower($dsn), 'smtp.gmail.com') && strcasecmp($configuredFrom, $dsnUser) !== 0) {
                return $dsnUser;
            }
        }

        return '' !== $configuredFrom ? $configuredFrom : 'noreply@konvix.music';
    }
}
