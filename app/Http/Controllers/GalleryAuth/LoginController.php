<?php

declare(strict_types=1);

namespace App\Http\Controllers\GalleryAuth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function show(): View|RedirectResponse
    {
        if (Auth::guard('gallery')->check()) {
            return redirect()->intended(route('public.events.index'));
        }

        return view('gallery-auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('gallery')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::guard('gallery')->user();

            if (! $user->hasVerifiedEmail()) {
                return redirect()->route('gallery.verification.notice');
            }

            return redirect()->intended(route('public.events.index'));
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'Email o password non corretti.']);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('gallery')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('public.events.index');
    }
}
