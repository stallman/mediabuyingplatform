<?php
namespace App\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Stopwatch\Stopwatch;
use Throwable;

class ReplaceSourcesCommand extends Command
{
    protected static $defaultName = 'app:replace:sources';
    public EntityManagerInterface $entityManager;
    public SymfonyStyle $io;
    public int $total = 0;
    public int $found = 0;
    public int $success = 0;
    public int $iterates = 0;
    public array $replacedUuids = [];

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
            ->setDescription('Replace incorrect source ids for visits')
            ->addOption('time', null, InputArgument::OPTIONAL, 'Max execution time for command')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);

        $time = 600; // 10 min

        if (null !== $input->getOption('time') && is_numeric($input->getOption('time'))) {
            $time = abs(intval($input->getOption('time')));
        }

        set_time_limit($time);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $stopwatch = new Stopwatch();
        $stopwatch->start($this->getName());

        $this->countVisits();

        $offset = 0;
        $limit = 1000;

        $conn = $this->entityManager->getConnection();
        $progressBar = new ProgressBar($output, $this->total);

        try {
            while ($offset < $this->total) {
                $offset += $count = $this->iterate($conn, $offset, $limit);
                $progressBar->advance($count);
            }

            $this->iterates += $offset;

            $progressBar->finish();
        } catch (Throwable $e) {
            $this->io->error($e->getMessage());
            $this->showResult();
            $this->showReplaced();
            return 1;
        }

        $this->showResult(true);

        $event = $stopwatch->stop($this->getName());

        if ($output->isVerbose()) {
            $this->showReplaced();

            $this->io->comment(sprintf('Visits source id replacement, elapsed time: %.2f ms / Consumed memory: %.2f MB',
                $event->getDuration(), $event->getMemory() / (1024 ** 2)));
        }

        return 0;
    }

    /**
     * @throws \Doctrine\DBAL\Driver\Exception|Exception
     */
    private function iterate(Connection $conn, int $offset, int $limit): int
    {
        $conn = $this->reconnect($conn);
        $stmt = $conn->prepare("select uuid, source_id, url from visits order by created_at limit :offset, :limit");
        $stmt->bindValue('offset', $offset, ParameterType::INTEGER);
        $stmt->bindValue('limit', $limit, ParameterType::INTEGER);
        $stmt->execute();

        $rows = $stmt->fetchAllNumeric();

        foreach ($rows as $row) {
            $uuid = strval($row[0]);

            /** @var int|null $visitSourceId */
            $visitSourceId = null === $row[1] ? null : intval($row[1]);

            $url = strval($row[2]);

            // skip visits without utm_source correct value
            if (null === $url) {
                continue;
            }

            $urlSourceId = $this->getUrlSourceId($url);

            if ($urlSourceId !== $visitSourceId) {
                $this->found++;
                $conn = $this->reconnect($conn);
                $stmt = $conn->prepare("update visits set source_id = :urlSourceId where uuid = :uuid");
                $stmt->bindValue('urlSourceId', $urlSourceId, ParameterType::INTEGER);
                $stmt->bindValue('uuid', $uuid, ParameterType::STRING);
                $isSuccess = $stmt->execute();

                if ($isSuccess) {
                    $this->success++;
                    $this->replacedUuids[] = $uuid;
                }
            }
        }

        return count($rows);
    }

    private function countVisits(): void
    {
        $conn = $this->entityManager->getConnection();

        try {
            $conn = $this->reconnect($conn);
            $stmt = $conn->prepare("select count(distinct uuid) from visits");
            $stmt->execute();
            $result = $stmt->fetchOne();

            if (false !== $result) {
                $this->total += intval($result);
            }
        } catch (Throwable $e) {
            $this->io->error($e->getMessage());
        }
    }

    private function getUrlSourceId(string $url): ?int
    {
        $sourceId = null;

        /**
         * url examples:
         * https://piedpiper.news/visit/undefined
         * https://piedpiper.news/previews/teaser/c5/216x162_original_c5d1ce56c6ca2ba903794599580ed624.jpeg
         * https://piedpiper.news/news/short/292?subid1=&utm_content=&utm_source=6&utm_term=
         * https://piedpiper.news/news/short/427?utm_campaign=%7Bcampaign%7D&utm_content=%7Bteaser%7D&utm_source=2&utm_term=%7Bblock%7D
         * https://piedpiper.news/news/short/387?subid1=b1&utm_campaign=268831&utm_content=3770740&utm_source=1&utm_term=17679
         *
         * we need utm_source numeric value
         */
        preg_match('/utm_source=([0-9]*)&/', $url, $matches);

        if (isset($matches[1]) && is_numeric($matches[1])) {
            $sourceId = intval($matches[1]);
        }

        return $sourceId;
    }

    private function showResult(bool $isSuccess = false): void
    {
        $message = sprintf('Found incorrect visits: %s. Success replacement: %s. Total visits: %s. Iterates: %s',
            $this->found, $this->success, $this->total, $this->iterates);

        if ($isSuccess) {
            $this->io->success($message);
        } else {
            $this->io->error($message);
        }
    }

    private function showReplaced(): void
    {
        if (!empty($this->replacedUuids)) {
            $this->io->write('Replaced uuids');
            $this->io->listing($this->replacedUuids);
        }
    }

    private function reconnect(Connection $conn): Connection
    {
        if (!$conn->isConnected()) {
            $conn->close();
            $conn->connect();
        }

        return $conn;
    }
}