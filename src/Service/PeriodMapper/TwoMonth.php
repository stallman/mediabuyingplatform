<?php

namespace App\Service\PeriodMapper;

class TwoMonth extends Period
{
    public function getDateBetween()
    {
        return [$this->getFrom()->modify('-59 days'), $this->getTo()];
    }
}