<?php

namespace MomoLog\Laravel;

use Illuminate\Support\ServiceProvider;
use MomoLog\MomoLog;

/**
 * Laravel Service Provider for MomoLog
 */
class MomoLogServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/momolog.php',
            'momolog'
        );

        $this->app->singleton('momolog', function ($app) {
            $config = $app['config']['momolog'];
            MomoLog::configure($config);
            return new MomoLog();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/momolog.php' => config_path('momolog.php'),
            ], 'momolog-config');
        }

        // Configure MomoLog with Laravel config
        $config = $this->app['config']['momolog'];
        MomoLog::configure($config);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return ['momolog'];
    }
}
