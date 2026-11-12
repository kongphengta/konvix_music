<?php

namespace App\Controller;

use App\Entity\ArtistProfile;
use App\Entity\Track;
use App\Entity\User;
use App\Entity\UserVerification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

final class AuthController extends AbstractController
{
    private function normalizeSlug(string $slug): string
    {
        $normalized = strtolower((string) preg_replace('/[^a-z0-9]+/i', '-', trim($slug)));

        return trim($normalized, '-');
    }

    private function generateListenerUsername(string $firstName, string $lastName, EntityManagerInterface $entityManager): string
    {
        $base = strtolower(str_replace(' ', '', trim($firstName) . '.' . trim($lastName)));
        $username = $base . '.listener';
        $suffix = 1;

        while (null !== $entityManager->getRepository(User::class)->findOneBy(['username' => $username])) {
            $username = sprintf('%s.%d.listener', $base, $suffix);
            ++$suffix;
        }

        return $username;
    }

    private function createVerificationForUser(User $user, MailerInterface $mailer): array
    {
        $verificationCode = (string) random_int(100000, 999999);

        $verification = new UserVerification();
        $verification->setUser($user);
        $verification->setCode($verificationCode);
        $verification->setExpiresAt(new \DateTimeImmutable('+15 minutes'));

        $emailMessage = (new Email())
            ->from($_ENV['MAILER_FROM'] ?? 'no-reply@localhost')
            ->to($user->getEmail())
            ->subject('Votre code de vérification Konvix Music')
            ->html(sprintf(
                '<p>Bonjour %s,</p><p>Voici votre code de vérification : <strong>%s</strong></p><p>Ce code expire dans 15 minutes.</p>',
                htmlspecialchars($user->getFirstName()),
                htmlspecialchars($verificationCode)
            ));

        try {
            $mailer->send($emailMessage);

            return ['verification' => $verification, 'sent' => true, 'code' => $verificationCode];
        } catch (\Throwable) {
            return ['verification' => $verification, 'sent' => false, 'code' => $verificationCode];
        }
    }

    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        MailerInterface $mailer,
    ): Response {
        $errors = [];
        $formData = [
            'firstName' => '',
            'lastName' => '',
            'username' => '',
            'email' => '',
        ];

        if ($request->isMethod('POST')) {
            $formData['firstName'] = trim((string) $request->request->get('firstName', ''));
            $formData['lastName'] = trim((string) $request->request->get('lastName', ''));
            $formData['username'] = trim((string) $request->request->get('username', ''));
            $formData['email'] = trim((string) $request->request->get('email', ''));
            $password = (string) $request->request->get('password', '');
            $confirmPassword = (string) $request->request->get('confirmPassword', '');

            if ('' === $formData['firstName']) {
                $errors[] = 'Le prénom est obligatoire.';
            }

            if ('' === $formData['lastName']) {
                $errors[] = 'Le nom est obligatoire.';
            }

            if ('' === $formData['username']) {
                $errors[] = 'Le nom d’artiste est obligatoire.';
            }

            if ('' === $formData['email'] || false === filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Une adresse email valide est requise.';
            }

            if (strlen($password) < 8) {
                $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';
            }

            if ($password !== $confirmPassword) {
                $errors[] = 'Les deux mots de passe ne correspondent pas.';
            }

            $existingUserByUsername = '' !== $formData['username'] ? $entityManager->getRepository(User::class)->findOneBy(['username' => $formData['username']]) : null;
            $existingUserByEmail = '' !== $formData['email'] ? $entityManager->getRepository(User::class)->findOneBy(['email' => $formData['email']]) : null;

            if ('' === $formData['username'] || ($existingUserByUsername && (!$existingUserByEmail || $existingUserByUsername->getId() !== $existingUserByEmail->getId()))) {
                $errors[] = 'Ce nom d’artiste est déjà utilisé.';
            }

            if ($existingUserByEmail && $existingUserByEmail->isVerified()) {
                $errors[] = 'Cette adresse email est déjà utilisée.';
            }

            if ([] === $errors) {
                $user = $existingUserByEmail ?? new User();
                $user->setFirstName($formData['firstName']);
                $user->setLastName($formData['lastName']);
                $user->setUsername($formData['username']);
                $user->setEmail($formData['email']);
                $user->setPassword($passwordHasher->hashPassword($user, $password));
                $user->setRoles(['ROLE_ARTIST']);
                $user->setIsVerified(false);

                $entityManager->persist($user);

                foreach ($user->getVerificationCodes() as $verificationCode) {
                    if (!$verificationCode->isUsed()) {
                        $verificationCode->setIsUsed(true);
                    }
                }

                $verificationData = $this->createVerificationForUser($user, $mailer);
                $verification = $verificationData['verification'];

                $entityManager->persist($verification);
                $entityManager->flush();

                $request->getSession()->set('pending_artist_id', $user->getId());

                if ($verificationData['sent']) {
                    $this->addFlash('success', 'Votre compte artiste a bien été créé. Un code de vérification a été envoyé par email.');
                } else {
                    $this->addFlash('warning', 'Votre compte artiste a bien été créé, mais l’envoi par email a échoué. Merci de vérifier votre adresse email ou de demander un nouveau code.');
                }

                return $this->redirectToRoute('app_verify', ['email' => $user->getEmail()], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('auth/register.html.twig', [
            'errors' => $errors,
            'formData' => $formData,
        ]);
    }

    #[Route('/register/listener', name: 'app_register_listener', methods: ['GET', 'POST'])]
    public function registerListener(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
    ): Response {
        $errors = [];
        $formData = [
            'firstName' => '',
            'lastName' => '',
            'email' => '',
        ];

        if ($request->isMethod('POST')) {
            $formData['firstName'] = trim((string) $request->request->get('firstName', ''));
            $formData['lastName'] = trim((string) $request->request->get('lastName', ''));
            $formData['email'] = trim((string) $request->request->get('email', ''));
            $password = (string) $request->request->get('password', '');
            $confirmPassword = (string) $request->request->get('confirmPassword', '');

            if ('' === $formData['firstName']) {
                $errors[] = 'Le prénom est obligatoire.';
            }

            if ('' === $formData['lastName']) {
                $errors[] = 'Le nom est obligatoire.';
            }

            if ('' === $formData['email'] || false === filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Une adresse email valide est requise.';
            }

            if (strlen($password) < 8) {
                $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';
            }

            if ($password !== $confirmPassword) {
                $errors[] = 'Les deux mots de passe ne correspondent pas.';
            }

            if ('' !== $formData['email'] && $entityManager->getRepository(User::class)->findOneBy(['email' => $formData['email']])) {
                $errors[] = 'Cette adresse email est déjà utilisée.';
            }

            if ([] === $errors) {
                $user = new User();
                $user->setFirstName($formData['firstName']);
                $user->setLastName($formData['lastName']);
                $user->setUsername($this->generateListenerUsername($formData['firstName'], $formData['lastName'], $entityManager));
                $user->setEmail($formData['email']);
                $user->setPassword($passwordHasher->hashPassword($user, $password));
                $user->setRoles(['ROLE_LISTENER']);

                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Votre compte auditeur a bien été créé.');

                return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('auth/register_listener.html.twig', [
            'errors' => $errors,
            'formData' => $formData,
        ]);
    }

    #[Route('/verify/resend', name: 'app_verify_resend', methods: ['POST'])]
    public function resendVerificationCode(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $email = trim((string) $request->request->get('email', ''));

        if ('' === $email || false === filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->addFlash('error', 'Une adresse email valide est requise.');

            return $this->redirectToRoute('app_verify', ['email' => $email], Response::HTTP_SEE_OTHER);
        }

        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            $this->addFlash('error', 'Aucun compte ne correspond à cette adresse email.');

            return $this->redirectToRoute('app_verify', [], Response::HTTP_SEE_OTHER);
        }

        $existingVerifications = $entityManager->getRepository(UserVerification::class)->findBy(['user' => $user]);
        foreach ($existingVerifications as $verification) {
            $verification->setIsUsed(true);
        }

        $verificationData = $this->createVerificationForUser($user, $mailer);
        $verification = $verificationData['verification'];
        $entityManager->persist($verification);
        $entityManager->flush();

        if ($verificationData['sent']) {
            $this->addFlash('success', 'Un nouveau code a été envoyé par email.');
        } else {
            $this->addFlash('warning', 'L’envoi par email a échoué. Merci de vérifier votre adresse email ou de réessayer plus tard.');
        }

        return $this->redirectToRoute('app_verify', ['email' => $email], Response::HTTP_SEE_OTHER);
    }

    #[Route('/verify', name: 'app_verify', methods: ['GET', 'POST'])]
    public function verify(Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): Response
    {
        $errors = [];
        $email = trim((string) $request->query->get('email', $request->request->get('email', '')));
        $submittedCode = (string) $request->request->get('code', '');

        if ($request->isMethod('POST')) {
            $email = trim((string) $request->request->get('email', ''));
            $submittedCode = trim((string) $request->request->get('code', ''));

            if ('' === $email || false === filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Une adresse email valide est requise.';
            }

            if ('' === $submittedCode) {
                $errors[] = 'Le code de vérification est requis.';
            }

            if ([] === $errors) {
                $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

                if (!$user) {
                    $errors[] = 'Aucun compte ne correspond à cette adresse email.';
                } else {
                    $verificationCodes = $entityManager->getRepository(UserVerification::class)->findBy(
                        ['user' => $user],
                        ['createdAt' => 'DESC']
                    );

                    $matchedVerification = null;
                    $now = new \DateTimeImmutable();

                    foreach ($verificationCodes as $verification) {
                        if ($verification->isUsed()) {
                            continue;
                        }

                        if ($verification->getCode() === $submittedCode && $verification->getExpiresAt() >= $now) {
                            $matchedVerification = $verification;
                            break;
                        }
                    }

                    if (null === $matchedVerification) {
                        $errors[] = 'Le code est invalide ou expiré.';
                    } else {
                        $user->setIsVerified(true);
                        $user->setUpdatedAt(new \DateTimeImmutable());
                        $matchedVerification->setIsUsed(true);
                        $entityManager->flush();

                        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
                        $tokenStorage->setToken($token);

                        $session = $request->getSession();
                        $session->set('_security_main', serialize($token));
                        $session->set('verified_artist_id', $user->getId());
                        $session->save();

                        $this->addFlash('success', 'Votre compte artiste a été vérifié avec succès.');

                        return $this->redirectToRoute('app_artist_profile', [], Response::HTTP_SEE_OTHER);
                    }
                }
            }
        }

        return $this->render('auth/verify.html.twig', [
            'errors' => $errors,
            'email' => $email,
        ]);
    }

    #[Route('/artist/profile', name: 'app_artist_profile', methods: ['GET', 'POST'])]
    public function profile(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            $userId = $request->getSession()->get('verified_artist_id');
            $user = $userId ? $entityManager->getRepository(User::class)->find($userId) : null;
        }

        if (!$user || !$user->isVerified()) {
            $this->addFlash('error', 'Veuillez verifier votre compte avant de compléter votre profil artiste.');

            return $this->redirectToRoute('app_verify', [], Response::HTTP_SEE_OTHER);
        }

        $errors = [];
        $formData = [
            'artistName' => $user->getArtistProfile()?->getArtistName() ?? '',
            'slug' => $user->getArtistProfile()?->getSlug() ?? '',
            'bio' => $user->getArtistProfile()?->getBio() ?? '',
            'city' => $user->getArtistProfile()?->getCity() ?? '',
            'country' => $user->getArtistProfile()?->getCountry() ?? '',
        ];

        if ($request->isMethod('POST')) {
            $formData['artistName'] = trim((string) $request->request->get('artistName', ''));
            $formData['slug'] = trim((string) $request->request->get('slug', ''));
            $formData['bio'] = trim((string) $request->request->get('bio', ''));
            $formData['city'] = trim((string) $request->request->get('city', ''));
            $formData['country'] = trim((string) $request->request->get('country', ''));

            if ('' === $formData['artistName']) {
                $errors[] = 'Le nom d’artiste est obligatoire.';
            }

            $normalizedSlug = $this->normalizeSlug($formData['slug']);

            if ('' === $normalizedSlug) {
                $errors[] = 'Le slug est obligatoire.';
            }

            if ('' !== $normalizedSlug) {
                $existingProfile = $entityManager->getRepository(ArtistProfile::class)->findOneBy(['slug' => $normalizedSlug]);

                if ($existingProfile && $existingProfile->getUser() !== $user) {
                    $errors[] = 'Ce slug est déjà utilisé par un autre artiste.';
                }
            }

            if ([] === $errors) {
                $profile = $user->getArtistProfile() ?? new ArtistProfile();
                $profile->setUser($user);
                $profile->setArtistName($formData['artistName']);
                $profile->setSlug($normalizedSlug);
                $profile->setBio('' !== $formData['bio'] ? $formData['bio'] : null);
                $profile->setCity('' !== $formData['city'] ? $formData['city'] : null);
                $profile->setCountry('' !== $formData['country'] ? $formData['country'] : null);
                $profile->setUpdatedAt(new \DateTimeImmutable());

                $entityManager->persist($profile);
                $user->setArtistProfile($profile);
                $user->setUpdatedAt(new \DateTimeImmutable());
                $entityManager->flush();

                $this->addFlash('success', 'Votre profil artiste est prêt. Vous pouvez maintenant accéder à votre espace de travail.');

                return $this->redirectToRoute('app_artist_dashboard', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('artist/profile.html.twig', [
            'errors' => $errors,
            'formData' => $formData,
        ]);
    }

    #[Route('/artist/dashboard', name: 'app_artist_dashboard', methods: ['GET'])]
    public function dashboard(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        }

        if (!$user->isVerified()) {
            $this->addFlash('error', 'Veuillez vérifier votre compte avant d’accéder à votre espace artiste.');

            return $this->redirectToRoute('app_verify', ['email' => $user->getEmail()], Response::HTTP_SEE_OTHER);
        }

        $profile = $user->getArtistProfile();

        if (!$profile) {
            $this->addFlash('info', 'Complétez votre profil artiste pour commencer à publier.');

            return $this->redirectToRoute('app_artist_profile', [], Response::HTTP_SEE_OTHER);
        }

        $trackCount = $entityManager->getRepository(Track::class)->count(['artist' => $user]);

        return $this->render('artist/dashboard.html.twig', [
            'user' => $user,
            'profile' => $profile,
            'trackCount' => $trackCount,
        ]);
    }
}
