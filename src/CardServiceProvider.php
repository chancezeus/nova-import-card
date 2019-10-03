<?php

namespace Sparclex\NovaImportCard;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Nova;

class CardServiceProvider extends ServiceProvider
{
    /**
     * Get the translation keys from file.
     *
     * @return array
     */
    private static function getTranslations(): array
    {
        $translationFile = resource_path(sprintf(
            "lang/vendor/%s/%s.json",
            NovaImportCard::$name,
            App::getLocale()
        ));

        if (!is_readable($translationFile)) {
            $translationFile = sprintf("%s/../resources/lang/%s.json", __DIR__, App::getLocale());

            if (!is_readable($translationFile)) {
                return [];
            }
        }

        return json_decode(file_get_contents($translationFile), true);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->booted(function () {
            $this->routes();
        });

        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__ . '/config.php' => config_path(sprintf("%s.php", NovaImportCard::$name))]);
        }

        Nova::serving(function () {
            Nova::script(NovaImportCard::$name, __DIR__ . '/../dist/js/card.js');
            Nova::style(NovaImportCard::$name, __DIR__ . '/../dist/css/card.css');
            Nova::translations(static::getTranslations());
        });
    }

    /**
     * Register the card's routes.
     *
     * @return void
     */
    protected function routes()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware(['nova'])
            ->prefix(sprintf("nova-vendor/sparclex/%s", NovaImportCard::$name))
            ->group(__DIR__ . '/../routes/api.php');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config.php', NovaImportCard::$name);
    }
}
