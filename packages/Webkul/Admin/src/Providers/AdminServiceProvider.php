<?php

namespace Webkul\Admin\Providers;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Webkul\Admin\Bouncer;
use Webkul\Admin\Exceptions\Handler;
use Webkul\Admin\Http\Controllers\User\EmergencyAccessController;
use Webkul\Admin\Http\Middleware\Bouncer as BouncerMiddleware;
use Webkul\Admin\Http\Middleware\ForceTls;
use Webkul\Admin\Http\Middleware\IdleTimeout;
use Webkul\Admin\Http\Middleware\Locale;
use Webkul\Admin\Http\Middleware\MaxSessionLifetime;
use Webkul\Admin\Http\Middleware\RequiresTwoFactor;
use Webkul\Admin\Http\Middleware\SanitizeUrl;
use Webkul\Admin\Http\Middleware\SecurityHeaders;
use Webkul\Contact\Models\Organization;
use Webkul\Contact\Models\Person;
use Webkul\Lead\Models\Lead;
use Webkul\Product\Models\Product;
use Webkul\Quote\Models\Quote;
use Webkul\Warehouse\Models\Warehouse;

class AdminServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(Router $router): void
    {
        $router->aliasMiddleware('user', BouncerMiddleware::class);

        $router->aliasMiddleware('admin_locale', Locale::class);

        $router->aliasMiddleware('sanitize_url', SanitizeUrl::class);

        $router->aliasMiddleware('two-factor', RequiresTwoFactor::class);

        $router->aliasMiddleware('idle-timeout', IdleTimeout::class);

        $router->aliasMiddleware('force-tls', ForceTls::class);

        $router->aliasMiddleware('max-session-lifetime', MaxSessionLifetime::class);

        $router->aliasMiddleware('security-headers', SecurityHeaders::class);

        include __DIR__.'/../Http/helpers.php';

        Route::middleware(['web', 'admin_locale', 'force-tls', 'security-headers', 'user', 'max-session-lifetime', 'idle-timeout', 'two-factor'])
            ->prefix(config('app.admin_path'))
            ->group(__DIR__.'/../Routes/Admin/web.php');

        Route::middleware(['web', 'admin_locale', 'security-headers', 'user'])
            ->prefix(config('app.admin_path'))
            ->group(function () {
                Route::get('emergency/grant', [EmergencyAccessController::class, 'grantForm'])->name('admin.emergency.grant.form');
                Route::post('emergency/grant', [EmergencyAccessController::class, 'grant'])->name('admin.emergency.grant');
            });

        Route::middleware(['web', 'admin_locale', 'security-headers'])
            ->prefix(config('app.admin_path'))
            ->group(function () {
                Route::get('emergency/access', [EmergencyAccessController::class, 'accessForm'])->name('admin.emergency.access.form');
                Route::post('emergency/access', [EmergencyAccessController::class, 'access'])->name('admin.emergency.access');
            });

        Route::middleware(['web', 'admin_locale'])
            ->group(__DIR__.'/../Routes/Front/web.php');

        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'admin');

        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'admin');

        Blade::anonymousComponentPath(__DIR__.'/../Resources/views/components', 'admin');

        $this->app->bind(ExceptionHandler::class, Handler::class);

        Relation::morphMap([
            'leads' => Lead::class,
            'organizations' => Organization::class,
            'persons' => Person::class,
            'products' => Product::class,
            'quotes' => Quote::class,
            'warehouses' => Warehouse::class,
        ]);

        $this->app->register(EventServiceProvider::class);
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerFacades();

        $this->registerConfig();
    }

    /**
     * Register Bouncer as a singleton.
     */
    protected function registerFacades(): void
    {
        $loader = AliasLoader::getInstance();

        $loader->alias('Bouncer', \Webkul\Admin\Facades\Bouncer::class);

        $this->app->singleton('bouncer', function () {
            return new Bouncer;
        });
    }

    /**
     * Register package config.
     */
    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(dirname(__DIR__).'/Config/acl.php', 'acl');

        $this->mergeConfigFrom(dirname(__DIR__).'/Config/menu.php', 'menu.admin');

        $this->mergeConfigFrom(dirname(__DIR__).'/Config/core_config.php', 'core_config');

        $this->mergeConfigFrom(dirname(__DIR__).'/Config/attribute_lookups.php', 'attribute_lookups');

        $this->mergeConfigFrom(dirname(__DIR__).'/Config/attribute_entity_types.php', 'attribute_entity_types');

        $this->mergeConfigFrom(dirname(__DIR__).'/Config/support.php', 'support');
    }
}
