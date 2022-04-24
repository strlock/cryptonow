<?php
namespace App\Crypto\Exchanges\Bitfinex;

use BFX\RESTv2 as BitfinexAPI;
use Illuminate\Support\Facades\Log;

class API extends BitfinexAPI
{
    private const SYMBOL_MAP = [
        'BTCUSDT' => 'tBTCUSD',
    ];

    /**
     * aggTrades get Market History / Aggregate Trades
     *
     * $trades = $api->aggTrades("BNBBTC");
     *
     * @param $symbol string the symbol to get the trade information for
     * @return array with error message or array of market history
     * @throws \Exception
     */
    public function trades(string $symbol, $startTime = null, $endTime = null, $limit = null, $fromId = null): array
    {
        $params = ['sort' => -1];
        if (!empty($startTime)) {
            $params['start'] = $startTime;
        }
        if (!empty($endTime)) {
            $params['end'] = $endTime;
        }
        if (!empty($limit)) {
            $params['limit'] = $limit;
        } else {
            $params['limit'] = 10000;
        }
        if (!empty($fromId)) {
            $params['fromId'] = $fromId;
        }
        $symbol = self::SYMBOL_MAP[$symbol];
        $i = 0;
        $response = [];
        while ($i < 100) {
            try {
                $response = $this->makePublicRequest('/v2/trades/' . $symbol . '/hist?' . http_build_query($params));
                break;
            } catch (\Throwable $e) {
                $sleepTime = rand(1,10)*3;
                echo $e->getMessage().PHP_EOL;
                echo 'BITFINEX: Sleeping for '.$sleepTime.' seconds'.PHP_EOL;
                Log::debug('BITFINEX: Sleeping for '.$sleepTime.' seconds');
                sleep($sleepTime);
                $i++;
            }
        }
        return $response;
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
