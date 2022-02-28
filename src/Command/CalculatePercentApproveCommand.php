<?php
namespace App\Command;

use App\Entity\Conversions;
use App\Entity\Teaser;
use App\Entity\TeasersClick;
use App\Entity\TeasersSubGroupSettings;
use App\Service\CronHistoryChecker;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CalculatePercentApproveCommand extends Command
{
    /** @var EntityManagerInterface  */
    public $entityManager;
    const SLUG = 'percent-approve';


    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
            ->setName('app:percent-approve:calculate')
            ->setDescription('Расчет % апрува для подгрупп')
            ->setHelp('Эта команда рассчитывает % апрува для подгрупп');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cronHistoryChecker = new CronHistoryChecker($this->entityManager);
        $startTime = new Carbon();

        $subGroupSettings = $this->entityManager->getRepository(TeasersSubGroupSettings::class)->getByUnDeletedSubGroup();
        /** @var TeasersSubGroupSettings $subGroupSetting */
        foreach ($subGroupSettings as $subGroupSetting) {

            $teasers = $this->entityManager->getRepository(Teaser::class)->getTeasersBySubGroup($subGroupSetting->getTeasersSubGroup());

            if(!$teasers) continue;

            $teasersClick = $this->entityManager->getRepository(TeasersClick::class)->getClickByTeasers($teasers);

            if(!$teasersClick) continue;

            $clickIdList = $this->getClickIdList($teasersClick);
            $conversionsCount = $this->entityManager->getRepository(Conversions::class)->getConversionsCountByTeasersClick($clickIdList);

            if($conversionsCount >= $_ENV['CONVERSIONS_COUNT_FOR_AUTO_CALCULATE']){
                $approveConversionsCount = $this->entityManager->getRepository(Conversions::class)->getConversionsCountByTeasersClickAndStatus($clickIdList, 'подтвержден');
                $approveConversionsCount = $approveConversionsCount ? $approveConversionsCount : 0;

                $subGroupSetting->setApproveAveragePercentage($approveConversionsCount/$conversionsCount)
                    ->setIsAutoCalculate(true);
            } else {
                $subGroupSetting->setIsAutoCalculate(false);
            }
            $this->entityManager->flush();
        }
        $endTime = new Carbon();
        $cronHistoryChecker->create(self::SLUG, $startTime->floatDiffInSeconds($endTime));

        return 0;
    }

    private function getClickIdList(array $teasersClick)
    {
        foreach ($teasersClick as $clickId)
        {
            $clickIdList[] =  $clickId['id'];
        }

        return $clickIdList;
    }
}