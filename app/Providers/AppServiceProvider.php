<?php

namespace App\Providers;

use App\Models\Exchange;
use App\Services\OrdersService;
use App\Services\OrdersServiceInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use App\Services\Crypto\Exchanges\FacadeInterface as ExchangeInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(OrdersServiceInterface::class, OrdersService::class);
        $this->app->bind(ExchangeInterface::class, function ($app, $parameters) {
            $class = 'App\\Services\\Crypto\\Exchanges\\'.ucfirst($parameters['name']).'\\Facade';
            return new $class();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
