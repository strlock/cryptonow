<?php


namespace App\Repositories;

use App\Models\MarketDelta;
use Illuminate\Support\Collection;

class MarketDeltaRepository
{
    /**
     * @param string $exchange
     * @param string $exchangeSymbol
     * @param int $fromTime
     * @return float
     */
    public function getMinuteMarketDelta(string $exchange, string $exchangeSymbol, int $fromTime): float
    {
        $marketDelta = MarketDelta::where('symbol', '=', $exchangeSymbol)
            ->where('exchange', '=', $exchange)
            ->where('time', '=', $fromTime)->first();
        return $marketDelta->value ?? 0.0;
    }

    public function getTimeRangeMarketDeltaByExchangeAndSymbol(int $fromTime, int $toTime): Collection
    {
        $result = collect();
        $items = MarketDelta::whereBetween('time', [$fromTime, $toTime])->get();
        foreach ($items as $item) {
            if (!$result->has($item->exchange)) {
                $result->put($item->exchange, collect());
            }
            if (!$result[$item->exchange]->has($item->symbol)) {
                $result[$item->exchange]->put($item->symbol, collect());
            }
            $result[$item->exchange][$item->symbol]->put($fromTime, $item->value);
        }
        return $result;
    }
}
