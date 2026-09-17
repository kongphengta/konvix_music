<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Create a real administrator account for EasyAdmin.',
)]
final class CreateAdminCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::OPTIONAL, 'Admin email address')
            ->addArgument('password', InputArgument::OPTIONAL, 'Admin password');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');

        if (null === $email || '' === $email) {
            $helper = $this->getHelper('question');
            $question = new Question('Email administrateur : ');
            $email = $helper->ask($input, $output, $question);
        }

        if (null === $password || '' === $password) {
            $helper = $this->getHelper('question');
            $question = new Question('Mot de passe administrateur : ');
            $question->setHidden(true);
            $question->setHiddenFallback(false);
            $password = $helper->ask($input, $output, $question);
        }

        if (empty($email) || empty($password)) {
            $output->writeln('<error>Email et mot de passe sont obligatoires.</error>');

            return Command::FAILURE;
        }

        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existingUser) {
            $output->writeln('<error>Un utilisateur avec cet email existe déjà.</error>');

            return Command::FAILURE;
        }

        $user = new User();
        $user->setEmail($email);
        $user->setFirstName('Administrateur');
        $user->setLastName('');
        $user->setAccountType('admin');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $user->setIsVerified(true);
        $user->setEmailVerificationCode(null);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln(sprintf('<info>Compte administrateur créé : %s</info>', $email));

        return Command::SUCCESS;
    }
}
