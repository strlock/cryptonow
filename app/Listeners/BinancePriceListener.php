<?php

namespace App\Listeners;

use App\Events\BinancePrice;
use App\Services\OrdersServiceInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class BinancePriceListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\BinancePrice  $event
     * @return void
     */
    public function handle(BinancePrice $event)
    {
    }
}
