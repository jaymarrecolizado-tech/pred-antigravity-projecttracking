<?php

namespace App\Http\Middleware;

use App\Domain\Access\GrantResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorEnforced
{
    public function __construct(
        protected GrantResolver $grantResolver
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // Exclude 2FA setup, 2FA confirmation, and logout routes from redirection
        if ($request->routeIs('two-factor.*') || $request->routeIs('logout')) {
            return $next($request);
        }

        if ($this->grantResolver->requiresTwoFactor($user) && !$user->hasEnabledTwoFactorAuthentication()) {
            return redirect()->route('two-factor.setup')
                ->with('warning', 'Your role requires Two-Factor Authentication (2FA) to be enabled for security compliance.');
        }

        return $next($request);
    }
}
