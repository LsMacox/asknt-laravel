<?php

namespace App\Providers;

use App\Services\Wialon\WialonResource;
use Illuminate\Support\ServiceProvider;
use App\Services\Wialon\Wialon;
use App\Services\ShipmentDataService;

class BinderServiceProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     *
     * @var array
     */
    public $bindings = [
    ];

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
//        $this->app->singleton('wialon', function ($app) {
//            return $app->make(Wialon::class);
//        });
        $this->app->instance('wialon', $this->app->make(Wialon::class));
        $this->app->instance('wialon-resource', $this->app->make(WialonResource::class));
        $this->app->instance('shipment-data-service', $this->app->make(ShipmentDataService::class));
    }
}
