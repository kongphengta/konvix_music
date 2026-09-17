<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-test-users',
    description: 'Create sample artiste, auditeur and admin users for local testing.',
)]
final class CreateTestUsersCommand extends Command
{
    public function __construct(
        private readonly ManagerRegistry $doctrine,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $entityManager = $this->doctrine->getManager();

        $fixtures = [
            [
                'email' => 'artist@konvix.test',
                'firstName' => 'Artiste',
                'lastName' => 'Test',
                'accountType' => 'artist',
                'password' => 'artist123',
                'roles' => ['ROLE_USER', 'ROLE_ARTISTE'],
                'verified' => true,
            ],
            [
                'email' => 'auditeur@konvix.test',
                'firstName' => 'Auditeur',
                'lastName' => 'Test',
                'accountType' => 'auditeur',
                'password' => 'auditeur123',
                'roles' => ['ROLE_USER', 'ROLE_AUDITEUR'],
                'verified' => true,
            ],
            [
                'email' => 'admin@konvix.test',
                'firstName' => 'Admin',
                'lastName' => 'Test',
                'accountType' => 'admin',
                'password' => 'admin123',
                'roles' => ['ROLE_USER', 'ROLE_ADMIN'],
                'verified' => true,
            ],
        ];

        foreach ($fixtures as $fixture) {
            $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $fixture['email']]);
            if ($existingUser) {
                $output->writeln(sprintf('<comment>Utilisateur existant ignoré : %s</comment>', $fixture['email']));
                continue;
            }

            $user = new User();
            $user->setEmail($fixture['email']);
            $user->setFirstName($fixture['firstName']);
            $user->setLastName($fixture['lastName']);
            $user->setAccountType($fixture['accountType']);
            $user->setRoles($fixture['roles']);
            $user->setPassword($this->passwordHasher->hashPassword($user, $fixture['password']));
            $user->setIsVerified($fixture['verified']);
            $user->setEmailVerificationCode(null);

            $entityManager->persist($user);
            $output->writeln(sprintf('<info>Compte créé : %s (%s)</info>', $fixture['email'], $fixture['accountType']));
        }

        $entityManager->flush();

        return Command::SUCCESS;
    }
}
