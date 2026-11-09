<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        // Batasi percobaan login: maksimal 5 kali per menit untuk kombinasi username + IP
        $key = Str::lower($credentials['username']) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $detik = RateLimiter::availableIn($key);

            return back()->withErrors([
                'username' => "Terlalu banyak percobaan. Coba lagi dalam {$detik} detik.",
            ])->onlyInput('username');
        }

        if (Auth::attempt($credentials)) {
            RateLimiter::clear($key);
            $request->session()->regenerate();

            return match (Auth::user()->role) {
                'koki'  => redirect()->route('koki.index'),
                'kasir' => redirect()->route('kasir.index'),
                default => Route::has('admin.menu.index')
                    ? redirect()->route('admin.menu.index')
                    : redirect()->route('dashboard'),
            };
        }

        RateLimiter::hit($key, 60);

        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
