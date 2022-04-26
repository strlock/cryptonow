<?php


namespace App\Services;


use App\Services\Crypto\Exchanges\FacadeInterface;
use App\Services\Crypto\Exchanges\Factory;
use App\Services\Crypto\Helpers\TimeHelper;
use Illuminate\Support\Collection;

class GetAggregateMarketStatService implements GetAggregateMarketStatServiceInterface
{
    private array $exchangeNames = [
        'bitfinex',
        'binance',
        'bitstamp',
    ];

    /**
     * @param string $symbol
     * @param int $fromTime
     * @param int|null $toTime
     * @param int|float $interval
     * @return Collection
     */
    public function getAggregateMarketDelta(string $symbol, int $fromTime, int $toTime = null, int $interval = TimeHelper::FIVE_MINUTE_MS): Collection
    {
        $result = collect();
        $fromTime = TimeHelper::roundTimestampMs($fromTime);
        $toTime = TimeHelper::roundTimestampMs($toTime);
        $interval = TimeHelper::roundTimestampMs($interval);
        if ($interval === 0) {
            return $result;
        }
        while ($fromTime < $toTime) {
            $result[$fromTime] = 0.0;
            $nextTime = $fromTime;
            while ($nextTime < $fromTime + $interval) {
                foreach ($this->exchangeNames as $exchangeName) {
                    $exchange = Factory::create($exchangeName);
                    /** @var FacadeInterface $exchange */
                    //$test = $exchange->getTrades($symbol, $fromTime, $toTime);
                    //$job = new BitstampFetchMinuteMarketStat(new FetchMinuteMarketDeltaDto('BTCUSDT', strtotime('10.04.2022 13:00')*1000));
                    //$job->handle();
                    $result[$fromTime] += $exchange->getMinuteMarketDelta($symbol, $nextTime);
                }
                $nextTime += TimeHelper::MINUTE_MS;
            }
            $fromTime = $nextTime;
        }
        return $result;
    }
}
