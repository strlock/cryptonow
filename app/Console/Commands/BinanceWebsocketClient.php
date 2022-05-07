<?php

namespace App\Console\Commands;

use App\Enums\TimeInterval;
use App\Services\Crypto\Exchanges\AbstractFacade;
use App\Services\Crypto\Exchanges\Factory;
use App\Helpers\TimeHelper;
use App\Events\BinancePrice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Throwable;
use WebSocket\Client;

class BinanceWebsocketClient extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'binance:client {symbol}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Binance WebSocket client';

    private const CHUNK_SIZE = 10000;

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
    public function handle()
    {
        $symbol = trim($this->argument('symbol'));
        $streamName = strtolower($symbol).'@aggTrade';
        /** @var AbstractFacade $exchange */
        $exchange = Factory::create('binance');
        while (true) {
            try {
                echo 'Retrieving '.self::CHUNK_SIZE.' trades'.PHP_EOL;
                $client = new Client('wss://stream.binance.com:9443/ws/'.$streamName, [
                    'timeout' => 5,
                ]);
                $i = 0;
                while ($i < self::CHUNK_SIZE) {
                    $response = json_decode($client->receive());
                    if (empty($response)) {
                        echo 'Invalid response!'.PHP_EOL;
                        Log::debug('BINANCE: Invalid response!');
                        continue;
                    }
                    $price = $response->p;
                    event(new BinancePrice($price));
                    $mdQueueName = 'binance:md:'.$symbol;
                    $fromTime = TimeHelper::round((int)($response->E), TimeInterval::MINUTE());
                    $marketDelta = (float)$exchange->getMinuteMarketDeltaFromDatabase($symbol, $fromTime);
                    Redis::zRem($mdQueueName, $fromTime.':'.$marketDelta);
                    $delta = $response->q*($response->m ? -1 : 1);
                    $marketDelta += $delta;
                    echo round($price, 2).': '.$marketDelta.PHP_EOL;
                    Redis::zAdd($mdQueueName, $fromTime, $fromTime.':'.$marketDelta);
                    $i++;
                }
                $client->close();
            } catch (Throwable $e) {
                echo $e->getMessage().PHP_EOL;
                Log::error($e);
            }
        }
        return 0;
    }
}
