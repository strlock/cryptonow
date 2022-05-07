<?php


namespace App\Services;


use App\Enums\TimeInterval;
use Illuminate\Support\Collection;

interface GetAggregateMarketStatServiceInterface
{
    public function getAggregateMarketDelta(string $symbol, int $fromTime, int $toTime = null, ?TimeInterval $interval = null): Collection;
}
