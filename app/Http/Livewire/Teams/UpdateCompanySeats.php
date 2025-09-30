<?php

namespace App\Http\Livewire\Teams;

use Illuminate\Support\Facades\Auth;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Exceptions\IncompletePayment;
use Livewire\Component;
use Livewire\WithFileUploads;
use Stripe\Invoice as StripeInvoice;
use Stripe\Stripe;
use WireUi\Traits\Actions;

class UpdateCompanySeats extends Component
{
    use WithFileUploads;
    use Actions;

    /**
     * The team instance.
     *
     * @var mixed
     */
    public $team;

    /**
     * The component's state.
     *
     * @var array
     */
    public $state = [];

    public $qty;

    public $newQty;

    public $planPrice = [];

    public $proRataPrice;

    /**
     * Mount the component.
     *
     * @param  mixed  $team
     * @return void
     */
    public function mount($team)
    {
        $this->team = $team;

        $this->state = $team->withoutRelations()->toArray();

        $this->qty = auth()->user()->currentTeam->subscriptions()->active()->first()->quantity ?? 0;
        $this->newQty = auth()->user()->currentTeam->subscriptions()->active()->first()->quantity ?? 0;

        $subscription = Cashier::stripe()->subscriptions->retrieve(auth()->user()->currentTeam->subscriptions()->first()->stripe_id);

        // See what the next invoice would look like with a price switch
        // and proration set:
        $items = [
            [
                'id' => $subscription->items->data[0]->id,
                'price' => auth()->user()->currentTeam->subscriptions()->first()->stripe_price, // Switch to new price
            ],
        ];
        $this->planPrice = $this->fetchPrice(auth()->user()->currentTeam->subscription('default')->stripe_price)->toArray();
    }

    /**
     * Get the current user of the application.
     *
     * @return mixed
     */
    public function getUserProperty()
    {
        return Auth::user();
    }

    public function updateSeats()
    {
        try {
            $addSeats = $this->newQty - $this->qty;
            $this->getUserProperty()->currentTeam->addSeat($addSeats);

            //$this->getUserProperty()->currentTeam->subscriptions()->active()->first()->updateQuantity($this->newQty);
            //$this->getUserProperty()->currentTeam->addSeat();
            $team = auth()->user()->currentTeam;
            $team->seats = $this->newQty;
            $team->save();
        } catch (IncompletePayment $exception) {
            return redirect()->route(
                'cashier.payment',
                [$exception->payment->id, 'redirect' => route('spark.portal')]
            );
        }

        $this->qty = $this->newQty;

        $this->notification()->success(
            $title = 'Seats Updated',
            $description = 'Your seats have been added and your card has been charged.'
        );

        //dd("TEST");
    }

    public function addSeats()
    {
        try {
            $this->getUserProperty()->currentTeam->addSeat();
            $team = auth()->user()->currentTeam;
            $team->seats = $team->seats + 1;
            $team->save();
        } catch (IncompletePayment $exception) {
            return redirect()->route(
                'cashier.payment',
                [$exception->payment->id, 'redirect' => route('spark.portal')]
            );
        }
        //dd("TEST");
    }

    public function removeSeats()
    {
        try {
            $this->getUserProperty()->currentTeam->removeSeat();
            $team = auth()->user()->currentTeam;
            $team->seats = $team->seats - 1;
            $team->save();
        } catch (IncompletePayment $exception) {
            return redirect()->route(
                'cashier.payment',
                [$exception->payment->id, 'redirect' => route('spark.portal')]
            );
        }
        //dd("TEST");
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('teams.update-company-seats');
    }

    public function fetchPrice($price)
    {
        //$plan = Cashier::stripe()->prices->all(['plan'=> $price ]);
        //dd($plan);
        // return Cashier::stripe()->subscriptions->all(['id' => 'sub_1Mrf0bC9VFYfAKG7CYY0QHth']);

        return Cashier::stripe()->prices->retrieve($price);
    }

    private function previewProrate(string $plan_id, $team)
    {
        $proration_date = time();
        $subscription = $team->subscription('default')->asStripeSubscription();

        $items = [
            [
                'id' => $subscription->items->data[0]->id,
                'plan' => $plan_id,
            ],
        ];

        $invoice = StripeInvoice::upcoming([
            'customer' => $subscription->customer,
            'subscription' => $subscription->id,
            'subscription_items' => $items,
            'subscription_proration_date' => $proration_date,
            'subscription_proration_behavior' => 'always_invoice',
            'subscription_billing_cycle_anchor' => 'now',
        ], ['api_key' => config('cashier.secret')]);

        $cost = 0;
        $current_prorations = [];
        foreach ($invoice->lines->data as $line) {
            if ($line->period->start == $proration_date) {
                array_push($current_prorations, $line);
                $cost += $line->amount;
            }
        }

        return $cost;
        //return number_format((float)$cost, 2, '.', '');
    }
}
