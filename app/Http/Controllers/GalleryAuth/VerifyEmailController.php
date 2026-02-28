<?php

declare(strict_types=1);

namespace App\Http\Controllers\GalleryAuth;

use App\Http\Controllers\Controller;
use App\Mail\GalleryVerifyEmailMail;
use App\Models\GalleryUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class VerifyEmailController extends Controller
{
    public function notice(Request $request): View|RedirectResponse
    {
        if ($request->user('gallery')?->hasVerifiedEmail()) {
            return redirect()->route('public.events.index');
        }

        return view('gallery-auth.verify-email');
    }

    public function verify(Request $request, int $id, string $hash): RedirectResponse
    {
        $user = GalleryUser::findOrFail($id);

        abort_unless(hash_equals($hash, sha1($user->email)), 403);
        abort_unless($request->hasValidSignature(), 403);

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        // Log in if not already (e.g. opened on a different device)
        if (! $request->user('gallery')) {
            \Illuminate\Support\Facades\Auth::guard('gallery')->login($user);
            $request->session()->regenerate();
        }

        return redirect()->route('public.events.index')
            ->with('success', 'Email verificata con successo! Benvenuto su Vinile Roma Gallery.');
    }

    public function resend(Request $request): RedirectResponse
    {
        $user = $request->user('gallery');

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('public.events.index');
        }

        $verifyUrl = URL::temporarySignedRoute(
            'gallery.verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)],
        );

        Mail::to($user->email)->send(new GalleryVerifyEmailMail($user, $verifyUrl));

        return back()->with('success', "Email di verifica inviata a {$user->email}.");
    }
}
