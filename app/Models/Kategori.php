<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    protected $table = 'kategori';
    protected $primaryKey = 'id_kategori';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_kategori',
        'nama_kategori',
        'status',
    ];

    public function barangs()
    {
        return $this->hasMany(Barang::class, 'id_kategori', 'id_kategori');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Ambil ID terbaru
            $lastId = Kategori::max('id_kategori');

            if (!$lastId) {
                $model->id_kategori = 'K001';
            } else {
                $number = (int) substr($lastId, 1) + 1;
                $model->id_kategori = 'K' . str_pad($number, 3, '0', STR_PAD_LEFT);
            }
        });
    }
}


//ksasakkk