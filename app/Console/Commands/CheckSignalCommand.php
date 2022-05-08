<?php

namespace App\Console\Commands;

use App\Dto\CreateAutomaticOrdersDto;
use App\Enums\OrderDirection;
use App\Enums\StrategySignal;
use App\Enums\TimeInterval;
use App\Helpers\TimeHelper;
use App\Services\OrdersService;
use App\Services\Strategy\StrategyInterface;
use Illuminate\Console\Command;

class CheckSignalCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:signal {symbol}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check signal in loop and make orders when triggered';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        private OrdersService $ordersService,
    )
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(StrategyInterface $strategy)
    {
        $symbol = $this->argument('symbol');
        while (true) {
            $signal = $strategy->getSignal('BTCUSDT', TimeInterval::FIVE_MINUTES());
            $toTime = TimeHelper::round(TimeHelper::time(), TimeInterval::FIVE_MINUTES());
            echo date('d.m.Y H:i:s', $toTime/1000).' '.$signal->key().PHP_EOL;
            if ($signal !== StrategySignal::NOTHING()) {
                $direction = match($signal) {
                    StrategySignal::BUY() => OrderDirection::BUY(),
                    StrategySignal::SELL() => OrderDirection::SELL(),
                };
                $this->ordersService->createUsersAutomaticOrders(new CreateAutomaticOrdersDto(
                    $symbol,
                    $direction,
                ));
            }
            sleep(60);
        }
        return 0;
    }
}
