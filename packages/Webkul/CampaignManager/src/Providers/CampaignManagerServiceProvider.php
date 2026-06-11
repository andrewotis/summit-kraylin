<?php

namespace Webkul\CampaignManager\Providers;

use Illuminate\Support\ServiceProvider;

class CampaignManagerServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->app->register(ModuleServiceProvider::class);
    }
}
