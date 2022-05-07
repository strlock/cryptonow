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
     * @param string $symbol
     * @param int $fromTime
     * @param int|null $toTime
     * @return Collection
     */
    public function getTrades(string $symbol, int $fromTime, int $toTime = null): Collection;

    /**
     * @param string $symbol
     * @param int $fromTime
     * @param int|null $toTime
     * @param TimeInterval|null $interval
     * @return Collection
     */
    public function getCandlesticks(string $symbol, int $fromTime, int $toTime = null, ?TimeInterval $interval = null): Collection;

    /**
     * @param PlaceOrderDto $dto
     * @return false|int
     */
    public function placeOrder(PlaceOrderDto $dto): false|int;

    /**
     * @param PlaceGoalOrderDto $dto
     */
    public function placeTakeProfitAndStopLossOrder(PlaceGoalOrderDto $dto): array|false;

    /**
     * @param CancelOrderDto $dto
     * @return bool
     */
    public function cancelOrder(CancelOrderDto $dto): bool;
}
