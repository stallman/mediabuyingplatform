<?php

namespace App\Service\PeriodMapper;

class CurrentYear extends Period
{
    public function getDateBetween()
    {
        return [$this->getFrom()->modify('first day of January this year'), $this->getTo()];
    }
}