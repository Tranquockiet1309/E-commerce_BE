<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Closure;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Xác định guard để redirect nếu chưa login
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        // Nếu request là API / JSON (SPA) → không redirect, trả 401
        if ($request->expectsJson()) {
            return null;
        }

        // Nếu request web bình thường → redirect tới route login (nếu có)
        // Nếu bạn không có route login, có thể trả null
        return route('login'); // hoặc null nếu SPA only
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  ...$guards
     * @return mixed
     */


}
