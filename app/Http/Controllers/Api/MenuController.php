<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetailPesanan;
use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        $query = Menu::query();

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        return response()->json(['data' => $query->orderBy('kategori')->orderBy('nama_menu')->get()]);
    }

    public function show(Menu $menu)
    {
        return response()->json(['data' => $menu]);
    }

    public function store(Request $request)
    {
        $menu = Menu::create($this->validasi($request));

        return response()->json(['message' => 'Menu ditambahkan.', 'data' => $menu], 201);
    }

    public function update(Request $request, Menu $menu)
    {
        $menu->update($this->validasi($request));

        return response()->json(['message' => 'Menu diperbarui.', 'data' => $menu]);
    }

    public function destroy(Menu $menu)
    {
        if (DetailPesanan::where('id_menu', $menu->id_menu)->exists()) {
            return response()->json(['message' => 'Menu sudah pernah dipesan, tidak bisa dihapus.'], 409);
        }

        $menu->delete();

        return response()->json(['message' => 'Menu dihapus.']);
    }

    private function validasi(Request $request): array
    {
        return $request->validate([
            'kategori'  => ['required', 'string', 'max:50'],
            'nama_menu' => ['required', 'string', 'max:100'],
            'harga'     => ['required', 'numeric', 'min:0'],
            'deskripsi' => ['nullable', 'string'],
            'stok'      => ['required', 'integer', 'min:0'],
        ]);
    }
}