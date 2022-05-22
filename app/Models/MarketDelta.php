<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketDelta extends Model
{
    use HasFactory;

    protected $table = 'market_delta';

    protected $fillable = [
        'symbol',
        'exchange',
        'time',
        'value',
    ];
}
