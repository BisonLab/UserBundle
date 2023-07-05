<?php

namespace BisonLab\UserBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Doctrine\ORM\EntityManagerInterface;

use BisonLab\UserBundle\Entity\User;

#[AsCommand(
    name: 'bisonlab:user:create',
    description: 'Create a user'
)]
class BisonLabCreateUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('username', InputArgument::REQUIRED, 'Username')
            ->addArgument('email', InputArgument::REQUIRED, 'Email address')
            ->addOption('role', null, InputOption::VALUE_REQUIRED, 'Role, default USER')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'Password')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $username = $input->getArgument('username');
        $email = $input->getArgument('email');

        $role = null;
        if ($input->getOption('role')) {
            $role = "ROLE_" . $input->getOption('role');
        }

        $user = new User();
        if ($password = $input->getOption('password')) {
            // Encode the plain password, and set it.
            $encodedPassword = $this->passwordHasher->hashPassword(
                $user, $password
            );
            $user->setPassword($encodedPassword);
        } else {
            $user->setPassword(uniqid());
        }
        $user->setUsername($username);
        $user->setEmail($email);
        if ($role)
            $user->setRoles([$role]);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success('You added the user ' . $username . '. Now send a password email with bisonlab:user:send-passwordmail ' . $username);

        return 0;
    }
}
