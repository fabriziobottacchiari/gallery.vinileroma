<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireMfaVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        if (! $request->user()->is_admin) {
            abort(403, 'Accesso riservato agli amministratori.');
        }

        if (! $request->session()->get('mfa_verified')) {
            return redirect()->route('admin.mfa.verify')
                ->with('warning', 'È richiesta la verifica in due fattori per accedere al pannello.');
        }

        return $next($request);
    }
}
