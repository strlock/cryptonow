<?php

namespace App\Http\Controllers;

use App\Services\Crypto\Exchanges\Aggregate\Facade as AggregateExchangeFacade;
use App\Services\Crypto\Exchanges\FacadeInterface;
use App\Services\Crypto\Helpers\TimeHelper;
use App\Http\Resources\SeriesResource;
use App\Services\GetAggregateMarketStatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Redis;

class MarketDeltaController extends Controller
{
    public function __construct(private GetAggregateMarketStatService $aggregateMarketDeltaService)
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, string $symbol, int $fromTime, ?int $toTime = null, ?int $interval = TimeHelper::FIVE_MINUTE_MS)
    {
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
