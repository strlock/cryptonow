<?php


namespace App\Services\Strategy;


use App\Dto\MarketDeltaClusterDto;
use App\Enums\StrategySignal;
use App\Enums\TimeInterval;
use App\Helpers\TimeHelper;
use App\Services\GetAggregateMarketStatService;
use Exception;
use Illuminate\Support\Collection;

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
        $toTime = TimeHelper::round(TimeHelper::time(), $interval);
        $mdClusters = $this->getMarketDeltaClusters($symbol, $toTime, $interval);
        $lastMdCluster = $mdClusters->last();
        echo date('d.m.Y H:i:s', $lastMdCluster->getFromTime()/1000).'  '.date('d.m.Y H:i:s', $lastMdCluster->getToTime()/1000).'  '.$lastMdCluster->getMarketDelta().PHP_EOL;
        foreach ($mdClusters as $mdCluster) {
            /** @var MarketDeltaClusterDto $mdCluster */
            if ($toTime >= $mdCluster->getFromTime() && $toTime <= $mdCluster->getToTime()) {
                return StrategySignal::BUY();
            }
        }
        return StrategySignal::NOTHING();
    }

    /**
     * @param string $symbol
     * @param int $toTime
     * @param TimeInterval $interval
     * @return Collection
     * @throws Exception
     */
    public function getMarketDeltaClusters(string $symbol, int $toTime, TimeInterval $interval): Collection
    {
        $toTime = TimeHelper::round($toTime, $interval);
        $fromTime = TimeHelper::round($toTime-(int)config('crypto.strategyPeriod'), $interval);
        $marketDeltaByTime = $this->aggregateMarketStatService->getAggregateMarketDelta($symbol, $fromTime, $toTime, $interval);
        $prevPositive = false;
        $md = 0.0;
        $mdFromTime = 0;
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
                    $clusters->push(new MarketDeltaClusterDto($md, $mdFromTime, $mdToTime));
                }
            }
            $prevPositive = $positive;
        }
        if ($prevPositive) {
            $clusters->push(new MarketDeltaClusterDto($md, $mdFromTime, $toTime));
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
        return $result->slice(0, 5);
    }
}
