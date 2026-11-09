<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Token berlaku 8 jam (satu shift kerja), setelah itu harus login lagi
    private const MASA_BERLAKU_JAM = 8;

    public function login(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('username', $data['username'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Username atau password salah.'], 401);
        }

        $berlakuSampai = now()->addHours(self::MASA_BERLAKU_JAM);
        $token = $user->createToken('api-token', ['*'], $berlakuSampai)->plainTextToken;

        return response()->json([
            'message'    => 'Login berhasil.',
            'token'      => $token,
            'expires_at' => $berlakuSampai->toIso8601String(),
            'user'       => [
                'id_user'  => $user->id_user,
                'nama'     => $user->nama,
                'username' => $user->username,
                'role'     => $user->role,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil.']);
    }
}
