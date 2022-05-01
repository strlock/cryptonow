<?php


namespace App\Services;


use App\Dto\CreateNewOrderDto;
use App\Models\OrderInterface;
use Illuminate\Database\Eloquent\Model;

interface OrdersServiceInterface
{
    public function createNewOrder(CreateNewOrderDto $dto): OrderInterface;
    public function changeOrderState(OrderInterface|Model $order, string $newState);
    public function placeGoalOrder(OrderInterface $order): bool;
    public function placeRevertMarketOrderToExchange(OrderInterface|Model $order): string;
}
