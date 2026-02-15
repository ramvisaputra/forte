<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Barang extends Model
{
    protected $table = 'barang';
    protected $primaryKey = 'id_barang';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nama_barang',
        'id_kategori',
        'harga_masuk',
        'harga_keluar',
        'biaya_pesan',
        'biaya_simpan',
        'stok_minimum',
        'stok',
    ];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'id_kategori', 'id_kategori');
    }

    public function eoqs()
    {
        return $this->hasMany(Eoq::class, 'id_barang', 'id_barang');
    }

    public function barangMasuks()
    {
        return $this->hasMany(BarangMasuk::class, 'id_barang', 'id_barang');
    }

    public function barangKeluars()
    {
        return $this->hasMany(BarangKeluar::class, 'id_barang', 'id_barang');
    }


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Ambil ID terbaru
            $lastId = Barang::max('id_barang');

            if (!$lastId) {
                $model->id_barang = 'B001';
            } else {
                $number = (int) substr($lastId, 1) + 1;
                $model->id_barang = 'B' . str_pad($number, 3, '0', STR_PAD_LEFT);
            }
        });
    }

    protected static function booted()
    {
        static::addGlobalScope('kategori_aktif', function (Builder $builder) {
            $builder->whereHas('kategori', function ($q) {
                $q->where('status', 'aktif');
            });
        });
    }
}
