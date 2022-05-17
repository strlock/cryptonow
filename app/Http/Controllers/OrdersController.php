<?php

namespace App\Http\Controllers;

use App\Dto\CreateNewOrderDto;
use App\Enums\OrderDirection;
use App\Enums\OrderState;
use App\Exceptions\CannotPlaceExchangeOrderException;
use App\Http\Resources\OrdersResource;
use App\Models\Order;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Repositories\OrdersRepository;
use App\Services\OrdersService;
use App\Services\OrdersServiceInterface;
use App\Services\TelegramService;
use App\Services\TelegramServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class OrdersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \App\Repositories\OrdersRepository $ordersRepository
     * @param \Illuminate\Http\Request $request
     * @param \App\Services\OrdersServiceInterface $ordersService
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(OrdersRepository $ordersRepository, Request $request, OrdersServiceInterface $ordersService): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
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
                $orders = $ordersRepository->getUserOrders($user, $states);
            } else {
                $states = [
                    OrderState::NEW(),
                    OrderState::READY(),
                ];
                $orders = $ordersRepository->getUserOrders($user, $states);
                $ordersService->setOrdersDiffPercent($orders);
            }
            return OrdersResource::collection($orders);
        } catch (Throwable $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return void
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\StoreOrderRequest $request
     * @param \App\Services\OrdersService $ordersService
     * @param \App\Services\TelegramServiceInterface $telegramService
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreOrderRequest $request, OrdersService $ordersService, TelegramServiceInterface $telegramService): \Illuminate\Http\JsonResponse
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
            $telegramService->sendMessage('Order '.$order->getId().' is created');
            return response()->json(['success' => true, 'id' => $order->id]);
        } catch (CannotPlaceExchangeOrderException $e) {
            $ordersService->cancelOrder($e->getOrder());
            $telegramService->sendMessage($e->getMessage().'. Order '.$e->getOrder()->getId().' is canceled');
            return response()->json(['error' => $e->getMessage()], 500);
        } catch (Throwable $e) {
            $telegramService->sendMessage($e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Order  $order
     * @return void
     */
    public function show(Order $order)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Order  $order
     * @return void
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
     * @return void
     */
    public function update(UpdateOrderRequest $request, Order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Order $order
     * @param \App\Services\OrdersService $ordersService
     * @param \App\Services\TelegramServiceInterface $telegramService
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Order $order, OrdersService $ordersService, TelegramServiceInterface $telegramService): \Illuminate\Http\JsonResponse
    {
        try {
            $ordersService->cancelOrder($order);
            $telegramService->sendMessage('Order '.$order->getId().' is canceled');
            return response()->json(['success' => true]);
        } catch (Throwable $e) {
            $telegramService->sendMessage($e->getMessage());
            return response()->json(['success' => false, 'message' => 'Cannot cancel order']);
        }
    }
}
