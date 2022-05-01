<?php

namespace App\Enums;

use Sourceboat\Enumeration\Enumeration;

/**
 * @method static self BUY()
 * @method static self SELL()
 * @method bool isBUY()
 * @method bool isSELL()
 */
class OrderDirection extends Enumeration
{
    public const BUY = 'buy';
    public const SELL = 'sell';
}
