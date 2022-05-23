<?php


namespace App\Services;


use App\Enums\TimeInterval;
use App\Repositories\MarketDeltaRepository;
use App\Services\Crypto\Exchanges\Factory;
use App\Helpers\TimeHelper;
use Exception;
use Illuminate\Support\Collection;

class GetAggregateMarketStatService implements GetAggregateMarketStatServiceInterface
{
    private array $exchangeNames = [
        'bitfinex',
        'binance',
        'bitstamp',
    ];

    public function __construct(private MarketDeltaRepository $marketDeltaRepository)
    {
        //
    }

    /**
     * @param string $symbol
     * @param int $fromTime
     * @param int|null $toTime
     * @param TimeInterval|null $interval
     * @return Collection
     * @throws Exception
     */
    public function getAggregateMarketDelta(string $symbol, int $fromTime, int $toTime = null, ?TimeInterval $interval = null): Collection
    {
        if (empty($interval)) {
            $interval = TimeInterval::FIVE_MINUTES();
        }
        $result = collect();
        $fromTime = TimeHelper::round($fromTime, $interval);
        $toTime = TimeHelper::round($toTime, $interval);
        $marketDelta = $this->marketDeltaRepository->getTimeRangeMarketDeltaByExchangeAndSymbol($fromTime, $toTime);
        while ($fromTime <= $toTime) {
            $result[$fromTime] = 0.0;
            $nextTime = $fromTime;
            while ($nextTime < $fromTime + $interval->value()) {
                foreach ($this->exchangeNames as $exchangeName) {
                    $exchange = Factory::create($exchangeName);
                    foreach ($exchange->getExchangeSymbols($symbol) as $exchangeSymbol) {
                        $result[$fromTime] += $marketDelta[$exchangeName][$exchangeSymbol][$nextTime];
                    }
                }
                $nextTime += TimeInterval::MINUTE()->value();
            }
            $fromTime = $nextTime;
        }
        return $result;
    }
}
