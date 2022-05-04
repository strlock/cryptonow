<?php

namespace App\Http\Controllers;

use App\Enums\StrategySignal;
use App\Http\Resources\SignalsResource;
use App\Services\Crypto\Helpers\TimeHelper;
use App\Services\Strategy\StrategyInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SignalsController extends Controller
{
    public function __construct(private StrategyInterface $strategy)
    {
        //
    }

    public function index(string $symbol, int $fromTime, ?int $toTime = null, ?int $interval = TimeHelper::FIVE_MINUTE_MS): JsonResponse
    {
        $data = collect();
        foreach ($this->strategy->getSignals($symbol, $fromTime, $toTime, $interval) as $time => $signal) {
            /** @var StrategySignal $signal */
            $data->push([
                'time' => $time,
                'signal' => $signal->value(),
            ]);
        }
        return response()->json(new SignalsResource($data));
    }
}
