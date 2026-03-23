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

        // Sidebar links — registered via PackageRegistryService with auto permission checks
        if (!config('hexa.app_controls_sidebar', false)) {
            $registry = app(\hexa_core\Services\PackageRegistryService::class);
            $registry->registerSidebarLink('settings.openai', 'OpenAI', 'M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z', 'OpenAI', 'openai', 61);
        }

        // Settings card on /settings page
        $this->registerSettingsCard();
    }

    /**
     * Register settings card on the core settings page.
     *
     * @return void
     */
    private function registerSettingsCard(): void
    {
        view()->composer('settings.index', function ($view) {
            $view->getFactory()->startPush('settings-cards', view('openai::partials.settings-card')->render());
        });
    }
}
