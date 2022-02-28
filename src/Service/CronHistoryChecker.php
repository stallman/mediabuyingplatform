<?php

namespace App\Service;

use App\Entity\CronDate;
use Doctrine\ORM\EntityManagerInterface;
use Carbon\Carbon;

class CronHistoryChecker
{
    private EntityManagerInterface $entityManager;      

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        date_default_timezone_set($_ENV['DEFAULT_TIMEZONE']);
    }

    /**
     * @param string $slug
     * @param string $runtime
     */
    public function create(string $slug, string $runtime): void
    {

       $cronDate = new CronDate();
       $cronDate->setSlug($slug)
                ->setRuntime($runtime);
       $this->entityManager->persist($cronDate);
       $this->entityManager->flush();
    }

    public function getLastCronTime(string $slug): ?Carbon
    {
        $query = $this->entityManager->createQueryBuilder()
            ->select('cd')
            ->from(CronDate::class, 'cd')
            ->where('cd.slug = :slug')
            ->orderBy('cd.id', 'DESC')
            ->setMaxResults(1)
            ->setParameter(
                'slug', $slug
            )
            ->getQuery();

            $result = $query->getOneOrNullResult();

            if ($result) {
                return new Carbon($result->getCreatedAt());
            }
             
            return null;
    }

}