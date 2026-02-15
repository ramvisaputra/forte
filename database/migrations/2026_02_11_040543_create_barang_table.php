<?php
 
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('barang', function (Blueprint $table) {
            $table->string('id_barang')->primary();
            $table->string('nama_barang');

            $table->string('id_kategori');
            $table->foreign('id_kategori')
                  ->references('id_kategori')
                  ->on('kategori')
                  ->cascadeOnDelete();

            $table->decimal('harga_masuk', 15, 2)->nullable();
            $table->decimal('harga_keluar', 15, 2)->nullable();
            $table->integer('biaya_pesan');
            $table->integer('biaya_simpan');
            $table->integer('stok_minimum');
            $table->integer('stok')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barangs', function (Blueprint $table) {
            $table->dropForeign(['id_kategori']);
        });
        Schema::dropIfExists('barangs');
    }
};
