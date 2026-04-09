<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Utf8JsonResponse
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($response instanceof \Illuminate\Http\JsonResponse) {
            $response->header('Content-Type', 'application/json; charset=UTF-8');
        }

        return $response;
    }
}
