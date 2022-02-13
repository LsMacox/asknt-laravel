<?php

namespace App\Providers;

use App\Services\Wialon\WialonResource;
use Illuminate\Support\ServiceProvider;
use App\Services\Wialon\Wialon;

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
    }
}
