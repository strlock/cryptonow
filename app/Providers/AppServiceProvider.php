<?php

namespace App\Providers;

use App\Services\OrdersService;
use App\Services\OrdersServiceInterface;
use App\Services\Strategy\AnomalousMarketDeltaBuyStrategy;
use App\Services\Strategy\StrategyInterface;
use Illuminate\Support\ServiceProvider;
use App\Services\Crypto\Exchanges\FactoryInterface as ExchangesFactoryInterface;
use App\Services\Crypto\Exchanges\Factory as ExchangesFactory;

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
        /*$this->app->bind(ExchangeInterface::class, function ($app, $parameters) {
            $class = 'App\\Services\\Crypto\\Exchanges\\'.ucfirst($parameters['name']).'\\Facade';
            return new $class();
        });*/
        $this->app->bind(StrategyInterface::class, AnomalousMarketDeltaBuyStrategy::class);
        $this->app->bind(ExchangesFactoryInterface::class, ExchangesFactory::class);
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
