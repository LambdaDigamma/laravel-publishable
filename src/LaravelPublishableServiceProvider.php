<?php

namespace LaravelPublishable;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\ServiceProvider;

class LaravelPublishableServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureMacros();
    }

    /**
     * Configure the macros to be used.
     *
     * @return void
     */
    protected function configureMacros()
    {
        Blueprint::macro('publishedAt', function ($column = 'published_at', $precision = 0) {
            return $this->timestamp($column, $precision)->nullable();
        });
        Blueprint::macro('expiredAt', function ($column = 'expired_at', $precision = 0) {
            return $this->timestamp($column, $precision)->nullable();
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
    }
}
