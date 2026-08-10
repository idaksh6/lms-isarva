<?php

namespace App\Providers;

use App\Support\AiSettings;
use App\Support\LmsTheme;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        AiSettings::applyToConfig();

        View::composer(['layouts.lms', 'layouts.guest'], function ($view): void {
            $themeKey = Auth::check() ? Auth::user()->theme : LmsTheme::defaultKey();

            $view->with('lmsTheme', LmsTheme::resolve($themeKey));
            $view->with('lmsThemes', LmsTheme::all());
        });
    }
}
