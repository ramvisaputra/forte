<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Barang;
use Illuminate\Support\Facades\DB;

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

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang', 'id_barang');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Ambil ID terbaru
            $lastId = BarangMasuk::max('id_masuk');

            if (!$lastId) {
                $model->id_masuk = 'BM001';
            } else {
                $number = (int) substr($lastId, 2) + 1;
                $model->id_masuk = 'BM' . str_pad($number, 3, '0', STR_PAD_LEFT);
            }
        });
    }

    protected static function booted()
    {
        static::creating(function ($row) {

            if ($row->jumlah_masuk <= 0) {
                throw new \Exception('Jumlah Barang masuk tidak valid');
            }

            $barang = Barang::where('id_barang', $row->id_barang)->first();

            if (!$barang) {
                throw new \Exception('Barang tidak ditemukan');
            }

            $row->total_harga = $row->jumlah_masuk * $barang->harga_masuk;
        });

        static::created(function ($row) {
            $barang = Barang::where('id_barang', $row->id_barang)->first();

            if ($barang) {
                $barang->stok += $row->jumlah_masuk;
                $barang->save();
            };
        });

        static::deleting(function ($row) {
            DB::transaction(function () use ($row) {

                $barang = Barang::where('id_barang', $row->id_barang)
                    ->lockForUpdate()
                    ->first();

                if ($barang) {
                    $barang->stok -= $row->jumlah_masuk;

                    if ($barang->stok < 0) {
                        throw new \Exception('Stok tidak boleh kurang dari 0');
                    }

                    $barang->save();
                }
            });
        });
    }
}
