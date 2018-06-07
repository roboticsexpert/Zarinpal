<?php

namespace Roboticsexpert\Zarinpal;

use Illuminate\Support\ServiceProvider;


class ZarinpalServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/zarinpal.php' => config_path('zarinpal.php'),
        ]);
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        if (empty(config('zarinpal.merchantId')))
            throw new \UnexpectedValueException('you should fill merchant id for zarinpal');

        $this->app->singleton(ZarinpalInterface::class, function ($app) {
            if (config('zarinpal.sandbox', true)) {
                //provide sandbox
                return new ZarinpalSandbox(
                    config('zarinpal.merchantId'),
                    config('zarinpal.serverLocatedInIran', true)
                );
            }
            //provide main zarinpal
            return new Zarinpal(
                config('zarinpal.merchantId'),
                config('zarinpal.serverLocatedInIran', true)
            );
        });
    }
}
