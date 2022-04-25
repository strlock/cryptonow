<?php
namespace App\Crypto\Exchanges;

use App\Dto\TimeIntervalChunkDto;
use App\Crypto\Helpers\TimeHelper;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

abstract class AbstractFacade implements FacadeInterface
{
    protected const MAX_PAGES = 100;

    protected object $api;

    /**
     * @param string $symbol
     * @param int $fromTime
     */
    abstract protected function dispatchMinuteMarketStatFetchJob(string $symbol, int $fromTime): void;

    /**
     * @param string $symbol
     * @param int $fromTime
     * @return float
     */
    final public function getMinuteMarketDelta(string $symbol, int $fromTime): float
    {
        $fromTime = TimeHelper::roundTimestampMs($fromTime);
        $result = $this->getMinuteMarketDeltaFromDatabase($symbol, $fromTime);
        if ($result !== false) {
            return $result;
        }
        $this->dispatchMinuteMarketStatFetchJob($symbol, $fromTime);
        return 0.0;
    }

    /**
     * @param string $symbol
     * @param int $fromTime
     * @return float|false
     */
    final public function getMinuteMarketDeltaFromDatabase (string $symbol, int $fromTime): float|false {
        $mdQueueName = strtolower($this->getExchangeName()).':md:'.$symbol;
        $value = Redis::zRangeByScore($mdQueueName, $fromTime, $fromTime);
        if (!empty($value)) {
            list(,$value) = explode(':', $value[0]);
            return $value;
        }
        return false;
    }

    /**
     * @param TradeInterface $first
     * @param TradeInterface $second
     * @return int
     */
    public function sortTradesByTime(TradeInterface $first,  TradeInterface $second): int
    {
        return $first->getTime() <=> $second->getTime();
    }

    /**
     * @param string $symbol
     * @param int $fromTime
     * @param int|null $toTime
     * @return Collection
     */
    public function getTrades(string $symbol, int $fromTime, int $toTime = null): Collection
    {
        //
    }

    /**
     * @param int $fromTime
     * @param int $toTime
     * @param int $chunkInterval
     * @return Collection
     */
    protected function chunkTimeInterval(int $fromTime, int $toTime, int $chunkInterval): Collection
    {
        $result = collect();
        while ($fromTime < $toTime) {
            $chunkEndTime = $fromTime + $chunkInterval;
            if ($chunkEndTime > $toTime) {
                $chunkEndTime = $toTime;
            }
            $result->push(new TimeIntervalChunkDto($fromTime, $chunkEndTime));
            $fromTime += $chunkInterval;
        }
        return $result;
    }

    /**
     * @return string
     */
    public function getExchangeName(): string
    {
        $components = explode('\\', get_class($this));
        return strtolower($components[count($components)-2]);
    }
}
