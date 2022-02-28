<?php


namespace App\Command;


use App\Entity\ConversionStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InstallCommand extends Command
{
    public const CONVERSION_STATUSES = [
        [
            'code' => 200,
            'label_ru' => 'подтвержден',
            'label_en' => 'approved',
        ],
        [
            'code' => 100,
            'label_ru' => 'в ожидании',
            'label_en' => 'pending',
        ],
        [
            'code' => 000,
            'label_ru' => 'отклонен',
            'label_en' => 'declined',
        ],
    ];

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    public function configure()
    {
        $this
            ->setName('app:install')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this
            ->fillConversionStatusTable()
        ;

        return 0;
    }

    /**
     * @return $this
     */
    private function fillConversionStatusTable()
    {
        foreach (self::CONVERSION_STATUSES as $status) {
            $conversionStatus = new ConversionStatus();
            $conversionStatus
                ->setCode($status['code'])
                ->setLabelRu($status['label_ru'])
                ->setLabelEn($status['label_en'])
            ;

            $this->entityManager->persist($conversionStatus);
        }

        $this->entityManager->flush();

        return $this;
    }
}