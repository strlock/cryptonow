<?php


namespace App\Services\Strategy;


use App\Enums\StrategySignal;
use App\Enums\TimeInterval;
use Illuminate\Support\Collection;

interface StrategyInterface
{
    /**
     * @param string $symbol
     * @return StrategySignal
     */
    public function getSignal(string $symbol): StrategySignal;

    /**
     * @param string $symbol
     * @return Collection
     */
    public function getMarketDeltaClusters(string $symbol): Collection;
}
