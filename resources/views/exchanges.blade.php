@extends('page')

@section('content')
    <div class="container">
        <div class="row">
            @foreach($exchanges as $exchange)
                <div class="alert alert-info">
                    {{$exchange->from}} - {{$exchange->to}}, {{$exchange->fromamount}} - {{$exchange->toamount}}
                </div>
            @endforeach
        </div>
    </div>
@endsection
