<?php

namespace App\Command;

use App\Entity\Geo;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class ParseGeoTableCommand extends Command
{
    /** @var EntityManagerInterface */
    public $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
            ->setName('app:parse:geo')
            ->setDescription('Спарсить ГЕО(страны, регионы, города)')
            ->setHelp('Эта команда парсит ГЕО информацию из .csv файла указанного в .env\'');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $startMessage = "Начинаем парсинг ГЕО...\n";
        $output->writeln('<info>' . $startMessage . '</info>');

        $serializer = new Serializer([new ObjectNormalizer()], [new CsvEncoder()]);
        $dataGEO = $serializer->decode(file_get_contents($_ENV['IP2LOCATION_COUNTRY_REGION_CITY']), 'csv');
        foreach($dataGEO as $itemGEO) {
            if($itemGEO['lang_code'] != 'RU') continue;

            if(!$this->entityManager->getRepository(Geo::class)->getCityTranslate($itemGEO['country_name'], $itemGEO['city_name'])){
                $geo = new Geo();
                $geo->setCountryName($itemGEO['country_name']);
                $geo->setCityName($itemGEO['city_name']);
                $geo->setCityNameRu($itemGEO['lang_city_name']);
                $geo->setLangCode($itemGEO['lang_code']);

                $this->entityManager->persist($geo);
                $this->entityManager->flush();
            }
        }

        $endMessage = "Выполнение скприта завершено\n";
        $output->writeln('<info>' . $endMessage . '</info>');
        return 0;
    }
}