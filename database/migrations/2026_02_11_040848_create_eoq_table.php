<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('eoq', function (Blueprint $table) {
            $table->id('id_eoq');

            $table->string('id_barang');
            $table->year('tahun');

            $table->integer('permintaan_tahunan');
            $table->decimal('biaya_pesan', 15, 2);
            $table->decimal('biaya_simpan', 15, 2);
            $table->decimal('nilai_eoq', 15, 2);
            $table->decimal('frekuensi_pesan', 15, 2);
            $table->decimal('total_pemesanan', 15, 2);

            $table->timestamps();

            $table->foreign('id_barang')
                ->references('id_barang')
                ->on('barang')
                ->cascadeOnDelete();

            $table->unique(['id_barang', 'tahun']); // 1 barang 1 EOQ per tahun
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eoq');
    }
};
