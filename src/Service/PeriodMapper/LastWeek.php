<?php

namespace App\Service\PeriodMapper;

class LastWeek extends Period
{
    public function getDateBetween()
    {
        return [$this->getFrom()->modify('monday last week'), $this->getTo()->modify('sunday last week')];
    }
}