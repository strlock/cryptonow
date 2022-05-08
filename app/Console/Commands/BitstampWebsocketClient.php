<?php

namespace App\Console\Commands;

use App\Enums\TimeInterval;
use App\Services\Crypto\Exchanges\AbstractFacade;
use App\Services\Crypto\Exchanges\Factory;
use App\Helpers\TimeHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Throwable;
use WebSocket\Client;

class BitstampWebsocketClient extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bitstamp:client {symbol}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bitstamp WebSocket client';

    private const CHUNK_SIZE = 1000;

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
        $exchange = Factory::create('bitstamp');
        $symbol = trim($this->argument('symbol'));
        $exchangeSymbol = $exchange->getExchangeSymbol($symbol);
        $channel = 'live_trades_'.$exchangeSymbol;
        /** @var AbstractFacade $exchange */
        while (true) {
            try {
                echo 'Retrieving '.self::CHUNK_SIZE.' trades'.PHP_EOL;
                $client = new Client('wss://ws.bitstamp.net', [
                    'timeout' => 10,
                ]);
                $client->send('{
                    "event": "bts:subscribe",
                        "data": {
                            "channel": "'.$channel.'"
                        }
                    }');
                $subscriptionResponse = json_decode($client->receive());
                if (empty($subscriptionResponse) || $subscriptionResponse->event !== 'bts:subscription_succeeded') {
                    echo 'Subscription error!'.PHP_EOL;
                    Log::debug('BITSTAMP: subscription error! Channel: '.$channel);
                    continue;
                } else {
                    echo 'Subscription success!'.PHP_EOL;
                    Log::debug('BITSTAMP: subscription success! Channel: '.$channel);
                }
                $i = 0;
                while ($i < self::CHUNK_SIZE) {
                    $response = json_decode($client->receive());
                    if (empty($response)) {
                        echo 'Invalid response!'.PHP_EOL;
                        Log::debug('BITSTAMP: Invalid response!');
                        continue;
                    }
                    $tradeData = $response->data;
                    if (empty($tradeData)) {
                        echo 'Empty trade data!'.PHP_EOL;
                        Log::debug('BITSTAMP: Empty trade data!');
                        continue;
                    }
                    $mdQueueName = 'bitstamp:md:'.$exchangeSymbol;
                    $fromTime = TimeHelper::round((int)($tradeData->microtimestamp/1000), TimeInterval::MINUTE());
                    $marketDelta = (float)$exchange->getMinuteMarketDeltaFromDatabase($exchangeSymbol, $fromTime);
                    Redis::zRem($mdQueueName, $fromTime.':'.$marketDelta);
                    $delta = $tradeData->amount*($tradeData->type === 1 ? -1 : 1);
                    $marketDelta += $delta;
                    echo $marketDelta.PHP_EOL;
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
