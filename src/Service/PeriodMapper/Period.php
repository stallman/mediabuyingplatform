<?php

namespace App\Service\PeriodMapper;

abstract class Period
{
    private \DateTimeInterface $from;
    private \DateTimeInterface $to;

    abstract public function getDateBetween();

    public function getFrom(): \DateTimeInterface
    {
        $this->from = new \DateTime();
        $this->from->setTime(00, 00, 0);

        return $this->from;
    }

    public function getTo(): \DateTimeInterface
    {
        $this->to = new \DateTime();
        $this->to->setTime(23, 59, 59);

        return $this->to;
    }
}