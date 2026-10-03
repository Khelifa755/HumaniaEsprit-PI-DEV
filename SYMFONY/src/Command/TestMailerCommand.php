<?php

namespace App\Command;

use App\Service\Utilisateur\MailService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-mailer',
    description: 'Test if email sending works',
)]
class TestMailerCommand extends Command
{
    public function __construct(
        private readonly MailService $mailService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('email', InputArgument::OPTIONAL, 'Email to send test to', 'aminezaaraoui95@gmail.com');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');

        $io->info("Sending test credentials email to: $email");

        try {
            $this->mailService->envoyerCredentials($email, 'testuser', 'testpass123');
            $io->success('Email sent successfully!');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Error sending email: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
