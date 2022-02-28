<?php


namespace App\Traits\Dashboard;


use App\Entity\Costs;
use App\Entity\CurrencyList;
use App\Entity\UserSettings;
use App\Form\CostsType;
use DateTime;
use DateTimeZone;
use App\Service\DateHelper;

trait CostsTrait
{

    public function getCostsTableHeader()
    {
        $th = [
            [
                'label' => 'ID'
            ],
            [
                'label' => 'Дата расхода',
                'pagingServerSide' => true,
                'searching' => false,
                'ajaxUrl' => $this->generateUrl('mediabuyer_dashboard.costs_list_ajax'),
                'columnName' => 'date',
                'defaultTableOrder' => 'desc',
                'sortable' => true
            ],
            [
                'label' => 'Источник',
                'columnName' => 'source',
                'sortable' => true
            ],
            [
                'label' => 'Расход',
            ],
            [
                'label' => 'Расход в ' . $this->getDefaultUserCurrency()->getName(),
            ],
            [
                'label' => 'Валюта',
            ],
            [
                'label' => 'Дата добавления'
            ],
            [
                'label' => ''
            ]
        ];

        $firstPart = array_slice($th, 0, 3);
        $secondPart = array_slice($th, 3);

        if ($this->candidate == 'news') {
            $candidateHeader[] = [
                'label' => 'Новость',
                'columnName' => 'news',
                'sortable' => true
            ];
            $th = array_merge($firstPart, $candidateHeader, $secondPart);
        } else {
            $candidateHeader[] = [
                'label' => 'Кампания',
                'columnName' => 'campaign',
                'sortable' => true
            ];
            $th = array_merge($firstPart, $candidateHeader, $secondPart);
        }

        return $th;
    }

    public function getCostsList(array $costs)
    {
        $costsList = [];

        /** @var Costs $cost */
        foreach($costs as $cost) {
            $item = [
                $this->getBulkCheckBox($cost),
                $cost->getId(),
                DateHelper::formatDefaultDate($cost->getDate()),
                $cost->getSource()->getTitle(),
            ];

            if ($this->candidate == 'news') {
                $item = array_merge($item, [$cost->getNews() ? "{$cost->getNews()->getId()}|{$cost->getNews()->getTitle()}" : '']);
            } else {
                $item = array_merge($item, [$cost->getCampaign()]);
            }

            $item = array_merge($item, [
                $cost->getCost(),
                $this->getCostAmountCode($cost, date_format($cost->getDate(), 'Y-m-d'), $this->getDefaultUserCurrency()->getIsoCode()),
                $cost->getCurrency()->getName(),
                DateHelper::formatDefaultDate($cost->getDateSetData()),
                $this->getActionButtons($cost, $actions = [
                    'edit' => $this->generateUrl('mediabuyer_dashboard.costs_edit', ['id' => $cost->getId()])
                ]),
            ]);

            $costsList[] = $item;
        }

        return $costsList;
    }

    private function getDefaultUserCurrency(): ?CurrencyList
    {
        $defaultCurrencyId = $this->getParameter('default_currency');

        $userCurrency = $this->entityManager->getRepository(UserSettings::class)->findOneBy(
            ['slug' => 'default_currency', 'user' => $this->getUser()]
        ) ;

        if ($userCurrency) {
            $defaultCurrencyId = $userCurrency->getValue();
        }

        return $this->entityManager->getRepository(CurrencyList::class)->find($defaultCurrencyId);
    }

    private function isFinal(Costs $cost)
    {
        return $cost->getIsFinal() ? '' : 'isNotFinal';
    }

    public function createCostsForm(string $candidate, Costs $cost = null, $options = [])
    {
        $cost = !$cost ? new Costs() : $cost;
        $options = array_merge(['user' => $this->getUser(), 'candidate' => $candidate], $options);
        return $this
            ->createForm(CostsType::class, $cost, $options)
            ->handleRequest($this->request);
    }

    public function changeIsFinal(Costs $cost)
    {
        if($this->checkDate($cost->getDate()) && $cost->getCost() != 0){
            $cost->setIsFinal(true);
        } else {
            $cost->setIsFinal(false);
        }

        return $cost;
    }

    private function checkDate(\DateTimeInterface $date){
        return new DateTime($time='now', $timezone= new DateTimeZone('UTC')) > $date;
    }
}
