<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('barang_keluar', function (Blueprint $table) {
            $table->string('id_keluar')->primary();
            $table->date('tgl_keluar');

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('id_barang');

            $table->foreign('id_barang')->references('id_barang')->on('barang')->cascadeOnDelete();

            $table->integer('jumlah_keluar');
            $table->decimal('total_harga', 15, 2)->notnull();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang_keluar');
    }
};
