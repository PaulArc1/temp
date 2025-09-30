<?php

namespace App\Http\Middleware;

use Inertia\Inertia;
use Spark\GuessesBillableTypes;
use Spark\Http\Middleware\VerifyBillableIsSubscribed;

class VerifyIsSubscribed extends VerifyBillableIsSubscribed
{
    use GuessesBillableTypes;

    /**
     * Verify the incoming request's user has a subscription.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $billableType
     * @param  string  $plan
     * @return \Illuminate\Http\Response
     */
    public function handle($request, $next, $billableType = null, $plan = null)
    {
        return $next($request);

        if (auth()->user()->hasRole('Admin')) {
            return $next($request);
        }

        $billableType = $billableType ?: $this->guessBillableType($billableType);

        if ($this->subscribed($request, $billableType, $plan)) {
            return $next($request);
        }

        $redirect = $this->redirect($billableType);

        if ($request->header('X-Inertia')) {
            return Inertia::location($redirect);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response('Subscription Required.', 402);
        }

        return redirect($redirect);
    }
}
