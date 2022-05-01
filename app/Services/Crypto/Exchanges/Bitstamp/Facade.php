<?php
namespace App\Services\Crypto\Exchanges\Bitstamp;

use App\Dto\CreateNewOrderDto;
use App\Dto\PlaceGoalOrderDto;
use App\Dto\PlaceOrderDto;
use App\Models\OrderInterface;
use App\Services\Crypto\Exchanges\AbstractFacade;
use App\Services\Crypto\Exchanges\Trade;
use App\Services\Crypto\Helpers\TimeHelper;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;

class Facade extends AbstractFacade
{
    private const SYMBOL_MAP = [
        'BTCUSDT' => 'btcusd',
    ];

    protected int $delay = 0;

    public function __construct(?int $userId = null){
        $this->api = new API(env('BITSTAMP_API_KEY'),env('BITSTAMP_API_SECRET'),env('BITSTAMP_CLIENT_ID'));
    }

    public function getTrades(string $symbol, int $fromTime, int $toTime = null): Collection
    {
        $result = collect();
        try {
            set_time_limit(0);
            $fromTimeDate = Date::createFromTimestampMs($fromTime);
            $toTimeDate = Date::createFromTimestampMs($toTime);
            Log::debug(
                'BITSTAMP: Fetching trades from API. ' .
                'Interval: ' . $fromTimeDate->format(config('crypto.dateFormat')) . '-' . $toTimeDate->format(
                    config('crypto.dateFormat')
                ),
                compact('symbol', 'fromTime', 'toTime')
            );
            $trades = $this->getTransactions($symbol, $fromTime, $toTime);
            foreach ($trades ?? [] as $trade) {
                $time = $trade['date']*1000;
                $result->push(
                    new Trade(
                        id: (int)$trade['tid'],
                        time: $time,
                        price: (float)$trade['price'],
                        volume: $trade['amount']*((int)$trade['type'] === 1 ? -1.0 : 1.0) // 0 - buy, 1 - sell
                    )
                );
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
        /*dispatch(
            (new BitstampFetchMinuteMarketStat(
                new FetchMinuteMarketStatDto($symbol, $fromTime)
            ))->onQueue(QueueNames::BITSTAMP_MARKET_STAT_CALCULATION)//->delay($this->delay),
        );*/
        //$this->delay += 2;
    }

    /**
     * @param $symbol
     * @param $fromTime
     * @param $toTime
     * @return array
     */
    private function getTransactions($symbol, $fromTime, $toTime)
    {
        $result = [];
        $dayTransactions = $this->api->transactions('day', self::SYMBOL_MAP[$symbol]);
        foreach ($dayTransactions as $transaction) {
            $transactionTime = $transaction['date']*1000;
            if ($transactionTime >= $fromTime && $transactionTime <= $toTime) {
                $result[] = $transaction;
            }
        }
        return $result;
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
     * @param PlaceOrderDto $dto
     * @return false|int
     */
    public function placeOrder(PlaceOrderDto $dto): false|int
    {
        return false;
    }


    /**
     * @param PlaceGoalOrderDto $dto
     */
    public function placeTakeProfitAndStopLossOrder(PlaceGoalOrderDto $dto): array|false
    {
        return false;
    }
}
