<?php


namespace App\Services\Strategy;


use App\Dto\MarketDeltaClusterDto;
use App\Enums\StrategySignal;

interface StrategyInterface
{
    /**
     * @param string $symbol
     * @return StrategySignal
     */
    public function getSignal(string $symbol): StrategySignal;

    /**
     * @param string $symbol
     * @return MarketDeltaClusterDto|null
     */
    public function getMaxMarketDeltaCluster(string $symbol): ?MarketDeltaClusterDto;
}
