<?php

use App\Enums\TimeInterval;

return [
    'defaultExchange' => env('DEFAULT_EXCHANGE', ''),
    'dateFormat' => env('DATE_FORMAT', 'd.m.Y H:i:s'),
    'strategyPeriod' => TimeInterval::SIX_HOURS,
    'strategyRelativePriceDiffPercent' => 1.5,
    'exchangesTestmode' => env('EXCHANGES_TESTMODE', ''),
];
