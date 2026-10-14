<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TwoFactorSetupController extends Controller
{
    public function show(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Auth/TwoFactorSetup', [
            'enabled' => $user->hasEnabledTwoFactorAuthentication(),
            'confirmed' => $user->two_factor_confirmed_at !== null,
        ]);
    }

    public function demoBypass(Request $request)
    {
        $request->session()->put('demo_2fa_bypassed', true);
        return redirect()->route('dashboard')->with('success', 'Demo mode active: Two-Factor Authentication bypassed for this session.');
    }
}
