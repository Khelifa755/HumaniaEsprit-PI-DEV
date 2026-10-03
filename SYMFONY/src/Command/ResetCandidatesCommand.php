<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:reset-candidates',
    description: 'Reset all converted candidates for testing',
)]
class ResetCandidatesCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->warning('This will reset all converted candidates (set employe_id to NULL)');
        
        if (!$io->confirm('Are you sure?', false)) {
            $io->info('Cancelled.');
            return Command::SUCCESS;
        }

        try {
            $conn = $this->em->getConnection();
            $stmt = $conn->executeStatement('UPDATE candidature SET employe_id = NULL WHERE employe_id IS NOT NULL');
            $count = $stmt;
            
            $io->success("✅ Reset complete! Updated $count candidates.");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error("Error: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
