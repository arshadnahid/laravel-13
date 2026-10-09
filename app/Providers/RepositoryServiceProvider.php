<?php

namespace App\Providers;

use App\Repositories\Auth\AuthInterfaces\AuthInterface;
use App\Repositories\Auth\AuthRepository;
use App\Repositories\Store\StoreInterface\StoreInterface;
use App\Repositories\Store\StoreRepository;
use App\Repositories\Supplier\SupplierInterface\SupplierInterface;
use App\Repositories\Supplier\SupplierRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{

    /**
     * Interface => implementation. To change storage, write a new class
     * for the interface and swap it here.
     */
    public array $bindings = [
        AuthInterface::class => AuthRepository::class,
        SupplierInterface::class => SupplierRepository::class,
        StoreInterface::class => StoreRepository::class,
    ];

    /**
     * Register services.
     */
    public function register(): void {}

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
