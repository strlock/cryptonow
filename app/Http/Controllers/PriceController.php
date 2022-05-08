<?php

namespace App\Http\Controllers;

use App\Enums\TimeInterval;
use App\Services\Crypto\Exchanges\Factory;
use App\Helpers\TimeHelper;
use App\Http\Resources\SeriesResource;
use Illuminate\Http\Request;

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
        foreach ($exchange->getCandlesticks($exchangeSymbol, $fromTime, $toTime, $interval) as $time => $candlestickData) {
            $data->push([
                'x' => $time,
                'y' => $candlestickData,
            ]);
        }
        return response()->json(new SeriesResource($data));
    }
}
