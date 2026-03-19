<?php

namespace hexa_package_openai\Providers;

use Illuminate\Support\ServiceProvider;
use hexa_package_openai\Services\WhisperService;

/**
 * OpenaiServiceProvider -- registers OpenAI package services, routes, views, and migrations.
 */
class OpenaiServiceProvider extends ServiceProvider
{
    /**
     * Register services into the container.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/openai.php', 'openai');

        $this->app->singleton(WhisperService::class, function ($app) {
            return new WhisperService();
        });
    }

    /**
     * Bootstrap package resources.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../../routes/openai.php');
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'openai');
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->registerSidebarItems();
    }

    /**
     * Push sidebar menu items and settings card into the core layout stacks.
     *
     * @return void
     */
    private function registerSidebarItems(): void
    {
        view()->composer('layouts.app', function ($view) {
            if (config('hexa.app_controls_sidebar', false)) return;
            $view->getFactory()->startPush('sidebar-menu', view('openai::partials.sidebar-menu')->render());
        });

        view()->composer('settings.index', function ($view) {
            $view->getFactory()->startPush('settings-cards', view('openai::partials.settings-card')->render());
        });
    }
}
