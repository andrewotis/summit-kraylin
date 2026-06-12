<?php

namespace Webkul\Marketing\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Webkul\Marketing\Console\Commands\CampaignCommand;

class MarketingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'marketing');

        $this->loadRoutes();

        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command('campaign:process')->daily();
        });
    }

    public function register(): void
    {
        $this->registerCommands();

        $this->app->register(ModuleServiceProvider::class);
    }

    private function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CampaignCommand::class,
            ]);
        }
    }

    private function loadRoutes(): void
    {
        Route::middleware('web')
            ->group(__DIR__.'/../Routes/web.php');
    }
}
