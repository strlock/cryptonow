<?php


namespace App\Services;


use App\Dto\CreateNewOrderDto;
use App\Models\OrderInterface;

interface OrdersServiceInterface
{
    public function checkAndExecuteOrders(string $exchange, float $price): void;
    public function createNewOrder(CreateNewOrderDto $dto): OrderInterface;
}
