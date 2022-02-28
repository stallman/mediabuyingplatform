<?php

namespace App\Repository;

use App\Entity\CurrencyRate;
use App\Service\ExchangeRatesApi;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;

/**
 * @method CurrencyRate|null find($id, $lockMode = null, $lockVersion = null)
 * @method CurrencyRate|null findOneBy(array $criteria, array $orderBy = null)
 * @method CurrencyRate[]    findAll()
 * @method CurrencyRate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CurrencyRateRepository extends ServiceEntityRepository
{
    private $era;
    private $logger;

    public function __construct(ManagerRegistry $registry, ExchangeRatesApi $era, LoggerInterface $logger)
    {
        parent::__construct($registry, CurrencyRate::class);
        $this->era = $era;
        $this->logger = $logger;
    }

    public function getRateByCode(string $code, string $date = ''): float
    {
        if (!$date) {
            $date = date('Y-m-d');
        }

        $query = $this->createQueryBuilder('rate')
            ->where('rate.currencyCode = :code')
            ->andWhere('rate.date = :date')
            ->setParameters([
                'code' => $code,
                'date' => $date,
            ])
            ->getQuery();

        $rate = $query->getOneOrNullResult();

        if (null === $rate) {
            $rate = $this->updateRate($code, $date);
        }

        $result = (null !== $rate) ? $rate->getRate() : 0;

        return floatval($result);
    }

    public function updateRate(string $code, string $date): ?CurrencyRate
    {
        $targetCurrency = 'rub';
        $rate = $this->era->getRate($code, $targetCurrency, $date);

        $currencyRate = null;

        if ($rate > 0) {
            try {
                $currencyRate = new CurrencyRate();
                $currencyRate->setDate(new \DateTime($date));
                $currencyRate->setCurrencyCode($code);
                $currencyRate->setRate($rate);

                $em = $this->getEntityManager();
                $em->persist($currencyRate);
                $em->flush();
            } catch (\Exception $e) {
                $this->logger->error('Currency exchange. Persist fail.', [
                    'code' => $code,
                    'date' => $date,
                    'currencyRate' => $currencyRate,
                    'message' => $e->getMessage(),
                    'trace' => $e->getTrace(),
                ]);
            }
        } else {
            $this->logger->error('Currency exchange. Exchange fail.', [
                'code' => $code,
                'date' => $date,
            ]);
        }

        return $currencyRate;
    }
}
