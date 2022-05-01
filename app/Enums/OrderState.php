<?php

namespace App\Enums;

use Sourceboat\Enumeration\Enumeration;
/**
 * @method static self NEW()
 * @method static self READY()
 * @method static self PROFIT()
 * @method static self LOSS()
 * @method static self COMPLETED()
 * @method static self FAILED()
 * @method static self CANCELED()
 * @method bool isNEW()
 * @method bool isREADY()
 * @method bool isPROFIT()
 * @method bool isLOSS()
 * @method bool isCOMPLETED()
 * @method bool isFAILED()
 * @method bool isCANCELED()
 */
class OrderState extends Enumeration
{
    public const NEW = 'new';
    public const READY = 'ready';
    public const PROFIT = 'profit';
    public const LOSS = 'loss';
    public const COMPLETED= 'completed';
    public const FAILED = 'failed';
    public const CANCELED = 'canceled';
}
