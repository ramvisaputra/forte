<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Eoq extends Model
{
    protected $table = 'eoq';

    protected $fillable = [
        'id_barang',
        'tahun',
        'permintaan_tahunan',
        'biaya_pesan',
        'biaya_simpan',
        'nilai_eoq',
        'frekuensi_pesan',
        'total_pemesanan',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'permintaan_tahunan' => 'integer',
        'biaya_pesan' => 'decimal:2',
        'biaya_simpan' => 'decimal:2',
        'nilai_eoq' => 'decimal:2',
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang', 'id_barang');
    }
}
