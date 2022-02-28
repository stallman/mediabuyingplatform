<?php

namespace App\Service\PeriodMapper;

class CurrentWeek extends Period
{
    public function getDateBetween()
    {
        return [$this->getFrom()->modify('monday this week'), $this->getTo()->modify('sunday this week')];
    }
}