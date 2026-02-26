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
    public function show(): View
    {
        return view('admin.mfa.verify');
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $user   = $request->user();
        $stored = Cache::get("mfa_code_{$user->id}");

        if ($stored === null || $stored !== $request->string('code')->toString()) {
            return back()->withErrors(['code' => 'Il codice non è corretto o è scaduto.']);
        }

        Cache::forget("mfa_code_{$user->id}");
        $request->session()->put('mfa_verified', true);

        return redirect()->route('admin.events.index')
            ->with('success', 'Accesso verificato con successo. Benvenuto nel pannello.');
    }

    public function resend(Request $request): RedirectResponse
    {
        $user = $request->user();
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
