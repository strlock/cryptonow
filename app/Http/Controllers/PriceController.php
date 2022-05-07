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
    public function index(Request $request, string $symbol, int $fromTime, ?int $toTime = null, ?TimeInterval $interval = null)
    {
        if (empty($interval)) {
            $interval = TimeInterval::FIVE_MINUTES();
        }
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
