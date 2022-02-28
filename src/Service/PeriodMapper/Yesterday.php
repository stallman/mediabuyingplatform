<?php

namespace App\Service\PeriodMapper;

class Yesterday extends Period
{
    public function getDateBetween()
    {
        return [$this->getFrom()->modify('yesterday'), $this->getTo()->modify('-1 days')];
    }
}