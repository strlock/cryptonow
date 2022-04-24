<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Exchange as ExchangeModel;
use App\Http\Requests\ExchangeRequest;

class ExchangeController extends Controller{
    public function save(ExchangeRequest $request){
        $exchange = new ExchangeModel();
        $exchange->fill([
            'from' => $request->post('from'),
            'to' => $request->post('to'),
            'fromamount' => $request->post('fromamount'),
            'toamount' => $request->post('toamount'),
        ]);
        $exchange->save();
        return response()->json(['success' => true], 200);
    }

    public function exchanges(Request $request){
        return view('exchanges', ['exchanges' => ExchangeModel::all()]);
    }

    public function exchange(Request $request, $exchangeId){
        return view('exchangeShow', ['data' => ExchangeModel::find($exchangeId)]);
    }
}
