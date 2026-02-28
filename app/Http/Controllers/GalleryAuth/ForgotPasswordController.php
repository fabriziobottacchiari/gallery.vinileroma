<?php

declare(strict_types=1);

namespace App\Http\Controllers\GalleryAuth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    public function show(): View
    {
        return view('gallery-auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        Password::broker('gallery_users')->sendResetLink(
            $request->only('email'),
        );

        // Always show success message to avoid email enumeration
        return back()->with('success', 'Se l\'email è registrata, riceverai un link per reimpostare la password.');
    }
}
