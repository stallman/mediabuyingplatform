<?php

namespace App\Command;

use App\Entity\CurrencyList;
use App\Entity\CurrencyRate;
use App\Service\CronHistoryChecker;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class UpdateExchangeCurrencyRateCommand extends Command
{
    const SLUG = 'exchange-rate';
    const EXCHANGE_CURRENCY_RATE_SITE_URL='http://www.cbr.ru/scripts/XML_daily.asp';
    public EntityManagerInterface $entityManager;
    public LoggerInterface $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    protected function configure()
    {
        $this
            ->setName('app:exchange-currency-rate:update')
            ->setDescription('Добавление и обновление обменного курса валют. В качестве опционального параметра принимает дату в формате dd.mm.YYYY')
            ->setDefinition(
                new InputDefinition([
                    new InputOption('date', 'd', InputOption::VALUE_REQUIRED),
                ])
            )
            ->setHelp('Эта команда добавляет и обновляет курсы валют на обмен');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cronHistoryChecker = new CronHistoryChecker($this->entityManager);
        $startTime = new Carbon();
        $dateRequest = $this->getDateRequest($input);
        $data = $this->getDataFromSource($dateRequest);
        $currencyList = $this->entityManager->getRepository(CurrencyList::class)->findAll();

        /** @var CurrencyList $currency */
        foreach($currencyList as $currency) {
            if($currency->getIsoCode() === 'rub') continue;

            $indexDataCurrencyRate = $this->getIndexDataCurrencyRate($data, $currency);
            $dataCurrencyRate = $data['Valute'][$indexDataCurrencyRate];
            if(!$indexDataCurrencyRate){

                $currencyRate = $this->entityManager->getRepository(CurrencyRate::class)->findOneBy([
                    'date' => $dateRequest,
                    'currencyCode' => $currency->getIsoCode()
                ]);
                $rate = $this->getRateValue($dataCurrencyRate);

                // есть ли значение за выбранную дату по текушей валюте
                if($currencyRate) {
                    $currencyRate->setRate($rate);
                } else {
                    $this->createCurrencyRate($dateRequest, $currency->getIsoCode(), (string)$rate);
                }
            } else {
                $this->createYesterdayCurrencyRateIfExist($dateRequest, $currency);
            }
        }
        $endTime = new Carbon();
        $cronHistoryChecker->create(self::SLUG, $startTime->floatDiffInSeconds($endTime));

        return 0;
    }

    private function getDateRequest($input){
        $dateRequest = new \DateTime(null, new \DateTimeZone("Europe/Moscow"));;
        if($input->getOption('date')){
            $dateOption = \DateTime::createFromFormat('d.m.Y', $input->getOption('date'));
            if ($dateOption){
                $dateRequest = $dateOption;
            } else {
                $this->logger->warning(
                    'Некорректный формат даты. Необходима дата в виде "d.m.Y". Выбрана текущая дата.');
            }
        }
        return $dateRequest;
    }

    private function getDataFromSource($dateRequest){
        $serializer = new Serializer([new ObjectNormalizer()], [new XmlEncoder()]);
        return $serializer->decode(file_get_contents(self::EXCHANGE_CURRENCY_RATE_SITE_URL . '?date_req='
            . $dateRequest->format('d/m/Y')), 'xml');
    }

    private function getRateValue($dataCurrencyRate){
        return
            (float)str_replace(',','.', $dataCurrencyRate['Value'])
            /
            (int)$dataCurrencyRate['Nominal']; // 1, 10, 100, 1000 ...
    }

    private function createCurrencyRate($date, $currencyCode, $rateValue){
        $rate = new CurrencyRate();
        $rate->setDate($date)
            ->setCurrencyCode($currencyCode)
            ->setRate($rateValue);

        $this->entityManager->persist($rate);
        $this->entityManager->flush();
    }

    private function getIndexDataCurrencyRate($data, $currency){
        $indexDataCurrencyRate = null;
        foreach($data['Valute'] as $key => $dataCurrencyRate){
            // Todo Сделать обозначения валют CurrencyList в UpperCase, упростить проверку
            if( strtolower($currency->getIsoCode()) === strtolower($dataCurrencyRate['CharCode']) ){
                $indexDataCurrencyRate = $key;
                break;
            }
        }
        return $indexDataCurrencyRate;
    }

    private function createYesterdayCurrencyRateIfExist($dateRequest, $currencyCode){
        $hasTodayCurrencyRate = 0 !== $this->entityManager->getRepository(CurrencyRate::class)->count([
            'date' => $dateRequest,
            'currencyCode' =>  $currencyCode
        ]);

        if( !$hasTodayCurrencyRate ) {
            $dateYesterday = clone $dateRequest;
            $dateYesterday->modify('-1 day');

            $yesterdayCurrencyRate = $this->entityManager->getRepository(CurrencyRate::class)->findOneBy([
                'date' => $dateYesterday,
                'currencyCode' => $currencyCode
            ]);

            if ($yesterdayCurrencyRate) {
                $this->createCurrencyRate($dateRequest, $currencyCode, $yesterdayCurrencyRate->getRate());
            }
        }
    }
}