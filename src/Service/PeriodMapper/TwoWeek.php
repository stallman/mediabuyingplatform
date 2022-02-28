<?php

namespace App\Service\PeriodMapper;

class TwoWeek extends Period
{
    public function getDateBetween()
    {
        return [$this->getFrom()->modify('-13 days'), $this->getTo()];
    }
}