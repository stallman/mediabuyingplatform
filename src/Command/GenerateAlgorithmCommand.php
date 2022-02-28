<?php

namespace App\Command;

use App\Entity\Algorithm;
use App\Entity\MediabuyerAlgorithms;
use App\Entity\User;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Stopwatch\Stopwatch;

class GenerateAlgorithmCommand extends Command
{
    /**
     * Tables with algorithm data:
     *  visits
     *  teasers_click
     *  conversions
     *  news_click
     *  news_click_short_to_full
     *  show_news
     *  statistic_promo_block_news
     *  statistic_promo_block_teasers
     * TODO FIRST
     *  1. purge uuid files --purge=1
     *  2. update news_click set page_type = null where page_type = ''; // in prod/dev
     *  3. execute in prod through cron
     *
     * TEST
     * todo rm .env var ALGORITHM=random
     */
    protected static $defaultName = 'app:generate:algorithm';
    public string $projectDir;
    public string $cmdDir = '/var/generate-uuids';
    public string $visitsDir = '/visits';
    public string $teaserClicksDir = '/teasers-click';
    public EntityManagerInterface $em;
    public LoggerInterface $logger;
    public SymfonyStyle $io;
    public int $limit = 2800000; //2800000 limits in dev
    public int $limitInserts = 8000; //8000 limits in dev
    public int $total = 0;
    public ?User $buyer = null;
    public ?Algorithm $algorithm = null;
    public array $visitUuids = [];
    public array $teaserUuidIds = [];
    public array $algIds = [];
    public bool $purge = false;

    public function __construct(string $projectDir, EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        parent::__construct();
        $this->projectDir = $projectDir;
        $this->cmdDir = $this->projectDir . $this->cmdDir;
        $this->em = $entityManager;
        $this->logger = $logger;
    }

    protected function configure()
    {
        $this
            ->setDescription('Generate active algorithms for buyer')
            ->addArgument('buyer', InputArgument::OPTIONAL, 'Medibuyer id')
            ->addOption('time', null, InputArgument::OPTIONAL, 'Max execution time for command')
            ->addOption('purge', null, InputArgument::OPTIONAL, 'Purge prepared uuid files')
            ->addOption('step', null, InputArgument::OPTIONAL, 'Which step starts with');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);

        $time = 0; // [600 => 10m] [21600 => 6h]

        if (null !== $input->getOption('time') && is_numeric($input->getOption('time'))) {
            $time = abs(intval($input->getOption('time')));
        }

        set_time_limit($time);

        if (null !== $input->getOption('purge')) {
            $this->purge = true;
        }
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        if (null !== $input->getArgument('buyer')) {
            return;
        }

        $this->io->title('Algorithm Generator Command Interactive Wizard');
        $this->io->text([
            'If you prefer to not use this interactive wizard, provide the',
            'arguments required by this command as follows:',
            '',
            ' $ php bin/console app:generate:algorithm buyer',
            '',
            'Now we\'ll ask you for the value of all the missing command arguments.',
        ]);

        // Ask for the buyer id if it's not defined
        $buyerId = $input->getArgument('buyer');

        if (null !== $buyerId) {
            $buyerId = $this->validateBuyer($buyerId);
            $this->io->text(' > <info>Mediabuyer id</info>: ' . $buyerId);
        } else {
            $buyerId = $this->io->ask('Mediabuyer id', null, [$this, 'validateBuyer']);
            $input->setArgument('buyer', $buyerId);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $stopwatch = new Stopwatch();
        $stopwatch->start($this->getName());

        $this->buyer = $this->fetchBuyer($input->getArgument('buyer'));
        $this->algorithm = $this->em->getRepository(Algorithm::class)->findOneBy(['isDefault' => true]);

        if (null === $this->algorithm) {
            $this->writeLine('Stopped! Default algorithm not found.');
            return 0;
        }


        /** @var MediabuyerAlgorithms $mediabuyerAlgorithm */
        foreach ($this->buyer->getMediabuyerAlgorithms() as $mediabuyerAlgorithm) {
            $this->algIds[] = $mediabuyerAlgorithm->getAlgorithm()->getId();
        }

        try {
            $this->prepareBuyerUuids($input, $output);
            $this->storeBuyerUuids($input, $output);
        } catch (\Throwable $e) {
            $this->writeLine(sprintf('Stopped! Cannot prepare uuids. %s', $e->getMessage()));
            return 0;
        }

        $step = 1;

        if (null !== $input->getOption('step') && is_numeric($input->getOption('step'))) {
            $step = abs(intval($input->getOption('step')));
        }

        /**
         * bin/console app:generate:algorithm 6 --purge=1 --verbose
         * bin/console app:generate:algorithm 6 --step=3 --verbose
         */
        switch ($step) {
            case 1:
                $isSuccessful = $this->cloneVisits($input, $output);

                if (!$isSuccessful) {
                    $this->writeLine(sprintf('Stopped! Step #1 fail. Cannot clone visits.'));
                    return 0;
                }
            case 2:
                $isSuccessful = $this->cloneTeasersClicks($input, $output);

                if (!$isSuccessful) {
                    $this->writeLine(sprintf('Stopped! Step #2 fail. Cannot clone teaser clicks.'));
                    return 0;
                }
            case 3:
                $isSuccessful = $this->cloneConversions($input, $output);

                if (!$isSuccessful) {
                    $this->writeLine(sprintf('Stopped! Step #3 fail. Cannot clone conversions.'));
                    return 0;
                }
            case 4:
                $isSuccessful = $this->cloneNewsClicks($input, $output);

                if (!$isSuccessful) {
                    $this->writeLine(sprintf('Stopped! Step #4 fail. Cannot clone news clicks.'));
                    return 0;
                }
            case 5:
                $isSuccessful = $this->cloneNewsClickShortToFull($input, $output);

                if (!$isSuccessful) {
                    $this->writeLine(sprintf('Stopped! Step #5 fail. Cannot clone news clicks short to full.'));
                    return 0;
                }
            case 6:
                $isSuccessful = $this->cloneShowNews($input, $output);

                if (!$isSuccessful) {
                    $this->writeLine(sprintf('Stopped! Step #6 fail. Cannot clone show news.'));
                    return 0;
                }
            case 7:
                $isSuccessful = $this->clonePromoNews($input, $output);

                if (!$isSuccessful) {
                    $this->writeLine(sprintf('Stopped! Step #7 fail. Cannot clone statistic_promo_block_news.'));
                    return 0;
                }
            case 8:
                $isSuccessful = $this->clonePromoTeasers($input, $output);

                if (!$isSuccessful) {
                    $this->writeLine(sprintf('Stopped! Step #8 fail. Cannot clone statistic_promo_block_teasers.'));
                    return 0;
                }
        }

        $event = $stopwatch->stop($this->getName());

        if ($output->isVerbose()) {
            $this->io->comment(sprintf('Generate active algorithms for buyer, elapsed time: %.2f ms / Consumed memory: %.2f MB',
                $event->getDuration(), $event->getMemory() / (1024 ** 2)));
        }

        $this->writeLine((sprintf('Generate active algorithms for buyer, elapsed time: %.2f ms / Consumed memory: %.2f MB',
                $event->getDuration(), $event->getMemory() / (1024 ** 2))));

        return 0;
    }

    private function storeBuyerUuids(InputInterface $input, OutputInterface $output): bool
    {


        $this->writeLine('Start store uuids.');

        try {
            // Visits
            $visitsDir = $this->cmdDir . '/' . $this->buyer->getId() . '/' . $this->visitsDir;
            $file = $visitsDir . '/' . $this->algorithm->getId();
            $uuids = $this->fetchUuidsFromFile($file);

            $countUuids = count($uuids);
            $total = $countUuids * $this->buyer->getMediabuyerAlgorithms()->count();

            $progressBar = null;

            /** @var string $uuid */
            foreach ($uuids as $i => $uuid) {
                $this->visitUuids[$this->algorithm->getId()][] = $uuid;
            }

            /** @var MediabuyerAlgorithms $mediabuyerAlgorithm */
            foreach ($this->buyer->getMediabuyerAlgorithms() as $j => $mediabuyerAlgorithm) {
                $alg = $mediabuyerAlgorithm->getAlgorithm();
                $algFile = $visitsDir . '/' . $alg->getId();
                $algUuids = $this->fetchUuidsFromFile($algFile);

                /** @var string $uuid */
                foreach ($uuids as $i => $uuid) {
                    $algUuid = $algUuids[$i] ?? null;
                    $this->visitUuids[$alg->getId()][] = $algUuid;
                }
            }


            unset($uuids, $algUuids, $progressBar, $total, $countUuids);

            $counts = [];
            foreach ($this->visitUuids as $algUuids) {
                $counts[] = count($algUuids);
            }

            if (count(array_unique($counts)) > 1) {

                $this->writeLine(sprintf('Visits uuids comparison fail. [%s]', implode(',', $counts)));
                return false;
            }

            // TeasersClick
            $teaserClicksDir = $this->cmdDir . '/' . $this->buyer->getId() . '/' . $this->teaserClicksDir;
            $file = $teaserClicksDir . '/' . $this->algorithm->getId();
            $uuids = $this->fetchUuidsFromFile($file);

            $countUuids = count($uuids);
            $total = $countUuids * $this->buyer->getMediabuyerAlgorithms()->count();

            $progressBar = null;

            /** @var string $uuid */
            foreach ($uuids as $i => $uuid) {
                $this->teaserUuidIds[$this->algorithm->getId()][] = $uuid;
            }

            /** @var MediabuyerAlgorithms $mediabuyerAlgorithm */
            foreach ($this->buyer->getMediabuyerAlgorithms() as $j => $mediabuyerAlgorithm) {
                $alg = $mediabuyerAlgorithm->getAlgorithm();
                $algFile = $teaserClicksDir . '/' . $alg->getId();
                $algUuids = $this->fetchUuidsFromFile($algFile);

                /** @var string $uuid */
                foreach ($uuids as $i => $uuid) {
                    $algUuid = $algUuids[$i] ?? null;
                    $this->teaserUuidIds[$alg->getId()][] = $algUuid;
                }
            }



            unset($uuids, $algUuids, $progressBar, $total, $countUuids);

            $counts = [];
            foreach ($this->teaserUuidIds as $algUuids) {
                $counts[] = count($algUuids);
            }

            if (count(array_unique($counts)) > 1) {

                $this->writeLine(sprintf('TeasersClick uuids comparison fail. [%s]', implode(',', $counts)));
                return false;
            }

        } catch (\Throwable $e) {

            $this->writeLine(sprintf('Cannot store uuids from file. %s', $e->getMessage()));

            return false;
        }



        return true;
    }

    private function cloneVisits(InputInterface $input, OutputInterface $output): bool
    {
        $conn = $this->em->getConnection();
        $conn->connect();

        try {
            $uuids = $this->visitUuids[$this->algorithm->getId()];

            $inserts = 0;
            $total = count($uuids) * $this->buyer->getMediabuyerAlgorithms()->count();
            $progressBar = null;

            $this->writeLine(sprintf('Step #1 Start clone visits. Total rows %s.', $total));

            /** @var MediabuyerAlgorithms $mediabuyerAlgorithm */
            foreach ($this->buyer->getMediabuyerAlgorithms() as $mediabuyerAlgorithm) {
                $alg = $mediabuyerAlgorithm->getAlgorithm();
                $this->writeLine(sprintf('Step #1. Start alg %s.', $alg->getId()));

                /** @var string $uuid */
                foreach ($uuids as $i => $uuid) {
                    $algUuid = $this->visitUuids[$alg->getId()][$i] ?? null;

                    if (null === $algUuid) {
                        continue;
                    }

                    $sql = <<<SQL
                    insert into visits
                        (uuid, source_id, news_id, mediabuyer_id, domain_id, design_id, algorithm_id, country_code, 
                         city, utm_medium, utm_term, utm_content, utm_campaign, ip, traffic_type, os, os_with_version, 
                         browser, browser_with_version, mobile_brand, mobile_model, mobile_operator, screen_size, 
                         subid1, subid2, subid3, subid4, subid5, user_agent, url, times_of_day, day_of_week, created_at)
                    select
                        :alg_uuid, source_id, news_id, mediabuyer_id, domain_id, design_id, :alg_id, country_code, 
                        city, utm_medium, utm_term, utm_content, utm_campaign, ip, traffic_type, os, os_with_version, 
                        browser, browser_with_version, mobile_brand, mobile_model, mobile_operator, screen_size, 
                        subid1, subid2, subid3, subid4, subid5, user_agent, url, times_of_day, day_of_week, created_at
                    from visits where uuid = :uuid;
                    SQL;

                    $stmt = $conn->prepare($sql);
                    $stmt->bindValue('alg_uuid', $algUuid, ParameterType::STRING);
                    $stmt->bindValue('uuid', $uuid, ParameterType::STRING);
                    $stmt->bindValue('alg_id', $alg->getId(), ParameterType::INTEGER);
                    $stmt->execute();

                    $inserts++;
                }
            }
        } catch (\Throwable $e) {
            $this->writeLine(sprintf('%s %s', $e->getMessage(), $e->getTraceAsString()));

            return false;
        }

        $conn->close();
        $this->writeLine(sprintf('Total visits cloned: %s of %s', $inserts, $total));
        unset($uuids, $progressBar, $total, $inserts, $conn, $stmt);

        return true;
    }

    private function cloneTeasersClicks(InputInterface $input, OutputInterface $output): bool
    {
        $conn = $this->em->getConnection();
        $conn->connect();

        try {
            $uuidIds = $this->teaserUuidIds[$this->algorithm->getId()];
            $uuids = $this->visitUuids[$this->algorithm->getId()];

            $inserts = 0;
            $total = count($uuidIds) * $this->buyer->getMediabuyerAlgorithms()->count();
            $progressBar = null;

            $this->writeLine(sprintf('Step #2 Start clone teaser clicks. Total rows %s.', $total));

            /** @var MediabuyerAlgorithms $mediabuyerAlgorithm */
            foreach ($this->buyer->getMediabuyerAlgorithms() as $mediabuyerAlgorithm) {
                $alg = $mediabuyerAlgorithm->getAlgorithm();
                $this->writeLine(sprintf('Step #2. Start alg %s.', $alg->getId()));

                /** @var string $uuidId */
                foreach ($uuidIds as $i => $uuidId) {
                    $algUuidId = $this->teaserUuidIds[$alg->getId()][$i] ?? null;

                    if (null === $algUuidId) {
                        continue;
                    }

                    /** @var array|false $originalTeaserClick */
                    $sql = "select * from teasers_click where id = :id";
                    $originalTeaserClick = $conn->fetchAssociative($sql, ['id' => $uuidId], [ParameterType::STRING]);

                    /** @var string|null $uuid */
                    $uuid = null;
                    $uuidKey = false;

                    if (false !== $originalTeaserClick && false !== $uuidKey = array_search($originalTeaserClick['uuid'], $uuids)) {
                        $uuid = $this->visitUuids[$alg->getId()][$uuidKey] ?? null;
                    }

                    if (null === $uuid) {
                        $this->writeLine(sprintf('Cannot fetch visit uuid for teaser click id %s with algorithm %s from file.', $uuidId, $alg->getId()));
                        continue;
                    }

                    $sql = <<<SQL
                    insert into teasers_click
                        (id, buyer_id, source_id, teaser_id, news_id, design_id, algorithm_id, country_code, traffic_type, page_type, user_ip, uuid, created_at)
                    select
                        :alg_uuid_id, buyer_id, source_id, teaser_id, news_id, design_id, :alg_id, country_code, traffic_type, page_type, user_ip, :uuid, created_at
                    from teasers_click where id = :uuid_id;
                    SQL;

                    $stmt = $conn->prepare($sql);
                    $stmt->bindValue('uuid_id', $uuidId, ParameterType::STRING);
                    $stmt->bindValue('alg_uuid_id', $algUuidId, ParameterType::STRING);
                    $stmt->bindValue('uuid', $uuid, ParameterType::STRING);
                    $stmt->bindValue('alg_id', $alg->getId(), ParameterType::INTEGER);
                    $stmt->execute();

                    $inserts++;
                }
            }
        } catch (\Throwable $e) {
            $this->writeLine(sprintf('%s %s', $e->getMessage(), $e->getTraceAsString()));
            return false;
        }

        $conn->close();
        $this->writeLine(sprintf('Total teaser clicks cloned: %s of %s', $inserts, $total));
        unset($uuidIds, $uuids, $progressBar, $total, $inserts, $conn, $stmt);

        return true;
    }

    private function cloneConversions(InputInterface $input, OutputInterface $output): bool
    {

        $conn = $this->em->getConnection();
        $conn->connect();

        try {
            $uuidIds = $this->teaserUuidIds[$this->algorithm->getId()];
            $uuids = $this->visitUuids[$this->algorithm->getId()];

            $inserts = 0;
            $total = count($uuidIds) * $this->buyer->getMediabuyerAlgorithms()->count();
            $progressBar = null;

            $this->writeLine(sprintf('Step #3 Start clone conversions. Total rows %s.', $total));

            /** @var MediabuyerAlgorithms $mediabuyerAlgorithm */
            foreach ($this->buyer->getMediabuyerAlgorithms() as $mediabuyerAlgorithm) {
                $alg = $mediabuyerAlgorithm->getAlgorithm();
                $this->writeLine(sprintf('Step #3. Start alg %s.', $alg->getId()));

                /** @var string $uuidId */
                foreach ($uuidIds as $i => $uuidId) {
                    $algUuidId = $this->teaserUuidIds[$alg->getId()][$i] ?? null;

                    /** @var array|false $originalConversion */
                    $sql = "select * from conversions where teaser_click_id = :teaser_click_id";
                    $originalConversion = $conn->fetchAssociative($sql, ['teaser_click_id' => $uuidId], [ParameterType::STRING]);

                    if (null === $algUuidId || false === $originalConversion) {
                        continue;
                    }

                    /** @var string|null $uuid */
                    $uuid = null;

                    if (false !== $uuidKey = array_search($originalConversion['uuid'], $uuids)) {
                        $uuid = $this->visitUuids[$alg->getId()][$uuidKey] ?? null;
                    }

                    if (null === $uuid) {
                        $this->writeLine(sprintf('Cannot fetch cloned visit uuid for teaser click id %s with algorithm %s from file.', $uuidId, $alg->getId()));
                        continue;
                    }

                    $sql = <<<SQL
                    insert into conversions
                        (mediabuyer_id, teaser_click_id, affilate_id, source_id, news_id, subgroup_id, country_id, currency_id, design_id, algorithm_id, status_id, amount, amount_rub, uuid, created_at, updated_at, is_deleted)
                    select
                        mediabuyer_id, :alg_uuid_id, affilate_id, source_id, news_id, subgroup_id, country_id, currency_id, design_id, :alg_id, status_id, amount, amount_rub, :uuid, created_at, updated_at, is_deleted
                    from conversions where teaser_click_id = :uuid_id;
                    SQL;

                    $stmt = $conn->prepare($sql);
                    $stmt->bindValue('uuid_id', $uuidId, ParameterType::STRING);
                    $stmt->bindValue('alg_uuid_id', $algUuidId, ParameterType::STRING);
                    $stmt->bindValue('uuid', $uuid, ParameterType::STRING);
                    $stmt->bindValue('alg_id', $alg->getId(), ParameterType::INTEGER);
                    $stmt->execute();

                    $inserts++;
                }
            }
        } catch (\Throwable $e) {
            $this->writeLine(sprintf('%s %s', $e->getMessage(), $e->getTraceAsString()));

            return false;
        }

        $conn->close();
        $this->writeLine(sprintf('Total conversions cloned: %s of %s', $inserts, $total));
        unset($uuidIds, $uuids, $progressBar, $total, $inserts, $conn, $stmt);

        return true;
    }

    private function cloneNewsClicks(InputInterface $input, OutputInterface $output): bool
    {
        $conn = $this->em->getConnection();
        $conn->connect();

        try {
            $sql = "select * from news_click where buyer_id = :buyer_id and algorithm_id = :algorithm_id";
            $rows = $conn->fetchAllAssociative($sql,
                ['buyer_id' => $this->buyer->getId(), 'algorithm_id' => $this->algorithm->getId()],
                [ParameterType::INTEGER, ParameterType::INTEGER]
            );

            $inserts = 0;
            $total = count($rows) * count($this->algIds);
            $this->writeLine(sprintf('Step #4 Start clone news clicks. Total rows %s.', $total));

            $sqlRows = [];
            foreach ($rows as $row) {
                $k = array_search($row['uuid'], $this->visitUuids[$this->algorithm->getId()]);
                if (false === $k) continue;

                foreach ($this->algIds as $algId) {
                    $algUuid = $this->visitUuids[$algId][$k] ?? null;
                    if (null === $algUuid) continue;

                    // update news_click set page_type = null where page_type = ''; // in prod/dev
                    // DELETE FROM news_click WHERE id > 482767; // in dev
                    // ALTER TABLE `news_click` AUTO_INCREMENT=482768; // in dev
                    $source_id = $row['source_id'] === null ? 'null' : $row['source_id'] ;
                    $design_id = $row['design_id'] === null ? 'null' : $row['design_id'] ;
                    $country_code = $row['country_code'] === null ? 'null' : $row['country_code'] ;
                    $traffic_type = $row['traffic_type'] === null ? 'null' : $row['traffic_type'] ; // set first value for enum in mysql
                    $page_type = $row['page_type'] === null ? 'null' : $row['page_type'] ; // set first value for enum in mysql
                    $sqlRows[] = "({$row['buyer_id']}, {$source_id}, {$row['news_id']}, '{$design_id}', {$algId}, '{$country_code}', '{$traffic_type}', '{$page_type}', '{$row['user_ip']}', '{$algUuid}', '{$row['created_at']}')";
                    $inserts++;

                    if (count($sqlRows) === $this->limitInserts) {
                        $insertList = implode(',', $sqlRows);
                        $sql = "insert into news_click (buyer_id, source_id, news_id, design_id, algorithm_id, country_code, traffic_type, page_type, user_ip, uuid, created_at) values {$insertList}";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute();

                        $sqlRows = [];
                        $this->writeLine(sprintf('Cloned: %s of %s', $inserts, $total));
                    }
                }
            }

            if (!empty($sqlRows)) {
                $insertList = implode(',', $sqlRows);
                $sql = "insert into news_click (buyer_id, source_id, news_id, design_id, algorithm_id, country_code, traffic_type, page_type, user_ip, uuid, created_at) values {$insertList}";
                $stmt = $conn->prepare($sql);
                $stmt->execute();
            }


        } catch (\Throwable $e) {
            $this->writeLine(sprintf('%s %s', $e->getMessage(), $e->getTraceAsString()));
            return false;
        }

        $conn->close();
        $this->writeLine(sprintf('Total news clicks cloned: %s of %s', $inserts, $total));
        unset($uuids, $total, $inserts, $conn, $stmt);
        return true;
    }

    private function cloneNewsClickShortToFull(InputInterface $input, OutputInterface $output): bool
    {
        $conn = $this->em->getConnection();
        $conn->connect();

        try {
            $sql = "select * from news_click_short_to_full where buyer_id = :buyer_id and algorithm_id = :algorithm_id";
            $rows = $conn->fetchAllAssociative($sql,
                ['buyer_id' => $this->buyer->getId(), 'algorithm_id' => $this->algorithm->getId()],
                [ParameterType::INTEGER, ParameterType::INTEGER]
            );

            $inserts = 0;
            $total = count($rows) * count($this->algIds);
            $this->writeLine(sprintf('Step #5 Start clone news clicks short to full. Total rows %s.', $total));

            $sqlRows = [];
            foreach ($rows as $row) {
                $k = array_search($row['uuid'], $this->visitUuids[$this->algorithm->getId()]);
                if (false === $k) continue;

                foreach ($this->algIds as $algId) {
                    $algUuid = $this->visitUuids[$algId][$k] ?? null;
                    if (null === $algUuid) continue;

                    $source_id = $row['source_id'] === null ? 'null' : $row['source_id'] ;
                    $design_id = $row['design_id'] === null ? 'null' : $row['design_id'] ;
                    $country_code = $row['country_code'] === null ? 'null' : $row['country_code'] ;
                    $traffic_type = $row['traffic_type'] === null ? 'null' : $row['traffic_type'] ; // set first value for enum in mysql
                    $sqlRows[] = "({$row['buyer_id']}, {$source_id}, {$row['news_id']}, '{$design_id}', {$algId}, '{$country_code}', '{$traffic_type}', '{$row['user_ip']}', '{$algUuid}', '{$row['created_at']}')";
                    $inserts++;

                    if (count($sqlRows) === $this->limitInserts) {
                        $insertList = implode(',', $sqlRows);
                        $sql = "insert into news_click_short_to_full (buyer_id, source_id, news_id, design_id, algorithm_id, country_code, traffic_type, user_ip, uuid, created_at) values {$insertList}";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute();

                        $sqlRows = [];
                        $this->writeLine(sprintf('Cloned: %s of %s', $inserts, $total));
                    }
                }
            }

            if (!empty($sqlRows)) {
                $insertList = implode(',', $sqlRows);
                $sql = "insert into news_click_short_to_full (buyer_id, source_id, news_id, design_id, algorithm_id, country_code, traffic_type, user_ip, uuid, created_at) values {$insertList}";
                $stmt = $conn->prepare($sql);
                $stmt->execute();
            }


        } catch (\Throwable $e) {
            $this->writeLine(sprintf('%s %s', $e->getMessage(), $e->getTraceAsString()));
            return false;
        }

        $conn->close();
        $this->writeLine(sprintf('Total news clicks short to full cloned: %s of %s', $inserts, $total));
        unset($uuids, $total, $inserts, $conn, $stmt);
        return true;
    }

    private function cloneShowNews(InputInterface $input, OutputInterface $output): bool
    {
        $conn = $this->em->getConnection();
        $conn->connect();

        try {
            $sql = "select * from show_news where mediabuyer_id = :mediabuyer_id and algorithm_id = :algorithm_id";
            $rows = $conn->fetchAllAssociative($sql,
                ['mediabuyer_id' => $this->buyer->getId(), 'algorithm_id' => $this->algorithm->getId()],
                [ParameterType::INTEGER, ParameterType::INTEGER]
            );

            $inserts = 0;
            $total = count($rows) * count($this->algIds);
            $this->writeLine(sprintf('Step #6 Start clone show news. Total rows %s.', $total));

            $sqlRows = [];
            foreach ($rows as $row) {
                $k = array_search($row['uuid'], $this->visitUuids[$this->algorithm->getId()]);
                if (false === $k) continue;

                foreach ($this->algIds as $algId) {
                    $algUuid = $this->visitUuids[$algId][$k] ?? null;
                    if (null === $algUuid) continue;

                    $source_id = $row['source_id'] === null ? 'null' : $row['source_id'] ;
                    $page_type = $row['page_type'] === null ? 'null' : $row['page_type'] ; // set first value for enum in mysql
                    $sqlRows[] = "({$row['news_id']}, {$row['mediabuyer_id']}, {$algId}, '{$row['design_id']}', {$source_id}, '{$page_type}', '{$algUuid}', '{$row['created_at']}')";
                    $inserts++;

                    if (count($sqlRows) === $this->limitInserts) {
                        $insertList = implode(',', $sqlRows);
                        $sql = "insert into show_news (news_id, mediabuyer_id, algorithm_id, design_id, source_id, page_type, uuid, created_at) values {$insertList}";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute();

                        $sqlRows = [];
                        $this->writeLine(sprintf('Cloned: %s of %s', $inserts, $total));
                    }
                }
            }

            if (!empty($sqlRows)) {
                $insertList = implode(',', $sqlRows);
                $sql = "insert into show_news (news_id, mediabuyer_id, algorithm_id, design_id, source_id, page_type, uuid, created_at) values {$insertList}";
                $stmt = $conn->prepare($sql);
                $stmt->execute();
            }


        } catch (\Throwable $e) {
            $this->writeLine(sprintf('%s %s', $e->getMessage(), $e->getTraceAsString()));
            return false;
        }

        $conn->close();
        $this->writeLine(sprintf('Total show news cloned: %s of %s', $inserts, $total));
        unset($uuids, $total, $inserts, $conn, $stmt);
        return true;
    }

    private function clonePromoNews(InputInterface $input, OutputInterface $output): bool
    {
        $conn = $this->em->getConnection();
        $conn->connect();

        try {
            $sql = "select count(*) from statistic_promo_block_news where mediabuyer_id = :mediabuyer_id and algorithm_id = :algorithm_id";
            $result = $conn->fetchOne($sql,
                ['mediabuyer_id' => $this->buyer->getId(), 'algorithm_id' => $this->algorithm->getId()],
                [ParameterType::INTEGER, ParameterType::INTEGER]
            );
            $count = intval($result);

            $total = $count * count($this->algIds);
            $this->writeLine(sprintf('Step #7 Start clone statistic_promo_block_news. Total rows %s.', $total));

            $inserts = $this->iterateNewsRows($conn, $total);
        } catch (\Throwable $e) {
            $this->writeLine(sprintf('%s %s', $e->getMessage(), $e->getTraceAsString()));
            return false;
        }

        $conn->close();
        $this->writeLine(sprintf('Total statistic_promo_block_news cloned: %s of %s', $inserts, $total));
        unset($uuids, $total, $inserts, $conn, $stmt);

        return true;
    }

    private function iterateNewsRows(Connection $conn, int $total, int $offset = 0)
    {
        if (!$conn->isConnected()) {
            $conn->connect();
        }

        $offsetRows = $offset * $this->limit;
        $sql = "select * from statistic_promo_block_news where mediabuyer_id = :mediabuyer_id and algorithm_id = :algorithm_id limit $offsetRows, $this->limit";
        $rows = $conn->fetchAllAssociative($sql,
            ['mediabuyer_id' => $this->buyer->getId(), 'algorithm_id' => $this->algorithm->getId()],
            [ParameterType::INTEGER, ParameterType::INTEGER]
        );

        $sqlRows = [];
        foreach ($rows as $row) {
            foreach ($this->algIds as $algId) {
                $source_id = $row['source_id'] === null ? 'null' : $row['source_id'] ;
                $design_id = $row['design_id'] === null ? 'null' : $row['design_id'] ;
                $country_code = $row['country_code'] === null ? 'null' : $row['country_code'] ;
                $traffic_type = $row['traffic_type'] === null ? 'null' : $row['traffic_type'] ; // set first value for enum in mysql
                $page_type = $row['page_type'] === null ? 'null' : $row['page_type'] ; // set first value for enum in mysql
                $sqlRows[] = "({$row['news_id']}, {$row['mediabuyer_id']}, {$source_id}, {$algId}, '{$design_id}', '{$country_code}', '{$traffic_type}', '{$page_type}', '{$row['created_at']}')";

                if (count($sqlRows) === $this->limitInserts) {
                    $insertList = implode(',', $sqlRows);
                    $sql = "insert into statistic_promo_block_news (news_id, mediabuyer_id, source_id, algorithm_id, design_id, country_code, traffic_type, page_type, created_at) values {$insertList}";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute();
                    $sqlRows = [];
                }
            }
        }

        if (!empty($sqlRows)) {
            $insertList = implode(',', $sqlRows);
            $sql = "insert into statistic_promo_block_news (news_id, mediabuyer_id, source_id, algorithm_id, design_id, country_code, traffic_type, page_type, created_at) values {$insertList}";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
        }

        unset($rows);

        $inserts = $offsetRows + $this->limit;
        $this->writeLine(sprintf('Cloned: %s of %s', $inserts, $total));

        if ($inserts < $total) {
            $inserts = $this->iterateNewsRows($conn, $total, $offset + 1);
        }

        return $inserts;
    }

    private function clonePromoTeasers(InputInterface $input, OutputInterface $output): bool
    {
        $conn = $this->em->getConnection();
        $conn->connect();

        try {
            $sql = "select count(*) from statistic_promo_block_teasers where mediabuyer_id = :mediabuyer_id and algorithm_id = :algorithm_id";
            $result = $conn->fetchOne($sql,
                ['mediabuyer_id' => $this->buyer->getId(), 'algorithm_id' => $this->algorithm->getId()],
                [ParameterType::INTEGER, ParameterType::INTEGER]
            );
            $count = intval($result);

            $total = $count * count($this->algIds);
            $this->writeLine(sprintf('Step #8 Start clone statistic_promo_block_teasers. Total rows %s.', $total));

            $inserts = $this->iterateTeasersRows($conn, $total);
        } catch (\Throwable $e) {
            $this->writeLine(sprintf('%s %s', $e->getMessage(), $e->getTraceAsString()));
            return false;
        }

        $conn->close();
        $this->writeLine(sprintf('Total statistic_promo_block_teasers cloned: %s of %s', $inserts, $total));
        unset($uuids, $total, $inserts, $conn, $stmt);

        return true;
    }

    private function iterateTeasersRows(Connection $conn, int $total, int $offset = 0)
    {
        if (!$conn->isConnected()) {
            $conn->connect();
        }

        $offsetRows = $offset * $this->limit;
        $sql = "select * from statistic_promo_block_teasers where mediabuyer_id = :mediabuyer_id and algorithm_id = :algorithm_id limit $offsetRows, $this->limit";
        $rows = $conn->fetchAllAssociative($sql,
            ['mediabuyer_id' => $this->buyer->getId(), 'algorithm_id' => $this->algorithm->getId()],
            [ParameterType::INTEGER, ParameterType::INTEGER]
        );

        $sqlRows = [];
        foreach ($rows as $row) {
            foreach ($this->algIds as $algId) {
                $source_id = $row['source_id'] === null ? 'null' : $row['source_id'] ;
                $design_id = $row['design_id'] === null ? 'null' : $row['design_id'] ;
                $news_id = $row['news_id'] === null ? 'null' : $row['news_id'] ;
                $country_code = $row['country_code'] === null ? 'null' : $row['country_code'] ;
                $traffic_type = $row['traffic_type'] === null ? 'null' : $row['traffic_type'] ; // set first value for enum in mysql
                $page_type = $row['page_type'] === null ? 'null' : $row['page_type'] ; // set first value for enum in mysql
                $sqlRows[] = "({$row['teaser_id']}, {$row['mediabuyer_id']}, {$source_id}, {$algId}, '{$design_id}', {$news_id}, '{$country_code}', '{$traffic_type}', '{$page_type}', '{$row['created_at']}')";

                if (count($sqlRows) === $this->limitInserts) {
                    $insertList = implode(',', $sqlRows);
                    $sql = "insert into statistic_promo_block_teasers (teaser_id, mediabuyer_id, source_id, algorithm_id, design_id, news_id, country_code, traffic_type, page_type, created_at) values {$insertList}";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute();
                    $sqlRows = [];
                }
            }
        }

        if (!empty($sqlRows)) {
            $insertList = implode(',', $sqlRows);
            $sql = "insert into statistic_promo_block_teasers (teaser_id, mediabuyer_id, source_id, algorithm_id, design_id, news_id, country_code, traffic_type, page_type, created_at) values {$insertList}";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
        }

        unset($rows);

        $inserts = $offsetRows + $this->limit;
        $this->writeLine(sprintf('Cloned: %s of %s', $inserts, $total));

        if ($inserts < $total) {
            $inserts = $this->iterateTeasersRows($conn, $total, $offset + 1);
        }

        return $inserts;
    }

    public function validateBuyer(?int $buyerId): int
    {
        if (!is_numeric($buyerId) && $buyerId < 1) {
            throw new InvalidArgumentException('Wrong value for Mediabuyer id.');
        }

        return $buyerId;
    }

    public function fetchBuyer(int $buyerId): User
    {
        /** @var User|null $buyer */
        $buyer = $this->em->getRepository(User::class)->find($buyerId);

        if (null == $buyer) {
            throw new InvalidArgumentException('User not found.');
        }

        if (!in_array('ROLE_MEDIABUYER', $buyer->getRoles())) {
            throw new InvalidArgumentException('Mediabuyer not found.');
        }

        return $buyer;
    }

    public function prepareBuyerUuids(InputInterface $input, OutputInterface $output): void
    {

        $this->writeLine('Start prepare uuids.');

        $fs = new Filesystem();
        $chmod = 0755;

        /** Prepare cmd directory for buyer files */
        $this->prepareDir($fs, $this->cmdDir, $chmod);

        $buyerDir = $this->cmdDir . '/' . $this->buyer->getId();
        $this->prepareDir($fs, $buyerDir, $chmod);

        $buyerVisitsDir = $buyerDir . $this->visitsDir;
        $this->prepareDir($fs, $buyerVisitsDir, $chmod);

        $buyerTeasersClickDir = $buyerDir . $this->teaserClicksDir;
        $this->prepareDir($fs, $buyerTeasersClickDir, $chmod);

        /** Prepare uuids */
        $this->populateVisitsUuids($input, $output, $buyerVisitsDir, $chmod);
        $this->populateTeaserUuids($input, $output, $buyerTeasersClickDir, $chmod);
    }

    private function populateVisitsUuids(InputInterface $input, OutputInterface $output, string $buyerVisitsDir, int $chmod): void
    {
        $stmt = $this->em->getConnection()->prepare("select uuid from visits where mediabuyer_id = :mediabuyer_id and algorithm_id = :algorithm_id");
        $stmt->bindValue('mediabuyer_id', $this->buyer->getId(), ParameterType::INTEGER);
        $stmt->bindValue('algorithm_id', $this->algorithm->getId(), ParameterType::INTEGER);
        $stmt->execute();
        $rows = $stmt->fetchAllNumeric();

        $countRows = count($rows);
        $total = $countRows * $this->buyer->getMediabuyerAlgorithms()->count();

        $progressBar = null;

        $uuids = [];

        foreach ($rows as $row) {
            $uuids[] = array_shift($row);
        }

        $algFile = $buyerVisitsDir . '/' . $this->algorithm->getId();

        $this->fillFile($algFile, $chmod, $uuids, true);

        /** @var MediabuyerAlgorithms $mediabuyerAlgorithm */
        foreach ($this->buyer->getMediabuyerAlgorithms() as $i => $mediabuyerAlgorithm) {
            $algFile = $buyerVisitsDir . '/' . $mediabuyerAlgorithm->getAlgorithm()->getId();

            $this->fillFile($algFile, $chmod, $uuids);
        }

        unset($uuids, $visits, $progressBar, $total, $countRows);
    }

    private function populateTeaserUuids(InputInterface $input, OutputInterface $output, string $buyerTeasersClickDir, int $chmod): void
    {
        $stmt = $this->em->getConnection()->prepare("select id from teasers_click where buyer_id = :buyer_id and algorithm_id = :algorithm_id");
        $stmt->bindValue('buyer_id', $this->buyer->getId(), ParameterType::INTEGER);
        $stmt->bindValue('algorithm_id', $this->algorithm->getId(), ParameterType::INTEGER);
        $stmt->execute();
        $rows = $stmt->fetchAllNumeric();

        $countRows = count($rows);
        $total = $countRows * $this->buyer->getMediabuyerAlgorithms()->count();

        $progressBar = null;

        $uuids = [];

        foreach ($rows as $row) {
            $uuids[] = array_shift($row);
        }

        $algFile = $buyerTeasersClickDir . '/' . $this->algorithm->getId();

        $this->fillFile($algFile, $chmod, $uuids, true);

        /** @var MediabuyerAlgorithms $mediabuyerAlgorithm */
        foreach ($this->buyer->getMediabuyerAlgorithms() as $i => $mediabuyerAlgorithm) {
            $algFile = $buyerTeasersClickDir . '/' . $mediabuyerAlgorithm->getAlgorithm()->getId();

            $this->fillFile($algFile, $chmod, $uuids);
        }

        unset($uuids, $teaserClicks, $progressBar, $total, $countRows);
    }

    private function prepareDir(Filesystem $fs, string $path, int $chmod): void
    {
        if (!$fs->exists($path)) {
            $fs->mkdir($path, $chmod);
        }
    }

    private function fillFile(string $filePath, int $chmod, array $uuids, bool $isMainAlg = false): void
    {
        $fs = new Filesystem();

        if ($this->purge) {
            if ($fs->exists($filePath)) {
                $fs->remove($filePath);
            }
            $fs->touch($filePath);
            $fs->chmod($filePath, $chmod);
        } else {
            if (!$fs->exists($filePath)) {
                $fs->touch($filePath);
                $fs->chmod($filePath, $chmod);
            }
        }

        $fileUuids = $this->fetchUuidsFromFile($filePath);

        if ($this->purge) {
            $this->appendToFile($filePath, $uuids, $fileUuids, $isMainAlg);
        }
    }

    private function appendToFile(string $filePath, array $uuids, array $fileUuids, bool $isMainAlg = false): void
    {
        $fs = new Filesystem();

        foreach ($uuids as $i => $uuid) {
            if (!$isMainAlg) {
                $uuid = Uuid::uuid4()->toString();
            }

            if (!in_array($uuid, $fileUuids)) {
                $fs->appendToFile(
                    $filePath,
                    $uuid . PHP_EOL,
                );
            }
        }
    }

    private function fetchUuidsFromFile(string $filePath): array
    {
        $fileUuids = [];
        $handle = fopen($filePath, 'r');

        while (false !== $line = fgets($handle)) {
            $fileUuids[] = trim($line);
        }

        fclose($handle);

        return $fileUuids;
    }

    private function write(string $msg): void
    {
        $time = (new \DateTime('now'))->format('Y-m-d H:i:s');
        $this->io->write( "[$time] " . $msg);
    }

    private function writeLine(string $msg): void
    {
        $time = (new \DateTime('now'))->format('Y-m-d H:i:s');
        $this->io->writeln( "[$time] " . $msg);
    }
}