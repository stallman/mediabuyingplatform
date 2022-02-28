<?php

namespace App\Command;

use App\Entity\Campaign;
use App\Entity\User;
use App\Entity\Visits;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Stopwatch\Stopwatch;

class GenerateMediabuyerCampaigns extends Command
{
    protected static $defaultName = 'app:generate:mediabuyer-campaigns';
    public EntityManagerInterface $em;
    public LoggerInterface $logger;
    public SymfonyStyle $io;
    public ObjectRepository $visits;
    public ?User $mediabuyer = null;
    public int $limit = 2800000; //2800000 limits in dev

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        parent::__construct();
        $this->em = $entityManager;
        $this->logger = $logger;
    }

    protected function configure()
    {
        $this
            ->setDescription('Generate campaigns for mediabuyers')
            ->addArgument('mediabuyer', InputArgument::OPTIONAL, 'Medibuyer id')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);

        set_time_limit(0);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $stopwatch = new Stopwatch();
        $stopwatch->start($this->getName());

        $mediabuyers = [];
        if ($input->getArgument('mediabuyer')) {
            $mediabuyers[] = $this->fetchBuyer($input->getArgument('mediabuyer'));
        } else {
            $mediabuyers = $this->getMediabuyerUsers();
        }

        $this->visits = $this->em->getRepository(Visits::class);
        $campaigns = $this->em->getRepository(Campaign::class);

        foreach ($mediabuyers as $mediabuyer) {
            $campaigns->purge($mediabuyer);
            $this->generateCampaignsForBuyer($mediabuyer);
        }

        $event = $stopwatch->stop($this->getName());

        if ($output->isVerbose()) {
            $this->io->comment(sprintf('Generate active algorithms for buyer, elapsed time: %.2f ms / Consumed memory: %.2f MB',
                $event->getDuration(), $event->getMemory() / (1024 ** 2)));
        }

        return 0;
    }

    private function generateCampaignsForBuyer(User $mediabuyer)
    {
        $conn = $this->em->getConnection();
        $res = $conn->fetchOne("select count(*) from visits where mediabuyer_id = :mediabuyer_id",
            ['mediabuyer_id' => intval($mediabuyer->getId())], [ParameterType::INTEGER]);
        $total = intval($res);

        $campaigns = $this->iterate($conn, $mediabuyer, $total);

        $campaigns = array_unique($campaigns, SORT_STRING);

        $conn->beginTransaction();
        $conn->setAutoCommit(false);

        try {
            foreach ($campaigns as $title) {
                $title = "NULL" === $title ? null : $title ;

                $sql = "insert ignore into campaign (mediabuyer_id, title) values (:mediabuyer_id, :title)";
                $conn->executeQuery($sql, [
                    'mediabuyer_id' => intval($mediabuyer->getId()),
                    'title' => $title,
                ]);
            }

            $conn->commit();
        } catch (\Throwable $e) {
            $conn->rollBack();
            $this->io->error($e->getMessage());
        }

        $conn->setAutoCommit(true);
    }

    private function iterate(Connection $conn, User $mediabuyer, int $total, array $campaigns = [], int $offset = 0): array
    {
        $offsetRows = $offset * $this->limit;

        $sql = "select distinct utm_campaign from visits where mediabuyer_id = :mediabuyer_id limit $offsetRows, $this->limit";
        $rows = $conn->fetchFirstColumn($sql, ['mediabuyer_id' => intval($mediabuyer->getId())], [ParameterType::INTEGER]);

        /** @var string|null $row */
        foreach ($rows as $row) {
            $utmCampaign = null === $row ? "NULL" : $row ;
            if (!in_array($utmCampaign, $campaigns)) {
                $campaigns[] = $utmCampaign;
            }
        }

        $rows = $offsetRows + $this->limit;

        if ($rows < $total) {
            $campaigns = $this->iterate($conn, $mediabuyer, $total, $campaigns, $offset + 1);
        }

        return $campaigns;
    }

    public function fetchBuyer(int $mediabuyerId): User
    {
        /** @var User|null $mediabuyer */
        $mediabuyer = $this->em->getRepository(User::class)->find($mediabuyerId);

        if (null == $mediabuyer) {
            throw new InvalidArgumentException('User not found.');
        }

        if (!in_array('ROLE_MEDIABUYER', $mediabuyer->getRoles())) {
            throw new InvalidArgumentException('Mediabuyer not found.');
        }

        return $mediabuyer;
    }

    private function getMediabuyerUsers()
    {
        $dql = "SELECT u FROM App\Entity\User u WHERE u.roles LIKE :role";
        return $this->em
            ->createQuery($dql)
            ->setParameter(
                'role', '%ROLE_MEDIABUYER%'
            )
            ->getResult();
    }
}