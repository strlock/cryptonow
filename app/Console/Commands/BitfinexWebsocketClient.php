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

class BitfinexWebsocketClient extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bitfinex:client {symbol}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bitfinex WebSocket client';

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
        /** @var AbstractFacade $exchange */
        $exchange = Factory::create('bitfinex');
        $exchangeSymbol = trim($this->argument('symbol'));
        while (true) {
            try {
                echo 'Retrieving '.self::CHUNK_SIZE.' trades'.PHP_EOL;
                $client = new Client('wss://api-pub.bitfinex.com/ws/2', [
                    'timeout' => 10,
                ]);
                $client->send('{
                    "event": "subscribe",
                    "channel": "trades",
                    "symbol": "'.$exchangeSymbol.'"
                }');
                $subscriptionResponse = json_decode($client->receive());
                if (empty($subscriptionResponse) || $subscriptionResponse->event !== 'info') {
                    Log::debug('BITFINEX: subscription error! Symbol: '.$exchangeSymbol);
                    continue;
                } else {
                    Log::debug('BITFINEX: subscription success! Symbol: '.$exchangeSymbol);
                }
                $i = 0;
                while ($i < self::CHUNK_SIZE) {
                    $response = json_decode($client->receive());
                    if (empty($response)) {
                        Log::debug('BITFINEX: Invalid response!');
                        continue;
                    }
                    if (!is_array($response) || !isset($response[0]) || !isset($response[1])) {
                        continue;
                    }
                    if ($response[1] === 'te') {
                        $tradeData = $response[2] ?? [];
                        $mdQueueName = 'bitfinex:md:'.$exchangeSymbol;
                        $tradeTime = $tradeData[1];
                        $tradeQuantity = $tradeData[2];
                        $fromTime = TimeHelper::round($tradeTime, TimeInterval::MINUTE());
                        $marketDelta = (float)$exchange->getMinuteMarketDeltaFromDatabase($exchangeSymbol, $fromTime);
                        Redis::zRem($mdQueueName, $fromTime . ':' . $marketDelta);
                        $marketDelta += $tradeQuantity;
                        echo $marketDelta . PHP_EOL;
                        Redis::zAdd($mdQueueName, $fromTime, $fromTime . ':' . $marketDelta);
                        $i++;
                    }
                }
                $client->close();
            } catch (Throwable $e) {
                Log::debug($e);
            }
        }
        return 0;
    }
}
