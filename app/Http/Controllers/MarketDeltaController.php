<?php

namespace App\Http\Controllers;

use App\Dto\MarketDeltaClusterDto;
use App\Enums\TimeInterval;
use App\Http\Resources\SeriesResource;
use App\Services\GetAggregateMarketStatService;
use App\Services\Strategy\StrategyInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        foreach ($this->aggregateMarketDeltaService->getAggregateMarketDelta($symbol, $fromTime, $toTime, $interval) as $time => $y) {
            $data->push([
                'x' => $time,
                'y' => $y,
            ]);
        }
        return response()->json(new SeriesResource($data));
    }

    /**
     * @param string $symbol
     * @param int|null $toTime
     * @param int|null $interval
     * @return JsonResponse
     * @throws \Exception
     */
    public function getMdClusters(string $symbol, ?int $toTime = null, ?int $interval = TimeInterval::FIVE_MINUTES): JsonResponse
    {
        $interval = TimeInterval::memberByValue($interval);
        $data = collect();
        foreach ($this->strategy->getMarketDeltaClusters($symbol, $toTime, $interval) as $mdCluster) {
            /** @var MarketDeltaClusterDto $mdCluster */
            $data->push([
                'fromTime' => $mdCluster->getFromTime(),
                'toTime' => $mdCluster->getToTime(),
                'marketDelta' => $mdCluster->getMarketDelta(),
            ]);
        }
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
