<?php

namespace App\Http\Controllers;

use App\Dto\CreateNewOrderDto;
use App\Enums\OrderDirection;
use App\Enums\OrderState;
use App\Http\Resources\OrdersResource;
use App\Models\Order;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Repositories\OrdersRepository;
use App\Services\OrdersService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class OrdersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(OrdersRepository $ordersRepository, Request $request)
    {
        try {
            $user = Auth::user();
            $history = (bool)$request->get('history', 0);
            if ($history) {
                $states = [
                    OrderState::CANCELED(),
                    OrderState::FAILED(),
                    OrderState::COMPLETED(),
                    OrderState::PROFIT(),
                    OrderState::LOSS(),
                ];
            } else {
                $states = [
                    OrderState::NEW(),
                    OrderState::READY(),
                ];
            }
            $orders = $ordersRepository->getUserOrders($user, $states);
            return OrdersResource::collection($orders);
        } catch (Throwable $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreOrderRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreOrderRequest $request, OrdersService $ordersService)
    {
        try {
            $user = Auth::user();
            $data = $request->post();
            $order = $ordersService->createNewOrder(new CreateNewOrderDto(
                (int)$user->id,
                OrderDirection::memberByValue($data['direction']),
                (float)$data['price'],
                (float)$data['amount'],
                $data['sl'] ? (float)$data['sl'] : null,
                $data['tp'] ? (float)$data['tp'] : null,
                $data['market'],
                $data['exchange'],
                $data['symbol'],
            ));
            return response()->json(['success' => true, 'id' => $order->id]);
        } catch (Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function show(Order $order)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function edit(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateOrderRequest  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateOrderRequest $request, Order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $order, OrdersService $ordersService)
    {
        $ordersService->cancelOrder($order);
        return response()->json(['success' => true]);
    }

}
