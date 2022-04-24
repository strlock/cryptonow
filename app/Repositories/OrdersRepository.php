<?php


namespace App\Repositories;


use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Collection;

class OrdersRepository
{
    public function __construct()
    {

    }

    public function getUserOrders(User $user): Collection
    {
        return $this->prepareOrders(Order::where('user_id', '=', $user->id)->get());
    }

    public function getAllOrders(): Collection
    {
        return $this->prepareOrders(Order::get());
    }

    private function prepareOrders(Collection $orders)
    {
        return $orders;
    }
}
