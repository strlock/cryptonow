<?php

namespace App\Enums;

use Sourceboat\Enumeration\Enumeration;

/**
 * @method static self BUY()
 * @method static self SELL()
 * @method static self NOTHING()
 * @method bool isBUY()
 * @method bool isSELL()
 * @method bool isNOTHING()
 */
class StrategySignal extends Enumeration
{
    public const BUY = 1;
    public const SELL = -1;
    public const NOTHING = 0;
}
