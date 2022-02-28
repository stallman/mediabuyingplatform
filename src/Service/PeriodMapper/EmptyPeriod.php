<?php

namespace App\Service\PeriodMapper;

class EmptyPeriod extends Period
{
    public function getDateBetween()
    {
        return [null, null];
    }
}