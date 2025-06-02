@extends('layouts.app')

@section('title', 'Detail Barang - ' . $barang->nama_barang)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Detail Barang: <span class="fw-normal">{{ $barang->nama_barang }}</span></h1>
    <div>
        @can('barang-edit')
            <a href="{{ route('barang.edit', $barang->id) }}" class="btn btn-warning">Edit Barang</a>
        @endcan
        <a href="{{ route('barang.index') }}" class="btn btn-secondary">Kembali ke Daftar</a>
    </div>
</div>

<div class="card">
    <div class="row g-0">
        <div class="col-md-4 text-center p-3">
            @if ($barang->gambar)
                <img src="{{ asset('images/barangs/' . $barang->gambar) }}" class="img-fluid rounded" alt="{{ $barang->nama_barang }}" style="max-height: 300px; object-fit: contain;">
            @else
                <img src="https://via.placeholder.com/300x300.png?text=Tidak+Ada+Gambar" class="img-fluid rounded" alt="Tidak ada gambar" style="max-height: 300px; object-fit: contain;">
            @endif
        </div>
        <div class="col-md-8">
            <div class="card-body">
                <h5 class="card-title">{{ $barang->nama_barang }}</h5>
                <p class="card-text mb-2"><small class="text-muted">Kode: {{ $barang->kode_barang ?? '-' }}</small></p>

                <table class="table table-sm table-striped">
                    <tbody>
                        <tr>
                            <th style="width: 180px;">Deskripsi</th>
                            <td>{{ $barang->deskripsi ?? '-' }}</td>
                        </tr>

                        <tr>
                            <th style="width: 180px;">Kategori</th>
                            <td>: {{ $barang->kategori->nama_kategori ?? 'Tidak Berkategori' }}</td>
                        </tr>

                        <tr>
                            <th style="width: 180px;">Unit</th>
                            <td>: {{ $barang->unit->nama_unit ?? ($barang->unit->singkatan_unit ?? 'Tidak Ada Unit') }}</td>
                        </tr>

                        <tr>
                            <th style="width: 180px;">Lokasi</th>
                            <td>: {{ $barang->lokasi->nama_lokasi ?? 'Tidak Ada Lokasi' }} {{ $barang->lokasi?->kode_lokasi ? '(' . $barang->lokasi->kode_lokasi . ')' : '' }}</td>
                        </tr>

                        <tr>
                            <th>Stok</th>
                            <td>{{ $barang->stok }}</td>
                        </tr>
                        <tr>
                            <th>Harga Beli</th>
                            <td>Rp {{ number_format($barang->harga_beli ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                @if ($barang->status == 'aktif')
                                    <span class="badge bg-success text-capitalize">{{ $barang->status }}</span>
                                @elseif ($barang->status == 'rusak')
                                    <span class="badge bg-warning text-capitalize">{{ $barang->status }}</span>
                                @elseif ($barang->status == 'hilang')
                                    <span class="badge bg-danger text-capitalize">{{ $barang->status }}</span>
                                @else
                                    <span class="badge bg-secondary text-capitalize">{{ $barang->status }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Dibuat</th>
                            <td>{{ $barang->created_at->translatedFormat('d F Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Terakhir Diperbarui</th>
                            <td>{{ $barang->updated_at->translatedFormat('d F Y H:i') }}</td>
                        </tr>
                        {{-- Tambahkan field lain jika perlu (Kategori, Unit, Lokasi nanti) --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection