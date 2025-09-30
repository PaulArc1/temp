<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyDomain
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            if (auth()->check()) {
                $team = \App\Models\Team::where('hostname', $request->getHost())->firstOrFail();
                if (! auth()->user()->currentTeam) {
                    auth()->user()->switchTeam($team);
                }
                if ($team?->id != auth()->user()->currentTeam?->id) {
                    //dd(auth()->user());
                    //\Auth::login(auth()->user());

                    \Session::flash('forceRoot', auth()->user()->id);
                    if (auth()->user()->currentTeam) {
                        app('url')->forceRootUrl('https://' . auth()->user()->currentTeam?->hostname);
                    }

                    return redirect()->to('/');
                }
            }
        } catch (ModelNotFoundException $e) {
            abort(404);
        }

        /*try {
            //Am i on the right app?
        } catch (\Exception $e) {
            return redirect(config('app.url'));
        }*/

        return $next($request);
    }
}
