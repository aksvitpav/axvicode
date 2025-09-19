<?php

namespace App\Providers\Filament;

use AbdulmajeedJamaan\FilamentTranslatableTabs\TranslatableTabs;
use Illuminate\Support\ServiceProvider;

class LairPluginsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        TranslatableTabs::configureUsing(function (TranslatableTabs $component) {
            $locales = config('app.available_locales');
            $localesWithLabels = collect($locales)
                ->mapWithKeys(fn($locale, $key) => [$locale => strtoupper($locale)])
                ->toArray();

            $component
                ->localesLabels($localesWithLabels)
                ->locales($locales);
        });
    }
}
