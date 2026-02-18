<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Barang;

class BarangMasuk extends Model
{
    protected $table = 'barang_masuk';
    protected $primaryKey = 'id_masuk';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_masuk',
        'tgl_masuk',
        'user_id',
        'id_barang',
        'jumlah_masuk',
        'total_harga',
    ];

    /* ================= RELATION ================= */

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang', 'id_barang');
    }

    /* ================= MODEL EVENT ================= */

    protected static function booted()
    {
        /**
         * BEFORE INSERT
         */
        static::creating(function ($row) {

            /* === GENERATE ID BM === */
            $lastId = self::max('id_masuk');

            $row->id_masuk = $lastId
                ? 'BM' . str_pad(((int) substr($lastId, 2)) + 1, 3, '0', STR_PAD_LEFT)
                : 'BM001';

            /* === VALIDASI JUMLAH === */
            if ($row->jumlah_masuk <= 0) {
                throw new \Exception('Jumlah barang masuk tidak valid');
            }

            /* === AMBIL BARANG === */
            $barang = Barang::where('id_barang', $row->id_barang)->firstOrFail();

            /* === HITUNG TOTAL HARGA (SERVER SIDE) === */
            $row->total_harga = $row->jumlah_masuk * $barang->harga_masuk;
        });

        /**
         * AFTER INSERT → TAMBAH STOK
         */
        static::created(function ($row) {
            Barang::where('id_barang', $row->id_barang)
                ->increment('stok', $row->jumlah_masuk);
        });

        /**
         * BEFORE DELETE → KURANGI STOK
         */
        static::deleting(function ($row) {
            DB::transaction(function () use ($row) {

                $barang = Barang::where('id_barang', $row->id_barang)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($barang->stok < $row->jumlah_masuk) {
                    throw new \Exception('Stok tidak mencukupi untuk penghapusan');
                }

                $barang->decrement('stok', $row->jumlah_masuk);
            });
        });
    }
}
