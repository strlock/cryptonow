<?php


namespace App\Services\Strategy;


use App\Dto\MaxMarketDeltaDto;
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
     * @return MaxMarketDeltaDto|null
     */
    public function getMaxMarketDelta(string $symbol): ?MaxMarketDeltaDto;
}
