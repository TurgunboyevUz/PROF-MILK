<?php

namespace App\Providers;

use App\Exceptions\PaymeExceptionHandler;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            ExceptionHandler::class,
            PaymeExceptionHandler::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
              
    }
}
