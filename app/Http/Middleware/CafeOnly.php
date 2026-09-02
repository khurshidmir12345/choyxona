<?php

namespace App\Http\Middleware;

use App\Support\Business;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Zal va joylar faqat kafe rejimida bor. */
class CafeOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Business::current()->hasHall()) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
