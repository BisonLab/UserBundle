<?php

namespace BisonLab\UserBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mailer\MailerInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use BisonLab\UserBundle\Entity\User;

#[AsCommand(
    name: 'bisonlab:user:send-passwordmail',
    description: 'Send a forgot password email'
)]
class BisonLabSendPasswordEmailCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private MailerInterface $mailer,
        private ParameterBagInterface $params)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('username', InputArgument::REQUIRED, 'Username')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $username = $input->getArgument('username');

        if (!$user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $username])) {
            $io->error('Error, did not find the user');
            return 1;
        }

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $e) {
            $io->error("Error creating the send password token.\n" . $e->getReason());
            return 1;
        }

        $email = (new TemplatedEmail())
            ->from(new Address($this->params->get('bisonlab_user.mailfrom'), $this->params->get('bisonlab_user.mailname')))
            ->to($user->getEmail())
            ->subject('Your password reset request')
            ->htmlTemplate('@BisonLabUser/reset_password/email.html.twig')
            ->context([
                'resetToken' => $resetToken,
                'tokenLifetime' => $this->resetPasswordHelper->getTokenLifetime(),
            ])
        ;

        $this->mailer->send($email);

        $io->success('You send an email to ' . (string)$user
            . " with email " . $user->getEmail());

        return 0;
    }
}
