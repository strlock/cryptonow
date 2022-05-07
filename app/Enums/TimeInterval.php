<?php

namespace App\Enums;

use App\Helpers\TimeHelper;

use Sourceboat\Enumeration\Enumeration;

/**
 * @method static self MINUTE()
 * @method static self FIVE_MINUTES()
 * @method static self TEN_MINUTES()
 * @method static self FIFTEEN_MINUTES()
 * @method static self THIRTEEN_MINUTES()
 * @method static self HOUR()
 * @method static self THREE_HOURS()
 * @method static self FOUR_HOURS()
 * @method static self SIX_HOURS()
 * @method static self TWELVE_HOURS()
 * @method static self DAY()
 * @method bool isMINUTE()
 * @method bool isFIVE_MINUTES()
 * @method bool isTEN_MINUTES()
 * @method bool isFIFTEEN_MINUTES()
 * @method bool isTHIRTEEN_MINUTES()
 * @method bool isHOUR()
 * @method bool isTHREE_HOURS()
 * @method bool isFOUR_HOURS()
 * @method bool isSIX_HOURS()
 * @method bool isTWELVE_HOURS()
 * @method bool isDAY()
 */
class TimeInterval extends Enumeration
{
    public const MINUTE = 60*1000;
    public const FIVE_MINUTES = 5*self::MINUTE;
    public const TEN_MINUTES = 10*self::MINUTE;
    public const FIFTEEN_MINUTES = 15*self::MINUTE;
    public const THIRTEEN_MINUTES = 30*self::MINUTE;
    public const HOUR = 60*self::MINUTE;
    public const THREE_HOURS = 3*self::HOUR;
    public const FOUR_HOURS = 4*self::HOUR;
    public const SIX_HOURS = 6*self::HOUR;
    public const TWELVE_HOURS = 12*self::HOUR;
    public const DAY = 24*self::HOUR;
}
