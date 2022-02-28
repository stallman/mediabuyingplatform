<?php

namespace App\Service\PeriodMapper;

class LastYear extends Period
{
    public function getDateBetween()
    {
        return [$this->getFrom()->modify('first day of January last year'), $this->getTo()->modify('last day of December last year')];
    }
}