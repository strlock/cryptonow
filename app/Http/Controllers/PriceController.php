<?php

namespace App\Http\Controllers;

use App\Enums\TimeInterval;
use App\Services\Crypto\Exchanges\Factory;
use App\Http\Resources\SeriesResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PriceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, string $symbol, int $fromTime, ?int $toTime = null, ?int $interval = null)
    {
        $interval = TimeInterval::memberByValue($interval);
        $data = collect();
        $exchange = Factory::create('binance');
        $exchangeSymbol = $exchange->getExchangeSymbol($symbol);
        $candlesticks = $exchange->getCandlesticks($exchangeSymbol, $fromTime, $toTime, $interval);
        $candlestickKeys = $candlesticks->keys();
        //Log::debug('Price times: '.$candlestickKeys->first().'-'.$candlestickKeys->last().' '.date('d.m.Y H:i:s', $candlestickKeys->first()/1000).'-'.date('d.m.Y H:i:s', $candlestickKeys->last()/1000));
        foreach ($candlesticks as $time => $candlestickData) {
            $data->push([
                'x' => $time,
                'y' => $candlestickData,
            ]);
        }
        return response()->json(new SeriesResource($data));
    }
}
