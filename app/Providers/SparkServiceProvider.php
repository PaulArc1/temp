<?php

namespace App\Providers;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;
use Spark\Plan;
use Spark\Spark;

class SparkServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Cashier::useCustomerModel(Team::class);

        // Resolve the current team...
        Spark::billable(Team::class)->resolve(function (Request $request) {
            return $request->user()->currentTeam;
        });

        // Verify that the current user owns the team...
        Spark::billable(Team::class)->authorize(function (Team $billable, Request $request) {
            return $request->user() &&
                $request->user()->id == $billable->user_id;
        });

        Spark::billable(Team::class)->checkPlanEligibility(function (Team $billable, Plan $plan) {
            // ...
        });

        //Spark::billable(Team::class)->chargePerSeat('user', function ($billable) {
        //  ray("TEST", request()->get('qty'));
        //return 3;
        //});

        Spark::billable(Team::class)->chargePerSeat('user', function ($billable) {
            return $billable->seats;
            //return $billable->users()->count();
        });
    }

    public function register()
    {
        Spark::ignoreMigrations();
    }
}
