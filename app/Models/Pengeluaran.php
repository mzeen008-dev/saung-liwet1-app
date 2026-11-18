<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengeluaran extends Model
{
    protected $table = 'pengeluaran';
    protected $primaryKey = 'id_pengeluaran';
    public $timestamps = false;
    protected $guarded = [];

    public const KATEGORI = ['Bahan baku', 'Gaji', 'Listrik & gas', 'Peralatan', 'Lainnya'];
}