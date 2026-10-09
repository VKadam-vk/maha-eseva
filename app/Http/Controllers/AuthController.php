<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show login form.
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    /**
     * Process authentication with rate limiting and lockout protection.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = Str::transliterate(Str::lower($credentials['email']) . '|' . $request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ])->onlyInput('email');
        }

        $user = User::withoutGlobalScopes()->where('email', $credentials['email'])->first();

        if ($user && $user->isLocked()) {
            AuditService::log('LOCKED_LOGIN_ATTEMPT', $user);
            return back()->withErrors([
                'email' => 'Your account is locked due to multiple failed login attempts. Please wait 15 minutes or contact admin.',
            ])->onlyInput('email');
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            $authUser = Auth::user();
            $authUser->resetFailedLogin();

            AuditService::log('LOGIN', $authUser);

            return redirect()->intended(route('dashboard'));
        }

        RateLimiter::hit($throttleKey, 900); // 15 mins decay

        if ($user) {
            $user->recordFailedLogin();
            AuditService::log('FAILED_LOGIN', $user);
        }

        return back()->withErrors([
            'email' => 'Invalid email or password credentials provided.',
        ])->onlyInput('email');
    }

    /**
     * Logout and invalidate session.
     */
    public function logout(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            AuditService::log('LOGOUT', Auth::user());
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been securely logged out.');
    }

    /**
     * Show profile page.
     */
    public function profile(): View
    {
        $user = Auth::user();
        return view('auth.profile', compact('user'));
    }

    /**
     * Update password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        AuditService::log('PASSWORD_CHANGED', $user);

        return back()->with('success', 'Your password has been changed successfully.');
    }
}
