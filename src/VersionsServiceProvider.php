<?php

namespace VildanBina\LaravelVersions;

use Illuminate\Support\ServiceProvider;
use VildanBina\LaravelVersions\Macros\VersionsBlueprintMacros;

class VersionsServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     */
    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/versions.php', 'drafts');
    }

    public function register(): void
    {
        $this->app->singleton(LaravelVersions::class, fn () => new LaravelVersions);
        VersionsBlueprintMacros::register();
    }

    /**
     * @return string[]
     */
    public function provides(): array
    {
        return [
            'laravel-versions',
        ];
    }
}
