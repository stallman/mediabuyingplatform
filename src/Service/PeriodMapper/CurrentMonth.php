<?php

namespace App\Service\PeriodMapper;

class CurrentMonth extends Period
{
    public function getDateBetween()
    {
        return [$this->getFrom()->modify('first day of this month'), $this->getTo()];
    }
}