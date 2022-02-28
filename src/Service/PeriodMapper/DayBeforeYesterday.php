<?php

namespace App\Service\PeriodMapper;

class DayBeforeYesterday extends Period
{
    public function getDateBetween()
    {
        return [$this->getFrom()->modify('-2 days'), $this->getTo()->modify('-2 days')];
    }
}