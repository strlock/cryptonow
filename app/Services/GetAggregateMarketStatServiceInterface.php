<?php


namespace App\Services;


use App\Crypto\Helpers\TimeHelper;
use Illuminate\Support\Collection;

interface GetAggregateMarketStatServiceInterface
{
    public function getAggregateMarketDelta(string $symbol, int $fromTime, int $toTime = null, int $interval = TimeHelper::FIVE_MINUTE_MS): Collection;
}
