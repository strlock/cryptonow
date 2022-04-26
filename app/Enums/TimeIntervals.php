<?php

namespace App\Enums;

use App\Services\Crypto\Helpers\TimeHelper;

class TimeIntervals
{
    public const ONE_MINUTE = 1*TimeHelper::MINUTE_MS;
    public const FIVE_MINUTES = 5*TimeHelper::MINUTE_MS;
    public const FIFTEEN_MINUTES = 15*TimeHelper::MINUTE_MS;
    public const THIRTEEN_MINUTES = 30*TimeHelper::MINUTE_MS;
    public const ONE_HOUR = 60*TimeHelper::MINUTE_MS;
    public const FOUR_HOURS = 240*TimeHelper::MINUTE_MS;
    public const ONE_DAY = 6*240*TimeHelper::MINUTE_MS;
}
