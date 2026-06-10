<?php

namespace Webkul\MailingList\Providers;

use Illuminate\Support\ServiceProvider;

class MailingListServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }
}
