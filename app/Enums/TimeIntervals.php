<?php

namespace App\Enums;

use App\Services\Crypto\Helpers\TimeHelper;

use Sourceboat\Enumeration\Enumeration;

/**
 * @method static self ONE_MINUTE()
 * @method static self FIVE_MINUTES()
 * @method static self FIFTEEN_MINUTES()
 * @method static self THIRTEEN_MINUTES()
 * @method static self ONE_HOUR()
 * @method static self FOUR_HOURS()
 * @method static self ONE_DAY()
 * @method bool isONE_MINUTE()
 * @method bool isFIVE_MINUTES()
 * @method bool isFIFTEEN_MINUTES()
 * @method bool isTHIRTEEN_MINUTES()
 * @method bool isONE_HOUR()
 * @method bool isFOUR_HOURS()
 * @method bool isONE_DAY()
 */
class TimeIntervals extends Enumeration
{
    public const ONE_MINUTE = 1*TimeHelper::MINUTE_MS;
    public const FIVE_MINUTES = 5*TimeHelper::MINUTE_MS;
    public const FIFTEEN_MINUTES = 15*TimeHelper::MINUTE_MS;
    public const THIRTEEN_MINUTES = 30*TimeHelper::MINUTE_MS;
    public const ONE_HOUR = 60*TimeHelper::MINUTE_MS;
    public const FOUR_HOURS = 240*TimeHelper::MINUTE_MS;
    public const ONE_DAY = 6*240*TimeHelper::MINUTE_MS;
}
