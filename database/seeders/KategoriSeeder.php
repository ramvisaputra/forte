<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriSeeder extends Seeder
{
    public function run(): void
    {
        $kategoris = [
            ['id_kategori' => 'K001', 'nama_kategori' => 'Ordner', 'status' => 'aktif', 'created_at' => now(), 'updated_at' => now()],
            ['id_kategori' => 'K002', 'nama_kategori' => 'Thermal Roll', 'status' => 'aktif', 'created_at' => now(), 'updated_at' => now()],
            ['id_kategori' => 'K003', 'nama_kategori' => 'Nota Kontan', 'status' => 'aktif', 'created_at' => now(), 'updated_at' => now()],
            ['id_kategori' => 'K004', 'nama_kategori' => 'Notebook', 'status' => 'aktif', 'created_at' => now(), 'updated_at' => now()],
            ['id_kategori' => 'K005', 'nama_kategori' => 'Origami', 'status' => 'aktif', 'created_at' => now(), 'updated_at' => now()],
            ['id_kategori' => 'K006', 'nama_kategori' => 'Sticky Note', 'status' => 'aktif', 'created_at' => now(), 'updated_at' => now()],
            ['id_kategori' => 'K007', 'nama_kategori' => 'Folder File', 'status' => 'aktif', 'created_at' => now(), 'updated_at' => now()],
            ['id_kategori' => 'K008', 'nama_kategori' => 'Buku Gambar', 'status' => 'aktif', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('kategori')->insert($kategoris);
    }
}
