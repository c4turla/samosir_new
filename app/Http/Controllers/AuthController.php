<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        return inertia('Auth/Login');
    }

    /**
     * Handle an incoming authentication request
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Cek user dan role
        $user = \App\Models\User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        // Cek apakah user aktif
        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Akun Anda telah dinonaktifkan.'],
            ]);
        }

        // Cek role - pengelola tidak boleh login ke web
        if ($user->role === 'pengelola') {
            throw ValidationException::withMessages([
                'email' => ['Role Pengelola Kapal hanya dapat login melalui aplikasi mobile.'],
            ]);
        }

        // Cek role - hanya admin, staff, dan syahbandar yang boleh login
        if (!in_array($user->role, ['admin', 'petugas', 'syahbandar'])) {
            throw ValidationException::withMessages([
                'email' => ['Anda tidak memiliki akses ke sistem web.'],
            ]);
        }

        // Attempt login
        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            return redirect()->intended(route('dashboard'));
        }

        throw ValidationException::withMessages([
            'email' => ['Email atau password salah.'],
        ]);
    }

    /**
     * Destroy an authenticated session
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}