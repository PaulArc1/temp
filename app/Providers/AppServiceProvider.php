<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (! app()->runningInConsole()) {
            /*if (!str_contains(url()->current(), '/spark/webhook')) {

                try {
                    $team = \App\Models\Team::where('hostname', request()->getHost())->firstOrFail();
                } catch (ModelNotFoundException $e) {
                    abort(404);
                }

                view()->composer('*', function ($view) {
                    try {
                        app('url')->forceRootUrl('https://' . auth()->user()->currentTeam->hostname);
                    } catch (\Exception $e) {
                        return redirect(config('app.url'));
                    }
                });
            }*/
        }
    }
}
