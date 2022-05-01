<?php


namespace App\Services;


use App\Dto\CreateNewOrderDto;
use App\Dto\PlaceGoalOrderDto;
use App\Dto\PlaceOrderDto;
use App\Enums\ExchangeOrderType;
use App\Enums\OrderState;
use App\Enums\OrderDirection;
use App\Models\Order;
use App\Models\OrderInterface;
use App\Services\Crypto\Exchanges\Factory as ExchangesFactory;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;

class OrdersService implements OrdersServiceInterface
{
    public function __construct()
    {
        //
    }

    /**
     * @param CreateNewOrderDto $dto
     * @return OrderInterface
     * @throws Exception
     */
    public function createNewOrder(CreateNewOrderDto $dto): OrderInterface
    {
        $order = new Order();
        $order->fill($dto->toArray());
        $order->setState(OrderState::NEW());
        $order->save();
        $this->placeNewOrderToExchange($order);
        return $order;
    }

    /**
     * @param OrderInterface|Model $order
     * @throws Exception
     */
    private function placeNewOrderToExchange(OrderInterface|Model $order)
    {
        $exchange = ExchangesFactory::create($order->getExchange(), $order->getUserId());
        $exchangeOrderId = $exchange->placeOrder(new PlaceOrderDto(
            $order->getDirection(),
            $order->getSymbol(),
            $order->getAmount(),
            $order->getPrice(),
            $order->isMarket() ? ExchangeOrderType::market() : ExchangeOrderType::limit(),
            $order->getId(),
        ));
        if ($exchangeOrderId === false) {
            throw new Exception('Cannot place order to exchange');
        }
        $order->setExchangeOrderId($exchangeOrderId);
        $order->save();
    }

    /**
     * @param OrderInterface|Model $order
     * @return string
     */
    public function placeRevertMarketOrderToExchange(OrderInterface|Model $order): string
    {
        $exchange = ExchangesFactory::create($order->getExchange(), $order->getUserId());
        return $exchange->placeOrder(new PlaceOrderDto(
            $order->getDirection()->isBUY() ? OrderDirection::SELL() : OrderDirection::BUY(),
            $order->getSymbol(),
            $order->getAmount(),
            $order->getPrice(),
            ExchangeOrderType::market(),
            'revert-'.$order->getId(),
        ));
    }

    /**
     * @param OrderInterface|Model $order
     * @param string $newState
     */
    public function changeOrderState(OrderInterface|Model $order, OrderState $newState): void
    {
        $oldState = $order->getState();
        if ($newState === $oldState) {
            Log::info('Refusing to set same order state. State: '.$newState);
            return;
        }
        $now = Date::now();
        if ($newState->isREADY()) {
            Log::info('Order '.$order->getId().' is ready to achieve the goal');
            $order->setReadyAt($now);
        }
        if (in_array($newState, [OrderState::PROFIT(), OrderState::LOSS(), OrderState::FAILED(), OrderState::COMPLETED()])) {
            Log::info('Order '.$order->getId().' is closed. State: '.$newState);
            $order->setCompletedAt($now);
        }
        $order->setState($newState);
        $order->save();
        Log::info('Order state is changed to: '.$newState.', Order id: '.$order->getId());
    }

    /**
     * @param OrderInterface $order
     * @return bool
     */
    public function placeGoalOrder(OrderInterface $order): bool
    {
        $result = false;
        $exchange = ExchangesFactory::create($order->getExchange(), $order->getUserId());
        $newOrderDirection = $order->getDirection()->isBUY() ? OrderDirection::SELL() : OrderDirection::BUY();
        if (!empty($order->getSl()) && !empty($order->getTp())) {
            if ($order->getDirection()->isSELL()) {
                $amount = $order->getPrice()*$order->getAmount()/$order->getTp();
            } else {
                $amount = $order->getAmount();
            }
            $placedOrderIds = $exchange->placeTakeProfitAndStopLossOrder(new PlaceGoalOrderDto(
                $newOrderDirection,
                $order->getSymbol(),
                $amount,
                $order->getSl(),
                $order->getTp(),
                $order->getId(),
            ));
            if ($placedOrderIds !== false) {
                Log::info('Goal order is placed to exchange. Order id: '.$order->getId().', Placed order ids: '.implode(',', $placedOrderIds));
                $result = true;
            } else {
                Log::info('Goal order is not placed');
            }
        } else if (!empty($order->getSl())) {
            $exchangeOrderId = $exchange->placeOrder(new PlaceOrderDto(
                $newOrderDirection,
                $order->getSymbol(),
                $order->getAmount(),
                $order->getSl(),
                ExchangeOrderType::stop_loss(),
                'sl-'.$order->getId(),
            ));
            if ($exchangeOrderId !== false) {
                $result = true;
            }
        } else if (!empty($order->getTp())) {
            $exchangeOrderId = $exchange->placeOrder(new PlaceOrderDto(
                $newOrderDirection,
                $order->getSymbol(),
                $order->getAmount(),
                $order->getTp(),
                ExchangeOrderType::limit(),
                'tp-'.$order->getId(),
            ));
            if ($exchangeOrderId !== false) {
                $result = true;
            }
        }
        return $result;
    }
}
