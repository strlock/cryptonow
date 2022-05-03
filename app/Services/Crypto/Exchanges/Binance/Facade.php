<?php
namespace App\Services\Crypto\Exchanges\Binance;

use App\Dto\CancelOrderDto;
use App\Dto\PlaceGoalOrderDto;
use App\Dto\PlaceOrderDto;
use App\Enums\BinanceOrderType;
use App\Enums\ExchangeOrderType;
use App\Models\User;
use App\Services\Crypto\Exchanges\AbstractFacade;
use App\Services\Crypto\Exchanges\Trade;
use App\Services\Crypto\Helpers\TimeHelper;
use App\Dto\TimeIntervalChunkDto;
use App\Enums\BinanceTimeIntervals;
use App\Enums\TimeIntervals;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Throwable;

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

    public function __construct(?int $userId = null){
        if (!empty($userId)) {
            /** @var User $user */
            $user = User::find($userId);
            if (empty($user)) {
                throw new Exception('User not found');
            }
            if (!$user->isBinanceConnected()) {
                throw new Exception('Binance is not connected');
            }
            $this->apiKey = $user->getBinanceApiKey();
            $this->apiSecret = $user->getBinanceApiSecret();
        } else {
            $this->apiKey = env('BINANCE_API_KEY');
            $this->apiSecret = env('BINANCE_API_SECRET');
        }
        $this->api = new API($this->apiKey, $this->apiSecret);
    }

    /**
     * @param string $symbol
     * @param int $fromTime
     * @param int|null $toTime
     * @return Collection
     */
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

    /**
     * @param string $symbol
     * @param int $fromTime
     */
    protected function dispatchMinuteMarketStatFetchJob(string $symbol, int $fromTime): void
    {
        /*dispatch(
            (new BinanceFetchMinuteMarketStat(
                new FetchMinuteMarketStatDto($symbol, $fromTime)
            ))->onQueue(QueueNames::BINANCE_MARKET_STAT_CALCULATION),
        );*/
    }

    /**
     * @param string $symbol
     * @param int $fromTime
     * @param int|null $toTime
     * @param int|float $interval
     * @return Collection
     * @throws Exception
     */
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

    /**
     * @param PlaceOrderDto $dto
     * @return false|int
     */
    public function placeOrder(PlaceOrderDto $dto): false|int
    {
        $result = false;
        $orderType = $this->getBinanceOrderType($dto->getOrderType())->value();
        $params = [
            'newClientOrderId' => $dto->getClientOrderId(),
        ];
        if ($orderType === 'STOP_LOSS_LIMIT') {
            if ($dto->getDirection()->isSELL()) {
                $params['stopPrice'] = 1.005*$dto->getPrice();
            }
            if ($dto->getDirection()->isBUY()) {
                $params['stopPrice'] = 0.995*$dto->getPrice();
            }
        }
        try {
            $response = $this->api->order(strtoupper($dto->getDirection()->value()), $dto->getSymbol(), $dto->getAmount(), $dto->getPrice(), $orderType, $params);
            if (is_array($response) && isset($response['orderId'])) {
                $result = $response['orderId'];
            }
        } catch (Throwable $e) {
            Log::error($e);
        }
        return $result;
    }

    public function userDataStream(callable $executionCallback)
    {
        $this->api->userDataStream($executionCallback);
    }

    /**
     * @param PlaceGoalOrderDto $dto
     * @return array|false
     */
    public function placeTakeProfitAndStopLossOrder(PlaceGoalOrderDto $dto): array|false
    {
        $result = false;
        try {
            $response = $this->api->orderOCO(strtoupper($dto->getDirection()->value()), $dto->getSymbol(), $dto->getAmount(), $dto->getTp(), $dto->getSl(), [
                'listClientOrderId' => $dto->getOrderId(),
                'limitClientOrderId' => 'limit-'.$dto->getOrderId(),
                'stopClientOrderId' => 'stop-'.$dto->getOrderId(),
            ]);
            if (is_array($response) && isset($response['orderReports'])) {
                foreach ($response['orderReports'] as $orderReport) {
                    if ($orderReport['type'] === 'LIMIT_MAKER') {
                        $result[0] = $orderReport['orderId'];
                    }
                    if ($orderReport['type'] === 'STOP_LOSS_LIMIT') {
                        $result[1] = $orderReport['orderId'];
                    }
                }
            }
        } catch (Throwable $e) {
            Log::error($e);
        }
        return $result;
    }

    /**
     * @param ExchangeOrderType $orderType
     * @return BinanceOrderType
     */
    private function getBinanceOrderType(ExchangeOrderType $orderType): BinanceOrderType
    {
        return match($orderType) {
            ExchangeOrderType::market() => BinanceOrderType::MARKET(),
            ExchangeOrderType::limit() => BinanceOrderType::LIMIT(),
            ExchangeOrderType::stop_loss() => BinanceOrderType::STOP_LOSS_LIMIT(),
            ExchangeOrderType::take_profit() => BinanceOrderType::TAKE_PROFIT(),
        };
    }

    /**
     * @param CancelOrderDto $dto
     * @return bool
     */
    public function cancelOrder(CancelOrderDto $dto): bool
    {
        $result = true;
        try {
            $this->api->cancel($dto->getSymbol(), $dto->getOrderId());
        } catch (Throwable $e) {
            Log::error($e);
            $result = false;
        }
        return $result;
    }
}
