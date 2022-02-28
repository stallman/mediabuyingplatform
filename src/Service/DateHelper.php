<?php

namespace App\Service;

class DateHelper
{
    public static function formatDefaultDateTime($date): string
    {
        return $date->format('d.m.y H:i');
    }

    public static function formatDefaultDate($date): string
    {
        return $date->format('d.m.Y');
    }

    public static function formatDatabaseFieldFormat($date): string
    {
        return $date->format('y.m.d H:i:s');
    }
}