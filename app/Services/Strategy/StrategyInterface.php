<?php


namespace App\Services\Strategy;


use App\Enums\StrategySignal;
use App\Enums\TimeInterval;
use Illuminate\Support\Collection;

interface StrategyInterface
{
    /**
     * @param string $symbol
     * @param TimeInterval|null $interval
     * @return StrategySignal
     */
    public function getSignal(string $symbol, ?TimeInterval $interval = null): StrategySignal;

    /**
     * @param string $symbol
     * @param TimeInterval $interval
     * @return Collection
     */
    public function getMarketDeltaClusters(string $symbol, TimeInterval $interval): Collection;
}
