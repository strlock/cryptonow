<?php


namespace App\Enums;

use Sourceboat\Enumeration\Enumeration;

/**
 * @method static self DEFAULT()
 * @method static self MARKET_STAT_CALCULATION()
 * @method static self BITFINEX_MARKET_STAT_CALCULATION()
 * @method static self BITSTAMP_MARKET_STAT_CALCULATION()
 * @method bool isDEFAULT()
 * @method bool isMARKET_STAT_CALCULATION()
 * @method bool isBITFINEX_MARKET_STAT_CALCULATION()
 * @method bool isBITSTAMP_MARKET_STAT_CALCULATION()
 */
class QueueNames extends Enumeration
{
    public const DEFAULT = 'default';
    public const BINANCE_MARKET_STAT_CALCULATION = 'binance-market-stat-calculation';
    public const BITFINEX_MARKET_STAT_CALCULATION = 'bitfinex-market-stat-calculation';
    public const BITSTAMP_MARKET_STAT_CALCULATION = 'bitstamp-market-stat-calculation';
}
