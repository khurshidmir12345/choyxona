<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bo'limga kirish ruxsati: `perm:hall`, `perm:orders` va hokazo.
 * Ruxsat bo'lmasa foydalanuvchi o'ziga ochiq birinchi bo'limga qaytariladi.
 */
class EnsurePermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if ($user instanceof User && $user->allows($permission)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(403, 'Bu bo\'limga ruxsat yo\'q.');
        }

        $home = $user instanceof User ? $user->homeRoute() : 'login';

        if ($request->routeIs($home)) {
            abort(403, 'Bu bo\'limga ruxsat yo\'q.');
        }

        return redirect()
            ->route($home)
            ->with('toast', ['type' => 'error', 'message' => 'Bu bo\'limga ruxsat yo\'q.']);
    }
}
