<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user(); // Получаем текущего пользователя

        if ($user && ($user->post === 'owner' || $user->post === 'admin')) {
            return $next($request);
        }

        abort(403, 'Доступ запрещен');
    }
}