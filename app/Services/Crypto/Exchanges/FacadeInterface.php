<?php
namespace App\Services\Crypto\Exchanges;

use App\Dto\CancelOrderDto;
use App\Dto\PlaceGoalOrderDto;
use App\Dto\PlaceOrderDto;
use App\Enums\TimeInterval;
use Illuminate\Support\Collection;

/**
 * Interface FacadeInterface
 * @package App\Services\Crypto\Exchanges
 */
interface FacadeInterface
{
    public function __construct(?int $userId = null);

    /**
     * @param string $exchangeSymbol
     * @param int $fromTime
     * @param int|null $toTime
     * @return Collection
     */
    public function getTrades(string $exchangeSymbol, int $fromTime, int $toTime = null): Collection;

    /**
     * @param string $exchangeSymbol
     * @param int $fromTime
     * @param int|null $toTime
     * @param TimeInterval|null $interval
     * @return Collection
     */
    public function getCandlesticks(string $exchangeSymbol, int $fromTime, int $toTime = null, ?TimeInterval $interval = null): Collection;

    /**
     * @param PlaceOrderDto $dto
     * @return false|int|null
     */
    public function placeOrder(PlaceOrderDto $dto): null|false|int;

    /**
     * @param PlaceGoalOrderDto $dto
     */
    public function placeTakeProfitAndStopLossOrder(PlaceGoalOrderDto $dto): array|false;

    /**
     * @param CancelOrderDto $dto
     * @return bool
     */
    public function cancelOrder(CancelOrderDto $dto): bool;

    /**
     * @param string $exchangeSymbol
     * @return float
     */
    public function getCurrentPrice(string $exchangeSymbol): float;

    /**
     * @param string $symbol
     * @return array
     */
    public function getExchangeSymbols(string $symbol): array;

    /**
     * @param string $symbol
     * @return string|null
     */
    public function getExchangeSymbol(string $symbol): ?string;

    /**
     * @param string $symbol
     * @return string|null
     */
    public function getExchangeOrderSymbol(string $symbol): ?string;
}
