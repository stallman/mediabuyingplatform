<?php
namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\CleanStatistic;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CleanStatsCommand extends Command
{
    private UserRepository $userRepo;
    private CleanStatistic $cleanService;

    public function __construct(UserRepository $userRepo, CleanStatistic $cleanService)
    {
        $this->userRepo = $userRepo;
        $this->cleanService = $cleanService;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:clean-stats')
            ->addOption('dry', 'd', InputOption::VALUE_OPTIONAL, 'Run logic without actual data deletion', 1)
            ->addOption('user', 'u', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Clean data for selected users')
            ->setDescription('Clean up statistics')
            ->setHelp('Clean DB based on mediabuyer settings');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $isDryRun = boolval($input->getOption('dry'));
        $users = $input->getOption('user');
        if($isDryRun){
            $output->writeln('DRY RUN. Data will not be deleted.');
        }

        $mediabuyers = $this->userRepo->queryByRole(User::ROLE_MEDIABUYER, $users);
        /**
         * @var User $_mediabuyer
         */
        foreach ($mediabuyers as $_mediabuyer){
            $output->writeln("Processing {$_mediabuyer->getEmail()}...");
            $this->cleanService->mediabuyer($_mediabuyer, $isDryRun, $output);
            $output->writeln("DONE.");
        }

        return 0;
    }
}
