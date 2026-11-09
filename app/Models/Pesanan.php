<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model
{
    protected $table = 'pesanan';
    protected $primaryKey = 'id_pesanan';
    public $timestamps = false;
    protected $guarded = [];

    // Lokasi file bukti tidak ikut keluar di JSON/API, hanya penanda "sudah ada atau belum"
    protected $hidden = ['bukti_bayar'];
    protected $appends = ['ada_bukti'];

    public function getAdaBuktiAttribute(): bool
    {
        return ! empty($this->bukti_bayar);
    }

    public function meja()
    {
        return $this->belongsTo(Meja::class, 'id_meja', 'id_meja');
    }

    public function detail()
    {
        return $this->hasMany(DetailPesanan::class, 'id_pesanan', 'id_pesanan');
    }
}
