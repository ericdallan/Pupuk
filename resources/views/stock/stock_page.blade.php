@extends('layouts/app')

@section('content')
@section('title', 'Stock Barang Dagangan')
<div class="table-responsive">
    <table class="table table-striped table-bordered table-hover text-center">
        <thead class="table-dark">
            <tr>
                <th>No</th>
                <th>Nama Barang</th>
                <th>Satuan</th>
                <th>Kuantitas</th>
                <th>Awal</th>
                <th>Masuk Barang</th>
                <th>Keluar Barang</th>
                <th>Akhir</th>
                <th>Detail</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($stockData as $stock)
            <tr>
                <td>{{ $stock->id }}</td>
                <td>{{ $stock->item }}</td>
                <td>{{ $stock->unit }}</td>
                <td>{{ $stock->quantity }}</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#detailModal{{ $stock->id }}">Detail</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@foreach ($stockData as $stock)
<div class="modal fade" id="detailModal{{ $stock->id }}" tabindex="-1" aria-labelledby="detailModalLabel{{ $stock->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailModalLabel{{ $stock->id }}">Detail Transaksi untuk {{ $stock->item }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if ($stock->transactions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover text-center">
                        <thead class="table-dark">
                            <tr>
                                <th>No</th>
                                <th>Deskripsi</th>
                                <th>Kuantitas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($stock->transactions as $transaction)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $transaction->description }}</td>
                                <td>{{ $transaction->quantity }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-center">Tidak ada transaksi terkait untuk barang ini.</p>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endforeach

@endsection