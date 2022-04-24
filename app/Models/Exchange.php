<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Exchange
 * @package App\Models
 */
class Exchange extends Model{
    use HasFactory;

    /**
     * @var string[]
     */
    protected $fillable = [
        'from',
        'to',
        'fromamount',
        'toamount',
    ];
}
