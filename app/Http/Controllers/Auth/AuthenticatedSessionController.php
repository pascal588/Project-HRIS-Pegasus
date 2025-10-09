<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): \Illuminate\View\View
    {
        // Get login attempts from session
        $attempts = session('login_attempts', 0);

        return view('auth.login', [
            'login_attempts' => $attempts
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // Rate limiting check
        $throttleKey = 'login:' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ]),
            ]);
        }

        // Increment login attempts
        $attempts = session('login_attempts', 0) + 1;
        session(['login_attempts' => $attempts]);
        RateLimiter::hit($throttleKey, 900); // 15 minutes

        try {
            $request->authenticate();

            // Reset attempts on successful login
            session(['login_attempts' => 0]);
            RateLimiter::clear($throttleKey);

            $request->session()->regenerate();

            // Handle "Remember Me"
            if ($request->boolean('remember')) {
                Cookie::queue('remember_email', $request->email, 43200); // 30 days
            } else {
                Cookie::queue(Cookie::forget('remember_email'));
            }

            $user = Auth::user();

            // 🔥 SOLUSI SIMPLE & PASTI WORK
            // Eager load relasi employee dan roles
            $userWithRoles = $user->load('employee.roles');

            if ($userWithRoles->employee && $userWithRoles->employee->roles->isNotEmpty()) {
                // Ambil role pertama (asumsi 1 user punya 1 role)
                $roleName = $userWithRoles->employee->roles->first()->nama_jabatan;

                // Redirect langsung berdasarkan role
                switch ($roleName) {
                    case 'HR':
                        return redirect()->route('hr.dashboard');
                    case 'Kepala Divisi':
                        return redirect()->route('penilai.dashboard');
                    case 'Karyawan':
                        return redirect()->route('karyawan.dashboard');
                    default:
                        return redirect('/');
                }
            }

            // Fallback jika tidak ada role
            return redirect('/');
        } catch (ValidationException $e) {
            // If authentication fails, check if we've reached the limit
            if ($attempts >= 5) {
                session(['login_attempts' => 5]);
                return back()->withErrors([
                    'email' => 'Terlalu banyak percobaan login. Silakan tunggu 15 menit atau reset sandi Anda.',
                ]);
            }

            throw $e;
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Clear login attempts on logout
        session(['login_attempts' => 0]);

        return redirect('/');
    }
}
