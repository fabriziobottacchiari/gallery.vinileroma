<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireGalleryAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user('gallery')) {
            return redirect()->route('gallery.login')
                ->with('info', 'Effettua l\'accesso per visualizzare le gallerie fotografiche.');
        }

        if (! $request->user('gallery')->hasVerifiedEmail()) {
            return redirect()->route('gallery.verification.notice');
        }

        return $next($request);
    }
}
