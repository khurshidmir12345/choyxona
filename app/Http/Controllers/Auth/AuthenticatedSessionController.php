<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        /*
         * Ilgari bu yerda "role->name === 'director'" sharti bor edi.
         * roles jadvali bo'sh bo'lgani uchun shart hech qachon bajarilmasdi
         * va hech kim tizimga kira olmasdi. Endi mezon oddiy va aniq:
         * hisob biror kompaniyaga bog'langan bo'lishi kerak.
         */
        $user = $request->user();

        if (! $user->companyId()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();

            throw ValidationException::withMessages([
                'phone_number' => 'Hisobingiz hech qaysi choyxonaga bog\'lanmagan. Administratorga murojaat qiling.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
