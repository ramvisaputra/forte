<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\BarangMasuk;

abstract class Controller
{
    //
}

class LaporanMasukController extends Controller
{
    public function exportPdf(Request $request)
    {
        $items = BarangMasuk::with(['barang', 'user'])->get();

        // Temporary/placeholder response: return JSON with count.
        // Replace this with actual PDF generation (dompdf/laravel-dompdf, etc.) when ready.
        return response()->json([
            'message' => 'PDF export not implemented yet',
            'count' => $items->count(),
        ]);
    }
}