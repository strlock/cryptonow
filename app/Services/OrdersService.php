<?php


namespace App\Services;


use App\Dto\CreateNewOrderDto;
use App\Enums\OrderDirection;
use App\Enums\OrderState;
use App\Enums\OrderType;
use App\Models\Order;
use App\Models\OrderInterface;
use App\Repositories\OrdersRepository;
use App\Services\Crypto\Exchanges\Factory as ExchangesFactory;
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
            $this->checkAndExecuteOrder($order, $price);
        }
    }

    private function checkAndExecuteOrder(OrderInterface $order, float $currentPrice = null): void
    {
        /*if ($order->getState() === OrderState::NEW) {
            $newState = $order->isSimple() ? OrderState::COMPLETED : OrderState::READY;
            if ($order->isMarket()) {
                $this->executeOrderByMarket($order, $order->getType(), $newState);
                return;
            }
            $priceDifference = $currentPrice - $order->getPrice();
            if (($order->getType() === OrderType::BUY && $priceDifference <= 0) || ($order->getType() === OrderType::SELL && $priceDifference >= 0)) {
                $this->executeOrderByMarket($order, $order->getType(), $newState);
            }
        }
        if ($order->getState() === OrderState::READY) {
            if ($order->getType() === OrderType::BUY) {
                if (!empty($order->getTp()) && $currentPrice >= $order->getTp()) {
                    $this->executeOrderByMarket($order, OrderType::SELL, OrderState::PROFIT);
                }
                if (!empty($order->getSl()) && $currentPrice <= $order->getSl()) {
                    $this->executeOrderByMarket($order, OrderType::SELL, OrderState::LOSS);
                }
            }
            if ($order->getType() === OrderType::SELL) {
                if (!empty($order->getTp()) && $currentPrice <= $order->getTp()) {
                    $this->executeOrderByMarket($order, OrderType::BUY, OrderState::PROFIT);
                }
                if (!empty($order->getSl()) && $currentPrice >= $order->getSl()) {
                    $this->executeOrderByMarket($order, OrderType::BUY, OrderState::LOSS);
                }
            }
        }*/
    }

    public function createNewOrder(CreateNewOrderDto $dto): OrderInterface
    {
        $order = new Order();
        $order->fill($dto->toArray());
        $order->setState(OrderState::NEW);
        $order->save();
        $exchange = ExchangesFactory::create($dto->getExchange());
        $exchange->placeOrder($order);
        $this->checkAndExecuteOrder($order);
        return $order;
    }

    private function executeOrderByMarket(OrderInterface $order, string $direction, string $stateAfterExecute)
    {
        Log::info(ucfirst($direction)." order executed: ".$order->getId().'. Simple: '.($order->isSimple() ? 'Yes' : 'No').', Market: '.($order->isMarket() ? 'Yes' : 'No'));
        $this->changeOrderState($order, $stateAfterExecute);
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
}
