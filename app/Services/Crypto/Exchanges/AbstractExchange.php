<?php
namespace App\Services\Crypto\Exchanges;

use App\Dto\CancelOrderDto;
use App\Dto\PlaceGoalOrderDto;
use App\Dto\PlaceOrderDto;
use App\Dto\TimeIntervalChunkDto;
use App\Enums\TimeInterval;
use App\Helpers\TimeHelper;
use App\Repositories\MarketDeltaRepository;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;

abstract class AbstractExchange implements ExchangeInterface
{
    protected const MAX_PAGES = 100;
    protected array $symbolMap = [];
    protected object $api;
    protected int $orderSymbolIndex = 0;

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
     * @param string $exchangeSymbol
     * @param int $fromTime
     * @param int|null $toTime
     * @return Collection
     * @throws Exception
     */
    public function getTrades(string $exchangeSymbol, int $fromTime, int $toTime = null): Collection
    {
        throw new Exception('getTrades is not implemented for this exchange');
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
     * @param string $exchangeSymbol
     * @param int $fromTime
     * @param int|null $toTime
     * @param TimeInterval|null $interval
     * @return Collection
     */
    public function getCandlesticks(string $exchangeSymbol, int $fromTime, int $toTime = null, ?TimeInterval $interval = null): Collection
    {
        throw new Exception('getCandlesticks is not implemented for this exchange');
        return collect();
    }

    /**
     * @param PlaceOrderDto $dto
     * @return false|int|null
     * @throws Exception
     */
    public function placeOrder(PlaceOrderDto $dto): null|false|int
    {
        throw new Exception('placeOrder is not implemented for this exchange');
        return false;
    }

    /**
     * @param PlaceGoalOrderDto $dto
     * @return array|false
     * @throws Exception
     */
    public function placeTakeProfitAndStopLossOrder(PlaceGoalOrderDto $dto): array|false
    {
        throw new Exception('placeTakeProfitAndStopLossOrder is not implemented for this exchange');
        return false;
    }

    /**
     * @param CancelOrderDto $dto
     * @return bool
     * @throws Exception
     */
    public function cancelOrder(CancelOrderDto $dto): bool
    {
        throw new Exception('cancelOrder is not implemented for this exchange');
        return false;
    }

    /**
     * @param string $exchangeSymbol
     * @return float
     * @throws Exception
     */
    public function getCurrentPrice(string $exchangeSymbol): float
    {
        throw new Exception('getCurrentPrice is not implemented for this exchange');
        return 0.0;
    }

    /**
     * @param string $symbol
     * @return array
     */
    public function getExchangeSymbols(string $symbol): array
    {
        return $this->symbolMap[$symbol] ?? [];
    }

    /**
     * @param string $symbol
     * @param int $index
     * @return string|null
     */
    public function getExchangeSymbol(string $symbol, $index = 0): ?string
    {
        $symbols = $this->getExchangeSymbols($symbol);
        return count($symbols) > 0 ? $symbols[$index] : null;
    }

    /**
     * @param string $symbol
     * @return string|null
     */
    public function getExchangeOrderSymbol(string $symbol): ?string
    {
        return $this->getExchangeSymbol($symbol, $this->orderSymbolIndex);
    }
}
