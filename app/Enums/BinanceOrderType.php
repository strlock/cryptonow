<?php

namespace App\Enums;

use Sourceboat\Enumeration\Enumeration;

/**
 * @method static self MARKET()
 * @method static self LIMIT()
 * @method static self STOP_LOSS_LIMIT()
 * @method static self STOP_LOSS()
 * @method static self TAKE_PROFIT()
 * @method static self TAKE_PROFIT_LIMIT()
 * @method bool isMARKET()
 * @method bool isLIMIT()
 * @method bool isSTOP_LOSS_LIMIT()
 * @method bool isSTOP_LOSS()
 * @method bool isTAKE_PROFIT()
 * @method bool isTAKE_PROFIT_LIMIT()
 */
class BinanceOrderType extends Enumeration
{
    public const MARKET = 'MARKET';
    public const LIMIT = 'LIMIT';
    public const STOP_LOSS_LIMIT = 'STOP_LOSS_LIMIT';
    public const STOP_LOSS = 'STOP_LOSS';
    public const TAKE_PROFIT = 'TAKE_PROFIT';
    public const TAKE_PROFIT_LIMIT  = 'TAKE_PROFIT_LIMIT ';
}
