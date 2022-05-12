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
use App\Dto\TimeIntervalChunkDto;
use App\Enums\BinanceTimeIntervals;
use App\Enums\TimeInterval;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Throwable;

class Facade extends AbstractFacade
{
    protected const LIMIT = 1000;
    protected const CHUNK_INTERVAL = 60*60*1000;
    protected array $symbolMap = [
        'BTCUSD' => ['BTCUSDT', 'BTCBUSD'],
    ];
    protected int $orderSymbolIndex = 1;

    protected const INTERVALS_MAP = [
        TimeInterval::MINUTE => BinanceTimeIntervals::ONE_MINUTE,
        TimeInterval::FIVE_MINUTES => BinanceTimeIntervals::FIVE_MINUTES,
        TimeInterval::FIFTEEN_MINUTES => BinanceTimeIntervals::FIFTEEN_MINUTES,
        TimeInterval::THIRTEEN_MINUTES => BinanceTimeIntervals::THIRTEEN_MINUTES,
        TimeInterval::HOUR => BinanceTimeIntervals::ONE_HOUR,
        TimeInterval::FOUR_HOURS => BinanceTimeIntervals::FOUR_HOURS,
        TimeInterval::DAY => BinanceTimeIntervals::ONE_DAY,
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
                               'Page: '.$page, compact('exchangeSymbol', 'fromTime', 'toTime'));
                    if (empty($fromId)) {
                        $trades = $this->api->aggTrades($exchangeSymbol, $fromTime, $toTime, self::LIMIT);
                    } else {
                        $trades = $this->api->aggTrades($exchangeSymbol, null, null, self::LIMIT, $fromId);
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
     * @param string $exchangeSymbol
     * @param int $fromTime
     */
    protected function dispatchMinuteMarketStatFetchJob(string $exchangeSymbol, int $fromTime): void
    {
        /*dispatch(
            (new BinanceFetchMinuteMarketStat(
                new FetchMinuteMarketStatDto($exchangeSymbol, $fromTime)
            ))->onQueue(QueueNames::BINANCE_MARKET_STAT_CALCULATION),
        );*/
    }

    /**
     * @param string $exchangeSymbol
     * @param int $fromTime
     * @param int|null $toTime
     * @param TimeInterval|null $interval
     * @return Collection
     * @throws Exception
     */
    public function getCandlesticks(string $exchangeSymbol, int $fromTime, int $toTime = null, ?TimeInterval $interval = null): Collection
    {
        if (empty($interval)) {
            $interval = TimeInterval::FIVE_MINUTES();
        }
        $result = collect();
        if (!isset(static::INTERVALS_MAP[$interval->value()])) {
            throw new Exception('Unknown Binance time interval');
        }
        $sInterval = static::INTERVALS_MAP[$interval->value()];
        foreach ($this->api->candlesticks($exchangeSymbol, $sInterval, null, $fromTime, $toTime) as $candlestickData) {
            $candlestickResultData = [
                $candlestickData['open'],
                $candlestickData['high'],
                $candlestickData['low'],
                $candlestickData['close'],
                $candlestickData['closeTime'],
            ];
            $result->put($candlestickData['openTime'], $candlestickResultData);
        }
        return $result;
    }

    /**
     * @param PlaceOrderDto $dto
     * @return false|int|null
     */
    public function placeOrder(PlaceOrderDto $dto): null|false|int
    {
        $result = false;
        if (config('crypto.exchangesTestmode') === true) {
            return null;
        }
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
            $response = $this->api->order(strtoupper($dto->getDirection()->value()), $dto->getExchangeSymbol(), $dto->getAmount(), $dto->getPrice(), $orderType, $params);
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
            if (config('crypto.exchangesTestmode') === true) {
                return [];
            }
            $response = $this->api->orderOCO(strtoupper($dto->getDirection()->value()), $dto->getExchangeSymbol(), $dto->getAmount(), $dto->getTp(), $dto->getSl(), [
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
            if (config('crypto.exchangesTestmode') === true) {
                return true;
            }
            $this->api->cancel($dto->getExchangeSymbol(), $dto->getOrderId());
        } catch (Throwable $e) {
            Log::error($e);
            $result = false;
        }
        return $result;
    }

    /**
     * @param string $exchangeSymbol
     * @return float
     * @throws Exception
     */
    public function getCurrentPrice(string $exchangeSymbol): float
    {
        return $this->api->price($exchangeSymbol);
    }
}
