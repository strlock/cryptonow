<?php
namespace App\Crypto\Exchanges\Binance;

use App\Crypto\Exchanges\AbstractFacade;
use App\Crypto\Exchanges\Trade;
use App\Crypto\Helpers\TimeHelper;
use App\Dto\FetchMinuteMarketDeltaDto;
use App\Dto\TimeIntervalChunkDto;
use App\Enums\BinanceTimeIntervals;
use App\Enums\QueueNames;
use App\Enums\TimeIntervals;
use App\Jobs\BinanceFetchMinuteMarketDelta;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class Facade extends AbstractFacade
{
    protected const LIMIT = 1000;
    protected const CHUNK_INTERVAL = 60*60*1000;

    protected const INTERVALS_MAP = [
        TimeIntervals::ONE_MINUTE => BinanceTimeIntervals::ONE_MINUTE,
        TimeIntervals::FIVE_MINUTES => BinanceTimeIntervals::FIVE_MINUTES,
        TimeIntervals::FIFTEEN_MINUTES => BinanceTimeIntervals::FIFTEEN_MINUTES,
        TimeIntervals::THIRTEEN_MINUTES => BinanceTimeIntervals::THIRTEEN_MINUTES,
        TimeIntervals::ONE_HOUR => BinanceTimeIntervals::ONE_HOUR,
        TimeIntervals::FOUR_HOURS => BinanceTimeIntervals::FOUR_HOURS,
        TimeIntervals::ONE_DAY => BinanceTimeIntervals::ONE_DAY,
    ];

    public function __construct(){
        $this->api = new API(env('BINANCE_API_KEY'), env('BINANCE_API_SECRET'));
    }

    public function getTrades(string $symbol, int $fromTime, int $toTime = null): Collection
    {
        $result = collect();
        try {
            set_time_limit(0);
            $fromId = null;
            $timeIntervalChunks = $this->chunkTimeInterval(fromTime: $fromTime, toTime: $toTime, chunkInterval: static::CHUNK_INTERVAL);
            foreach ($timeIntervalChunks as $timeIntervalChunk) {
                /** @var TimeIntervalChunkDto $timeIntervalChunk */
                $fromTime = $timeIntervalChunk->getFromTime();
                $toTime = $timeIntervalChunk->getToTime();
                $fromTimeDate = Date::createFromTimestampMs($fromTime);
                $toTimeDate = Date::createFromTimestampMs($toTime);
                for ($page=0; $page < self::MAX_PAGES; $page++) {
                    Log::debug('BINANCE: Fetching '.self::LIMIT.' trades from API. '.
                               'Interval: '.$fromTimeDate->format(config('crypto.dateFormat')).'-'.$toTimeDate->format(config('crypto.dateFormat')).' '.
                               'Page: '.$page, compact('symbol', 'fromTime', 'toTime'));
                    if (empty($fromId)) {
                        $trades = $this->api->aggTrades($symbol, $fromTime, $toTime, self::LIMIT);
                    } else {
                        $trades = $this->api->aggTrades($symbol, null, null, self::LIMIT, $fromId);
                    }
                    foreach ($trades ?? [] as $trade) {
                        $time = (int)$trade['timestamp'];
                        if ($time > $toTime) {
                            break 2;
                        }
                        $result->push(new Trade(
                                          id:         (int)$trade['id'],
                                          time:       $time,
                                          price:      (float)$trade['price'],
                                          volume:     (float)$trade['quantity']*($trade['maker'] === 'true' ? -1 : 1),
                                      ));
                    }
                    if (count($trades) < self::LIMIT) {
                        break;
                    }
                    $fromId = $trades[count($trades)-1]['id']+1;
                }
            }
        } catch (\Exception $e) {
            echo $e->getMessage()."\n";
            Log::error(get_class($this).': '.$e->getMessage());
        }
        return $result;
    }

    protected function dispatchMinuteMarketDeltaFetchJob(string $symbol, int $fromTime): void
    {
        /*dispatch(
            (new BinanceFetchMinuteMarketDelta(
                new FetchMinuteMarketDeltaDto($symbol, $fromTime)
            ))->onQueue(QueueNames::BINANCE_MARKET_DELTA_CALCULATION),
        );*/
    }

    public function getCandlesticks(string $symbol, int $fromTime, int $toTime = null, int $interval = TimeHelper::FIVE_MINUTE_MS): Collection
    {
        $result = collect();
        if (!isset(static::INTERVALS_MAP[$interval])) {
            throw new Exception('Unknown Binance time interval');
        }
        $sInterval = static::INTERVALS_MAP[$interval];
        foreach ($this->api->candlesticks($symbol, $sInterval, null, $fromTime, $toTime) as $candlestickData) {
            $tradeTime = (int)$candlestickData['openTime'];
            $result->put($candlestickData['openTime'], [
                $candlestickData['open'],
                $candlestickData['high'],
                $candlestickData['low'],
                $candlestickData['close'],
            ]);
        }
        return $result;
    }
}
