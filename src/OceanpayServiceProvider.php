<?php

namespace Xditn\Oceanpay;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Xditn\Oceanpay\Actions\DatabaseConfigurationResolver;
use Xditn\Oceanpay\Contracts\ConfigurationResolvers;
use Xditn\Oceanpay\Contracts\Factory;
use Xditn\Oceanpay\Contracts\HandleDepositWebhooks;
use Xditn\Oceanpay\Contracts\HandleWithdrawalWebhooks;
use Xditn\Oceanpay\Providers\OceanpayProvider;
use Illuminate\Http\Request;

class OceanpayServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/oceanpay.php', 'oceanpay');

        $this->app->singleton(Factory::class, function (Application $app) {
            return new PaymentGatewayManager($app);
        });

        $this->app->singleton(ConfigurationResolvers::class, DatabaseConfigurationResolver::class);
        $this->app->singleton(HandleDepositWebhooks::class, fn () => new class implements HandleDepositWebhooks
        {
            public function handle(Request $request): bool
            {
                return true;
            }
        });
        $this->app->singleton(HandleWithdrawalWebhooks::class, fn () => new class implements HandleWithdrawalWebhooks
        {
            public function handle(Request $request): bool
            {
                return true;
            }
        });
    }

    public function boot(): void
    {
        Http::globalRequestMiddleware(fn ($request) => $request->withHeader(
            'User-Agent', config('app.name').'/'.app()->version()
        ));

        $this->registerOceanpayDriver();
        $this->configurePublishing();
        $this->configureRoutes();
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    protected function registerOceanpayDriver(): void
    {
        Facades\PaymentGateway::extend('oceanpay', function () {
            return new OceanpayProvider(Config::get('oceanpay.providers.oceanpay'));
        });
    }

    protected function configurePublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/oceanpay.php' => config_path('oceanpay.php'),
        ], 'oceanpay-config');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'oceanpay-migrations');

        $this->publishes([
            __DIR__.'/../stubs/HandleDepositWebhook.php' => app_path('Actions/PaymentGateway/HandleDepositWebhook.php'),
            __DIR__.'/../stubs/HandleWithdrawalWebhook.php' => app_path('Actions/PaymentGateway/HandleWithdrawalWebhook.php'),
        ], 'oceanpay-stubs');
    }

    protected function configureRoutes(): void
    {
        if (! PaymentGateway::$registersRoutes) {
            return;
        }

        Route::group([
            'domain' => config('oceanpay.domain'),
            'prefix' => config('oceanpay.prefix'),
            'middleware' => config('oceanpay.middleware', ['api']),
        ], function () {
            if (config('oceanpay.routes.webhook', true)) {
                $this->loadRoutesFrom(__DIR__.'/../routes/webhooks.php');
            }

            if (config('oceanpay.routes.admin', true)) {
                $this->loadRoutesFrom(__DIR__.'/../routes/admin.php');
            }
        });
    }
}
