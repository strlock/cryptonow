<?php

namespace App\Enums;

use Sourceboat\Enumeration\Enumeration;

/**
 * @method static self NEW()
 * @method static self CANCELED()
 * @method static self REPLACED()
 * @method static self REJECTED()
 * @method static self TRADE()
 * @method static self EXPIRED()
 * @method bool isNEW()
 * @method bool isCANCELED()
 * @method bool isREPLACED()
 * @method bool isREJECTED()
 * @method bool isTRADE()
 * @method bool isEXPIRED()
 */
class BinanceOrderExecutionType extends Enumeration
{
    public const NEW = 'NEW';
    public const CANCELED = 'CANCELED';
    public const REPLACED = 'REPLACED';
    public const REJECTED = 'REJECTED';
    public const TRADE = 'TRADE';
    public const EXPIRED = 'EXPIRED';
}
