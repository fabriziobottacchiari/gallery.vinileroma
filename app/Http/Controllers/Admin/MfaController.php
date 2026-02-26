<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\MfaCodeMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class MfaController extends Controller
{
    private const MAX_ATTEMPTS   = 5;
    private const LOCKOUT_MINUTES = 10;

    public function show(): View
    {
        return view('admin.mfa.verify');
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $user         = $request->user();
        $attemptsKey  = "mfa_attempts_{$user->id}";
        $attempts     = (int) Cache::get($attemptsKey, 0);

        // Lockout check
        if ($attempts >= self::MAX_ATTEMPTS) {
            return back()->withErrors([
                'code' => 'Troppi tentativi falliti. Attendi ' . self::LOCKOUT_MINUTES . ' minuti prima di riprovare.',
            ]);
        }

        $stored = Cache::get("mfa_code_{$user->id}");

        if ($stored === null || $stored !== $request->string('code')->toString()) {
            $newAttempts = $attempts + 1;
            Cache::put($attemptsKey, $newAttempts, now()->addMinutes(self::LOCKOUT_MINUTES));

            $remaining = self::MAX_ATTEMPTS - $newAttempts;

            return back()->withErrors([
                'code' => $remaining > 0
                    ? "Codice errato o scaduto. Tentativi rimanenti: {$remaining}."
                    : 'Troppi tentativi falliti. Account bloccato per ' . self::LOCKOUT_MINUTES . ' minuti.',
            ]);
        }

        // Success — clear code and attempt counter
        Cache::forget("mfa_code_{$user->id}");
        Cache::forget($attemptsKey);

        // Regenerate session ID after MFA to prevent session fixation
        $request->session()->regenerate();
        $request->session()->put('mfa_verified', true);

        return redirect()->route('admin.events.index')
            ->with('success', 'Accesso verificato con successo. Benvenuto nel pannello.');
    }

    public function resend(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Clear any existing attempt counter so the user can try again
        Cache::forget("mfa_attempts_{$user->id}");

        $code = $this->generateCode();

        Cache::put("mfa_code_{$user->id}", $code, now()->addMinutes(15));
        Mail::to($user)->send(new MfaCodeMail($code));

        return back()->with('status', 'Nuovo codice inviato. Controlla la tua email.');
    }

    private function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}
