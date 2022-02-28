<?php


namespace App\Command;

use App\Entity\OtherFiltersData;
use App\Entity\Visits;
use App\Service\CronHistoryChecker;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class GenerateOtherFiltersDataCommand extends Command
{

    public EntityManagerInterface $entityManager;
    public LoggerInterface $logger;
    const CRON_HISTORY_SLUG = 'other-filter-data';


    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }


    protected function configure()
    {
        $this->setName('app:other-filters-data:generate');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $cronHistoryChecker = new CronHistoryChecker($this->entityManager);
        $startTime = new Carbon();
        $lastCronTime = $cronHistoryChecker->getLastCronTime(self::CRON_HISTORY_SLUG);

        if($lastCronTime){
            $visits = $this->entityManager->getRepository(Visits::class)->getVisitsByDate($lastCronTime);
        } else {
            $visits = $this->entityManager->getRepository(Visits::class)->findAll();
        }

        if($visits){
            /** @var Visits $visit */
            foreach($visits as $visit) {
                $this->setFilterData($visit);
            }
        }

        $endTime = new Carbon();
        $cronHistoryChecker->create(self::CRON_HISTORY_SLUG, $startTime->floatDiffInSeconds($endTime));

        return 0;
    }

    private function setFilterData(Visits $visit)
    {
        if($visit->getUtmTerm()){
            if(!$this->entityManager->getRepository(OtherFiltersData::class)->checkExists($visit->getMediabuyer(), 'utm_term', $visit->getUtmTerm())){
                $filterData = new OtherFiltersData();
                $filterData->setMediabuyer($visit->getMediabuyer())
                    ->setType('utm_term')
                    ->setOptions($visit->getUtmTerm());

                $this->entityManager->persist($filterData);
                $this->entityManager->flush();
            }
        }

        if($visit->getUtmCampaign()){
            if(!$this->entityManager->getRepository(OtherFiltersData::class)->checkExists($visit->getMediabuyer(), 'utm_campaign', $visit->getUtmCampaign())){
                $filterData = new OtherFiltersData();
                $filterData->setMediabuyer($visit->getMediabuyer())
                    ->setType('utm_campaign')
                    ->setOptions($visit->getUtmCampaign());

                $this->entityManager->persist($filterData);
                $this->entityManager->flush();
            }
        }

        if($visit->getUtmContent()){
            if(!$this->entityManager->getRepository(OtherFiltersData::class)->checkExists($visit->getMediabuyer(), 'utm_content', $visit->getUtmContent())){
                $filterData = new OtherFiltersData();
                $filterData->setMediabuyer($visit->getMediabuyer())
                    ->setType('utm_content')
                    ->setOptions($visit->getUtmContent());

                $this->entityManager->persist($filterData);
                $this->entityManager->flush();
            }
        }

        if($visit->getSubid1()){
            if(!$this->entityManager->getRepository(OtherFiltersData::class)->checkExists($visit->getMediabuyer(), 'subid1', $visit->getSubid1())){
                $filterData = new OtherFiltersData();
                $filterData->setMediabuyer($visit->getMediabuyer())
                    ->setType('subid1')
                    ->setOptions($visit->getSubid1());

                $this->entityManager->persist($filterData);
                $this->entityManager->flush();
            }
        }

        if($visit->getSubid2()){
            if(!$this->entityManager->getRepository(OtherFiltersData::class)->checkExists($visit->getMediabuyer(), 'subid2', $visit->getSubid2())){
                $filterData = new OtherFiltersData();
                $filterData->setMediabuyer($visit->getMediabuyer())
                    ->setType('subid2')
                    ->setOptions($visit->getSubid2());

                $this->entityManager->persist($filterData);
                $this->entityManager->flush();
            }
        }

        if($visit->getSubid3()){
            if(!$this->entityManager->getRepository(OtherFiltersData::class)->checkExists($visit->getMediabuyer(), 'subid3', $visit->getSubid3())){
                $filterData = new OtherFiltersData();
                $filterData->setMediabuyer($visit->getMediabuyer())
                    ->setType('subid3')
                    ->setOptions($visit->getSubid3());

                $this->entityManager->persist($filterData);
                $this->entityManager->flush();
            }
        }

        if($visit->getSubid4()){
            if(!$this->entityManager->getRepository(OtherFiltersData::class)->checkExists($visit->getMediabuyer(), 'subid4', $visit->getSubid4())){
                $filterData = new OtherFiltersData();
                $filterData->setMediabuyer($visit->getMediabuyer())
                    ->setType('subid4')
                    ->setOptions($visit->getSubid4());

                $this->entityManager->persist($filterData);
                $this->entityManager->flush();
            }
        }

        if($visit->getSubid5()){
            if(!$this->entityManager->getRepository(OtherFiltersData::class)->checkExists($visit->getMediabuyer(), 'subid5', $visit->getSubid5())){
                $filterData = new OtherFiltersData();
                $filterData->setMediabuyer($visit->getMediabuyer())
                    ->setType('subid5')
                    ->setOptions($visit->getSubid5());
                $this->entityManager->persist($filterData);
                $this->entityManager->flush();
            }
        }
    }
}
