@extends('layouts/app')

@section('title', 'Stock Barang Dagangan')

@section('content')
    <style>
        .table-section {
            display: block;
        }

        .table-section.hidden {
            display: none;
        }

        .table-responsive {
            max-height: 500px;
            overflow-y: auto;
        }

        .table thead {
            position: sticky;
            top: 0;
            z-index: 1;
            background-color: #343a40;
            color: #fff;
        }

        .table thead th {
            border-bottom: 2px solid #dee2e6;
        }

        .highlight {
            background-color: yellow;
            font-weight: bold;
        }

        .no-results-message {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 1rem;
            text-align: center;
            color: #6c757d;
        }

        .btn.disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .btn-group .btn {
            margin-right: 0;
        }

        .btn-group .btn:not(:last-child) {
            border-right: none;
        }

        .btn-sm {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }

        .btn.disabled,
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            pointer-events: none;
        }

        .btn-group {
            display: inline-flex;
            vertical-align: middle;
        }

        .btn-group .btn {
            margin-right: 0;
            position: relative;
            flex: 0 1 auto;
        }

        .btn-group .btn:not(:first-child) {
            margin-left: -1px;
        }

        .btn-group .btn:not(:last-child) {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        .btn-group .btn:not(:first-child) {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }

        .btn-sm {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            line-height: 1.5;
        }

        .btn-sm .fas {
            font-size: 0.7rem;
            margin-right: 0.25rem;
        }

        .btn[title] {
            cursor: help;
        }

        .btn:disabled[title] {
            cursor: not-allowed;
        }

        @media (max-width: 768px) {
            .btn-group {
                display: flex;
                flex-direction: column;
                width: 100%;
            }

            .btn-group .btn {
                margin-left: 0;
                margin-bottom: 0.25rem;
                border-radius: 0.375rem !important;
            }

            .btn-group .btn:last-child {
                margin-bottom: 0;
            }
        }

        .mode-switch-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-check:checked+.btn-outline-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
            color: white;
        }

        .btn-check:checked+.btn-outline-success {
            background-color: #198754;
            border-color: #198754;
            color: white;
        }

        .mode-switch-container .btn {
            transition: all 0.3s ease;
            font-size: 0.875rem;
            padding: 0.375rem 0.75rem;
        }

        .mode-switch-container .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        #mode-indicator {
            white-space: nowrap;
        }

        /* Animation for mode switch */
        .table {
            transition: all 0.3s ease;
        }

        @media (max-width: 768px) {
            .d-flex.justify-content-between {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .mode-switch-container {
                width: 100%;
                justify-content: space-between;
            }

            .mode-switch-container .btn {
                flex: 1;
                font-size: 0.8rem;
            }
        }

        .applied-cost-detail {
            max-height: 100px;
            overflow-y: auto;
            font-size: 0.875rem;
        }

        .applied-cost-item {
            display: flex;
            justify-content: space-between;
            padding: 2px 0;
            border-bottom: 1px solid #eee;
        }

        .applied-cost-item:last-child {
            border-bottom: none;
        }

        .status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }

        .table tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
        }

        input[name="appliedCostSelection"]:checked+td {
            background-color: rgba(255, 193, 7, 0.2);
        }

        #appliedCostModeSelection {
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Styling untuk kolom HPP di mode manajemen */
        .management-mode .hpp-column {
            background-color: rgba(40, 167, 69, 0.1);
            /* Hijau muda transparan */
            transition: background-color 0.3s ease;
        }

        /* Styling untuk indikator Plus */
        .hpp-plus-indicator {
            font-size: 0.75rem;
            color: #28a745;
            /* Warna hijau */
            font-weight: bold;
            margin-top: 0.25rem;
            text-align: center;
            animation: fadeIn 0.5s ease-in-out;
            display: none;
            /* Sembunyikan secara default */
        }

        /* Animasi fade-in untuk indikator */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(5px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Pastikan indikator muncul di mode manajemen */
        .management-mode .hpp-plus-indicator {
            display: block;
        }

        /* Responsif untuk layar kecil */
        @media (max-width: 768px) {
            .hpp-plus-indicator {
                font-size: 0.65rem;
            }
        }
    </style>

    <!-- Notifikasi -->
    @if (session('success'))
        <div id="success-message" class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div id="error-message" class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if ($errors->any())
        <div id="error-message" class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="mt-4">
        <!-- Main Filter Form -->
        <div class="mb-4">
            <div class="card">
                <div class="card-body">
                    <form id="filterForm" method="GET" action="{{ route('stock_page') }}"
                        class="d-flex flex-wrap align-items-end gap-3">
                        <div class="flex-shrink-0">
                            <label for="start_date" class="form-label small">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control"
                                value="{{ request('start_date', now()->startOfYear()->toDateString()) }}"
                                min="{{ now()->subYears(5)->startOfYear()->toDateString() }}"
                                max="{{ now()->toDateString() }}">
                        </div>
                        <div class="flex-shrink-0">
                            <label for="end_date" class="form-label small">End Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control"
                                value="{{ request('end_date', now()->toDateString()) }}"
                                min="{{ request('start_date', now()->startOfYear()->toDateString()) }}"
                                max="{{ now()->toDateString() }}">
                        </div>
                        <div class="flex-shrink-0" hidden>
                            <input type="hidden" name="table_filter" id="table_filter"
                                value="{{ request('table_filter', 'all') }}">
                            <div class="dropdown me-2">
                                <button class="btn btn-light dropdown-toggle" type="button" id="tableFilterDropdown"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    {{ request('table_filter', 'all') == 'all' ? 'Semua Table' : (request('table_filter') == 'stocks' ? 'Table Stocks' : (request('table_filter') == 'transfer_stocks' ? 'Table Transfer Stocks' : 'Table Used Stocks')) }}
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="tableFilterDropdown">
                                    <li><a class="dropdown-item" href="#" data-filter="all">Semua Table</a></li>
                                    <li><a class="dropdown-item" href="#" data-filter="stocks">Table Stok</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('stock.export') . '?start_date=' . request('start_date', now()->startOfYear()->toDateString()) . '&end_date=' . request('end_date', now()->toDateString()) . '&table_filter=' . request('table_filter', 'all') }}"
                                class="btn btn-success">Export to Excel</a>
                            <!-- New Button: Perhitungan Beban -->
                            @if (App\Http\Controllers\Auth\AuthController::isMaster())
                                <button type="button" class="btn btn-warning" data-bs-toggle="modal"
                                    data-bs-target="#loadCalculationModal">
                                    Perhitungan Beban
                                </button>
                                <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal"
                                    data-bs-target="#appliedCostHistoryModal">
                                    <i class="fas fa-history me-1"></i>
                                    Riwayat Perhitungan Beban
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Global Search Filter -->
        <div class="mb-4">
            <div class="input-group">
                <input type="text" id="globalSearch" class="form-control" placeholder="Cari nama barang atau ukuran...">
                <button class="btn btn-outline-secondary" type="button" id="clearSearch">Clear</button>
            </div>
            <small id="searchCount" class="form-text text-muted mt-2"></small>
        </div>

        <!-- Tables Section -->
        <div class="table-container">
            <!-- Stocks Table -->
            <div class="table-section mb-4" data-table="stocks">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="mb-0">Stok Barang Dagangan</h3>

                    <!-- Toggle Switch for Accounting/Management Mode -->
                    @if (App\Http\Controllers\Auth\AuthController::isMaster())
                        <div class="mode-switch-container">
                            <div class="btn-group" role="group" aria-label="Mode Selection">
                                <input type="radio" class="btn-check" name="stockMode" id="accounting-mode"
                                    value="accounting" checked>
                                <label class="btn btn-outline-primary" for="accounting-mode">
                                    <i class="fas fa-calculator me-1"></i>
                                    Akuntansi
                                </label>

                                <input type="radio" class="btn-check" name="stockMode" id="management-mode"
                                    value="management">
                                <label class="btn btn-outline-success" for="management-mode">
                                    <i class="fas fa-chart-line me-1"></i>
                                    Manajemen
                                </label>
                            </div>

                            <!-- Mode Indicator -->
                            <small class="text-muted ms-2" id="mode-indicator">
                                Mode: <span class="fw-bold text-primary">Akuntansi</span>
                            </small>
                        </div>
                    @endif
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover text-center">
                        <thead class="table-dark">
                            <tr>
                                <th>No</th>
                                <th>Nama Barang</th>
                                <th>Ukuran</th>
                                <th colspan="2">Saldo Awal</th>
                                <th colspan="2">Masuk Barang</th>
                                <th colspan="2">Keluar Barang</th>
                                <th colspan="2">Saldo Tersedia (Akhir)</th>
                                <th>Detail</th>
                            </tr>
                            <tr>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th>Qty</th>
                                <th class="hpp-column">HPP</th>
                                <th>Qty</th>
                                <th class="hpp-column">HPP</th>
                                <th>Qty</th>
                                <th class="hpp-column">HPP</th>
                                <th>Qty</th>
                                <th class="hpp-column">HPP</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $itemCount = 1;
                                $currentItem = '';
                            @endphp
                            @if (is_array($stockData) && !empty($stockData))
                                @foreach ($stockData as $item => $sizes)
                                    @foreach ($sizes as $index => $stock)
                                        <tr data-item="{{ htmlspecialchars($stock->item ?? 'Unknown Item') }}"
                                            data-size="{{ htmlspecialchars($stock->size ?? 'Unknown Size') }}"
                                            data-opening-hpp="{{ number_format($stock->opening_hpp ?? 0, 2, '.', '') }}"
                                            data-incoming-hpp="{{ number_format($stock->incoming_hpp ?? 0, 2, '.', '') }}"
                                            data-outgoing-hpp="{{ number_format($stock->outgoing_hpp ?? 0, 2, '.', '') }}"
                                            data-final-hpp="{{ number_format($stock->final_hpp ?? 0, 2, '.', '') }}">
                                            @if ($index === 0)
                                                <td rowspan="{{ count($sizes) }}">{{ $itemCount++ }}</td>
                                                <td rowspan="{{ count($sizes) }}" class="item-name">
                                                    {{ htmlspecialchars($stock->item ?? 'Unknown Item') }}</td>
                                            @endif
                                            <td class="item-size">{{ htmlspecialchars($stock->size ?? 'Unknown Size') }}
                                            </td>
                                            <td>{{ $stock->opening_qty ?? 0 }}</td>
                                            <td class="hpp-column">
                                                Rp. {{ number_format($stock->opening_hpp ?? 0, 2, ',', '.') }}
                                                <div class="hpp-plus-indicator"></div>
                                            </td>
                                            <td>{{ $stock->incoming_qty ?? 0 }}</td>
                                            <td class="hpp-column">
                                                Rp. {{ number_format($stock->incoming_hpp ?? 0, 2, ',', '.') }}
                                                <div class="hpp-plus-indicator"></div>
                                            </td>
                                            <td>{{ $stock->outgoing_qty ?? 0 }}</td>
                                            <td class="hpp-column">
                                                Rp. {{ number_format($stock->outgoing_hpp ?? 0, 2, ',', '.') }}
                                                <div class="hpp-plus-indicator"></div>
                                            </td>
                                            <td>{{ $stock->final_stock_qty ?? 0 }}</td>
                                            <td class="hpp-column">
                                                Rp. {{ number_format($stock->final_hpp ?? 0, 2, ',', '.') }}
                                                <div class="hpp-plus-indicator"></div>
                                            </td>
                                            <td>
                                                @if ($stock->id)
                                                    <button class="btn btn-sm btn-primary detail-btn"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#detailModal_stocks_{{ $stock->id }}"
                                                        data-stock-id="{{ $stock->id }}"
                                                        data-item="{{ htmlspecialchars($stock->item ?? 'Unknown Item') }}"
                                                        data-table-name="stocks">Detail</button>
                                                @else
                                                    <span>No Detail</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="12" class="text-center">
                                        <div class="alert alert-info mb-0">Data stok belum ditemukan.</div>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Transaction Detail Modals -->
        @php
            $allStocks = [
                'stocks' => collect($stockData)->flatten()->toArray() ?? [],
            ];
        @endphp
        @foreach ($allStocks as $tableName => $stocks)
            @foreach ($stocks as $stock)
                @if ($stock->id)
                    <div class="modal fade" id="detailModal_{{ $tableName }}_{{ $stock->id }}" tabindex="-1"
                        aria-labelledby="detailModalLabel_{{ $tableName }}_{{ $stock->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content" data-table-name="{{ $tableName }}">
                                <div class="modal-header">
                                    <h5 class="modal-title"
                                        id="detailModalLabel_{{ $tableName }}_{{ $stock->id }}">Detail Transaksi
                                        untuk {{ htmlspecialchars($stock->item ?? 'Unknown Item') }}
                                        ({{ $tableName }})
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="modal_filter_{{ $tableName }}_{{ $stock->id }}"
                                            class="form-label">Tampilkan Transaksi</label>
                                        <select id="modal_filter_{{ $tableName }}_{{ $stock->id }}"
                                            class="form-select modal-filter" data-stock-id="{{ $stock->id }}">
                                            <option value="all"
                                                {{ request('filter', 'all') == 'all' ? 'selected' : '' }}>Semua Transaksi
                                            </option>
                                            <option value="7_days"
                                                {{ request('filter', 'all') == '7_days' ? 'selected' : '' }}>7 Hari
                                                Terakhir</option>
                                            <option value="1_month"
                                                {{ request('filter', 'all') == '1_month' ? 'selected' : '' }}>1 Bulan
                                                Terakhir</option>
                                        </select>
                                    </div>
                                    <div class="transaction-table"
                                        id="transactionTable_{{ $tableName }}_{{ $stock->id }}">
                                        @if (isset($stock->transactions) && !empty($stock->transactions))
                                            <div class="table-responsive">
                                                <table class="table table-striped table-bordered table-hover text-center">
                                                    <thead class="table-dark">
                                                        <tr>
                                                            <th>No</th>
                                                            <th>Nama Item</th>
                                                            <th>No Voucher</th>
                                                            <th>Tipe Transaksi</th>
                                                            <th>Kuantitas</th>
                                                            <th>Nominal</th>
                                                            <th>Tanggal</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($stock->transactions as $transaction)
                                                            @if (!str_starts_with($transaction->description ?? '', 'HPP '))
                                                                <tr
                                                                    data-transaction-date="{{ $transaction->created_at }}">
                                                                    <td>{{ $loop->iteration }}</td>
                                                                    <td>{{ htmlspecialchars($transaction->description ?? 'No Description') }}
                                                                    </td>
                                                                    <td>
                                                                        @if ($transaction->voucher_id && $transaction->voucher_number !== 'N/A')
                                                                            <a href="{{ route('voucher_detail', $transaction->voucher_id) }}"
                                                                                class="text-decoration-none">
                                                                                {{ htmlspecialchars($transaction->voucher_number) }}
                                                                            </a>
                                                                        @else
                                                                            {{ htmlspecialchars($transaction->voucher_number ?? 'No Voucher') }}
                                                                        @endif
                                                                    </td>
                                                                    <td>
                                                                        @switch($transaction->voucher_type)
                                                                            @case('PJ')
                                                                                Penjualan
                                                                            @break

                                                                            @case('PB')
                                                                                Pembelian
                                                                            @break

                                                                            @case('PYK')
                                                                                Penyesuaian Berkurang
                                                                            @break

                                                                            @case('PYB')
                                                                                Penyesuaian Bertambah
                                                                            @break

                                                                            @case('RPB')
                                                                                Retur Pembelian
                                                                            @break

                                                                            @case('RPJ')
                                                                                Retur Penjualan
                                                                            @break

                                                                            @default
                                                                                {{ htmlspecialchars($transaction->voucher_type ?? 'Unknown') }}
                                                                        @endswitch
                                                                    </td>
                                                                    <td>{{ $transaction->quantity ?? ($transaction->transaction_quantity ?? 0) }}
                                                                    </td>
                                                                    <td>{{ number_format($transaction->nominal ?? 0, 2, ',', '.') }}
                                                                    </td>
                                                                    <td>{{ \Carbon\Carbon::parse($transaction->created_at ?? now())->format('d-m-Y') }}
                                                                    </td>
                                                                </tr>
                                                            @endif
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <p class="text-center">Tidak ada transaksi terkait untuk barang ini.</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Tutup</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        @endforeach

        <!-- Modal Perhitungan Beban -->
        <div class="modal fade" id="loadCalculationModal" tabindex="-1" aria-labelledby="loadCalculationModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="loadCalculationModalLabel">Perhitungan Beban</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="loadCalculationForm" action="{{ route('applied_cost.store') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div id="errorMessage" class="alert alert-danger" style="display: none;"></div>
                            <div id="bebanInputs">
                                <!-- Default Initial Row -->
                                <div class="input-group mb-2 beban-row">
                                    <span class="input-group-text">Beban 1</span>
                                    <input type="text"
                                        class="form-control @error('beban_description.*') is-invalid @enderror"
                                        name="beban_description[]" placeholder="Deskripsi Beban (e.g., Beban Operasional)"
                                        required>
                                    <input type="number"
                                        class="form-control @error('beban_nominal.*') is-invalid @enderror"
                                        name="beban_nominal[]" placeholder="Nominal (Rp)" step="0.01" min="0"
                                        required>
                                    <button type="button" class="btn btn-outline-danger remove-row"
                                        style="display: none;">Hapus</button>
                                    @error('beban_description.*')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @error('beban_nominal.*')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <button type="button" class="btn btn-outline-primary mb-3" id="addBebanRow">Tambah
                                Beban</button>
                            <div class="row">
                                <div class="col-md-8"></div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Total Akumulasi:</label>
                                    <input type="text" class="form-control bg-light" id="totalAkumulasi" readonly
                                        value="Rp 0">
                                    <input type="hidden" name="total" id="totalHidden" value="0">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Hitung & Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Applied Cost History Modal -->
        <div class="modal fade" id="appliedCostHistoryModal" tabindex="-1"
            aria-labelledby="appliedCostHistoryModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title" id="appliedCostHistoryModalLabel">
                            <i class="fas fa-history me-2"></i>
                            Riwayat Perhitungan Beban
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Mode Selection for Applied Cost -->
                        @if ($currentMode == 'management')
                            <div class="row mb-3" id="appliedCostModeSelection">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Mode Manajemen Aktif:</strong> Pilih riwayat perhitungan beban untuk
                                        diterapkan
                                        pada perhitungan HPP.
                                    </div>
                                    <div class="d-flex gap-2 align-items-center">
                                        <label class="form-label mb-0">Pilih Riwayat yang Digunakan:</label>
                                        <select class="form-select w-auto" id="selectedAppliedCostId"
                                            name="applied_cost_id">
                                            <option value="">Pilih Riwayat...</option>
                                            @foreach ($appliedCostHistory as $cost)
                                                <option value="{{ $cost->id }}"
                                                    {{ $selectedAppliedCostId == $cost->id ? 'selected' : '' }}>
                                                    #{{ $cost->id }} - Rp.
                                                    {{ number_format($cost->total_nominal, 2, ',', '.') }}
                                                    ({{ $cost->created_at->format('d M Y H:i') }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-sm btn-success" id="applySelectedCost">
                                            <i class="fas fa-check me-1"></i>
                                            Terapkan
                                        </button>
                                        <button type="button" class="btn btn-sm btn-secondary" id="clearSelectedCost">
                                            <i class="fas fa-times me-1"></i>
                                            Reset
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Search and Filter -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <input type="text" class="form-control" id="historySearch"
                                    placeholder="Cari berdasarkan tanggal atau deskripsi...">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" id="historyDateFilter">
                                    <option value="">Semua Tanggal</option>
                                    <option value="today">Hari Ini</option>
                                    <option value="week">Minggu Ini</option>
                                    <option value="month">Bulan Ini</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="button" class="btn btn-outline-primary w-100" id="refreshHistory">
                                    <i class="fas fa-sync-alt me-1"></i>
                                    Refresh
                                </button>
                            </div>
                        </div>

                        <!-- History Table -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="appliedCostHistoryTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th width="5%">
                                            <input type="radio" name="appliedCostSelection" value=""
                                                id="noCostSelection" checked>
                                        </th>
                                        <th width="10%">ID</th>
                                        <th width="15%">Tanggal</th>
                                        <th width="15%">Total Nominal</th>
                                        <th width="40%">Detail Beban</th>
                                        <th width="10%">Status</th>
                                        <th width="5%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="historyTableBody">
                                    @if ($appliedCostHistory->isEmpty())
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                                Tidak ada riwayat perhitungan beban
                                            </td>
                                        </tr>
                                    @else
                                        @foreach ($appliedCostHistory as $item)
                                            <tr data-date="{{ $item->created_at->toDateString() }}"
                                                data-description="{{ collect($item->details)->pluck('description')->implode(' ') }}">
                                                <td>
                                                    <input type="radio" name="appliedCostSelection"
                                                        value="{{ $item->id }}"
                                                        {{ $selectedAppliedCostId == $item->id ? 'checked' : '' }}>
                                                </td>
                                                <td><strong>#{{ $item->id }}</strong></td>
                                                <td>{{ $item->created_at->format('d M Y H:i') }}</td>
                                                <td><strong class="text-success">Rp.
                                                        {{ number_format($item->total_nominal, 2, ',', '.') }}</strong>
                                                </td>
                                                <td>
                                                    <div class="applied-cost-detail">
                                                        @foreach ($item->details as $detail)
                                                            <div class="applied-cost-item">
                                                                <span>{{ $detail->description }}</span>
                                                                <span class="text-success">Rp.
                                                                    {{ number_format($detail->nominal, 2, ',', '.') }}</span>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge status-badge {{ $selectedAppliedCostId == $item->id ? 'bg-success' : 'bg-secondary' }}">
                                                        {{ $selectedAppliedCostId == $item->id ? 'Aktif' : 'Tidak Aktif' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-primary view-detail-btn"
                                                        data-cost-id="{{ $item->id }}" title="Lihat Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <nav aria-label="History pagination">
                            {{ $appliedCostHistory->links() }}
                        </nav>
                    </div>
                    <div class="modal-footer">
                        <div class="d-flex justify-content-between w-100">
                            <div>
                                <span class="text-muted" id="historyCount">Total: {{ $appliedCostHistory->total() }}
                                    riwayat</span>
                            </div>
                            <div>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Data resep dan riwayat beban dari controller
            const appliedCostHistory = @json($appliedCostHistory->items() ?? []); // Data riwayat untuk JS
            let currentSelectedCostId = '{{ $selectedAppliedCostId ?? '' }}';

            // Define variables with null checks
            const form = document.getElementById('filterForm');
            const tableFilterInput = document.getElementById('table_filter');
            const dropdownButton = document.getElementById('tableFilterDropdown');
            const tableSections = document.querySelectorAll('.table-section');
            const globalSearch = document.getElementById('globalSearch');
            const clearSearch = document.getElementById('clearSearch');
            const searchCount = document.getElementById('searchCount');
            const addBebanRow = document.getElementById('addBebanRow');
            const bebanInputs = document.getElementById('bebanInputs');
            const totalAkumulasi = document.getElementById('totalAkumulasi');
            const totalHidden = document.getElementById('totalHidden');
            const loadCalculationForm = document.getElementById('loadCalculationForm');
            const errorMessage = document.getElementById('errorMessage');
            const accountingMode = document.getElementById('accounting-mode');
            const managementMode = document.getElementById('management-mode');
            const modeIndicator = document.getElementById('mode-indicator');
            const tableContainer = document.querySelector('[data-table="stocks"]');

            // Applied Cost Modal Elements (conditional for masters)
            const appliedCostHistoryModal = document.getElementById('appliedCostHistoryModal');
            const applySelectedCostBtn = document.getElementById('applySelectedCost');
            const clearSelectedCostBtn = document.getElementById('clearSelectedCost');
            const historySearch = document.getElementById('historySearch');
            const historyDateFilter = document.getElementById('historyDateFilter');
            const refreshHistoryBtn = document.getElementById('refreshHistory');
            const historyCount = document.getElementById('historyCount');
            const selectedAppliedCostId = document.getElementById('selectedAppliedCostId');

            // Function to format currency
            function formatCurrency(value) {
                return 'Rp. ' + Number(value).toLocaleString('id-ID', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            // Function to update HPP columns with applied cost and show Plus indicator
            function updateHPPColumns(totalNominal = 0) {
                if (!tableContainer) return;

                const isManagementMode = managementMode && managementMode.checked;
                const rows = tableContainer.querySelectorAll('tbody tr');

                rows.forEach(row => {
                    const hppCells = row.querySelectorAll('.hpp-column');
                    if (!hppCells.length) return;

                    // Original HPP values stored in data attributes
                    const openingHpp = parseFloat(row.dataset.openingHpp || 0);
                    const incomingHpp = parseFloat(row.dataset.incomingHpp || 0);
                    const outgoingHpp = parseFloat(row.dataset.outgoingHpp || 0);
                    const finalHpp = parseFloat(row.dataset.finalHpp || 0);

                    // Debug logging to verify values
                    console.log('HPP Values for row:', {
                        item: row.dataset.item,
                        size: row.dataset.size,
                        openingHpp,
                        incomingHpp,
                        outgoingHpp,
                        finalHpp,
                        totalNominal
                    });

                    // Update HPP cells and Plus indicators
                    const updateCell = (cell, originalHpp) => {
                        const plusIndicator = cell.querySelector('.hpp-plus-indicator');
                        if (isManagementMode && totalNominal > 0) {
                            const newHpp = originalHpp + totalNominal;
                            cell.firstChild.textContent = formatCurrency(newHpp);
                            if (plusIndicator) {
                                plusIndicator.textContent = `+ ${formatCurrency(totalNominal)}`;
                            }
                        } else {
                            cell.firstChild.textContent = formatCurrency(originalHpp);
                            if (plusIndicator) {
                                plusIndicator.textContent = '';
                            }
                        }
                    };

                    updateCell(hppCells[0], openingHpp);
                    updateCell(hppCells[1], incomingHpp);
                    updateCell(hppCells[2], outgoingHpp);
                    updateCell(hppCells[3], finalHpp);
                });
            }

            // Function to auto-select first applied cost
            function autoSelectFirstCost() {
                if (!appliedCostHistoryModal || !selectedAppliedCostId) return;

                // Select first applied cost
                const firstCost = appliedCostHistory[0];

                if (firstCost) {
                    currentSelectedCostId = firstCost.id;
                    selectedAppliedCostId.value = firstCost.id;

                    // Update radio button
                    const radio = document.querySelector(
                        `input[name="appliedCostSelection"][value="${firstCost.id}"]`);
                    if (radio) radio.checked = true;

                    // Update status badges
                    document.querySelectorAll('#historyTableBody tr').forEach(row => {
                        const badge = row.querySelector('.status-badge');
                        const rowRadio = row.querySelector('input[name="appliedCostSelection"]');
                        if (badge && rowRadio && rowRadio.value === firstCost.id) {
                            badge.textContent = 'Aktif';
                            badge.classList.remove('bg-secondary');
                            badge.classList.add('bg-success');
                        } else if (badge) {
                            badge.textContent = 'Tidak Aktif';
                            badge.classList.remove('bg-success');
                            badge.classList.add('bg-secondary');
                        }
                    });

                    // Update HPP with total_nominal
                    console.log('Applying first cost:', firstCost);
                    updateHPPColumns(parseFloat(firstCost.total_nominal || 0));

                    // Submit form to apply cost
                    if (form && tableFilterInput) {
                        form.action = '{{ route('stock_page') }}?start_date=' + document.getElementById(
                                'start_date').value +
                            '&end_date=' + document.getElementById('end_date').value +
                            '&table_filter=' + tableFilterInput.value +
                            '&mode=management&applied_cost_id=' + firstCost.id;
                        form.submit();
                    }
                } else {
                    // No history available
                    alert('Tidak ada riwayat perhitungan beban untuk mode manajemen.');
                    updateHPPColumns(0);
                }
            }

            // Initialize applied cost modal logic if elements exist (master user)
            if (appliedCostHistoryModal) {
                // Modal shown event
                appliedCostHistoryModal.addEventListener('shown.bs.modal', function() {
                    const isManagementMode = managementMode && managementMode.checked;
                    const modeSelection = document.getElementById('appliedCostModeSelection');
                    if (modeSelection) {
                        modeSelection.style.display = isManagementMode ? 'block' : 'none';
                    }
                });

                // Search functionality
                if (historySearch) {
                    historySearch.addEventListener('input', debounce(filterHistory, 300));
                }

                // Date filter
                if (historyDateFilter) {
                    historyDateFilter.addEventListener('change', filterHistory);
                }

                // Refresh button
                if (refreshHistoryBtn) {
                    refreshHistoryBtn.addEventListener('click', function() {
                        if (historySearch) historySearch.value = '';
                        if (historyDateFilter) historyDateFilter.value = '';
                        filterHistory();
                    });
                }

                // Apply selected cost
                if (applySelectedCostBtn) {
                    applySelectedCostBtn.addEventListener('click', function() {
                        if (!selectedAppliedCostId || !selectedAppliedCostId.value) {
                            alert('Silakan pilih riwayat perhitungan beban terlebih dahulu.');
                            return;
                        }

                        const selectedId = selectedAppliedCostId.value;
                        currentSelectedCostId = selectedId;

                        // Update HPP with selected cost's total_nominal
                        const selectedCost = appliedCostHistory.find(cost => cost.id == selectedId);
                        if (selectedCost) {
                            console.log('Applying selected cost:', selectedCost);
                            updateHPPColumns(parseFloat(selectedCost.total_nominal || 0));
                        }

                        // Update form action and submit
                        if (form && tableFilterInput) {
                            form.action = '{{ route('stock_page') }}?start_date=' + document
                                .getElementById('start_date').value +
                                '&end_date=' + document.getElementById('end_date').value +
                                '&table_filter=' + tableFilterInput.value +
                                '&mode=management&applied_cost_id=' + selectedId;
                            form.submit();
                        }
                    });
                }

                // Clear selected cost
                if (clearSelectedCostBtn) {
                    clearSelectedCostBtn.addEventListener('click', function() {
                        currentSelectedCostId = '';
                        if (selectedAppliedCostId) selectedAppliedCostId.value = '';
                        const radios = document.querySelectorAll('input[name="appliedCostSelection"]');
                        radios.forEach(radio => radio.checked = radio.value === '');

                        // Update status badges
                        const rows = document.querySelectorAll('#historyTableBody tr');
                        rows.forEach(row => {
                            const badge = row.querySelector('.status-badge');
                            if (badge) {
                                badge.textContent = 'Tidak Aktif';
                                badge.classList.remove('bg-success');
                                badge.classList.add('bg-secondary');
                            }
                        });

                        // Reset HPP to original values
                        updateHPPColumns(0);

                        // Redirect to clear applied_cost_id
                        if (form && tableFilterInput) {
                            form.action = '{{ route('stock_page') }}?start_date=' + document
                                .getElementById('start_date').value +
                                '&end_date=' + document.getElementById('end_date').value +
                                '&table_filter=' + tableFilterInput.value +
                                '&mode=accounting';
                            form.submit();
                        }
                    });
                }

                // View detail buttons
                document.querySelectorAll('.view-detail-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        const costId = this.dataset.costId;
                        const row = document.querySelector(
                            `#historyTableBody tr:has(input[value="${costId}"])`);
                        if (row) {
                            const detailDiv = row.querySelector('.applied-cost-detail');
                            if (detailDiv) {
                                detailDiv.style.display = detailDiv.style.display === 'none' ?
                                    'block' : 'none';
                            }
                        }
                    });
                });

                // Handle applied cost selection (radio buttons)
                document.querySelectorAll('input[name="appliedCostSelection"]').forEach(radio => {
                    radio.addEventListener('change', function() {
                        const selectedId = this.value;
                        if (selectedAppliedCostId) selectedAppliedCostId.value = selectedId;

                        // Update status badges
                        const rows = document.querySelectorAll('#historyTableBody tr');
                        rows.forEach(row => {
                            const badge = row.querySelector('.status-badge');
                            const rowRadio = row.querySelector(
                                'input[name="appliedCostSelection"]');
                            if (badge && rowRadio && rowRadio.value === selectedId &&
                                rowRadio.checked) {
                                badge.textContent = 'Aktif';
                                badge.classList.remove('bg-secondary');
                                badge.classList.add('bg-success');
                            } else if (badge) {
                                badge.textContent = 'Tidak Aktif';
                                badge.classList.remove('bg-success');
                                badge.classList.add('bg-secondary');
                            }
                        });

                        // Update HPP with selected cost's total_nominal
                        const selectedCost = appliedCostHistory.find(cost => cost.id == selectedId);
                        if (selectedCost) {
                            console.log('Selected cost changed:', selectedCost);
                            updateHPPColumns(parseFloat(selectedCost.total_nominal || 0));
                        }
                    });
                });

                // Filter history function
                function filterHistory() {
                    const searchValue = (historySearch ? historySearch.value.trim().toLowerCase() : '');
                    const dateFilter = (historyDateFilter ? historyDateFilter.value : '');
                    const rows = document.querySelectorAll('#historyTableBody tr:not([data-date=""])');
                    let visibleCount = 0;

                    rows.forEach(row => {
                        const dateStr = row.dataset.date;
                        const description = (row.dataset.description || '').toLowerCase();
                        const date = new Date(dateStr);
                        const now = new Date();
                        let showRow = true;

                        if (searchValue && !description.includes(searchValue)) {
                            showRow = false;
                        }

                        if (dateFilter === 'today') {
                            showRow = showRow && date.toDateString() === now.toDateString();
                        } else if (dateFilter === 'week') {
                            showRow = showRow && date >= new Date(now.setDate(now.getDate() - 7));
                        } else if (dateFilter === 'month') {
                            showRow = showRow && date >= new Date(now.setMonth(now.getMonth() - 1));
                        }

                        row.style.display = showRow ? '' : 'none';
                        if (showRow) visibleCount++;
                    });

                    if (historyCount) {
                        historyCount.textContent = `Total: ${visibleCount} riwayat`;
                    }
                }
            }

            // Table visibility function
            function updateTableVisibility(filter) {
                if (!tableSections || !dropdownButton || !tableFilterInput) return;
                tableSections.forEach(section => {
                    section.classList.toggle('hidden', filter !== 'all' && filter !== section.dataset
                        .table);
                });
                applyGlobalFilter();
            }

            // Initialize table visibility
            if (tableFilterInput && dropdownButton) {
                const initialFilter = tableFilterInput.value || 'all';
                updateTableVisibility(initialFilter);
                dropdownButton.textContent = {
                    'all': 'Semua Table',
                    'stocks': 'Table Stocks',
                    'transfer_stocks': 'Table Transfer Stocks',
                    'used_stocks': 'Table Used Stocks'
                } [initialFilter] || 'Semua Table';
            }

            // Handle dropdown item clicks
            document.querySelectorAll('.dropdown-item[data-filter]').forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    const filterValue = this.dataset.filter;
                    if (tableFilterInput) tableFilterInput.value = filterValue;
                    if (dropdownButton) dropdownButton.textContent = this.textContent;
                    updateTableVisibility(filterValue);
                });
            });

            // Global Filter Function
            function applyGlobalFilter() {
                if (!globalSearch || !searchCount) return;
                const searchValue = globalSearch.value.trim().toLowerCase();
                let totalVisibleItems = 0;
                let currentItem = '';
                let itemRows = [];
                let itemMatches = false;

                document.querySelectorAll('.highlight').forEach(el => {
                    el.replaceWith(el.textContent);
                });

                tableSections.forEach(section => {
                    if (section.classList.contains('hidden')) return;
                    const rows = section.querySelectorAll('tbody tr');
                    rows.forEach(row => {
                        if (row.querySelector('td[colspan]')) return;

                        const item = row.dataset.item ? row.dataset.item.toLowerCase() : '';
                        const size = row.dataset.size ? row.dataset.size.toLowerCase() : '';

                        if (item !== currentItem) {
                            if (itemRows.length > 0) {
                                if (itemMatches) {
                                    itemRows.forEach(r => {
                                        r.style.display = '';
                                        totalVisibleItems++;
                                    });
                                } else {
                                    itemRows.forEach(r => r.style.display = 'none');
                                }
                            }
                            currentItem = item;
                            itemRows = [];
                            itemMatches = false;
                        }

                        itemRows.push(row);

                        const matchesItem = item.includes(searchValue);
                        const matchesSize = size.includes(searchValue);
                        if (matchesItem || matchesSize) {
                            itemMatches = true;
                            if (searchValue) {
                                if (matchesItem) {
                                    const nameTd = row.querySelector('.item-name') || row.closest(
                                        'tbody').querySelector(
                                        `td.item-name[data-original-text="${row.dataset.item}"]`
                                    );
                                    if (nameTd) {
                                        nameTd.innerHTML = nameTd.textContent.replace(new RegExp(
                                                searchValue, 'gi'), match =>
                                            `<span class="highlight">${match}</span>`);
                                    }
                                }
                                if (matchesSize) {
                                    const sizeTd = row.querySelector('.item-size');
                                    if (sizeTd) {
                                        sizeTd.innerHTML = sizeTd.textContent.replace(new RegExp(
                                                searchValue, 'gi'), match =>
                                            `<span class="highlight">${match}</span>`);
                                    }
                                }
                            }
                        }
                    });

                    if (itemRows.length > 0) {
                        if (itemMatches) {
                            itemRows.forEach(r => {
                                r.style.display = '';
                                totalVisibleItems++;
                            });
                        } else {
                            itemRows.forEach(r => r.style.display = 'none');
                        }
                    }
                });

                searchCount.textContent = searchValue ? `${totalVisibleItems} item ditemukan` : '';
            }

            // Event listeners for global search
            if (globalSearch) globalSearch.addEventListener('keyup', applyGlobalFilter);
            if (clearSearch) clearSearch.addEventListener('click', () => {
                if (globalSearch) globalSearch.value = '';
                applyGlobalFilter();
            });

            // Mode switch
            function updateMode(mode) {
                if (!modeIndicator || !tableContainer) return;
                const indicatorSpan = modeIndicator.querySelector('span');

                if (mode === 'accounting') {
                    indicatorSpan.textContent = 'Akuntansi';
                    indicatorSpan.className = 'fw-bold text-primary';
                    tableContainer.classList.remove('management-mode');
                    document.querySelectorAll('.hpp-column').forEach(col => col.style.display = '');
                    const emptyRow = document.querySelector('td[colspan="12"]');
                    if (emptyRow) emptyRow.setAttribute('colspan', '12');
                    updateHPPColumns(0); // Reset HPP
                } else if (mode === 'management') {
                    indicatorSpan.textContent = 'Manajemen';
                    indicatorSpan.className = 'fw-bold text-success';
                    tableContainer.classList.add('management-mode');
                    document.querySelectorAll('.hpp-column').forEach(col => col.style.display = '');
                    const emptyRow = document.querySelector('td[colspan="12"]');
                    if (emptyRow) emptyRow.setAttribute('colspan', '12');
                    autoSelectFirstCost(); // Auto-select first cost and update HPP
                }

                localStorage.setItem('stockViewMode', mode);

                window.dispatchEvent(new CustomEvent('stockModeChanged', {
                    detail: {
                        mode
                    }
                }));
            }

            if (accountingMode) {
                accountingMode.addEventListener('change', function() {
                    if (this.checked) {
                        updateMode('accounting');
                        if (form && tableFilterInput) {
                            form.action = '{{ route('stock_page') }}?start_date=' + document
                                .getElementById('start_date').value +
                                '&end_date=' + document.getElementById('end_date').value +
                                '&table_filter=' + tableFilterInput.value +
                                '&mode=accounting';
                            form.submit();
                        }
                    }
                });
            }

            if (managementMode) {
                managementMode.addEventListener('change', function() {
                    if (this.checked) {
                        updateMode('management');
                    }
                });
            }

            // Initialize mode
            const savedMode = localStorage.getItem('stockViewMode') || 'accounting';
            if (savedMode === 'management' && managementMode) {
                managementMode.checked = true;
                updateMode('management');
            } else if (accountingMode) {
                accountingMode.checked = true;
                updateMode('accounting');
            }

            // Apply initial HPP if in management mode and cost selected
            if (managementMode && managementMode.checked && currentSelectedCostId) {
                const selectedCost = appliedCostHistory.find(cost => cost.id == currentSelectedCostId);
                if (selectedCost) {
                    console.log('Initial cost applied:', selectedCost);
                    updateHPPColumns(parseFloat(selectedCost.total_nominal || 0));
                }
            }

            // Debounce function
            function debounce(func, wait) {
                let timeout;
                return function executedFunction(...args) {
                    const later = () => {
                        clearTimeout(timeout);
                        func(...args);
                    };
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                };
            }
        });
    </script>
@endsection
