<?php

namespace App\Http\Controllers;

use App\Dto\CreateNewOrderDto;
use App\Http\Resources\OrdersResource;
use App\Models\Order;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Repositories\OrdersRepository;
use App\Services\OrdersService;
use Illuminate\Support\Facades\Auth;
use Throwable;

class OrdersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(OrdersRepository $ordersRepository)
    {
        try {
            $user = Auth::user();
            $orders = $ordersRepository->getUserOrders($user);
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
                $data['type'],
                (float)$data['price'],
                (float)$data['amount'],
                (float)$data['sl'],
                (float)$data['tp'],
                $data['market'],
                $data['exchange'],
            ));
            return response()->json(['success' => true, 'id' => $order->id]);
        } catch (Throwable $e) {
            return response()->json(['error' => $e->getMessage()]);
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
    public function destroy(Order $order)
    {
        //
    }

}
