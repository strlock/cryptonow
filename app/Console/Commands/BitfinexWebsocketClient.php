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
        $exchange = Factory::create('bitfinex');
        $exchangeSymbol = trim($this->argument('symbol'));
        /** @var AbstractFacade $exchange */
        while (true) {
            try {
                $client = new Client('wss://api-pub.bitfinex.com/ws/2');
                $client->send('{
                    "event": "subscribe",
                    "channel": "trades",
                    "symbol": "'.$exchangeSymbol.'",
                }');
                $subscriptionResponse = json_decode($client->receive());
                if (empty($subscriptionResponse) || $subscriptionResponse->event !== 'info') {
                    Log::debug('BITFINEX: subscription error! Symbol: '.$exchangeSymbol);
                    continue;
                } else {
                    Log::debug('BITFINEX: subscription success! Symbol: '.$exchangeSymbol);
                }
                var_dump($client->receive());exit;
                $response = json_decode($client->receive());
                var_dump($response);exit;
                if (empty($response)) {
                    Log::debug('BITFINEX: Invalid response!');
                    continue;
                }
                $tradeData = $response->data;
                if (empty($tradeData)) {
                    Log::debug('BITFINEX: Empty trade data!');
                    continue;
                }
                $mdQueueName = 'bitfinex:md:'.$exchangeSymbol;
                $fromTime = TimeHelper::round((int)($tradeData->microtimestamp/1000), TimeInterval::MINUTE());
                $marketDelta = (float)$exchange->getMinuteMarketDeltaFromDatabase($exchangeSymbol, $fromTime);
                Redis::zRem($mdQueueName, $fromTime.':'.$marketDelta);
                $delta = $tradeData->amount*($tradeData->type === 1 ? -1 : 1);
                $marketDelta += $delta;
                echo $marketDelta.PHP_EOL;
                Redis::zAdd($mdQueueName, $fromTime, $fromTime.':'.$marketDelta);
                $i++;
                //$client->close();
            } catch (Throwable $e) {
                Log::debug($e);
            }
        }
        return 0;
    }
}
