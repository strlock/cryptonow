<?php


namespace App\Repositories;


use App\Models\Order;
use App\Models\OrderInterface;
use App\Models\User;
use Illuminate\Support\Collection;

class OrdersRepository
{
    public function __construct()
    {

    }

    public function getUserOrders(User $user): Collection
    {
        return Order::where('user_id', '=', $user->id)->get();
    }

    public function getAllOrders(): Collection
    {
        return Order::get();
    }

    public function getOrderByExchangeOrderId($exchangeOrderId, $fieldName = 'exchange_order_id'): OrderInterface
    {
        return Order::where($fieldName, '=', $exchangeOrderId)->first();
    }

    public function getOrder($orderId)
    {
        return Order::find($orderId);
    }
}
