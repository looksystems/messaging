<?php

namespace Look\Messaging\Laravel;

use Look\Messaging\Contracts\MessageBus as MessageBusContract;
use Illuminate\Support\ServiceProvider;

class MessagingServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(
            MessageBusContract::class,
            function ($app) {
                return (new MessageBus)
                    ->setContainer($app)
                    ->applyConfig(
                        config('look.messaging', [])
                    );
            }
        );

        $this->app->bind('look.message-bus', function ($app) {
            return $app[MessageBusContract::class];
        });
    }

    public function boot()
    {
        $this->bootConfig();
    }

    protected function bootConfig()
    {
        $this->mergeConfigFrom(
            $this->getPackagePath('config/messaging.php'),
            'look.messaging'
        );

        $this->publishes(
            [
                $this->getPackagePath('config/messaging.php') => config_path('look/messaging.php'),
            ],
            'config'
        );
    }

    public function getPackagePath($path = null): string
    {
        return __DIR__.'/'.$path;
    }
}
