<?php


namespace App\Services\Strategy;


use App\Enums\StrategySignal;
use App\Enums\TimeInterval;

interface StrategyInterface
{
    /**
     * @param string $symbol
     * @param TimeInterval|null $interval
     * @return StrategySignal
     */
    public function getSignal(string $symbol, ?TimeInterval $interval = null): StrategySignal;
}
