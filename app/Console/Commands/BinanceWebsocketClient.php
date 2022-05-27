<?php

namespace App\Console\Commands;

use App\Enums\TimeInterval;
use App\Models\MarketDelta;
use App\Repositories\MarketDeltaRepository;
use App\Helpers\TimeHelper;
use App\Events\BinancePrice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
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
        $exchangeSymbol = trim($this->argument('symbol'));
        $streamName = strtolower($exchangeSymbol).'@aggTrade';
        while (true) {
            try {
                echo 'Retrieving '.self::CHUNK_SIZE.' trades'.PHP_EOL;
                $client = new Client('wss://stream.binance.com:9443/ws/'.$streamName, [
                    'timeout' => 5,
                ]);
                $fromTime = TimeHelper::round(TimeHelper::time(), TimeInterval::MINUTE());
                $minutesMarketDelta = [
                    $fromTime => $this->marketDeltaRepository->getMinuteMarketDelta('binance', $exchangeSymbol, $fromTime)
                ];
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
                    $fromTime = TimeHelper::round((int)($response->E), TimeInterval::MINUTE());
                    if (empty($minutesMarketDelta[$fromTime])) {
                        $minutesMarketDelta[$fromTime] = 0.0;
                    }
                    $minutesMarketDelta[$fromTime] += $response->q*($response->m ? -1 : 1);
                    echo $i.':'.$fromTime.':'.round($price, 2).': '.$minutesMarketDelta[$fromTime].PHP_EOL;
                    $i++;
                }
                foreach ($minutesMarketDelta as $fromTime => $marketDelta) {
                    MarketDelta::updateOrCreate([
                        'symbol' => $exchangeSymbol,
                        'exchange' => 'binance',
                        'time' => $fromTime,
                    ], [
                        'value' => $marketDelta,
                    ]);
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
