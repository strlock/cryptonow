<?php


namespace App\Services\Strategy;


use App\Enums\StrategySignal;
use App\Services\Crypto\Helpers\TimeHelper;
use App\Services\GetAggregateMarketStatService;
use App\Services\Strategy\StrategyInterface;
use Illuminate\Support\Collection;

class AnomalousMarketDeltaBuyStrategy implements StrategyInterface
{
    private const PREV_PERIOD = TimeHelper::DAY_MS;
    private const DOMINATION = 5.0;

    public function __construct(private GetAggregateMarketStatService $aggregateMarketStatService)
    {
        //
    }

    /**
     * @param string $symbol
     * @param int $fromTime
     * @param int|null $toTime
     * @param int $interval
     * @return Collection
     */
    public function getSignals(string $symbol, int $fromTime, int $toTime = null, int $interval = TimeHelper::FIVE_MINUTE_MS): Collection
    {
        $result = collect();
        $fromTime = TimeHelper::roundTimestampMs($fromTime);
        $toTime = TimeHelper::roundTimestampMs($toTime);
        $interval = TimeHelper::roundTimestampMs($interval);
        $prevMdPeriod = intdiv(TimeHelper::roundTimestampMs(self::PREV_PERIOD), $interval)*$interval;
        $marketDeltaByTime = $this->aggregateMarketStatService->getAggregateMarketDelta($symbol, $fromTime - $prevMdPeriod, $toTime, $interval);
        for ($time=$fromTime; $time<$toTime; $time += $interval) {
            $currentMarketDelta = $marketDeltaByTime[$time];
            if ($currentMarketDelta <= 0.0) {
                continue;
            }
            $prevMarketDelta = 0.0;
            $count = 0;
            for ($prevTime=$time-$prevMdPeriod; $prevTime < $time; $prevTime += $interval) {
                if ($marketDeltaByTime[$prevTime] > 0.0) {
                    $prevMarketDelta += $marketDeltaByTime[$prevTime];
                    $count++;
                }
            }
            $prevMarketDeltaAvg = $prevMarketDelta/$count;
            if ($currentMarketDelta/$prevMarketDeltaAvg > self::DOMINATION) {
                $signal = StrategySignal::BUY();
            } else {
                $signal = StrategySignal::NOTHING();
            }
            $result->put($time, $signal);
        }
        return $result;
    }
}
