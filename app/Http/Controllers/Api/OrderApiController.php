<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Meja;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\DetailPesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderApiController extends Controller
{
    // GET: /api/order/meja/{id_meja}
    public function getMenuByMeja($id_meja)
    {
        $meja = Meja::find($id_meja);

        if (!$meja) {
            return response()->json([
                'status' => 'error',
                'message' => 'Meja tidak ditemukan'
            ], 404);
        }

        $menus = Menu::where('stok', '>', 0)->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'meja' => $meja,
                'menus' => $menus
            ]
        ], 200);
    }

    // POST: /api/order/checkout
    public function checkout(Request $request)
    {
        $request->validate([
            'id_meja' => 'required|exists:meja,id_meja',
            'no_meja' => 'required|string',
            'nama_pelanggan' => 'required|string|max:100',
            'metode_pembayaran' => 'required|in:tunai,qris',
            'items' => 'required|array|min:1',
            'items.*.id_menu' => 'required|exists:menu,id_menu',
            'items.*.jumlah' => 'required|integer|min:1',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                $totalBayar = 0;

                foreach ($request->items as $item) {
                    $menu = Menu::where('id_menu', $item['id_menu'])->lockForUpdate()->first();
                    
                    if ($menu->stok < $item['jumlah']) {
                        return response()->json([
                            'status' => 'error',
                            'message' => "Stok menu {$menu->nama_menu} tersisa {$menu->stok}"
                        ], 400);
                    }
                    $totalBayar += $menu->harga * $item['jumlah'];
                }

                $pesanan = Pesanan::create([
                    'no' => Pesanan::count() + 1,
                    'kode_pesanan' => '#SL-' . strtoupper(Str::random(5)),
                    'id_meja' => $request->id_meja,
                    'no_meja' => $request->no_meja,
                    'nama_pelanggan' => $request->nama_pelanggan,
                    'no_hp_pelanggan' => $request->no_hp_pelanggan ?? null,
                    'id_user' => null,
                    'total_bayar' => $totalBayar,
                    'status_pesanan' => 'diterima',
                    'metode_pembayaran' => $request->metode_pembayaran,
                    'status_pembayaran' => 'pending',
                    'tanggal_pesan' => now(),
                ]);

                foreach ($request->items as $index => $item) {
                    $menu = Menu::find($item['id_menu']);
                    $subtotal = $menu->harga * $item['jumlah'];

                    DetailPesanan::create([
                        'no' => $index + 1,
                        'id_pesanan' => $pesanan->id_pesanan,
                        'no_meja' => $request->no_meja,
                        'id_menu' => $menu->id_menu,
                        'jumlah' => $item['jumlah'],
                        'subtotal' => $subtotal,
                        'catatan' => $item['catatan'] ?? null,
                    ]);

                    $menu->decrement('stok', $item['jumlah']);
                }

                Meja::where('id_meja', $request->id_meja)->update(['status' => 'terisi']);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Pesanan berhasil dibuat',
                    'data' => [
                        'kode_pesanan' => $pesanan->kode_pesanan,
                        'total_bayar' => $pesanan->total_bayar
                    ]
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memproses pesanan: ' . $e->getMessage()
            ], 500);
        }
    }

    // GET: /api/order/status/{kode_pesanan}
    public function checkStatus($kode_pesanan)
    {
        $pesanan = Pesanan::with('detailPesanan.menu')
            ->where('kode_pesanan', $kode_pesanan)
            ->first();

        if (!$pesanan) {
            return response()->json(['status' => 'error', 'message' => 'Pesanan tidak ditemukan'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $pesanan
        ], 200);
    }
}