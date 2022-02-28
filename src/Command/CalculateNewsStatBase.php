<?php
namespace App\Command;

use App\Service\CalculateStatistic;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;

abstract class CalculateNewsStatBase extends Command
{
    public EntityManagerInterface $entityManager;
    public CalculateStatistic $calculateStatistic;

    public function __construct(EntityManagerInterface $entityManager, CalculateStatistic $calculateStatistic)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->calculateStatistic = $calculateStatistic;
    }

    protected function isDeleted($newsItem)
    {
        return $newsItem['is_deleted'];
    }

    protected function isOlderThen24Hours($newsItem)
    {
        return $newsItem['updatedAt'] > Carbon::now()->subHours(24);
    }
}