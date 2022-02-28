<?php

namespace App\Command;

use App\Entity\DomainParking;
use App\Service\CronHistoryChecker;
use App\Service\PeriodMapper\CurrentWeek;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AutoLetsEncryptDomainCommand extends Command
{
    use LockableTrait;

    /** @var EntityManagerInterface */
    public $entityManager;
    public LoggerInterface $logger;

    const CRON_HISTORY_SLUG = 'letsencrypt-generate';


    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    protected function configure()
    {
        $this
            ->setName('app:auto-letsencrypt:domain');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if(!$this->lock()){
            $output->writeln('The command is already running in another process.');

            return 0;
        }

        $cronHistoryChecker = new CronHistoryChecker($this->entityManager);
        $startTime = new Carbon();

        $currentWeek = new CurrentWeek();
        [$from, $to] = $currentWeek->getDateBetween();

        $domains = $this->entityManager->getRepository(DomainParking::class)->getDomainsNeedCert($from, $to);

        $flag = 0;
        foreach($domains as $domain) {
            [$domain, $code] = $this->executeLetsEncrypt($domain, $output);
            $flag += $code;
            $this->entityManager->flush();
        }

        $this->release();

        $endTime = new Carbon();
        $cronHistoryChecker->create(self::CRON_HISTORY_SLUG, $startTime->floatDiffInSeconds($endTime));

        $this->logger->info("flag - {$flag}");
        if($flag != 0){
            $this->logger->info('trying reload nginx');
            try{
                exec("echo '{$_ENV['SUDO_PASSWORD']}' | sudo -S /etc/init.d/nginx reload");
                $this->logger->info('reload nginx');
            } catch(\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }

        return 0;
    }

    private function executeLetsEncrypt(DomainParking $domain, OutputInterface $output)
    {
        $command = $this->getApplication()->find('app:letsencrypt:domain');

        $arguments = [
            'command' => 'app:letsencrypt:domain',
            'domain' => $domain->getDomain(),
        ];

        $greetInput = new ArrayInput($arguments);
        try{
            $returnCode = $command->run($greetInput, $output);
            if($returnCode){
                $domain->setErrorMessage(null)
                    ->setCertEndDate(new \DateTime('+90 days'));
            }
        } catch(\Exception $e) {
            $domain->setErrorMessage($e->getMessage());
            $returnCode = 0;
        }

        return [$domain, $returnCode];
    }
}