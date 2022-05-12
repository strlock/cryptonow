<?php


namespace App\Repositories;


use App\Enums\OrderState;
use App\Models\Order;
use App\Models\OrderInterface;
use App\Models\UserInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class OrdersRepository
{
    public function __construct()
    {

    }

    public function getUserOrders(UserInterface $user, $states = [], $limit = 10): LengthAwarePaginator
    {
        /** @var Builder $query */
        $query = Order::where('user_id', '=', $user->getId())->orderBy('created_at', 'DESC');
        if (count($states) > 0) {
            $query->whereIn('state', array_map(function (OrderState $state) {
                return $state->value();
            }, $states));
        }
        return $query->paginate($limit);
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
