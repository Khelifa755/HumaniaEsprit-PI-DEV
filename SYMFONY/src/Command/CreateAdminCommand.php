<?php

namespace App\Command;

use App\Entity\Utilisateur;
use App\Enum\Role;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Replaces Java ServiceAdmin::initializeAdmin().
 * Run: php bin/console app:create-admin
 */
#[AsCommand(name: 'app:create-admin', description: 'Create default admin account if it does not exist')]
class CreateAdminCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $repo = $this->em->getRepository(Utilisateur::class);
        if ($repo->findOneBy(['email' => 'admin@humania.tn'])) {
            $io->success('Admin account already exists.');
            return Command::SUCCESS;
        }

        $admin = new Utilisateur();
        $admin->setNom('Admin');
        $admin->setPrenom('System');
        $admin->setEmail('admin@humania.tn');
        $admin->setUsername('admin');
        $admin->setNumtel('00000000');
        $admin->setRole(Role::ADMIN);
        $admin->setStatut('Actif');
        $hashedPassword = hash('sha256', 'admin123');
        $admin->setMotDePasse($hashedPassword);

        $this->em->persist($admin);
        $this->em->flush();

        $io->success('Default admin created!');
        $io->table(['Field', 'Value'], [
            ['Email',    'admin@humania.tn'],
            ['Username', 'admin'],
            ['Password', 'admin123'],
        ]);
        $io->warning('Change the admin password immediately after first login!');

        return Command::SUCCESS;
    }
}