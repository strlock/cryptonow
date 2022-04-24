<?php
namespace App\Crypto\Exchanges\Binance;

use Binance\API as BinanceAPI;

class API extends BinanceAPI
{
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

}
