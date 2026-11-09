<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AdminAkunController extends Controller
{
    private const PERAN = ['admin', 'kasir', 'koki'];

    public function index()
    {
        $akun = User::orderBy('role')->orderBy('nama')->get();

        // Akun yang pernah memverifikasi pembayaran tidak boleh dihapus (menjaga riwayat)
        $punyaRiwayat = Pesanan::whereNotNull('id_user')->distinct()->pluck('id_user')->all();

        return view('admin.akun.index', compact('akun', 'punyaRiwayat'));
    }

    public function create()
    {
        return view('admin.akun.form', ['akun' => new User()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama'     => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9_.-]+$/', 'unique:users,username'],
            'role'     => ['required', Rule::in(self::PERAN)],
            'password' => ['required', 'string', 'min:8', 'max:100', 'confirmed'],
        ], $this->pesan());

        User::create($data); // password otomatis di-hash oleh cast pada model User

        return redirect()->route('admin.akun.index')->with('sukses', "Akun {$data['username']} berhasil dibuat.");
    }

    public function edit(User $akun)
    {
        return view('admin.akun.form', compact('akun'));
    }

    public function update(Request $request, User $akun)
    {
        $data = $request->validate([
            'nama'     => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9_.-]+$/',
                           Rule::unique('users', 'username')->ignore($akun->id_user, 'id_user')],
            'role'     => ['required', Rule::in(self::PERAN)],
            'password' => ['nullable', 'string', 'min:8', 'max:100', 'confirmed'],
        ], $this->pesan());

        $turunPeran = $akun->role === 'admin' && $data['role'] !== 'admin';

        if ($turunPeran && $akun->id_user === Auth::id()) {
            return back()->withInput()->with('info', 'Kamu tidak bisa menurunkan peran akunmu sendiri.');
        }
        if ($turunPeran && $this->adminTerakhir()) {
            return back()->withInput()->with('info', 'Harus tersisa minimal satu akun admin.');
        }

        $ganti = filled($data['password'] ?? null);
        if (! $ganti) {
            unset($data['password']); // kosong = password tidak diubah
        }

        // Token API dicabut kalau password atau peran berubah, supaya akses lama tidak lolos
        $cabutToken = $ganti || $data['role'] !== $akun->role;

        $akun->update($data);

        if ($cabutToken && method_exists($akun, 'tokens')) {
            $akun->tokens()->delete();
        }

        return redirect()->route('admin.akun.index')->with('sukses', "Akun {$akun->username} berhasil diperbarui.");
    }

    public function destroy(User $akun)
    {
        if ($akun->id_user === Auth::id()) {
            return back()->with('info', 'Kamu tidak bisa menghapus akunmu sendiri.');
        }
        if ($akun->role === 'admin' && $this->adminTerakhir()) {
            return back()->with('info', 'Harus tersisa minimal satu akun admin.');
        }
        if (Pesanan::where('id_user', $akun->id_user)->exists()) {
            return back()->with('info', "Akun {$akun->username} pernah memverifikasi pembayaran, jadi tidak bisa dihapus agar riwayat tetap utuh. Kamu bisa mengganti password atau namanya.");
        }

        if (method_exists($akun, 'tokens')) {
            $akun->tokens()->delete();
        }
        $akun->delete();

        return redirect()->route('admin.akun.index')->with('sukses', 'Akun berhasil dihapus.');
    }

    private function adminTerakhir(): bool
    {
        return User::where('role', 'admin')->count() <= 1;
    }

    private function pesan(): array
    {
        return [
            'username.regex'     => 'Username hanya boleh berisi huruf, angka, titik, garis bawah, dan strip.',
            'username.unique'    => 'Username sudah dipakai akun lain.',
            'password.min'       => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sama.',
        ];
    }
}
