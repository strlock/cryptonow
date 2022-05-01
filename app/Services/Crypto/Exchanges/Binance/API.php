<?php
namespace App\Services\Crypto\Exchanges\Binance;

use Binance\API as BinanceAPI;
use Exception;
use Throwable;
use WebSocket\Client;

class API extends BinanceAPI
{
    protected $useTestnet = false;
    protected $httpDebug = false;

    /**
     * aggTrades get Market History / Aggregate Trades
     *
     * $trades = $api->aggTrades("BNBBTC");
     *
     * @param $symbol string the symbol to get the trade information for
     * @return array with error message or array of market history
     * @throws \Exception
     */
    public function aggTrades(string $symbol, $startTime = null, $endTime = null, $limit = null, $fromId = null): array
    {
        $params = [
            "symbol" => $symbol,
        ];
        if (!empty($startTime)) {
            $params['startTime'] = $startTime;
        }
        if (!empty($endTime)) {
            $params['endTime'] = $endTime;
        }
        if (!empty($limit)) {
            $params['limit'] = $limit;
        } else {
            $params['limit'] = 1000;
        }
        if (!empty($fromId)) {
            $params['fromId'] = $fromId;
        }
        return $this->tradesData($this->httpRequest("v3/aggTrades", "GET", $params));
    }

    /**
     * tradesData Convert aggTrades data into easier format
     *
     * $tradesData = $this->tradesData($trades);
     *
     * @param $trades array of trade information
     * @return array easier format for trade information
     */
    protected function tradesData(array $trades): array
    {
        $result = parent::tradesData($trades);
        foreach ($trades as $key => $trade) {
            $result[$key]['id'] = $trade['a'];
        }
        return $result;
    }

    public function userDataStream(callable $executionCallback): void
    {
        $response = $this->httpRequest("v1/userDataStream", "POST", []);
        $listenKey = $response['listenKey'];
        if (empty($listenKey)) {
            return;
        }
        $client = new Client('wss://stream.binance.com:9443/ws/'.$listenKey, ['timeout' => 60]);
        $i = 0;
        while (true) {
            try {
                if (($i % 30) === 0) {
                    $this->httpRequest("v1/userDataStream?listenKey=".$listenKey, "PUT", []);
                }
                $response = json_decode($client->receive());
                if (!is_object($response)) {
                    continue;
                }
                if ($response->e !== 'executionReport') {
                    continue;
                }
                $executionCallback([
                    'symbol' => $response->s,
                    'side' => $response->S,
                    'orderType' => $response->o,
                    'quantity' => $response->q,
                    'price' => $response->p,
                    'executionType' => $response->x,
                    'orderStatus' => $response->X,
                    'rejectReason' => $response->r,
                    'exchangeOrderId' => $response->i,
                    'orderTime' => $response->T,
                    'eventTime' => $response->E,
                    'clientOrderId' => $response->x === 'CANCELED' || $response->x === 'EXPIRED' ? $response->C : $response->c,
                ]);
            } catch (Throwable $e) {
                echo $e->getMessage().PHP_EOL;
            } finally {
                $i++;
            }
        }
    }

    /**
     * @param string $side
     * @param string $symbol
     * @param float $quantity
     * @param float $tpPrice
     * @param float $slPrice
     * @param float $slDiff
     * @return array
     * @throws \Exception
     */
    public function orderOCO(string $side, string $symbol, float $quantity, float $tpPrice, float $slPrice, array $params = []): array
    {
        $params['symbol'] = $symbol;
        $params['side'] = strtoupper($side);
        $params['quantity'] = $quantity;
        $params['recvWindow'] = 60000;
        $params['price'] = $tpPrice;
        $params['stopLimitTimeInForce'] = "GTC";
        $params['stopPrice'] = $slPrice;
        $slDiff = ($tpPrice - $slPrice)*0.01;
        $params['stopLimitPrice'] = $slPrice - $slDiff;
        return $this->httpRequest('v3/order/oco', 'POST', $params, true);
    }

    /**
     * @param string $symbol
     * @param float $quantity
     * @param float $tpPrice
     * @param float $slPrice
     * @param float $slDiff
     * @return array
     * @throws Exception
     */
    public function buyOCO(string $symbol, float $quantity, float $tpPrice, float $slPrice, float $slDiff = 100): array
    {
        return $this->orderOCO('BUY', $symbol, $quantity, $tpPrice, $slPrice, $slDiff);
    }

    /**
     * @param string $symbol
     * @param float $quantity
     * @param float $tpPrice
     * @param float $slPrice
     * @param float $slDiff
     * @return array
     * @throws Exception
     */
    public function sellOCO(string $symbol, float $quantity, float $tpPrice, float $slPrice, float $slDiff = 100): array
    {
        return $this->orderOCO('SELL', $symbol, $quantity, $tpPrice, $slPrice, $slDiff);
    }

    /**
     * @param string $side
     * @param string $symbol
     * @param string $quantity
     * @param string $price
     * @param string $type
     * @param array $params
     * @param false $test
     * @return array
     * @throws Exception
     */
    public function order(string $side, string $symbol, $quantity, $price, string $type = "LIMIT", array $params = [], $test = false)
    {
        $params["symbol"] = $symbol;
        $params["side"] = $side;
        $params["type"] = $type;
        $params["quantity"] = $quantity;
        $params["recvWindow"] = 60000;
        if (gettype($price) !== "string") {
            $price = number_format($price, 8, '.', '');
        }
        if ($type === "LIMIT" || $type === "STOP_LOSS_LIMIT" || $type === "TAKE_PROFIT_LIMIT") {
            $params["price"] = $price;
            $params["timeInForce"] = "GTC";
        }
        if ($type === "MARKET" && isset($params['isQuoteOrder']) && $params['isQuoteOrder']) {
            unset($params['quantity']);
            $params['quoteOrderQty'] = $quantity;
        }
        return $this->httpRequest($test === false ? 'v3/order' : 'v3/order/test', 'POST', $params, true);
    }
}
