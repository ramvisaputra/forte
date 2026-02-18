<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriSeeder extends Seeder
{
    public function run(): void
    {
        $kategoris = [
            ['id_kategori' => 'K001', 'nama_kategori' => 'BUKU GAMBAR', 'status' => 'aktif', 'created_at' => now(), 'updated_at' => now()],
            ['id_kategori' => 'K002', 'nama_kategori' => 'BOX ORDNER', 'status' => 'aktif', 'created_at' => now(), 'updated_at' => now()],
            ['id_kategori' => 'K003', 'nama_kategori' => 'BUKU TULIS', 'status' => 'aktif', 'created_at' => now(), 'updated_at' => now()],
            ['id_kategori' => 'K004', 'nama_kategori' => 'DOUBLE FOLIO BERGARIS', 'status' => 'aktif', 'created_at' => now(), 'updated_at' => now()],
            ['id_kategori' => 'K005', 'nama_kategori' => 'EVA FOAM', 'status' => 'aktif', 'created_at' => now(), 'updated_at' => now()],
            ['id_kategori' => 'K006', 'nama_kategori' => 'KAIN FLANEL', 'status' => 'aktif', 'created_at' => now(), 'updated_at' => now()],
            ['id_kategori' => 'K007', 'nama_kategori' => 'CLIP BOARD', 'status' => 'aktif', 'created_at' => now(), 'updated_at' => now()],
            ['id_kategori' => 'K008', 'nama_kategori' => 'RING BINDER', 'status' => 'aktif', 'created_at' => now(), 'updated_at' => now()],
            ['id_kategori' => 'K009', 'nama_kategori' => 'HARD COVER', 'status' => 'aktif', 'created_at' => now(), 'updated_at' => now()],
            ['id_kategori' => 'K010', 'nama_kategori' => 'KUITANSI', 'status' => 'aktif', 'created_at' => now(), 'updated_at' => now()],
            ['id_kategori' => 'K011', 'nama_kategori' => 'LAKBAN', 'status' => 'aktif', 'created_at' => now(), 'updated_at' => now()],
            ['id_kategori' => 'K012', 'nama_kategori' => 'ORIGAMI', 'status' => 'aktif', 'created_at' => now(), 'updated_at' => now()],
            ['id_kategori' => 'K013', 'nama_kategori' => 'LOOSE LEAF', 'status' => 'aktif', 'created_at' => now(), 'updated_at' => now()],
            ['id_kategori' => 'K014', 'nama_kategori' => 'STICKY NOTE', 'status' => 'aktif', 'created_at' => now(), 'updated_at' => now()],
            ['id_kategori' => 'K015', 'nama_kategori' => 'NOTA KONTAN', 'status' => 'aktif', 'created_at' => now(), 'updated_at' => now()],
            ['id_kategori' => 'K016', 'nama_kategori' => 'ORDNER MARBLE', 'status' => 'aktif', 'created_at' => now(), 'updated_at' => now()],
            ['id_kategori' => 'K017', 'nama_kategori' => 'ORDNER PLASTIK PLUS', 'status' => 'aktif', 'created_at' => now(), 'updated_at' => now()],
            ['id_kategori' => 'K018', 'nama_kategori' => 'ORDNER MARBLE PREMIUM', 'status' => 'aktif', 'created_at' => now(), 'updated_at' => now()],
            ['id_kategori' => 'K019', 'nama_kategori' => 'CLIP & SPRING FILE', 'status' => 'aktif', 'created_at' => now(), 'updated_at' => now()],
            ['id_kategori' => 'K020', 'nama_kategori' => 'ORDNER PVC', 'status' => 'aktif', 'created_at' => now(), 'updated_at' => now()],
            ['id_kategori' => 'K021', 'nama_kategori' => 'PP-BUSSINES FILE', 'status' => 'aktif', 'created_at' => now(), 'updated_at' => now()],
            ['id_kategori' => 'K022', 'nama_kategori' => 'PP-SCHOOL BAG', 'status' => 'aktif', 'created_at' => now(), 'updated_at' => now()],
            ['id_kategori' => 'K023', 'nama_kategori' => 'PP-ZIPPER BAG', 'status' => 'aktif', 'created_at' => now(), 'updated_at' => now()],
            ['id_kategori' => 'K024', 'nama_kategori' => 'MAP HANGMAP', 'status' => 'aktif', 'created_at' => now(), 'updated_at' => now()],
            ['id_kategori' => 'K025', 'nama_kategori' => 'THERMAL ROLL', 'status' => 'aktif', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('kategori')->insert($kategoris);
    }
}
