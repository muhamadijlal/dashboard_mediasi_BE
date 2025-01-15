<?php

namespace App\Providers;

use App\Interfaces\RekapDataInterface;
use App\Repositories\DBRepository;
use App\Repositories\JMTORepository;
use App\Repositories\MIYRepository;
use App\Repositories\MMSRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
       $this->app->bind(RekapDataInterface::class, MIYRepository::class);
       $this->app->bind(RekapDataInterface::class, MMSRepository::class);
       $this->app->bind(RekapDataInterface::class, DBRepository::class);
       $this->app->bind(RekapDataInterface::class, JMTORepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
