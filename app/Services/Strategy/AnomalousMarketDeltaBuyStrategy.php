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

class AnomalousMarketDeltaBuyStrategy implements StrategyInterface
{
    public function __construct(private GetAggregateMarketStatService $aggregateMarketStatService)
    {
        //
    }

    /**
     * @param string $symbol
     * @param TimeInterval|null $interval
     * @return StrategySignal
     * @throws Exception
     */
    public function getSignal(string $symbol, ?TimeInterval $interval = null): StrategySignal
    {
        if (empty($interval)) {
            $interval = TimeInterval::FIVE_MINUTES();
        }
        /** @var MarketDeltaClusterDto $maxMdCluster */
        $maxMdCluster = $this->getMarketDeltaClusters($symbol, $interval)->first();
        $relativePriceDiffPercent = 100*($maxMdCluster->getToPrice()-$maxMdCluster->getFromPrice())/$maxMdCluster->getFromPrice();
        echo date('d.m.Y H:i:s', $maxMdCluster->getFromTime()/1000).'  '.
            date('d.m.Y H:i:s', $maxMdCluster->getToTime()/1000).'  '.
            $maxMdCluster->getMarketDelta().' '.
            $maxMdCluster->getFromPrice().'-'.
            $maxMdCluster->getToPrice().' '.
            round($relativePriceDiffPercent, 2).'%'.PHP_EOL;
        $toTime = TimeHelper::round(TimeHelper::time(), $interval);
        if ($toTime >= $maxMdCluster->getFromTime() &&
                $toTime <= $maxMdCluster->getToTime() &&
                    abs($relativePriceDiffPercent) < (float)config('crypto.strategyRelativePriceDiffPercent')) {
            return StrategySignal::BUY();
        }
        return StrategySignal::NOTHING();
    }

    /**
     * @param string $symbol
     * @param TimeInterval $interval
     * @return Collection
     * @throws Exception
     */
    public function getMarketDeltaClusters(string $symbol, TimeInterval $interval): Collection
    {
        $toTime = TimeHelper::round(TimeHelper::time(), $interval);
        $fromTime = TimeHelper::round($toTime-(int)config('crypto.strategyPeriod'), $interval);
        $marketDeltaByTime = $this->aggregateMarketStatService->getAggregateMarketDelta($symbol, $fromTime, $toTime, $interval);
        $prevPositive = false;
        $md = 0.0;
        $mdFromTime = 0;
        $fromPrice = 0.0;
        $toPrice = 0.0;
        $clusters = collect();
        for ($time=$fromTime; $time<$toTime; $time += $interval->value()) {
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
                        $this->getMDClusterPriceAtTime($symbol, $mdFromTime, $interval),
                        $this->getMDClusterPriceAtTime($symbol, $mdToTime, $interval),
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
                $this->getMDClusterPriceAtTime($symbol, $mdFromTime, $interval),
                $this->getMDClusterPriceAtTime($symbol, $toTime, $interval),
            ));
        }
        $clusters = $clusters->sort(function ($first, $second) {
            /** @var MarketDeltaClusterDto $first */
            $first = $first->getMarketDelta();
            /** @var MarketDeltaClusterDto $second */
            $second = $second->getMarketDelta();
            if ($first === $second) {
                return 0;
            }
            return $first > $second ? -1 : 1;
        });
        $result = collect();
        $prevMd = null;
        foreach ($clusters as $cluster) {
            /** @var MarketDeltaClusterDto $cluster */
            $md = $cluster->getMarketDelta();
            if ($md < (float)config('crypto.strategyMinMdCluster')) {
                break;
            }
            if (!empty($prevMd) && $prevMd/$md >= (float)config('crypto.strategyMdClusterDomination')) {
                break;
            }
            $result->push($cluster);
            $prevMd = $md;
        }
        return $result;
    }

    /**
     * @param string $symbol
     * @param int $time
     * @param TimeInterval $interval
     * @return float
     * @throws Exception
     */
    private function getMDClusterPriceAtTime(string $symbol, int $time, TimeInterval $interval): float
    {
        $toTime = TimeHelper::round(TimeHelper::time(), $interval);
        $fromTime = TimeHelper::round($toTime-(int)config('crypto.strategyPeriod'), $interval);
        $cacheKey = md5(implode('.', [$symbol, $toTime, $interval->value()]));
        if (!Cache::has($cacheKey)) {
            $exchange = ExchangesFactory::create();
            Cache::put($cacheKey, $exchange->getCandlesticks($symbol, $fromTime, $toTime, $interval));
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
