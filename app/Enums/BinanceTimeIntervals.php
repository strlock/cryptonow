<?php

namespace App\Enums;

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


class BinanceTimeIntervals extends Enumeration
{
    public const ONE_MINUTE = '1m';
    public const FIVE_MINUTES = '5m';
    public const FIFTEEN_MINUTES = '15m';
    public const THIRTEEN_MINUTES = '30m';
    public const ONE_HOUR = '1h';
    public const FOUR_HOURS = '4h';
    public const ONE_DAY = '1d';
}
