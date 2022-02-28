<?php

namespace App\Service\PeriodMapper;

class Week extends Period
{
    public function getDateBetween()
    {
        return [$this->getFrom()->modify('-6 days'), $this->getTo()];
    }
}