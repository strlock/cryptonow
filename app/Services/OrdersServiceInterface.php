<?php


namespace App\Services;


use App\Dto\CreateNewOrderDto;
use App\Dto\CreateAutomaticOrdersDto;
use App\Enums\OrderDirection;
use App\Enums\OrderState;
use App\Models\OrderInterface;
use App\Models\UserInterface;
use Illuminate\Database\Eloquent\Model;

interface OrdersServiceInterface
{
    public function createNewOrder(CreateNewOrderDto $dto): OrderInterface;
    public function changeOrderState(OrderInterface|Model $order, OrderState $newState);
    public function placeGoalOrder(OrderInterface $order): bool;
    public function placeRevertMarketOrderToExchange(OrderInterface|Model $order): string;
    public function cancelOrder(OrderInterface|Model $order): void;
    public function createUsersAutomaticOrders(CreateAutomaticOrdersDto $dto): void;
}
