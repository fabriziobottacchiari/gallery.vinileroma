<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adds hardening HTTP headers to all responses.
 *
 * References:
 *  - https://owasp.org/www-project-secure-headers/
 *  - https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent clickjacking — disallow framing by third-party sites
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Prevent MIME-type sniffing (guards against polyglot file attacks)
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Limit Referer header leakage when navigating to external sites
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Restrict access to browser features not used by this app
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');

        // Remove X-Powered-By if present (information disclosure)
        $response->headers->remove('X-Powered-By');

        return $response;
    }
}
