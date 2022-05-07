<?php

use App\Enums\TimeInterval;

return [
    'dateFormat' => env('DATE_FORMAT', 'd.m.Y H:i:s'),
    'strategyMinMdCluster' => 100,
    'strategyMdClusterDomination' => 5,
    'strategyPeriod' => TimeInterval::TWELVE_HOURS,
];
