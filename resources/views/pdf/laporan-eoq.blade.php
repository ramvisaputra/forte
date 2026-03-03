<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 4px; }
        th { background: #f0f0f0; }
    </style>
</head>
<body>

<h3>Laporan Perhitungan EOQ Tahun {{ $tahun }}</h3>
<p>Tanggal Cetak: {{ $cetak }}</p>

<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Id Barang</th>
            <th>Nama Barang</th>
            {{-- <th>Kategori</th> --}}
            <th>Permintaan</th>
            <th>Biaya Pesan</th>
            <th>Biaya Simpan</th>
            <th>EOQ</th>
            <th>Frekuensi Pesan</th>
            <th>Total Pemesanan</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $i => $row)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $row->id_barang }}</td>
            <td>{{ $row->nama_barang }}</td>
            {{-- <td>{{ $row->nama_kategori }}</td> --}}
            <td>{{ $row->permintaan_tahunan }}</td>
            <td>{{ number_format($row->biaya_pesan) }}</td>
            <td>{{ number_format($row->biaya_simpan) }}</td>
            <td>{{ $row->eoq }}</td>
            <td>{{ $row->frekuensi_pesan }}</td>
            <td>{{ number_format($row->total_pemesanan) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>