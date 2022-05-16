<?php

namespace App\Http\Controllers;

use App\Dto\MaxMarketDeltaDto;
use App\Enums\TimeInterval;
use App\Http\Resources\SeriesResource;
use App\Services\GetAggregateMarketStatService;
use App\Services\Strategy\StrategyInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MarketDeltaController extends Controller
{
    public function __construct(
        private GetAggregateMarketStatService $aggregateMarketDeltaService,
        private StrategyInterface $strategy
    )
    {
        //
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function index(Request $request, string $symbol, int $fromTime, ?int $toTime = null, ?int $interval = TimeInterval::FIVE_MINUTES)
    {
        $interval = TimeInterval::memberByValue($interval);
        $data = collect();
        $marketDeltaItems = $this->aggregateMarketDeltaService->getAggregateMarketDelta($symbol, $fromTime, $toTime, $interval);
        $marketDeltaItemKeys = $marketDeltaItems->keys();
        //Log::debug('MarketDelta times: '.$marketDeltaItemKeys->first().'-'.$marketDeltaItemKeys->last().' '.date('d.m.Y H:i:s', $marketDeltaItemKeys->first()/1000).'-'.date('d.m.Y H:i:s', $marketDeltaItemKeys->last()/1000));
        foreach ($marketDeltaItems as $time => $y) {
            $data->push([
                'x' => $time,
                'y' => $y,
            ]);
        }
        return response()->json(new SeriesResource($data));
    }

    /**
     * @param string $symbol
     * @return JsonResponse
     */
    public function getMdClusters(string $symbol): JsonResponse
    {
        $data = collect();
        /** @var MaxMarketDeltaDto $mdCluster */
        $mdCluster = $this->strategy->getMaxMarketDelta($symbol);
        $data->push([
            'fromTime' => $mdCluster->getFromTime(),
            'toTime' => $mdCluster->getToTime(),
            'marketDelta' => $mdCluster->getMarketDelta(),
            'fromPrice' => $mdCluster->getFromPrice(),
            'toPrice' => $mdCluster->getToPrice(),
            'fromTimeFormatted' => date('d.m.Y H:i:s', $mdCluster->getFromTime()/1000),
            'toTimeFormatted' => date('d.m.Y H:i:s', $mdCluster->getToTime()/1000),
        ]);
        return response()->json(['data' => $data]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
