<?php


namespace App\Services\Crypto\Helpers;


use Illuminate\Support\Facades\Date;

abstract class TimeHelper
{
    public const MINUTE_MS = 60*1000;
    public const FIVE_MINUTES_MS = 5*self::MINUTE_MS;
    public const TEN_MINUTES_MS = 10*self::MINUTE_MS;
    public const HOUR_MS = 60*self::MINUTE_MS;
    public const THREE_HOURS_MS = 3*self::HOUR_MS;
    public const SIX_HOURS_MS = 6*self::HOUR_MS;
    public const DAY_MS = 24*self::HOUR_MS;

    /**
     * @param int $ts
     * @return int
     */
    public static function roundTimestampMs(int $ts): int
    {
        return self::MINUTE_MS*intdiv($ts, self::MINUTE_MS);
    }

    /**
     * @return int
     */
    public static function timeMs(): int
    {
        return (int)(microtime(true)*1000);
    }
}
