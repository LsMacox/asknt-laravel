<?php

namespace App\Providers;

use App\Models\LoadingZone;
use App\Models\LoadingZoneShipment;
use App\Models\RetailOutlet;
use App\Models\ShipmentList\Shipment;
use App\Models\ShipmentList\ShipmentRetailOutlet;
use App\Models\ShipmentList\ShipmentShipmentRetailOutlet;
use App\Models\Wialon\WialonGeofence;
use App\Models\Wialon\WialonNotification;
use App\Observers\LoadingZoneObserver;
use App\Observers\LoadingZoneShipmentObserver;
use App\Observers\RetailOutletObserver;
use App\Observers\ShipmentObserver;
use App\Observers\ShipmentRetailOutletObserver;
use App\Observers\ShipmentShipmentRetailOutletObserver;
use App\Observers\WialonGeofenceObserver;
use App\Observers\WialonNotificationObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Shipment::observe(ShipmentObserver::class);
        ShipmentRetailOutlet::observe(ShipmentRetailOutletObserver::class);
        LoadingZone::observe(LoadingZoneObserver::class);
        RetailOutlet::observe(RetailOutletObserver::class);
        ShipmentShipmentRetailOutlet::observe(ShipmentShipmentRetailOutletObserver::class);
        LoadingZoneShipment::observe(LoadingZoneShipmentObserver::class);
    }
}
