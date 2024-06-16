<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WaitConfirm
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()) {
            $notesWaitConfirm = $request->user()->moderatedOperationNotes()->where('status', 'awaitConfirm')->count();
            Inertia::share('notesWaitConfirm', $notesWaitConfirm);
        }
        return $next($request);
    }
}
