<?php

namespace App\Console\Commands;

use App\Enums\BinanceOrderExecutionType;
use App\Enums\OrderState;
use App\Models\OrderInterface;
use App\Models\User;
use App\Repositories\MarketDeltaRepository;
use App\Services\Crypto\Exchanges\Binance\Exchange as BinanceExchange;
use App\Services\Crypto\Exchanges\Factory;
use App\Services\OrdersService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class BinanceWebsocketUserDataClient extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'binance:userdata:client {userId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Binance websocket user data client';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(OrdersService $ordersService, MarketDeltaRepository $ordersRepository)
    {
        $userId = (int)$this->argument('userId');
        $user = User::find($userId);
        if (empty($user)) {
            Log::debug('User not found');
            return 0;
        }
        /** @var BinanceExchange $exchange */
        $exchange = Factory::create('binance', $userId);
        while (true) {
            try {
                $exchange->userDataStream(function ($report) use($ordersService, $ordersRepository) {
                    /*$report = [
                        'clientOrderId' => '140',
                        'executionType' => 'TRADE',
                        'exchangeOrderId' => '4842488533',
                        'side'          => 'BUY',
                        'lastExecutedPrice' => 25432.34,
                        'quantity' => 0.00041,
                    ];*/
                    /** @var OrderInterface $order */
                    $clientOrderId = $report['clientOrderId'];
                    $isStopReport = str_starts_with($clientOrderId, 'stop-');
                    $isLimitReport = str_starts_with($clientOrderId, 'limit-');
                    $clientOrderId = str_replace(['stop-', 'limit-'], '', $clientOrderId);
                    $executionType = BinanceOrderExecutionType::memberByValue($report['executionType']);
                    $lastExecutedPrice = $report['lastExecutedPrice'];
                    Log::debug('Execution type: '.$executionType->value().', Order id: '.$clientOrderId.', Binance order id: '.$report['exchangeOrderId'].
                               ', Direction: '.$report['side'].', Price:'.$lastExecutedPrice.', Quantity: '.$report['quantity'].', Stop: '.($isStopReport ? 'Yes' : 'No').', Limit: '.($isLimitReport ? 'Yes' : 'No'));
                    Log::debug('Report: '.var_export($report, true));
                    if (!is_numeric($clientOrderId)) {
                        Log::debug('Ignoring report with client order id '.$clientOrderId);
                        return;
                    }
                    $order = $ordersRepository->getOrder($clientOrderId);
                    Log::debug('Order: Symbol: '.$order->getSymbol().', Direction: '.$order->getDirection()->value().
                               ', Price: '.$order->getPrice().', Amount: '.$order->getAmount().', SL: '.$order->getSl().
                               ', TP: '.$order->getTp().', Market: '.($order->isMarket() ? 'Yes' : 'No').
                               ', State: '.$order->getState()->value());
                    if (empty($order)) {
                        Log::debug('Order not found. Client order id: '.$clientOrderId);
                        return;
                    }
                    if (!$order->getState()->isNEW() && !$order->getState()->isREADY()) {
                        Log::debug('Order already has state: '.$order->getState()->value().'. Ignoring.');
                        return;
                    }
                    if ($executionType->isCANCELED()) {
                        $ordersService->changeOrderState($order, OrderState::CANCELED(), $lastExecutedPrice);
                    }
                    if ($executionType->isTRADE()) {
                        if (!$isStopReport && !$isLimitReport) {
                            if ($order->hasGoal()) {
                                if (!$ordersService->placeGoalOrder($order)) {
                                    Log::debug('Reverting initial order');
                                    $ordersService->placeRevertMarketOrderToExchange($order);
                                    $ordersService->changeOrderState($order, OrderState::CANCELED(), $lastExecutedPrice);
                                } else {
                                    $ordersService->changeOrderState($order, OrderState::READY(), $lastExecutedPrice);
                                }
                            } else {
                                $ordersService->changeOrderState($order, OrderState::COMPLETED(), $lastExecutedPrice);
                            }
                        } else {
                            Log::debug('Order '.$order->getId().' is completed');
                            $ordersService->changeOrderState($order, OrderState::COMPLETED(), $lastExecutedPrice);
                        }
                    }
                });
            } catch (Throwable $e) {
                Log::debug($e->getMessage());
            }
            sleep(5);
            Log::debug('Retrying to start user data stream');
        }
        return 0;
    }
}
