<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Barang Keluar</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 4px; }
        th { background-color: #f2f2f2; }
        .title { text-align: center; font-size: 14px; margin-bottom: 10px; }
    </style>
</head>
<body>

<div class="title">
    <strong>LAPORAN BARANG KELUAR</strong><br>
    Dicetak: {{ $tanggal_cetak }}
</div>

<table>
    <thead>
        <tr>
            <th>ID Keluar</th>
            <th>Dicatat Oleh</th>
            <th>Tanggal Keluar</th>
            <th>Nama Barang</th>
            <th>Kategori</th>
            <th>Jumlah Keluar</th>
            <th>Total Harga</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $row)
            <tr>
                <td>{{ $row->id_keluar }}</td>
                <td>{{ optional($row->user)->name }}</td>
                <td>{{ $row->tgl_keluar }}</td>
                <td>{{ optional($row->barang)->nama_barang }}</td>
                <td>{{ optional($row->barang?->kategori)->nama_kategori }}</td>
                <td>{{ $row->jumlah_keluar }}</td>
                <td>{{ number_format($row->total_harga, 0, ',', '.') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>