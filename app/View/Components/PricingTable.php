<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Laravel\Cashier\Cashier;

class PricingTable extends Component
{
    /**
     * Create a new component instance.
     */
    public $plans;

    public function __construct()
    {
        //$prices = Cashier::stripe()->prices->retrieve('price_1MgTsnC9VFYfAKG7DyLREHCV');

        //dd($prices);

        $plans = config('spark.billables.team.plans');

        foreach ($plans as &$plan) {
            $plan['price'] = Cashier::stripe()->prices->retrieve($plan['yearly_id'])->unit_amount / 100;
        }

        $this->plans = collect($plans)->sortBy('price');

        //$this->plans = $plans;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.pricing-table');
    }
}
