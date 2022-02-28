<?php


namespace App\Traits;


trait TimeInformationTrait
{
    private function getTimesOfDay(\DateTimeInterface $time): string
    {
        return $time->format('H');
    }

    private function getDayOfWeek(\DateTimeInterface $time)
    {
        $daysOfWeek = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        $dayOfWeek = date('w', strtotime($time->format('Y-m-d')));

        return $daysOfWeek[$dayOfWeek];
    }
}