<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;
use Whitecube\NovaFlexibleContent\Flexible;
use Whitecube\NovaFlexibleContent\Value\FlexibleCast;

class NovaServiceProvider extends NovaApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        \Outl1ne\NovaSettings\NovaSettings::addSettingsFields([
            File::make('Logo', 'logo')->disk('public'),
            //Text::make('Name', 'name'),

            Flexible::make('Equipment')
                ->addLayout('Equipment', 'equipment', [
                    Text::make('Name'),
                ])->button('Add Equipment'),

            Flexible::make('Equipment ID')
                ->addLayout('Equipment ID', 'equipment_id', [
                    Text::make('ID'),
                ])->button('Add Equipment ID'),

            Flexible::make('Features')
                ->addLayout('Feature', 'features', [
                    Text::make('Name'),
                ])->button('Add Feature'),

        ], [
            'equipment' => FlexibleCast::class,
            'equipment_id' => FlexibleCast::class,
            'features' => FlexibleCast::class,
        ]);

        parent::boot();
    }

    /**
     * Register the Nova routes.
     *
     * @return void
     */
    protected function routes()
    {
        Nova::routes()
                ->withAuthenticationRoutes()
                ->withPasswordResetRoutes()
                ->register();
    }

    /**
     * Register the Nova gate.
     *
     * This gate determines who can access Nova in non-local environments.
     *
     * @return void
     */
    protected function gate()
    {
        Gate::define('viewNova', function ($user) {
            return $user->hasRole('Admin') ? true : null;
        });
    }

    /**
     * Get the dashboards that should be listed in the Nova sidebar.
     *
     * @return array
     */
    protected function dashboards()
    {
        return [
            new \App\Nova\Dashboards\Main,
        ];
    }

    /**
     * Get the tools that should be listed in the Nova sidebar.
     *
     * @return array
     */
    public function tools()
    {
        return [
            \Vyuldashev\NovaPermission\NovaPermissionTool::make(),
            new \Outl1ne\NovaSettings\NovaSettings,
            new \Tighten\NovaStripe\NovaStripe,
        ];
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
