<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Email atau password salah.',
            ]);
        }

        $request->session()->regenerate();

        // Ambil role dari employees
        $user = Auth::user();
        $roles = $user->employee?->roles->pluck('nama_jabatan')->toArray() ?? [];
        if (in_array('HR', $roles)) {
            return redirect()->route('hr.dashboard');
        } elseif (in_array('Kepala-divisi', $roles)) {
            return redirect()->route('penilai.dashboard');
        } elseif (in_array('Karyawan', $roles)) {
            return redirect()->route('karyawan.dashboard');
        }

        return redirect()->intended('/'); // fallback
    }


    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
