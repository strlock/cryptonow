<?php
namespace App\Services\Crypto\Exchanges\Bitfinex;

use App\Dto\CreateNewOrderDto;
use App\Dto\PlaceGoalOrderDto;
use App\Dto\PlaceOrderDto;
use App\Models\OrderInterface;
use App\Services\Crypto\Exchanges\AbstractFacade;
use App\Services\Crypto\Exchanges\Trade;
use App\Services\Crypto\Helpers\TimeHelper;
use App\Dto\FetchMinuteMarketStatDto;
use App\Dto\TimeIntervalChunkDto;
use App\Enums\QueueNames;
use App\Jobs\BitfinexFetchMinuteMarketStat;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;

class Facade extends AbstractFacade
{
    protected const LIMIT = 10000;
    protected const CHUNK_INTERVAL = 10*60*1000;

    protected int $delay = 0;

    public function __construct(?int $userId = null){
        $this->api = new API([
            'apiKey' => env('BITFINEX_API_KEY'),
            'apiSecret' => env('BITFINEX_API_SECRET'),
            'transform' => true
        ]);
    }

    public function getTrades(string $symbol, int $fromTime, int $toTime = null): Collection
    {
        $result = collect();
        try {
            set_time_limit(0);
            $timeIntervalChunks = $this->chunkTimeInterval(fromTime: $fromTime, toTime: $toTime, chunkInterval: static::CHUNK_INTERVAL);
            foreach ($timeIntervalChunks as $timeIntervalChunk) {
                /** @var TimeIntervalChunkDto $timeIntervalChunk */
                $fromTime = $timeIntervalChunk->getFromTime();
                $toTime = $timeIntervalChunk->getToTime();
                $fromTimeDate = Date::createFromTimestampMs($fromTime);
                $toTimeDate = Date::createFromTimestampMs($toTime);
                Log::debug(
                    'BITFINEX: Fetching ' . self::LIMIT . ' trades from API. ' .
                    'Interval: ' . $fromTimeDate->format(config('crypto.dateFormat')) . '-' . $toTimeDate->format(
                        config('crypto.dateFormat')
                    ),
                    compact('symbol', 'fromTime', 'toTime')
                );
                $trades = $this->api->trades($symbol, $fromTime, $toTime, self::LIMIT);
                foreach ($trades ?? [] as $trade) {
                    $time = (int)$trade[1];
                    if ($time > $toTime) {
                        break 2;
                    }
                    $result->push(
                        new Trade(
                            id: (int)$trade[0],
                            time: $time,
                            price: (float)$trade[3],
                            volume: (float)$trade[2],
                        )
                    );
                }
            }
        } catch (\Exception $e) {
            echo $e->getMessage()."\n";
            Log::error(get_class($this).': '.$e->getMessage());
        }
        return $result;
    }

    /**
     * @param string $symbol
     * @param int $fromTime
     */
    protected function dispatchMinuteMarketStatFetchJob(string $symbol, int $fromTime): void
    {
        dispatch(
            (new BitfinexFetchMinuteMarketStat(
                new FetchMinuteMarketStatDto($symbol, $fromTime)
            ))->onQueue(QueueNames::BITFINEX_MARKET_STAT_CALCULATION)->delay($this->delay),
        );
        $this->delay += 2;
    }

    /**
     * @param string $symbol
     * @param int $fromTime
     * @param int|null $toTime
     * @param int|float $interval
     * @return Collection
     */
    public function getCandlesticks(string $symbol, int $fromTime, int $toTime = null, int $interval = TimeHelper::FIVE_MINUTE_MS): Collection
    {
        // TODO: Implement getCandlesticks() method.
    }

    /**
     * @param PlaceOrderDto $order
     * @return false|int
     */
    public function placeOrder(PlaceOrderDto $dto): false|int
    {
        return false;
    }

    /**
     * @param PlaceGoalOrderDto $dto
     * @return array|false
     */
    public function placeTakeProfitAndStopLossOrder(PlaceGoalOrderDto $dto): array|false
    {
        return false;
    }
}
