<?php

namespace App\Service\PeriodMapper;

class LastMonth extends Period
{
    public function getDateBetween()
    {
        return [$this->getFrom()->modify('first day of last month'), $this->getTo()->modify('last day of last month')];
    }
}