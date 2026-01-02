<?php

namespace DenizGolbas\LaravelEqualityValidation;

use Illuminate\Support\ServiceProvider;

class EqualityValidationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/equality-validation.php' => config_path('equality-validation.php'),
            ], 'equality-validation-config');

            $this->publishes([
                __DIR__ . '/../lang' => lang_path('vendor/equality-validation'),
            ], 'equality-validation-lang');
        }

        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'equality-validation');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/equality-validation.php',
            'equality-validation'
        );
    }
}

