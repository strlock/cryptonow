<?php


namespace App\Services;


use App\Dto\CancelOrderDto;
use App\Dto\CreateNewOrderDto;
use App\Dto\CreateAutomaticOrdersDto;
use App\Dto\PlaceGoalOrderDto;
use App\Dto\PlaceOrderDto;
use App\Enums\ExchangeOrderType;
use App\Enums\OrderState;
use App\Enums\OrderDirection;
use App\Models\Order;
use App\Models\OrderInterface;
use App\Models\UserInterface;
use App\Notifications\TelegramNotification;
use App\Repositories\OrdersRepository;
use App\Repositories\UsersRepository;
use App\Services\Crypto\Exchanges\AbstractFacade;
use App\Services\Crypto\Exchanges\Factory as ExchangesFactory;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class OrdersService implements OrdersServiceInterface
{
    public function __construct(
        private OrdersRepository $ordersRepository,
        private UsersRepository $usersRepository,
    )
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
        $exchangeSymbol = $exchange->getExchangeOrderSymbol($order->getSymbol());
        $exchangeOrderId = $exchange->placeOrder(new PlaceOrderDto(
            $order->getDirection(),
            $exchangeSymbol,
            $order->getAmount(),
            round($order->getPrice(), 2),
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
        $exchangeSymbol = $exchange->getExchangeOrderSymbol($order->getSymbol());
        return $exchange->placeOrder(new PlaceOrderDto(
            $order->getDirection()->isBUY() ? OrderDirection::SELL() : OrderDirection::BUY(),
            $exchangeSymbol,
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
     * @param OrderInterface|Model $order
     * @return bool
     */
    public function placeGoalOrder(OrderInterface|Model $order): bool
    {
        $result = false;
        $exchange = ExchangesFactory::create($order->getExchange(), $order->getUserId());
        $exchangeSymbol = $exchange->getExchangeOrderSymbol($order->getSymbol());
        $newOrderDirection = $order->getDirection()->isBUY() ? OrderDirection::SELL() : OrderDirection::BUY();
        if (!empty($order->getSl()) && !empty($order->getTp())) {
            if ($order->getDirection()->isSELL()) {
                $amount = $order->getPrice()*$order->getAmount()/$order->getTp();
            } else {
                $amount = $order->getAmount();
            }
            $exchangeOrderIds = $exchange->placeTakeProfitAndStopLossOrder(new PlaceGoalOrderDto(
                $newOrderDirection,
                $exchangeSymbol,
                $amount,
                $order->getSl(),
                $order->getTp(),
                $order->getId(),
            ));
            if ($exchangeOrderIds !== false) {
                $order->setExchangeSlOrderId($exchangeOrderIds[1]);
                $order->setExchangeTpOrderId($exchangeOrderIds[0]);
                $order->save();
                Log::info('Goal order is placed to exchange. Order id: '.$order->getId().', Placed order ids: '.implode(',', $exchangeOrderIds));
                $result = true;
            } else {
                Log::info('Goal order is not placed');
            }
        } else if (!empty($order->getSl())) {
            $exchangeOrderId = $exchange->placeOrder(new PlaceOrderDto(
                $newOrderDirection,
                $exchangeSymbol,
                $order->getAmount(),
                $order->getSl(),
                ExchangeOrderType::stop_loss(),
                'sl-'.$order->getId(),
            ));
            if ($exchangeOrderId !== false) {
                $order->setExchangeSlOrderId($exchangeOrderId);
                $order->save();
                $result = true;
            }
        } else if (!empty($order->getTp())) {
            $exchangeOrderId = $exchange->placeOrder(new PlaceOrderDto(
                $newOrderDirection,
                $exchangeSymbol,
                $order->getAmount(),
                $order->getTp(),
                ExchangeOrderType::limit(),
                'tp-'.$order->getId(),
            ));
            if ($exchangeOrderId !== false) {
                $order->setExchangeTpOrderId($exchangeOrderId);
                $order->save();
                $result = true;
            }
        }
        return $result;
    }

    /**
     * @param OrderInterface|Model $order
     */
    public function cancelOrder(OrderInterface|Model $order): void
    {
        $this->cancelPlacedOrdersFromExchange($order);
        $order->setState(OrderState::CANCELED());
        $order->save();
    }

    /**
     * @param OrderInterface $order
     */
    private function cancelPlacedOrdersFromExchange(OrderInterface $order): void
    {
        /** @var AbstractFacade $exchange */
        $exchange = ExchangesFactory::create($order->getExchange(), $order->getUserId());
        $exchangeSymbol = $exchange->getExchangeOrderSymbol($order->getSymbol());
        $placedOrderIds = collect();
        if (!empty($order->getExchangeOrderId())) {
            $placedOrderIds->push($order->getExchangeOrderId());
        }
        if (!empty($order->getExchangeSlOrderId())) {
            $placedOrderIds->push($order->getExchangeSlOrderId());
        }
        if (!empty($order->getExchangeTpOrderId())) {
            $placedOrderIds->push($order->getExchangeTpOrderId());
        }
        if (count($placedOrderIds) > 0) {
            foreach ($placedOrderIds as $placedOrderId) {
                $exchange->cancelOrder(new CancelOrderDto($exchangeSymbol, $placedOrderId));
            }
        }
    }

    private function userHasOpenedOrder(UserInterface $user): bool
    {
        foreach($this->ordersRepository->getUserOrders($user) as $order) {
            /** @var OrderInterface $order */
            if (in_array($order->getState(), [OrderState::NEW(), OrderState::READY()])) {
                return true;
            }
        }
        return false;
    }

    public function createUsersAutomaticOrders(CreateAutomaticOrdersDto $dto): void
    {
        $exchange = ExchangesFactory::create();
        foreach ($this->usersRepository->getAllUsers() as $user) {
            /** @var UserInterface $user */
            if ($this->userHasOpenedOrder($user) || !$user->isAOEnabled()) {
                continue;
            }
            $currentPrice = $exchange->getCurrentPrice($dto->getSymbol());
            $sl = null;
            $tp = null;
            if ($dto->getDirection()->isBUY()) {
                $price = $currentPrice*((100-$user->getAOLimitIndentPercent())/100);
                $sl = $price*((100-$user->getAOSlPercent())/100);
                $tp = $price*((100+$user->getAOTpPercent())/100);
            }
            if ($dto->getDirection()->isSELL()) {
                $price = $currentPrice*((100+$user->getAOLimitIndentPercent())/100);
                $sl = $price*((100+$user->getAOSlPercent())/100);
                $tp = $price*((100-$user->getAOTpPercent())/100);
            }
            $amount = $user->getAOAmount();
            $order = $this->createNewOrder(new CreateNewOrderDto(
                $user->getId(),
                $dto->getDirection(),
                $price,
                $amount,
                $sl,
                $tp,
                false,
                config('crypto.defaultExchange'),
                $dto->getSymbol(),
            ));
            if (empty($order)) {
                continue;
            }
            $message = $dto->getDirection()->key().' '.$dto->getSymbol().' '.$price.' '.$amount.', TP: '.$tp.', SL: '.$sl;
            Log::info($message);
            echo $message.PHP_EOL;
            Notification::route('telegram', config('telegram.botChatId'))->notify(new TelegramNotification($message));
        }
    }
}
