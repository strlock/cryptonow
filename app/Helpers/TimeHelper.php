<?php


namespace App\Helpers;


use App\Enums\TimeInterval;
use Exception;

abstract class TimeHelper
{
    /**
     * @param int $timestamp
     * @param TimeInterval|null $interval
     * @return int
     * @throws Exception
     */
    public static function round(int $timestamp, ?TimeInterval $interval): int
    {
        return $interval->value()*intdiv($timestamp, $interval->value());
    }

    /**
     * @return int
     */
    public static function time(): int
    {
        return (int)(microtime(true)*1000);
    }
}
