<?php

namespace App\Console\Commands;

use App\Enums\TimeInterval;
use App\Models\MarketDelta;
use App\Repositories\MarketDeltaRepository;
use App\Services\Crypto\Exchanges\AbstractExchange;
use App\Services\Crypto\Exchanges\Factory;
use App\Helpers\TimeHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;
use WebSocket\Client;
use WebSocket\TimeoutException;

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
    public function __construct(private MarketDeltaRepository $marketDeltaRepository)
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
        $exchangeSymbol = trim($this->argument('symbol'));
        $channel = 'live_trades_'.$exchangeSymbol;
        /** @var AbstractExchange $exchange */
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
                    $fromTime = TimeHelper::round((int)($tradeData->microtimestamp/1000), TimeInterval::MINUTE());
                    $marketDelta = (float)$this->marketDeltaRepository->getMinuteMarketDelta('bitstamp', $exchangeSymbol, $fromTime);
                    $delta = $tradeData->amount*($tradeData->type === 1 ? -1 : 1);
                    $marketDelta += $delta;
                    echo $marketDelta.PHP_EOL;
                    MarketDelta::updateOrCreate([
                        'symbol' => $exchangeSymbol,
                        'exchange' => 'bitstamp',
                        'time' => $fromTime,
                    ], [
                        'value' => $marketDelta,
                    ]);
                    $i++;
                }
                $client->close();
            } catch (TimeoutException $e) {
                echo 'BITSTAMP: Timeout'.PHP_EOL;
            } catch (Throwable $e) {
                echo $e->getMessage().PHP_EOL;
                Log::error($e);
            }
        }
        return 0;
    }
}
