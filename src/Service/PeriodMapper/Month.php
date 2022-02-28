<?php

namespace App\Service\PeriodMapper;

class Month extends Period
{
    public function getDateBetween()
    {
        return [$this->getFrom()->modify('-29 days'), $this->getTo()];
    }
}