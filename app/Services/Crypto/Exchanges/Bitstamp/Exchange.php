<?php
namespace App\Services\Crypto\Exchanges\Bitstamp;

use App\Services\Crypto\Exchanges\AbstractExchange;
use App\Services\Crypto\Exchanges\Trade;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;

class Exchange extends AbstractExchange
{
    protected array $symbolMap = [
        'BTCUSD' => ['btcusd'],
    ];

    protected int $delay = 0;

    public function __construct(?int $userId = null){
        $this->api = new API(env('BITSTAMP_API_KEY'),env('BITSTAMP_API_SECRET'),env('BITSTAMP_CLIENT_ID'));
    }

    /**
     * @param string $exchangeSymbol
     * @param int $fromTime
     * @param int|null $toTime
     * @return Collection
     */
    public function getTrades(string $exchangeSymbol, int $fromTime, int $toTime = null): Collection
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
                compact('exchangeSymbol', 'fromTime', 'toTime')
            );
            $trades = $this->getTransactions($exchangeSymbol, $fromTime, $toTime);
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
     * @param string $exchangeSymbol
     * @param int $fromTime
     */
    protected function dispatchMinuteMarketStatFetchJob(string $exchangeSymbol, int $fromTime): void
    {
        /*dispatch(
            (new BitstampFetchMinuteMarketStat(
                new FetchMinuteMarketStatDto($exchangeSymbol, $fromTime)
            ))->onQueue(QueueNames::BITSTAMP_MARKET_STAT_CALCULATION)//->delay($this->delay),
        );*/
        //$this->delay += 2;
    }

    /**
     * @param $exchangeSymbol
     * @param $fromTime
     * @param $toTime
     * @return array
     */
    private function getTransactions($exchangeSymbol, $fromTime, $toTime): array
    {
        $result = [];
        $dayTransactions = $this->api->transactions('day', $exchangeSymbol);
        foreach ($dayTransactions as $transaction) {
            $transactionTime = $transaction['date']*1000;
            if ($transactionTime >= $fromTime && $transactionTime <= $toTime) {
                $result[] = $transaction;
            }
        }
        return $result;
    }
}
