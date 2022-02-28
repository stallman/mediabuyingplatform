<?php

namespace App\Command;

use App\Entity\DomainParking;
use App\Service\CronHistoryChecker;
use App\Service\PeriodMapper\CurrentWeek;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearAppCachesCommand extends Command
{
    private RedisAdapter $redis;
    public function __construct()
    {
        parent::__construct();
        $this->redis = new RedisAdapter(
            RedisAdapter::createConnection($_ENV['REDIS_URL']),
            '',
            $_ENV['CACHE_LIFETIME']
        );
    }

    protected function configure()
    {
        $this
            ->setName('app:clear-caches')
            ->setDescription('Clear all app caches');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>' . 'Очищаем кэш приложения..' . '</info>');
        $this->executeClearCache($output);
        $output->writeln('<info>' . 'Очищаем кэш redis..' . '</info>');
        $this->redis->clear();

        return 0;
    }

    private function executeClearCache(OutputInterface $output)
    {
        $command = $this->getApplication()->find('cache:clear');
        $arguments = [
            'command' => 'cache:clear'
        ];
        $greetInput = new ArrayInput($arguments);

        return $command->run($greetInput, $output);
    }
}