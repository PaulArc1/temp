<?php

namespace App\Http\Livewire;

use Laravel\Cashier\Cashier;
use Livewire\Component;

class PricingTable extends Component
{
    public $plans;

    public $qty = 1;

    public function mount()
    {
        //$prices = Cashier::stripe()->prices->retrieve('price_1MgTsnC9VFYfAKG7DyLREHCV');

        //dd($prices);

        $plans = config('spark.billables.team.plans');

        foreach ($plans as &$plan) {
            $plan['price'] = Cashier::stripe()->prices->retrieve($plan['yearly_id'])->unit_amount / 100;
        }

        $this->plans = collect($plans)->sortBy('price');
    }

    public function render()
    {
        return view('livewire.pricing-table');
    }
}
