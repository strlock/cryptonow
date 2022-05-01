<?php
namespace App\Services\Crypto\Exchanges;

use App\Dto\CreateNewOrderDto;
use App\Dto\PlaceGoalOrderDto;
use App\Dto\PlaceOrderDto;
use App\Models\OrderInterface;
use App\Services\Crypto\Helpers\TimeHelper;
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
     * @return array
     */
    public function getTrades(string $symbol, int $fromTime, int $toTime = null): Collection;

    /**
     * @param string $symbol
     * @param int $fromTime
     * @param int|null $toTime
     * @return Collection
     */
    public function getCandlesticks(string $symbol, int $fromTime, int $toTime = null, int $interval = TimeHelper::FIVE_MINUTE_MS): Collection;

    /**
     * @param PlaceOrderDto $dto
     * @return false|int
     */
    public function placeOrder(PlaceOrderDto $dto): false|int;

    /**
     * @param PlaceGoalOrderDto $dto
     */
    public function placeTakeProfitAndStopLossOrder(PlaceGoalOrderDto $dto): array|false;
}
