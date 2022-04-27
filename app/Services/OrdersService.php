<?php


namespace App\Services;


use App\Dto\CreateNewOrderDto;
use App\Enums\OrderState;
use App\Enums\OrderType;
use App\Models\Order;
use App\Models\OrderInterface;
use App\Repositories\OrdersRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;

class OrdersService implements OrdersServiceInterface
{
    public function __construct(private OrdersRepository $ordersRepository)
    {
        //
    }

    public function checkAndExecuteOrders(string $exchange, float $price): void
    {
        $orders = $this->ordersRepository->getAllOrders();
        foreach ($orders as $order) {
            /** @var OrderInterface $order */
            if ($order->getExchange() !== $exchange) {
                continue;
            }
            $this->checkAndExecuteOrder($exchange, $order, $price);
        }
    }

    private function checkAndExecuteOrder(string $exchange, OrderInterface $order, float $price): void
    {
        if ($order->getState() === OrderState::NEW) {
            if (!$order->isMarket()) {
                $priceDifference = $price - $order->getPrice();
                if ($order->getType() === OrderType::BUY && $priceDifference <= 0) {
                    $this->executeBuyOrder($order);
                }
                if ($order->getType() === OrderType::SELL && $priceDifference >= 0) {
                    $this->executeSellOrder($order);
                }
            } else {
                $this->executeMarketOrder($order);
            }
        }
        if ($order->getState() === OrderState::READY) {
            if ($order->getType() === OrderType::BUY) {
                if (!empty($order->getTp())) {
                    if ($price >= $order->getTp()) {
                        $this->executeBuyOrderTakeProfit($order);
                    }
                }
                if (!empty($order->getSl())) {
                    if ($price <= $order->getSl()) {
                        $this->executeBuyOrderStopLoss($order);
                    }
                }
            }
            if ($order->getType() === OrderType::SELL) {
                if (!empty($order->getTp())) {
                    if ($price <= $order->getTp()) {
                        $this->executeSellOrderTakeProfit($order);
                    }
                }
                if (!empty($order->getSl())) {
                    if ($price >= $order->getSl()) {
                        $this->executeSellOrderStopLoss($order);
                    }
                }
            }
        }
    }

    public function createNewOrder(CreateNewOrderDto $dto): OrderInterface
    {
        $order = new Order();
        $order->fill($dto->toArray());
        $order->save();
        return $order;
    }

    private function executeBuyOrder(OrderInterface $order)
    {
        Log::info("Buy order executed: ".$order->getId().'. Simple: '.($order->isSimple() ? 'Yes' : 'No').', Market: '.($order->isMarket() ? 'Yes' : 'No'));
        if (!$order->isSimple()) {
            $this->changeOrderState($order, OrderState::READY);
        } else {
            $this->changeOrderState($order, OrderState::COMPLETED);
        }
    }

    private function executeSellOrder(OrderInterface $order)
    {
        Log::info("Sell order executed: ".$order->getId().'. Simple: '.($order->isSimple() ? 'Yes' : 'No').', Market: '.($order->isMarket() ? 'Yes' : 'No'));
        if (!$order->isSimple()) {
            $this->changeOrderState($order, OrderState::READY);
        } else {
            $this->changeOrderState($order, OrderState::COMPLETED);
        }
    }

    private function executeBuyOrderTakeProfit(OrderInterface $order)
    {
        Log::info("Buy order take profit executed: ".$order->getId());
        $this->changeOrderState($order, OrderState::PROFIT);
    }

    private function executeBuyOrderStopLoss(OrderInterface $order)
    {
        Log::info("Buy order stop loss executed: ".$order->getId());
        $this->changeOrderState($order, OrderState::LOSS);
    }

    private function executeSellOrderTakeProfit(OrderInterface $order)
    {
        Log::info("Sell order take profit executed: ".$order->getId());
        $this->changeOrderState($order, OrderState::PROFIT);
    }

    private function executeSellOrderStopLoss(OrderInterface $order)
    {
        Log::info("Sell order stop loss executed: ".$order->getId());
        $this->changeOrderState($order, OrderState::LOSS);
    }

    private function changeOrderState(OrderInterface|Model $order, string $newState)
    {
        $now = Date::now();
        Log::debug('Order state changed to: '.$newState);
        $order->setState($newState);
        if ($newState === OrderState::READY) {
            Log::debug('Order '.$order->id.' is ready at '.$now->format(config('crypto.dateFormat')));
            $order->setReadyAt($now);
        }
        if (in_array($newState, [OrderState::PROFIT, OrderState::LOSS, OrderState::FAILED, OrderState::COMPLETED])) {
            Log::debug('Order '.$order->id.' is completed at '.$now->format(config('crypto.dateFormat')));
            $order->setCompletedAt($now);
        }
        $order->save();
    }

    private function executeMarketOrder(OrderInterface $order)
    {
        if ($order->getType() === OrderType::BUY) {
            $this->executeBuyOrder($order);
        }
        if ($order->getType() === OrderType::SELL) {
            $this->executeSellOrder($order);
        }
    }
}
