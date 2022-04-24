<?php

namespace App\Http\Controllers;

use App\Crypto\Exchanges\Factory;
use App\Crypto\Helpers\TimeHelper;
use App\Http\Resources\SeriesResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PriceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, string $symbol, int $fromTime, ?int $toTime = null, ?int $interval = TimeHelper::FIVE_MINUTE_MS)
    {
        $data = collect();
        $exchange = Factory::create('binance');
        foreach ($exchange->getCandlesticks($symbol, $fromTime, $toTime, $interval) as $time => $candlestickData) {
            $data->push([
                'x' => $time,
                'y' => $candlestickData,
            ]);
        }
        return response()->json(new SeriesResource($data));
    }
}
