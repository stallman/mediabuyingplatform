<?php

namespace App\Service;


use App\Contract\CleanableRepositoryInterface;
use App\Entity\Conversions;
use App\Entity\Costs;
use App\Entity\NewsClick;
use App\Entity\Postback;
use App\Entity\TeasersClick;
use App\Entity\User;
use App\Entity\UserSettings;
use App\Entity\Visits;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanStatistic
{
    private EntityManagerInterface $entityManager;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param User $mediabuyer
     * @param int $days
     * @param array|null $toDeleteEntities List of entity classes
     */
    public function mediabuyer(User $mediabuyer, bool $dry_run, OutputInterface $output, ?array $toDeleteEntities = null) : void
    {
        if(is_null($toDeleteEntities)){
            $toDeleteEntities = [Conversions::class, Costs::class, NewsClick::class, Postback::class, TeasersClick::class, Visits::class];
        }
        $daysSetting = $mediabuyer->getUserSettingsBySlug(UserSettings::SLUG_STATS_STORAGE_DAYS);
        $days = UserSettings::DEFAULT_STATS_STORAGE_DAYS;
        if($daysSetting){
            $days = $daysSetting->getValue();
        }
        if(empty($days)){
            $output->writeln("Data retention period is 0. SKIP.");
            return;
        }
        $output->writeln("Data for last $days days will be deleted.");
        foreach ($toDeleteEntities as $_entityClass) {
            try {
                $output->writeln(" - $_entityClass");
                $repo = $this->entityManager->getRepository($_entityClass);
                if($repo instanceof CleanableRepositoryInterface){
                    $count = $repo->queryOlderThan($mediabuyer, $days, true)->getQuery()->getSingleScalarResult();
                    $output->writeln("     $_entityClass: $count rows to remove");
                    if(!$dry_run){
                        $repo->queryOlderThan($mediabuyer, $days, false)->getQuery()->execute();
                        $output->writeln("     DELETED!");
                    }
                }else{
                    throw new \Exception("$_entityClass not implementing " . CleanableRepositoryInterface::class);
                }
            }catch (\Throwable $e){
                $output->writeln("     ERROR: " . $e->getMessage());
            }
        }
    }
}
