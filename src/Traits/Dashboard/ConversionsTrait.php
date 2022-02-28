<?php


namespace App\Traits\Dashboard;

use App\Entity\Conversions;
use App\Entity\Country;
use App\Entity\CurrencyList;
use App\Entity\Postback;
use App\Entity\TeasersClick;
use App\Form as Form;
use App\Entity as Entity;
use App\Service\PeriodMapper\CurrentMonth;
use App\Service\PeriodMapper\CurrentYear;
use App\Service\PeriodMapper\EmptyPeriod;
use App\Service\PeriodMapper\LastMonth;
use App\Service\PeriodMapper\LastYear;
use App\Service\PeriodMapper\Today;
use App\Service\PeriodMapper\TwoWeek;
use App\Service\PeriodMapper\Week;
use App\Service\PeriodMapper\Yesterday;
use Symfony\Component\Form\FormInterface;

trait ConversionsTrait
{
    public function getConversionsTableHeader($ajaxUrl = null)
    {
        return [
            [
                'label' => 'ID клика',
                'pagingServerSide' => true,
                'searching' => false,
                'sortable' => false,
                'ajaxUrl' => $ajaxUrl
            ],
            [
                'label' => 'Партнерка',
            ],
            [
                'label' => 'Источник',
            ],
            [
                'label' => 'Группа и подгруппа тизеров',
            ],
            [
                'label' => 'Страна',
            ],
            [
                'label' => 'Статус',
            ],
            [
                'label' => 'Стоимость',
            ],
            [
                'label' => 'Валюта',
            ],
            [
                'label' => 'Дата добавления',
            ],
            [
                'label' => 'Дата обновления',
            ],
            [
                'label' => '',
            ],
        ];
    }

    /**
     * @param Entity\Conversions|null $conversion
     * @return FormInterface
     */
    public function createConversionForm(Entity\Conversions $conversion = null)
    {
        $conversion = !$conversion ? new Entity\Conversions() : $conversion;

        return $this
            ->createForm(Form\ConversionType::class, $conversion, ['user' => $this->getUser()])
            ->handleRequest($this->request);
    }


    public function getPeriod($period)
    {
        switch($period) {
            case 'today':
                return new Today();
            case 'yesterday':
                return new Yesterday();
            case 'week':
                return new Week();
            case 'two-week':
                return new TwoWeek();
            case 'current-month':
                return new CurrentMonth();
            case 'last-month':
                return new LastMonth();
            case 'current-year':
                return new CurrentYear();
            case 'last-year':
                return new LastYear();
            default:
                return new EmptyPeriod();
        }
    }

    public function convertDate($from, $to)
    {
        try{
            $from = new \DateTime($from);
        } catch(\Exception $e) {
            $from = null;
        }

        try{
            $to = new \DateTime($to);
            $to->setTime(23, 59, 59);
        } catch(\Exception $e) {
            $to = null;
        }

        return [$from, $to];
    }

    public function setConversionData(Conversions $conversion, TeasersClick $click, ?int $addDate)
    {
        $country = $this->entityManager->getRepository(Country::class)->getCountryByIsoCode($click->getCountryCode());
        $currency = $conversion->getAffilate()->getCurrency();

        $conversion->setMediabuyer($click->getBuyer())
            ->setSource($click->getSource())
            ->setSubgroup($click->getTeaser()->getTeasersSubGroup())
            ->setCountry($country)
            ->setCurrency($currency)
            ->setDesign($click->getDesign())
            ->setAlgorithm($click->getAlgorithm())
            ->setUuid($click->getUuid());

        $addDate == 0 ? $conversion->setCreatedAt($click->getCreatedAt()) : $conversion->setCreatedAt(null);

        $conversion
            ->setAmountRub($this->currencyConverter->convertCurrencies($currency->getId(), $conversion->getAmount(), 4,
                $conversion->getCreatedAt()->format('Y-m-d')));

        if($click->getNews()) $conversion->setNews($click->getNews());

        return $conversion;
    }
}
