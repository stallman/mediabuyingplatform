<?php

namespace App\Service\PeriodMapper;

class Today extends Period
{
    public function getDateBetween()
    {
        return [$this->getFrom(), $this->getTo()];
    }
}