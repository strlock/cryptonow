<?php


namespace App\Services;


use App\Dto\CreateNewOrderDto;
use App\Dto\CreateAutomaticOrdersDto;
use App\Enums\OrderState;
use App\Models\OrderInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface OrdersServiceInterface
{
    public function createNewOrder(CreateNewOrderDto $dto): OrderInterface;
    public function changeOrderState(OrderInterface|Model $order, OrderState $newState, ?float $price = null): void;
    public function placeGoalOrder(OrderInterface $order): bool;
    public function placeRevertMarketOrderToExchange(OrderInterface|Model $order): string;
    public function cancelOrder(OrderInterface|Model $order): void;
    public function createUsersAutomaticOrders(CreateAutomaticOrdersDto $dto): void;
    public function setOrdersDiffPercent(Collection|LengthAwarePaginator $orders): void;
}
