<?php

namespace App\Console\Commands;

use App\Enums\BinanceOrderExecutionType;
use App\Enums\OrderState;
use App\Models\OrderInterface;
use App\Models\User;
use App\Repositories\OrdersRepository;
use App\Services\Crypto\Exchanges\Binance\Facade as BinanceExchange;
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
    public function handle(OrdersService $ordersService, OrdersRepository $ordersRepository)
    {
        $userId = (int)$this->argument('userId');
        $user = User::find($userId);
        if (empty($user)) {
            echo 'User not found'.PHP_EOL;
            return 0;
        }
        /** @var BinanceExchange $exchange */
        $exchange = Factory::create('binance', $userId);
        while (true) {
            try {
                $exchange->userDataStream(function ($report) use($ordersService, $ordersRepository) {
                    /*$report = [
                        'clientOrderId' => '162',
                        'executionType' => 'TRADE',
                        'exchangeOrderId' => '4842488533',
                        'side'          => 'BUY',
                    ];*/
                    /** @var OrderInterface $order */
                    $clientOrderId = $report['clientOrderId'];
                    $isReportForStop = false;
                    if (str_starts_with($clientOrderId, 'stop-')) {
                        $clientOrderId = str_replace('stop-', '', $clientOrderId);
                        $isReportForStop = true;
                    }
                    $isReportForLimit = false;
                    if (str_starts_with($clientOrderId, 'limit-')) {
                        $clientOrderId = str_replace('limit-', '', $clientOrderId);
                        $isReportForLimit = true;
                    }
                    $executionType = $report['executionType'];
                    $this->log('Execution type: '.$executionType.', Order id: '.$clientOrderId.', Binance order id: '.$report['exchangeOrderId'].
                               ', Direction: '.$report['side'].', Stop: '.($isReportForStop ? 'Yes' : 'No').', Limit: '.($isReportForLimit ? 'Yes' : 'No'));
                    $order = $ordersRepository->getOrder($clientOrderId);
                    if (empty($order)) {
                        echo 'Order not found. Order id: '.$clientOrderId.PHP_EOL;
                        return;
                    }
                    if ($executionType === BinanceOrderExecutionType::CANCELED) {
                        $ordersService->changeOrderState($order, OrderState::CANCELED);
                    }
                    if ($executionType === BinanceOrderExecutionType::TRADE) {
                        if (!$isReportForStop && !$isReportForLimit) {
                            echo 'Order is '.($order->hasGoal() ? 'ready' : 'completed').PHP_EOL;
                            if ($order->hasGoal()) {
                                if (!$ordersService->placeGoalOrder($order)) {
                                    echo 'Reverting initial order'.PHP_EOL;
                                    $ordersService->placeRevertMarketOrderToExchange($order);
                                    $ordersService->changeOrderState($order,OrderState::CANCELED);
                                } else {
                                    $ordersService->changeOrderState($order, OrderState::READY);
                                }
                            } else {
                                $ordersService->changeOrderState($order, OrderState::COMPLETED);
                            }
                        } else {
                            echo 'Order '.$order->getId().' is completed'.PHP_EOL;
                            $ordersService->changeOrderState($order, OrderState::COMPLETED);
                        }
                    }
                });
            } catch (Throwable $e) {
                $this->log($e->getMessage());
            }
            sleep(5);
            echo 'Retrying to start user data stream'.PHP_EOL;
        }
        return 0;
    }

    private function log(string $message): void
    {
        echo $message.PHP_EOL;
        Log::info($message);
    }
}
