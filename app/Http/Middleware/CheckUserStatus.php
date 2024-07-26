<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            $user = auth()->user();

            // Se l'utente non ha una sottoscrizione attiva o è in periodo di grazia
            if (!$user->subscribed('default') || ($user->subscription('default')->canceled() && !$user->subscription('default')->onGracePeriod())) {
                // Se non è già sulla pagina di creazione della sottoscrizione, reindirizza
                if (!$request->routeIs('subscription.create')) {
                    return redirect()->route('subscription.create');
                }
            }
            // Se l'utente ha una sottoscrizione attiva ma sta cercando di accedere alla pagina di creazione
            elseif ($user->subscribed('default') && $request->routeIs('subscription.create')) {
                return redirect()->route('dashboard'); // o qualsiasi altra pagina appropriata
            }
        }

        return $next($request);
    }
}
