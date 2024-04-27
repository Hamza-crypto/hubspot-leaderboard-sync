<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAppKey
{
    public function handle($request, Closure $next)
    {

        // Check if "appkey" parameter exists in the request URL and matches the expected value
        if ($request->has('key') && $request->key === env('TELEGRAM_BOT_TOKEN')) {
            return $next($request);
        }

        // Redirect or abort the request if the appkey is missing or invalid
        abort(403, 'Unauthorized');
    }
}