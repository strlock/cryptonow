<?php


namespace App\Services\Strategy;


use App\Services\Crypto\Helpers\TimeHelper;
use Illuminate\Support\Collection;

interface StrategyInterface
{
    /**
     * @param string $symbol
     * @param int $fromTime
     * @param int|null $toTime
     * @param int $interval
     * @return Collection
     */
    public function getSignals(string $symbol, int $fromTime, int $toTime = null, int $interval = TimeHelper::FIVE_MINUTE_MS): Collection;
}
