<?php


namespace App\Services\Strategy;


use App\Dto\MarketDeltaClusterDto;
use App\Enums\StrategySignal;
use App\Enums\TimeInterval;
use App\Helpers\TimeHelper;
use App\Services\Crypto\Exchanges\Factory as ExchangesFactory;
use App\Services\GetAggregateMarketStatService;
use Exception;
use Illuminate\Support\Collection;
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
        $maxMdCluster = $this->getMaxMarketDeltaCluster($symbol);
        if (empty($maxMdCluster)) {
            Log::debug('No maximum cluster right now');
            return StrategySignal::NOTHING();
        }
        $relativePriceDiffPercent = 100*($maxMdCluster->getToPrice()-$maxMdCluster->getFromPrice())/$maxMdCluster->getFromPrice();
        Log::debug(date('d.m.Y H:i:s', $maxMdCluster->getFromTime()/1000).'  '.
            date('d.m.Y H:i:s', $maxMdCluster->getToTime()/1000).'  '.
            $maxMdCluster->getMarketDelta().' '.
            $maxMdCluster->getFromPrice().'-'.
            $maxMdCluster->getToPrice().' '.
            round($relativePriceDiffPercent, 2).'%');
        $toTime = TimeHelper::round(TimeHelper::time(), TimeInterval::MINUTE());
        if ($toTime >= $maxMdCluster->getFromTime() &&
                $toTime <= $maxMdCluster->getToTime() &&
                    abs($relativePriceDiffPercent) < (float)config('crypto.strategyRelativePriceDiffPercent')) {
            return StrategySignal::BUY();
        }
        return StrategySignal::NOTHING();
    }

    /**
     * @param string $symbol
     * @return MarketDeltaClusterDto|null
     * @throws Exception
     */
    public function getMaxMarketDeltaCluster(string $symbol): ?MarketDeltaClusterDto
    {
        $interval = TimeInterval::MINUTE();
        $toTime = TimeHelper::round(TimeHelper::time(), $interval);
        $fromTime = TimeHelper::round($toTime-(int)config('crypto.strategyPeriod'), $interval);
        $marketDeltaByTime = $this->aggregateMarketStatService->getAggregateMarketDelta($symbol, $fromTime, $toTime, $interval);
        $mdMaxSum = 0.0;
        $mdSum = 0.0;
        $mdFromTime = 0;
        $mdToTime = 0;
        for ($time = $fromTime; $time < $toTime; $time += $interval->value()) {
            $currentMarketDelta = $marketDeltaByTime[$time];
            $mdSum += $currentMarketDelta;
            if ($mdSum > $mdMaxSum) {
                $mdMaxSum = $mdSum;
                $mdToTime = $time;
            }
            if ($mdSum < 0.0) {
                $mdSum = 0.0;
                $mdFromTime = $time + $interval->value();
            }
        }
        return new MarketDeltaClusterDto(
            $mdMaxSum,
            $mdFromTime,
            $mdToTime,
            $this->getMDClusterPriceAtTime($symbol, $mdFromTime),
            $this->getMDClusterPriceAtTime($symbol, $mdToTime),
        );
    }

    //public function getMarketDeltaClusters(string $symbol): Collection
    //{
        //$interval = TimeInterval::MINUTE();
        //$toTime = TimeHelper::round(TimeHelper::time(), $interval);
        //$fromTime = TimeHelper::round($toTime-(int)config('crypto.strategyPeriod'), $interval);
        //$marketDeltaByTime = $this->aggregateMarketStatService->getAggregateMarketDelta($symbol, $fromTime, $toTime, $interval);
        //$prevPositive = false;
        //$md = 0.0;
        //$mdFromTime = 0;
        //$clusters = collect();
        /*for ($time=$fromTime; $time<$toTime; $time += $interval->value()) {
            $currentMarketDelta = $marketDeltaByTime[$time];
            $positive = $currentMarketDelta > 0;
            if ($positive) {
                if (!$prevPositive) {
                    $md = 0.0;
                    $mdFromTime = $time;
                }
                $md += $currentMarketDelta;
            } else {
                if ($prevPositive) {
                    $mdToTime = $time;
                    $clusters->push(new MarketDeltaClusterDto(
                        $md,
                        $mdFromTime,
                        $mdToTime,
                        $this->getMDClusterPriceAtTime($symbol, $mdFromTime),
                        $this->getMDClusterPriceAtTime($symbol, $mdToTime),
                    ));
                }
            }
            $prevPositive = $positive;
        }
        if ($prevPositive) {
            $clusters->push(new MarketDeltaClusterDto(
                $md,
                $mdFromTime,
                $toTime,
                $this->getMDClusterPriceAtTime($symbol, $mdFromTime),
                $this->getMDClusterPriceAtTime($symbol, $toTime),
            ));
        }
        $clusters = $clusters->sort(function ($first, $second) {
           $first = $first->getMarketDelta();
            $second = $second->getMarketDelta();
            if ($first === $second) {
                return 0;
            }
            return $first > $second ? -1 : 1;
        });
        $result = collect();
        $prevMd = null;
        foreach ($clusters as $cluster) {
            $md = $cluster->getMarketDelta();
            if ($md < (float)config('crypto.strategyMinMdCluster')) {
                break;
            }
            if (!empty($prevMd) && $prevMd/$md >= (float)config('crypto.strategyMdClusterDomination')) {
                break;
            }
            $result->push($cluster);
            $prevMd = $md;
        }*/
        //return $result;
    //}

    /**
     * @param string $symbol
     * @param int $time
     * @return float
     * @throws Exception
     */
    private function getMDClusterPriceAtTime(string $symbol, int $time): float
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
