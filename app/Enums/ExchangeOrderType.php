<?php

namespace App\Enums;

use Sourceboat\Enumeration\Enumeration;

/**
 * @method static self market()
 * @method static self limit()
 * @method static self stop_loss()
 * @method static self take_profit()
 * @method bool isMarket()
 * @method bool isLimit()
 * @method bool isStop_Loss()
 * @method bool isTake_Profit()
 */
class ExchangeOrderType extends Enumeration
{
    public const market = 'market';
    public const limit = 'limit';
    public const stop_loss = 'stop_loss';
    public const take_profit = 'take_profit';
}
