<?php

namespace App\Tests;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class RegistrationFlowTest extends WebTestCase
{
    private function uniqueEmail(string $prefix): string
    {
        return sprintf('%s.%s@example.com', $prefix, uniqid('', true));
    }

    public function testArtistRegistrationCreatesPendingUserWithSixDigitCode(): void
    {
        $client = static::createClient();
        $email = $this->uniqueEmail('luna.artist');

        $crawler = $client->request('GET', '/register/artist');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $form = $crawler->selectButton('Créer mon compte')->form([
            'registration_form[firstName]' => 'Luna',
            'registration_form[lastName]' => 'Artist',
            'registration_form[email]' => $email,
            'registration_form[plainPassword][first]' => 'secret123',
            'registration_form[plainPassword][second]' => 'secret123',
        ]);

        $client->submit($form);

        $this->assertResponseRedirects(sprintf('/verify-email?email=%s', $email));

        $user = static::getContainer()->get('doctrine')->getManager()->getRepository(User::class)
            ->findOneBy(['email' => $email]);

        $this->assertNotNull($user);
        $this->assertSame('artist', $user->getAccountType());
        $this->assertContains('ROLE_ARTISTE', $user->getRoles());
        $this->assertFalse($user->isVerified());
        $this->assertMatchesRegularExpression('/^\d{6}$/', (string) $user->getEmailVerificationCode());
    }

    public function testValidVerificationCodeActivatesTheAccount(): void
    {
        $client = static::createClient();
        $email = $this->uniqueEmail('auditeur.test');

        $crawler = $client->request('GET', '/register/auditeur');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $form = $crawler->selectButton('Créer mon compte')->form([
            'registration_form[firstName]' => 'Auditeur',
            'registration_form[lastName]' => 'Test',
            'registration_form[email]' => $email,
            'registration_form[plainPassword][first]' => 'secret456',
            'registration_form[plainPassword][second]' => 'secret456',
        ]);

        $client->submit($form);

        $this->assertResponseRedirects(sprintf('/verify-email?email=%s', $email));

        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        $this->assertNotNull($user);
        $this->assertNotNull($user->getEmailVerificationCode());

        $crawler = $client->request('GET', '/verify-email?email=' . urlencode($email));
        $verifyForm = $crawler->selectButton('Valider mon compte')->form([
            'code' => $user->getEmailVerificationCode(),
            'email' => $email,
        ]);

        $client->submit($verifyForm);

        $this->assertResponseRedirects('/login');

        $freshUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        $this->assertNotNull($freshUser);
        $this->assertTrue($freshUser->isVerified());
        $this->assertNull($freshUser->getEmailVerificationCode());
    }

    public function testAdminRegistrationCreatesAdminRoleUser(): void
    {
        $client = static::createClient();
        $email = $this->uniqueEmail('admin.test');

        $crawler = $client->request('GET', '/register/admin');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $form = $crawler->selectButton('Créer mon compte')->form([
            'registration_form[firstName]' => 'Admin',
            'registration_form[lastName]' => 'Test',
            'registration_form[email]' => $email,
            'registration_form[plainPassword][first]' => 'secret789',
            'registration_form[plainPassword][second]' => 'secret789',
        ]);

        $client->submit($form);

        $this->assertResponseRedirects(sprintf('/verify-email?email=%s', $email));

        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        $this->assertNotNull($user);
        $this->assertSame('admin', $user->getAccountType());
        $this->assertContains('ROLE_ADMIN', $user->getRoles());
        $this->assertFalse($user->isVerified());
    }

    public function testAdminDashboardIsAccessibleForVerifiedAdmin(): void
    {
        $client = static::createClient();
        $email = $this->uniqueEmail('super.admin');

        $user = new User();
        $user->setFirstName('Super');
        $user->setLastName('Admin');
        $user->setEmail($email);
        $user->setAccountType('admin');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setPassword(password_hash('secretAdmin123', PASSWORD_BCRYPT));
        $user->setIsVerified(true);

        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => $email,
            '_password' => 'secretAdmin123',
        ]);

        $client->submit($form);
        $this->assertResponseRedirects();
        $client->followRedirect();

        $client->request('GET', '/admin');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Tableau de bord');
        $this->assertSelectorTextContains('body', 'Administration');
    }
}
