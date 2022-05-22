<?php


namespace App\Repositories;

use App\Models\MarketDelta;

class MarketDeltaRepository
{
    /**
     * @param string $symbol
     * @param int $time
     * @return MarketDelta|null
     */
    public function getMarketDeltaByTime(string $exchange, string $symbol, int $time): ?MarketDelta
    {
        return MarketDelta::where('symbol', '=', $symbol)
            ->where('exchange', '=', $exchange)
            ->where('time', '=', $time)->first();
    }
}
