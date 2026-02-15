<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Barang;
use App\Services\EoqService;

class BarangKeluar extends Model
{
    protected $table = 'barang_keluar';
    protected $primaryKey = 'id_keluar';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_keluar',
        'tgl_keluar',
        'user_id',
        'id_barang',
        'jumlah_keluar',
        'total_harga',
    ];
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang', 'id_barang');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
