<?php

declare(strict_types=1);

namespace App\Http\Controllers\GalleryAuth;

use App\Http\Controllers\Controller;
use App\Mail\GalleryVerifyEmailMail;
use App\Models\GalleryUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function show(): View|RedirectResponse
    {
        if (Auth::guard('gallery')->check()) {
            return redirect()->route('public.events.index');
        }

        return view('gallery-auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'first_name'         => ['required', 'string', 'max:100'],
            'last_name'          => ['required', 'string', 'max:100'],
            'email'              => ['required', 'email', 'max:255', 'unique:gallery_users,email'],
            'password'           => ['required', 'string', 'min:8', 'confirmed'],
            'phone'              => ['required', 'string', 'max:30'],
            'birth_year'         => ['required', 'integer', 'min:1920', 'max:' . (now()->year - 13)],
            'gender'             => ['required', 'in:male,female,prefer_not_to_say'],
            'instagram_handle'   => ['nullable', 'string', 'max:60'],
            'privacy_accepted'   => ['accepted'],
            'newsletter_consent' => ['nullable', 'boolean'],
            'marketing_consent'  => ['nullable', 'boolean'],
        ], [
            'first_name.required'       => 'Il nome è obbligatorio.',
            'last_name.required'        => 'Il cognome è obbligatorio.',
            'email.unique'              => 'Questa email è già registrata.',
            'password.min'              => 'La password deve essere di almeno 8 caratteri.',
            'password.confirmed'        => 'Le password non coincidono.',
            'phone.required'            => 'Il telefono è obbligatorio.',
            'birth_year.required'       => "L'anno di nascita è obbligatorio.",
            'birth_year.max'            => 'Devi avere almeno 13 anni per registrarti.',
            'gender.required'           => 'Il campo sesso è obbligatorio.',
            'privacy_accepted.accepted' => 'Devi accettare la Privacy Policy per continuare.',
        ]);

        $user = GalleryUser::create([
            'first_name'         => $data['first_name'],
            'last_name'          => $data['last_name'],
            'email'              => $data['email'],
            'password'           => $data['password'],
            'phone'              => $data['phone'],
            'birth_year'         => $data['birth_year'],
            'gender'             => $data['gender'],
            'instagram_handle'   => $data['instagram_handle'] ?? null,
            'privacy_accepted'   => true,
            'newsletter_consent' => $request->boolean('newsletter_consent'),
            'marketing_consent'  => $request->boolean('marketing_consent'),
        ]);

        Auth::guard('gallery')->login($user);
        $request->session()->regenerate();

        $verifyUrl = URL::temporarySignedRoute(
            'gallery.verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)],
        );

        Mail::to($user->email)->send(new GalleryVerifyEmailMail($user, $verifyUrl));

        return redirect()->route('gallery.verification.notice')
            ->with('success', 'Registrazione completata! Controlla la tua email per verificare il tuo account.');
    }
}
