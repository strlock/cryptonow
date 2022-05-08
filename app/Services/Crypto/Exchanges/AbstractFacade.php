<?php
namespace App\Services\Crypto\Exchanges;

use App\Dto\CancelOrderDto;
use App\Dto\PlaceGoalOrderDto;
use App\Dto\PlaceOrderDto;
use App\Dto\TimeIntervalChunkDto;
use App\Enums\TimeInterval;
use App\Helpers\TimeHelper;
use Exception;
use Illuminate\Support\Collection;
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
     * @throws Exception
     */
    final public function getMinuteMarketDelta(string $symbol, int $fromTime): float
    {
        $fromTime = TimeHelper::round($fromTime, TimeInterval::MINUTE());
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

    /**
     * @param string $symbol
     * @param int $fromTime
     * @param int|null $toTime
     * @param TimeInterval|null $interval
     * @return Collection
     */
    public function getCandlesticks(string $symbol, int $fromTime, int $toTime = null, ?TimeInterval $interval = null): Collection
    {
        return collect();
    }

    /**
     * @param PlaceOrderDto $dto
     * @return false|int
     */
    public function placeOrder(PlaceOrderDto $dto): false|int
    {
        return false;
    }

    /**
     * @param PlaceGoalOrderDto $dto
     * @return array|false
     */
    public function placeTakeProfitAndStopLossOrder(PlaceGoalOrderDto $dto): array|false
    {
        return false;
    }

    /**
     * @param CancelOrderDto $dto
     * @return bool
     */
    public function cancelOrder(CancelOrderDto $dto): bool
    {
        return false;
    }

    /**
     * @param string $symbol
     * @return float
     * @throws Exception
     */
    public function getCurrentPrice(string $symbol): float
    {
        throw new Exception('getCurrentPrice is not implemented for this exchange');
        return 0.0;
    }
}
