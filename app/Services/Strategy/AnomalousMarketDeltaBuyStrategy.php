<?php


namespace App\Services\Strategy;


use App\Dto\MaxMarketDeltaDto;
use App\Enums\StrategySignal;
use App\Enums\TimeInterval;
use App\Helpers\TimeHelper;
use App\Services\Crypto\Exchanges\Factory as ExchangesFactory;
use App\Services\GetAggregateMarketStatService;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AnomalousMarketDeltaBuyStrategy implements StrategyInterface
{
    public function __construct(private GetAggregateMarketStatService $aggregateMarketStatService)
    {
        //
    }

    /**
     * @param string $symbol
     * @return StrategySignal
     * @throws Exception
     */
    public function getSignal(string $symbol): StrategySignal
    {
        $maxMd = $this->getMaxMarketDelta($symbol);
        if (empty($maxMd)) {
            Log::debug('No maximum market delta right now');
            return StrategySignal::NOTHING();
        }
        $relativePriceDiffPercent = 100*($maxMd->getToPrice()-$maxMd->getFromPrice())/$maxMd->getFromPrice();
        Log::debug(date('d.m.Y H:i:s', $maxMd->getFromTime()/1000).'  '.
            date('d.m.Y H:i:s', $maxMd->getToTime()/1000).'  '.
                   $maxMd->getMarketDelta().' '.
                   $maxMd->getFromPrice().'-'.
                   $maxMd->getToPrice().' '.
            round($relativePriceDiffPercent, 2).'%');
        $toTime = TimeHelper::round(TimeHelper::time(), TimeInterval::HOUR());
        if ($toTime <= $maxMd->getToTime() && abs($relativePriceDiffPercent) < (float)config('crypto.strategyRelativePriceDiffPercent')) {
            return StrategySignal::BUY();
        }
        return StrategySignal::NOTHING();
    }

    /**
     * @throws Exception
     */
    public function getMaxMarketDelta(string $symbol): ?MaxMarketDeltaDto
    {
        $interval = TimeInterval::HOUR();
        $toTime = TimeHelper::round(TimeHelper::time(), $interval);
        $fromTime = TimeHelper::round($toTime-(int)config('crypto.strategyPeriod'), $interval);
        $marketDeltaByTime = $this->aggregateMarketStatService->getAggregateMarketDelta($symbol, $fromTime, $toTime, $interval);
        $mdFromTime = 0;
        $mdMax = 0.0;
        $mdToTime = 0;
        $mdPositiveSum = 0.0;
        $intervalsCount = 0;
        for ($time=$fromTime; $time<$toTime; $time+=$interval->value()) {
            $mdCurrent = $marketDeltaByTime[$time];
            $intervalsCount++;
            if ($mdCurrent > 0.0) {
                $mdPositiveSum += $mdCurrent;
            }
            $mdAvg = $mdPositiveSum/$intervalsCount;
            if ($mdCurrent > $mdMax && $mdCurrent > 2*$mdAvg) {
                $mdMax = $mdCurrent;
                $mdFromTime = $time;
                $mdToTime = $time + $interval->value();
            }
        }
        return new MaxMarketDeltaDto(
            $mdMax,
            $mdFromTime,
            $mdToTime,
            $this->getPriceAtTime($symbol, $mdFromTime),
            $this->getPriceAtTime($symbol, $mdToTime),
        );
    }

    /**
     * @param string $symbol
     * @param int $time
     * @return float
     * @throws Exception
     */
    private function getPriceAtTime(string $symbol, int $time): float
    {
        $interval = TimeInterval::MINUTE();
        $toTime = TimeHelper::round(TimeHelper::time(), $interval);
        $fromTime = TimeHelper::round($toTime-(int)config('crypto.strategyPeriod'), $interval);
        $cacheKey = md5(implode('.', [$symbol, $toTime, $interval->value()]));
        if (!Cache::has($cacheKey)) {
            $exchange = ExchangesFactory::create();
            $exchangeSymbol = $exchange->getExchangeSymbol($symbol);
            Cache::put($cacheKey, $exchange->getCandlesticks($exchangeSymbol, $fromTime, $toTime, $interval));
        }
        $candlesticks = Cache::get($cacheKey);
        foreach ($candlesticks as $openTime => $candlestickData) {
            $closeTime = (int)$candlestickData[4];
            if ($time >= $openTime && $time <= $closeTime) {
                return ($candlestickData[0] + $candlestickData[3])/2;
            }
        }
        return 0.0;
    }
}
