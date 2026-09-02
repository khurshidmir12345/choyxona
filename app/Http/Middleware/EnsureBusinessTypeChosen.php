<?php

namespace App\Http\Middleware;

use App\Support\Business;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Kompaniya biznes turini tanlamagan bo'lsa, avval o'rnatish ekraniga. */
class EnsureBusinessTypeChosen
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && ! Business::isChosen()) {
            return redirect()->route('setup.business');
        }

        return $next($request);
    }
}
