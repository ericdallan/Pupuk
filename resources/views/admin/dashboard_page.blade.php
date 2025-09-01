@extends('layouts/app')
@section('title', 'Dashboard')
@section('content')
    @if (session('success'))
        <div id="success-message" class="alert alert-success" style="cursor: pointer;">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div id="error-message" class="alert alert-danger" style="cursor: pointer;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @elseif (session('message'))
        <div id="error-message" class="alert alert-danger" style="cursor: pointer;">
            {{ session('message') }}
        </div>
    @endif

    <div class="container">
        <!-- Charts -->
        <div class="row">
            <!-- Row 1, Column 1: Profit Trend -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm chart-container">
                    <div class="card-body">
                        <h3 class="card-title">Laba Bersih
                            ({{ $dashboardData['profit_trend_period'] == 'last_12_months' ? '12 Bulan Terakhir' : $dashboardData['profit_trend_year'] }})
                        </h3>
                        <form action="{{ route('dashboard_page') }}" method="GET" class="mb-3">
                            <div class="row align-items-end">
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <label for="profit_trend_period" class="form-label fw-bold">Periode</label>
                                    <select name="profit_trend_period" id="profit_trend_period"
                                        class="form-select rounded-3" onchange="toggleProfitTrendYear(this)" required
                                        aria-label="Pilih periode tren laba bersih">
                                        <option value="last_12_months"
                                            {{ $dashboardData['profit_trend_period'] == 'last_12_months' ? 'selected' : '' }}>
                                            12 Bulan Terakhir</option>
                                        <option value="yearly"
                                            {{ $dashboardData['profit_trend_period'] == 'yearly' ? 'selected' : '' }}>Tahun
                                            Tertentu</option>
                                    </select>
                                </div>
                                <div class="col-md-4 col-sm-6 mb-3" id="profit-trend-year-container"
                                    style="{{ $dashboardData['profit_trend_period'] == 'yearly' ? '' : 'display: none;' }}">
                                    <label for="profit_trend_year" class="form-label fw-bold">Tahun</label>
                                    <select name="profit_trend_year" id="profit_trend_year" class="form-select rounded-3"
                                        {{ $dashboardData['profit_trend_period'] == 'yearly' ? '' : 'disabled' }}
                                        aria-label="Pilih tahun tren laba bersih">
                                        @for ($i = \Carbon\Carbon::now()->year; $i >= \Carbon\Carbon::now()->year - 5; $i--)
                                            <option value="{{ $i }}"
                                                {{ $dashboardData['profit_trend_year'] == $i ? 'selected' : '' }}>
                                                {{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-4 col-sm-12 mb-3">
                                    <button type="submit" class="btn btn-primary px-4 rounded-3">Filter</button>
                                </div>
                            </div>
                            <!-- Hidden inputs untuk mempertahankan filter lain -->
                            <input type="hidden" name="sales_trend_period"
                                value="{{ $dashboardData['sales_trend_period'] }}">
                            @if ($dashboardData['sales_trend_period'] == 'yearly')
                                <input type="hidden" name="sales_trend_year"
                                    value="{{ $dashboardData['sales_trend_year'] }}">
                            @endif
                            <input type="hidden" name="stock_qty_month" value="{{ $dashboardData['stock_qty_month'] }}">
                            <input type="hidden" name="stock_qty_year" value="{{ $dashboardData['stock_qty_year'] }}">
                            <input type="hidden" name="stock_amount_month"
                                value="{{ $dashboardData['stock_amount_month'] }}">
                            <input type="hidden" name="stock_amount_year"
                                value="{{ $dashboardData['stock_amount_year'] }}">
                            <input type="hidden" name="stock_qty_limit" value="{{ $dashboardData['stock_qty_limit'] }}">
                            <input type="hidden" name="stock_qty_chart_type"
                                value="{{ $dashboardData['stock_qty_chart_type'] }}">
                            <input type="hidden" name="stock_amount_limit"
                                value="{{ $dashboardData['stock_amount_limit'] }}">
                            <input type="hidden" name="stock_amount_chart_type"
                                value="{{ $dashboardData['stock_amount_chart_type'] }}">
                            <input type="hidden" name="sales_month" value="{{ $dashboardData['sales_month'] }}">
                            <input type="hidden" name="sales_year" value="{{ $dashboardData['sales_year'] }}">
                            <input type="hidden" name="sales_qty_limit" value="{{ $dashboardData['sales_qty_limit'] }}">
                            <input type="hidden" name="sales_qty_chart_type"
                                value="{{ $dashboardData['sales_qty_chart_type'] }}">
                            <input type="hidden" name="sales_profit_limit"
                                value="{{ $dashboardData['sales_profit_limit'] }}">
                            <input type="hidden" name="sales_profit_chart_type"
                                value="{{ $dashboardData['sales_profit_chart_type'] }}">
                        </form>
                        <canvas id="profitTrendChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            <!-- Row 1, Column 2: Sales Trend -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm chart-container">
                    <div class="card-body">
                        <h3 class="card-title">Penjualan
                            ({{ $dashboardData['sales_trend_period'] == 'last_12_months' ? '12 Bulan Terakhir' : $dashboardData['sales_trend_year'] }})
                        </h3>
                        <form action="{{ route('dashboard_page') }}" method="GET" class="mb-3">
                            <div class="row align-items-end">
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <label for="sales_trend_period" class="form-label fw-bold">Periode</label>
                                    <select name="sales_trend_period" id="sales_trend_period"
                                        class="form-select rounded-3" onchange="toggleSalesTrendYear(this)" required
                                        aria-label="Pilih periode tren penjualan">
                                        <option value="last_12_months"
                                            {{ $dashboardData['sales_trend_period'] == 'last_12_months' ? 'selected' : '' }}>
                                            12 Bulan Terakhir</option>
                                        <option value="yearly"
                                            {{ $dashboardData['sales_trend_period'] == 'yearly' ? 'selected' : '' }}>Tahun
                                            Tertentu</option>
                                    </select>
                                </div>
                                <div class="col-md-4 col-sm-6 mb-3" id="sales-trend-year-container"
                                    style="{{ $dashboardData['sales_trend_period'] == 'yearly' ? '' : 'display: none;' }}">
                                    <label for="sales_trend_year" class="form-label fw-bold">Tahun</label>
                                    <select name="sales_trend_year" id="sales_trend_year" class="form-select rounded-3"
                                        {{ $dashboardData['sales_trend_period'] == 'yearly' ? '' : 'disabled' }}
                                        aria-label="Pilih tahun tren penjualan">
                                        @for ($i = \Carbon\Carbon::now()->year; $i >= \Carbon\Carbon::now()->year - 5; $i--)
                                            <option value="{{ $i }}"
                                                {{ $dashboardData['sales_trend_year'] == $i ? 'selected' : '' }}>
                                                {{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-4 col-sm-12 mb-3">
                                    <button type="submit" class="btn btn-primary px-4 rounded-3">Filter</button>
                                </div>
                            </div>
                            <!-- Hidden inputs untuk mempertahankan filter lain -->
                            <input type="hidden" name="profit_trend_period"
                                value="{{ $dashboardData['profit_trend_period'] }}">
                            @if ($dashboardData['profit_trend_period'] == 'yearly')
                                <input type="hidden" name="profit_trend_year"
                                    value="{{ $dashboardData['profit_trend_year'] }}">
                            @endif
                            <input type="hidden" name="stock_qty_month"
                                value="{{ $dashboardData['stock_qty_month'] }}">
                            <input type="hidden" name="stock_qty_year" value="{{ $dashboardData['stock_qty_year'] }}">
                            <input type="hidden" name="stock_amount_month"
                                value="{{ $dashboardData['stock_amount_month'] }}">
                            <input type="hidden" name="stock_amount_year"
                                value="{{ $dashboardData['stock_amount_year'] }}">
                            <input type="hidden" name="stock_qty_limit"
                                value="{{ $dashboardData['stock_qty_limit'] }}">
                            <input type="hidden" name="stock_qty_chart_type"
                                value="{{ $dashboardData['stock_qty_chart_type'] }}">
                            <input type="hidden" name="stock_amount_limit"
                                value="{{ $dashboardData['stock_amount_limit'] }}">
                            <input type="hidden" name="stock_amount_chart_type"
                                value="{{ $dashboardData['stock_amount_chart_type'] }}">
                            <input type="hidden" name="sales_month" value="{{ $dashboardData['sales_month'] }}">
                            <input type="hidden" name="sales_year" value="{{ $dashboardData['sales_year'] }}">
                            <input type="hidden" name="sales_qty_limit"
                                value="{{ $dashboardData['sales_qty_limit'] }}">
                            <input type="hidden" name="sales_qty_chart_type"
                                value="{{ $dashboardData['sales_qty_chart_type'] }}">
                            <input type="hidden" name="sales_profit_limit"
                                value="{{ $dashboardData['sales_profit_limit'] }}">
                            <input type="hidden" name="sales_profit_chart_type"
                                value="{{ $dashboardData['sales_profit_chart_type'] }}">
                        </form>
                        <canvas id="salesTrendChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            <!-- Row 2, Column 1: Stock Composition by Quantity -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm chart-container">
                    <div class="card-body">
                        <h3 class="card-title">Stok (Kuantitas)</h3>
                        <form action="{{ route('dashboard_page') }}" method="GET" class="mb-3">
                            <div class="row align-items-end">
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <label for="stock_qty_month" class="form-label fw-bold">Bulan</label>
                                    <select name="stock_qty_month" id="stock_qty_month" class="form-select rounded-3"
                                        required aria-label="Pilih bulan untuk stok kuantitas">
                                        @for ($i = 1; $i <= 12; $i++)
                                            <option value="{{ $i }}"
                                                {{ $dashboardData['stock_qty_month'] == $i ? 'selected' : '' }}>
                                                {{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <label for="stock_qty_year" class="form-label fw-bold">Tahun</label>
                                    <select name="stock_qty_year" id="stock_qty_year" class="form-select rounded-3"
                                        required aria-label="Pilih tahun untuk stok kuantitas">
                                        @for ($i = \Carbon\Carbon::now()->year; $i >= \Carbon\Carbon::now()->year - 5; $i--)
                                            <option value="{{ $i }}"
                                                {{ $dashboardData['stock_qty_year'] == $i ? 'selected' : '' }}>
                                                {{ $i }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-4 col-sm-12 mb-3">
                                    <button type="submit" class="btn btn-primary px-4 rounded-3">Filter</button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 col-sm-12">
                                    <label for="stock_qty_limit" class="form-label fw-bold">Tampilkan</label>
                                    <select id="stock_qty_limit" class="form-select rounded-3"
                                        onchange="toggleStockQtyChartType()"
                                        aria-label="Pilih jumlah item untuk ditampilkan">
                                        <option value="5"
                                            {{ $dashboardData['stock_qty_limit'] == 5 ? 'selected' : '' }}>Top 5</option>
                                        <option value="10"
                                            {{ $dashboardData['stock_qty_limit'] == 10 ? 'selected' : '' }}>Top 10</option>
                                    </select>
                                </div>
                                <div class="col-md-6 col-sm-12">
                                    <label for="stock_qty_chart_type" class="form-label fw-bold">Tipe Grafik</label>
                                    <select id="stock_qty_chart_type" class="form-select rounded-3"
                                        onchange="toggleStockQtyChartType()"
                                        aria-label="Pilih tipe grafik stok kuantitas">
                                        <option value="bar"
                                            {{ $dashboardData['stock_qty_chart_type'] == 'bar' ? 'selected' : '' }}>Bar
                                        </option>
                                        <option value="pie"
                                            {{ $dashboardData['stock_qty_chart_type'] == 'pie' ? 'selected' : '' }}>Pie
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <!-- Hidden inputs untuk mempertahankan filter tren -->
                            <input type="hidden" name="profit_trend_period"
                                value="{{ $dashboardData['profit_trend_period'] }}">
                            @if ($dashboardData['profit_trend_period'] == 'yearly')
                                <input type="hidden" name="profit_trend_year"
                                    value="{{ $dashboardData['profit_trend_year'] }}">
                            @endif
                            <input type="hidden" name="sales_trend_period"
                                value="{{ $dashboardData['sales_trend_period'] }}">
                            @if ($dashboardData['sales_trend_period'] == 'yearly')
                                <input type="hidden" name="sales_trend_year"
                                    value="{{ $dashboardData['sales_trend_year'] }}">
                            @endif
                            <input type="hidden" name="stock_amount_month"
                                value="{{ $dashboardData['stock_amount_month'] }}">
                            <input type="hidden" name="stock_amount_year"
                                value="{{ $dashboardData['stock_amount_year'] }}">
                            <input type="hidden" name="stock_amount_limit"
                                value="{{ $dashboardData['stock_amount_limit'] }}">
                            <input type="hidden" name="stock_amount_chart_type"
                                value="{{ $dashboardData['stock_amount_chart_type'] }}">
                            <input type="hidden" name="sales_month" value="{{ $dashboardData['sales_month'] }}">
                            <input type="hidden" name="sales_year" value="{{ $dashboardData['sales_year'] }}">
                            <input type="hidden" name="sales_qty_limit"
                                value="{{ $dashboardData['sales_qty_limit'] }}">
                            <input type="hidden" name="sales_qty_chart_type"
                                value="{{ $dashboardData['sales_qty_chart_type'] }}">
                            <input type="hidden" name="sales_profit_limit"
                                value="{{ $dashboardData['sales_profit_limit'] }}">
                            <input type="hidden" name="sales_profit_chart_type"
                                value="{{ $dashboardData['sales_profit_chart_type'] }}">
                        </form>
                        @if (empty($dashboardData['stock_composition_qty']['labels']) ||
                                $dashboardData['stock_composition_qty']['labels'][0] == 'No Stock Data')
                            <p class="text-muted text-center">Tidak ada data stok untuk periode ini.</p>
                        @else
                            <canvas id="stockCompositionQtyChart" height="200"></canvas>
                        @endif
                    </div>
                </div>
            </div>
            <!-- Row 2, Column 2: Stock Composition by Amount -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm chart-container">
                    <div class="card-body">
                        <h3 class="card-title">Stok (Nominal)</h3>
                        <form action="{{ route('dashboard_page') }}" method="GET" class="mb-3">
                            <div class="row align-items-end">
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <label for="stock_amount_month" class="form-label fw-bold">Bulan</label>
                                    <select name="stock_amount_month" id="stock_amount_month"
                                        class="form-select rounded-3" required
                                        aria-label="Pilih bulan untuk stok nominal">
                                        @for ($i = 1; $i <= 12; $i++)
                                            <option value="{{ $i }}"
                                                {{ $dashboardData['stock_amount_month'] == $i ? 'selected' : '' }}>
                                                {{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <label for="stock_amount_year" class="form-label fw-bold">Tahun</label>
                                    <select name="stock_amount_year" id="stock_amount_year" class="form-select rounded-3"
                                        required aria-label="Pilih tahun untuk stok nominal">
                                        @for ($i = \Carbon\Carbon::now()->year; $i >= \Carbon\Carbon::now()->year - 5; $i--)
                                            <option value="{{ $i }}"
                                                {{ $dashboardData['stock_amount_year'] == $i ? 'selected' : '' }}>
                                                {{ $i }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-4 col-sm-12 mb-3">
                                    <button type="submit" class="btn btn-primary px-4 rounded-3">Filter</button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 col-sm-12">
                                    <label for="stock_amount_limit" class="form-label fw-bold">Tampilkan</label>
                                    <select id="stock_amount_limit" class="form-select rounded-3"
                                        onchange="toggleStockAmountChartType()"
                                        aria-label="Pilih jumlah item untuk ditampilkan">
                                        <option value="5"
                                            {{ $dashboardData['stock_amount_limit'] == 5 ? 'selected' : '' }}>Top 5
                                        </option>
                                        <option value="10"
                                            {{ $dashboardData['stock_amount_limit'] == 10 ? 'selected' : '' }}>Top 10
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-6 col-sm-12">
                                    <label for="stock_amount_chart_type" class="form-label fw-bold">Tipe Grafik</label>
                                    <select id="stock_amount_chart_type" class="form-select rounded-3"
                                        onchange="toggleStockAmountChartType()"
                                        aria-label="Pilih tipe grafik stok nominal">
                                        <option value="bar"
                                            {{ $dashboardData['stock_amount_chart_type'] == 'bar' ? 'selected' : '' }}>Bar
                                        </option>
                                        <option value="pie"
                                            {{ $dashboardData['stock_amount_chart_type'] == 'pie' ? 'selected' : '' }}>Pie
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <!-- Hidden inputs untuk mempertahankan filter tren -->
                            <input type="hidden" name="profit_trend_period"
                                value="{{ $dashboardData['profit_trend_period'] }}">
                            @if ($dashboardData['profit_trend_period'] == 'yearly')
                                <input type="hidden" name="profit_trend_year"
                                    value="{{ $dashboardData['profit_trend_year'] }}">
                            @endif
                            <input type="hidden" name="sales_trend_period"
                                value="{{ $dashboardData['sales_trend_period'] }}">
                            @if ($dashboardData['sales_trend_period'] == 'yearly')
                                <input type="hidden" name="sales_trend_year"
                                    value="{{ $dashboardData['sales_trend_year'] }}">
                            @endif
                            <input type="hidden" name="stock_qty_month"
                                value="{{ $dashboardData['stock_qty_month'] }}">
                            <input type="hidden" name="stock_qty_year" value="{{ $dashboardData['stock_qty_year'] }}">
                            <input type="hidden" name="stock_qty_limit"
                                value="{{ $dashboardData['stock_qty_limit'] }}">
                            <input type="hidden" name="stock_qty_chart_type"
                                value="{{ $dashboardData['stock_qty_chart_type'] }}">
                            <input type="hidden" name="sales_month" value="{{ $dashboardData['sales_month'] }}">
                            <input type="hidden" name="sales_year" value="{{ $dashboardData['sales_year'] }}">
                            <input type="hidden" name="sales_qty_limit"
                                value="{{ $dashboardData['sales_qty_limit'] }}">
                            <input type="hidden" name="sales_qty_chart_type"
                                value="{{ $dashboardData['sales_qty_chart_type'] }}">
                            <input type="hidden" name="sales_profit_limit"
                                value="{{ $dashboardData['sales_profit_limit'] }}">
                            <input type="hidden" name="sales_profit_chart_type"
                                value="{{ $dashboardData['sales_profit_chart_type'] }}">
                        </form>
                        @if (empty($dashboardData['stock_composition_amount']['labels']) ||
                                $dashboardData['stock_composition_amount']['labels'][0] == 'No Stock Data')
                            <p class="text-muted text-center">Tidak ada data stok untuk periode ini.</p>
                        @else
                            <canvas id="stockCompositionAmountChart" height="200"></canvas>
                        @endif
                    </div>
                </div>
            </div>
            <!-- Row 3, Column 1: Top Selling Items by Quantity -->
            <div class="col-md-6 mb-4" hidden>
                <div class="card shadow-sm chart-container">
                    <div class="card-body">
                        <h3 class="card-title">Penjualan Terbanyak (Kuantitas)</h3>
                        <form action="{{ route('dashboard_page') }}" method="GET" class="mb-3">
                            <div class="row align-items-end">
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <label for="sales_month" class="form-label fw-bold">Bulan</label>
                                    <select name="sales_month" id="sales_month" class="form-select rounded-3" required
                                        aria-label="Pilih bulan untuk penjualan kuantitas">
                                        @for ($i = 1; $i <= 12; $i++)
                                            <option value="{{ $i }}"
                                                {{ $dashboardData['sales_month'] == $i ? 'selected' : '' }}>
                                                {{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <label for="sales_year" class="form-label fw-bold">Tahun</label>
                                    <select name="sales_year" id="sales_year" class="form-select rounded-3" required
                                        aria-label="Pilih tahun untuk penjualan kuantitas">
                                        @for ($i = \Carbon\Carbon::now()->year; $i >= \Carbon\Carbon::now()->year - 5; $i--)
                                            <option value="{{ $i }}"
                                                {{ $dashboardData['sales_year'] == $i ? 'selected' : '' }}>
                                                {{ $i }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-4 col-sm-12 mb-3">
                                    <button type="submit" class="btn btn-primary px-4 rounded-3">Filter</button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 col-sm-12">
                                    <label for="sales_qty_limit" class="form-label fw-bold">Tampilkan</label>
                                    <select id="sales_qty_limit" class="form-select rounded-3"
                                        onchange="toggleSalesQtyChartType()"
                                        aria-label="Pilih jumlah item untuk ditampilkan">
                                        <option value="5"
                                            {{ $dashboardData['sales_qty_limit'] == 5 ? 'selected' : '' }}>Top 5</option>
                                        <option value="10"
                                            {{ $dashboardData['sales_qty_limit'] == 10 ? 'selected' : '' }}>Top 10
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-6 col-sm-12">
                                    <label for="sales_qty_chart_type" class="form-label fw-bold">Tipe Grafik</label>
                                    <select id="sales_qty_chart_type" class="form-select rounded-3"
                                        onchange="toggleSalesQtyChartType()"
                                        aria-label="Pilih tipe grafik penjualan kuantitas">
                                        <option value="bar"
                                            {{ $dashboardData['sales_qty_chart_type'] == 'bar' ? 'selected' : '' }}>Bar
                                        </option>
                                        <option value="pie"
                                            {{ $dashboardData['sales_qty_chart_type'] == 'pie' ? 'selected' : '' }}>Pie
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <!-- Hidden inputs untuk mempertahankan filter lain -->
                            <input type="hidden" name="profit_trend_period"
                                value="{{ $dashboardData['profit_trend_period'] }}">
                            @if ($dashboardData['profit_trend_period'] == 'yearly')
                                <input type="hidden" name="profit_trend_year"
                                    value="{{ $dashboardData['profit_trend_year'] }}">
                            @endif
                            <input type="hidden" name="sales_trend_period"
                                value="{{ $dashboardData['sales_trend_period'] }}">
                            @if ($dashboardData['sales_trend_period'] == 'yearly')
                                <input type="hidden" name="sales_trend_year"
                                    value="{{ $dashboardData['sales_trend_year'] }}">
                            @endif
                            <input type="hidden" name="stock_qty_month"
                                value="{{ $dashboardData['stock_qty_month'] }}">
                            <input type="hidden" name="stock_qty_year" value="{{ $dashboardData['stock_qty_year'] }}">
                            <input type="hidden" name="stock_qty_limit"
                                value="{{ $dashboardData['stock_qty_limit'] }}">
                            <input type="hidden" name="stock_qty_chart_type"
                                value="{{ $dashboardData['stock_qty_chart_type'] }}">
                            <input type="hidden" name="stock_amount_month"
                                value="{{ $dashboardData['stock_amount_month'] }}">
                            <input type="hidden" name="stock_amount_year"
                                value="{{ $dashboardData['stock_amount_year'] }}">
                            <input type="hidden" name="stock_amount_limit"
                                value="{{ $dashboardData['stock_amount_limit'] }}">
                            <input type="hidden" name="stock_amount_chart_type"
                                value="{{ $dashboardData['stock_amount_chart_type'] }}">
                            <input type="hidden" name="sales_profit_limit"
                                value="{{ $dashboardData['sales_profit_limit'] }}">
                            <input type="hidden" name="sales_profit_chart_type"
                                value="{{ $dashboardData['sales_profit_chart_type'] }}">
                        </form>
                        @if (empty($dashboardData['top_selling_qty']['labels']) ||
                                $dashboardData['top_selling_qty']['labels'][0] == 'No Sales Data')
                            <p class="text-muted text-center">Tidak ada data penjualan untuk periode ini.</p>
                        @else
                            <canvas id="topSellingQtyChart" height="200"></canvas>
                        @endif
                    </div>
                </div>
            </div>
            <!-- Row 3, Column 2: Top Profitable Items -->
            <div class="col-md-6 mb-4" hidden>
                <div class="card shadow-sm chart-container">
                    <div class="card-body">
                        <h3 class="card-title">Penjualan Paling Untung</h3>
                        <form action="{{ route('dashboard_page') }}" method="GET" class="mb-3">
                            <div class="row align-items-end">
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <label for="sales_month" class="form-label fw-bold">Bulan</label>
                                    <select name="sales_month" id="sales_month" class="form-select rounded-3" required
                                        aria-label="Pilih bulan untuk penjualan untung">
                                        @for ($i = 1; $i <= 12; $i++)
                                            <option value="{{ $i }}"
                                                {{ $dashboardData['sales_month'] == $i ? 'selected' : '' }}>
                                                {{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <label for="sales_year" class="form-label fw-bold">Tahun</label>
                                    <select name="sales_year" id="sales_year" class="form-select rounded-3" required
                                        aria-label="Pilih tahun untuk penjualan untung">
                                        @for ($i = \Carbon\Carbon::now()->year; $i >= \Carbon\Carbon::now()->year - 5; $i--)
                                            <option value="{{ $i }}"
                                                {{ $dashboardData['sales_year'] == $i ? 'selected' : '' }}>
                                                {{ $i }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-4 col-sm-12 mb-3">
                                    <button type="submit" class="btn btn-primary px-4 rounded-3">Filter</button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 col-sm-12">
                                    <label for="sales_profit_limit" class="form-label fw-bold">Tampilkan</label>
                                    <select id="sales_profit_limit" class="form-select rounded-3"
                                        onchange="toggleSalesProfitChartType()"
                                        aria-label="Pilih jumlah item untuk ditampilkan">
                                        <option value="5"
                                            {{ $dashboardData['sales_profit_limit'] == 5 ? 'selected' : '' }}>Top 5
                                        </option>
                                        <option value="10"
                                            {{ $dashboardData['sales_profit_limit'] == 10 ? 'selected' : '' }}>Top 10
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-6 col-sm-12">
                                    <label for="sales_profit_chart_type" class="form-label fw-bold">Tipe Grafik</label>
                                    <select id="sales_profit_chart_type" class="form-select rounded-3"
                                        onchange="toggleSalesProfitChartType()"
                                        aria-label="Pilih tipe grafik penjualan untung">
                                        <option value="bar"
                                            {{ $dashboardData['sales_profit_chart_type'] == 'bar' ? 'selected' : '' }}>Bar
                                        </option>
                                        <option value="pie"
                                            {{ $dashboardData['sales_profit_chart_type'] == 'pie' ? 'selected' : '' }}>Pie
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <!-- Hidden inputs untuk mempertahankan filter lain -->
                            <input type="hidden" name="profit_trend_period"
                                value="{{ $dashboardData['profit_trend_period'] }}">
                            @if ($dashboardData['profit_trend_period'] == 'yearly')
                                <input type="hidden" name="profit_trend_year"
                                    value="{{ $dashboardData['profit_trend_year'] }}">
                            @endif
                            <input type="hidden" name="sales_trend_period"
                                value="{{ $dashboardData['sales_trend_period'] }}">
                            @if ($dashboardData['sales_trend_period'] == 'yearly')
                                <input type="hidden" name="sales_trend_year"
                                    value="{{ $dashboardData['sales_trend_year'] }}">
                            @endif
                            <input type="hidden" name="stock_qty_month"
                                value="{{ $dashboardData['stock_qty_month'] }}">
                            <input type="hidden" name="stock_qty_year" value="{{ $dashboardData['stock_qty_year'] }}">
                            <input type="hidden" name="stock_qty_limit"
                                value="{{ $dashboardData['stock_qty_limit'] }}">
                            <input type="hidden" name="stock_qty_chart_type"
                                value="{{ $dashboardData['stock_qty_chart_type'] }}">
                            <input type="hidden" name="stock_amount_month"
                                value="{{ $dashboardData['stock_amount_month'] }}">
                            <input type="hidden" name="stock_amount_year"
                                value="{{ $dashboardData['stock_amount_year'] }}">
                            <input type="hidden" name="stock_amount_limit"
                                value="{{ $dashboardData['stock_amount_limit'] }}">
                            <input type="hidden" name="stock_amount_chart_type"
                                value="{{ $dashboardData['stock_amount_chart_type'] }}">
                            <input type="hidden" name="sales_qty_limit"
                                value="{{ $dashboardData['sales_qty_limit'] }}">
                            <input type="hidden" name="sales_qty_chart_type"
                                value="{{ $dashboardData['sales_qty_chart_type'] }}">
                        </form>
                        @if (empty($dashboardData['top_profitable']['labels']) ||
                                $dashboardData['top_profitable']['labels'][0] == 'No Sales Data')
                            <p class="text-muted text-center">Tidak ada data penjualan untuk periode ini.</p>
                        @else
                            <canvas id="topProfitableChart" height="200"></canvas>
                        @endif
                    </div>
                </div>
            </div>
            <!-- Row 4, Column 1: Bottom Selling Items by Quantity -->
            <div class="col-md-6 mb-4" hidden>
                <div class="card shadow-sm chart-container">
                    <div class="card-body">
                        <h3 class="card-title">Penjualan Tersedikit (Kuantitas)</h3>
                        <form action="{{ route('dashboard_page') }}" method="GET" class="mb-3">
                            <div class="row align-items-end">
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <label for="sales_month" class="form-label fw-bold">Bulan</label>
                                    <select name="sales_month" id="sales_month" class="form-select rounded-3" required
                                        aria-label="Pilih bulan untuk penjualan kuantitas tersedikit">
                                        @for ($i = 1; $i <= 12; $i++)
                                            <option value="{{ $i }}"
                                                {{ $dashboardData['sales_month'] == $i ? 'selected' : '' }}>
                                                {{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <label for="sales_year" class="form-label fw-bold">Tahun</label>
                                    <select name="sales_year" id="sales_year" class="form-select rounded-3" required
                                        aria-label="Pilih tahun untuk penjualan kuantitas tersedikit">
                                        @for ($i = \Carbon\Carbon::now()->year; $i >= \Carbon\Carbon::now()->year - 5; $i--)
                                            <option value="{{ $i }}"
                                                {{ $dashboardData['sales_year'] == $i ? 'selected' : '' }}>
                                                {{ $i }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-4 col-sm-12 mb-3">
                                    <button type="submit" class="btn btn-primary px-4 rounded-3">Filter</button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 col-sm-12">
                                    <label for="sales_qty_limit" class="form-label fw-bold">Tampilkan</label>
                                    <select id="sales_qty_limit" class="form-select rounded-3"
                                        onchange="toggleBottomSalesQtyChartType()"
                                        aria-label="Pilih jumlah item untuk ditampilkan">
                                        <option value="5"
                                            {{ $dashboardData['sales_qty_limit'] == 5 ? 'selected' : '' }}>Bottom 5
                                        </option>
                                        <option value="10"
                                            {{ $dashboardData['sales_qty_limit'] == 10 ? 'selected' : '' }}>Bottom 10
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-6 col-sm-12">
                                    <label for="sales_qty_chart_type" class="form-label fw-bold">Tipe Grafik</label>
                                    <select id="sales_qty_chart_type" class="form-select rounded-3"
                                        onchange="toggleBottomSalesQtyChartType()"
                                        aria-label="Pilih tipe grafik penjualan kuantitas tersedikit">
                                        <option value="bar"
                                            {{ $dashboardData['sales_qty_chart_type'] == 'bar' ? 'selected' : '' }}>Bar
                                        </option>
                                        <option value="pie"
                                            {{ $dashboardData['sales_qty_chart_type'] == 'pie' ? 'selected' : '' }}>Pie
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <!-- Hidden inputs untuk mempertahankan filter lain -->
                            <input type="hidden" name="profit_trend_period"
                                value="{{ $dashboardData['profit_trend_period'] }}">
                            @if ($dashboardData['profit_trend_period'] == 'yearly')
                                <input type="hidden" name="profit_trend_year"
                                    value="{{ $dashboardData['profit_trend_year'] }}">
                            @endif
                            <input type="hidden" name="sales_trend_period"
                                value="{{ $dashboardData['sales_trend_period'] }}">
                            @if ($dashboardData['sales_trend_period'] == 'yearly')
                                <input type="hidden" name="sales_trend_year"
                                    value="{{ $dashboardData['sales_trend_year'] }}">
                            @endif
                            <input type="hidden" name="stock_qty_month"
                                value="{{ $dashboardData['stock_qty_month'] }}">
                            <input type="hidden" name="stock_qty_year" value="{{ $dashboardData['stock_qty_year'] }}">
                            <input type="hidden" name="stock_qty_limit"
                                value="{{ $dashboardData['stock_qty_limit'] }}">
                            <input type="hidden" name="stock_qty_chart_type"
                                value="{{ $dashboardData['stock_qty_chart_type'] }}">
                            <input type="hidden" name="stock_amount_month"
                                value="{{ $dashboardData['stock_amount_month'] }}">
                            <input type="hidden" name="stock_amount_year"
                                value="{{ $dashboardData['stock_amount_year'] }}">
                            <input type="hidden" name="stock_amount_limit"
                                value="{{ $dashboardData['stock_amount_limit'] }}">
                            <input type="hidden" name="stock_amount_chart_type"
                                value="{{ $dashboardData['stock_amount_chart_type'] }}">
                            <input type="hidden" name="sales_profit_limit"
                                value="{{ $dashboardData['sales_profit_limit'] }}">
                            <input type="hidden" name="sales_profit_chart_type"
                                value="{{ $dashboardData['sales_profit_chart_type'] }}">
                        </form>
                        @if (empty($dashboardData['bottom_selling_qty']['labels']) ||
                                $dashboardData['bottom_selling_qty']['labels'][0] == 'No Sales Data')
                            <p class="text-muted text-center">Tidak ada data penjualan untuk periode ini.</p>
                        @else
                            <canvas id="bottomSellingQtyChart" height="200"></canvas>
                        @endif
                    </div>
                </div>
            </div>
            <!-- Row 4, Column 2: Bottom Profitable Items -->
            <div class="col-md-6 mb-4" hidden>
                <div class="card shadow-sm chart-container">
                    <div class="card-body">
                        <h3 class="card-title">Penjualan Untung Terendah</h3>
                        <form action="{{ route('dashboard_page') }}" method="GET" class="mb-3">
                            <div class="row align-items-end">
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <label for="sales_month" class="form-label fw-bold">Bulan</label>
                                    <select name="sales_month" id="sales_month" class="form-select rounded-3" required
                                        aria-label="Pilih bulan untuk penjualan untung terendah">
                                        @for ($i = 1; $i <= 12; $i++)
                                            <option value="{{ $i }}"
                                                {{ $dashboardData['sales_month'] == $i ? 'selected' : '' }}>
                                                {{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <label for="sales_year" class="form-label fw-bold">Tahun</label>
                                    <select name="sales_year" id="sales_year" class="form-select rounded-3" required
                                        aria-label="Pilih tahun untuk penjualan untung terendah">
                                        @for ($i = \Carbon\Carbon::now()->year; $i >= \Carbon\Carbon::now()->year - 5; $i--)
                                            <option value="{{ $i }}"
                                                {{ $dashboardData['sales_year'] == $i ? 'selected' : '' }}>
                                                {{ $i }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-4 col-sm-12 mb-3">
                                    <button type="submit" class="btn btn-primary px-4 rounded-3">Filter</button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 col-sm-12">
                                    <label for="sales_profit_limit" class="form-label fw-bold">Tampilkan</label>
                                    <select id="sales_profit_limit" class="form-select rounded-3"
                                        onchange="toggleBottomSalesProfitChartType()"
                                        aria-label="Pilih jumlah item untuk ditampilkan">
                                        <option value="5"
                                            {{ $dashboardData['sales_profit_limit'] == 5 ? 'selected' : '' }}>Bottom 5
                                        </option>
                                        <option value="10"
                                            {{ $dashboardData['sales_profit_limit'] == 10 ? 'selected' : '' }}>Bottom 10
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-6 col-sm-12">
                                    <label for="sales_profit_chart_type" class="form-label fw-bold">Tipe Grafik</label>
                                    <select id="sales_profit_chart_type" class="form-select rounded-3"
                                        onchange="toggleBottomSalesProfitChartType()"
                                        aria-label="Pilih tipe grafik penjualan untung terendah">
                                        <option value="bar"
                                            {{ $dashboardData['sales_profit_chart_type'] == 'bar' ? 'selected' : '' }}>Bar
                                        </option>
                                        <option value="pie"
                                            {{ $dashboardData['sales_profit_chart_type'] == 'pie' ? 'selected' : '' }}>Pie
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <!-- Hidden inputs untuk mempertahankan filter lain -->
                            <input type="hidden" name="profit_trend_period"
                                value="{{ $dashboardData['profit_trend_period'] }}">
                            @if ($dashboardData['profit_trend_period'] == 'yearly')
                                <input type="hidden" name="profit_trend_year"
                                    value="{{ $dashboardData['profit_trend_year'] }}">
                            @endif
                            <input type="hidden" name="sales_trend_period"
                                value="{{ $dashboardData['sales_trend_period'] }}">
                            @if ($dashboardData['sales_trend_period'] == 'yearly')
                                <input type="hidden" name="sales_trend_year"
                                    value="{{ $dashboardData['sales_trend_year'] }}">
                            @endif
                            <input type="hidden" name="stock_qty_month"
                                value="{{ $dashboardData['stock_qty_month'] }}">
                            <input type="hidden" name="stock_qty_year" value="{{ $dashboardData['stock_qty_year'] }}">
                            <input type="hidden" name="stock_qty_limit"
                                value="{{ $dashboardData['stock_qty_limit'] }}">
                            <input type="hidden" name="stock_qty_chart_type"
                                value="{{ $dashboardData['stock_qty_chart_type'] }}">
                            <input type="hidden" name="stock_amount_month"
                                value="{{ $dashboardData['stock_amount_month'] }}">
                            <input type="hidden" name="stock_amount_year"
                                value="{{ $dashboardData['stock_amount_year'] }}">
                            <input type="hidden" name="stock_amount_limit"
                                value="{{ $dashboardData['stock_amount_limit'] }}">
                            <input type="hidden" name="stock_amount_chart_type"
                                value="{{ $dashboardData['stock_amount_chart_type'] }}">
                            <input type="hidden" name="sales_qty_limit"
                                value="{{ $dashboardData['sales_qty_limit'] }}">
                            <input type="hidden" name="sales_qty_chart_type"
                                value="{{ $dashboardData['sales_qty_chart_type'] }}">
                        </form>
                        @if (empty($dashboardData['bottom_profitable']['labels']) ||
                                $dashboardData['bottom_profitable']['labels'][0] == 'No Sales Data')
                            <p class="text-muted text-center">Tidak ada data penjualan untuk periode ini.</p>
                        @else
                            <canvas id="bottomProfitableChart" height="200"></canvas>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Toggle trend year visibility for Profit Trend
        function toggleProfitTrendYear(select) {
            const trendYearContainer = document.getElementById('profit-trend-year-container');
            const trendYearSelect = document.getElementById('profit_trend_year');
            if (select.value === 'yearly') {
                trendYearContainer.style.display = 'block';
                trendYearSelect.disabled = false;
            } else {
                trendYearContainer.style.display = 'none';
                trendYearSelect.disabled = true;
            }
        }

        // Toggle trend year visibility for Sales Trend
        function toggleSalesTrendYear(select) {
            const trendYearContainer = document.getElementById('sales-trend-year-container');
            const trendYearSelect = document.getElementById('sales_trend_year');
            if (select.value === 'yearly') {
                trendYearContainer.style.display = 'block';
                trendYearSelect.disabled = false;
            } else {
                trendYearContainer.style.display = 'none';
                trendYearSelect.disabled = true;
            }
        }

        // Ensure DOM is fully loaded before initializing charts
        document.addEventListener('DOMContentLoaded', function() {
            // Stock Composition by Quantity Chart
            let stockQtyChart = null;
            window.toggleStockQtyChartType = function() {
                if (stockQtyChart) {
                    stockQtyChart.destroy();
                }
                const chartType = document.getElementById('stock_qty_chart_type').value;
                const limit = parseInt(document.getElementById('stock_qty_limit').value);
                const labels = @json($dashboardData['stock_composition_qty']['labels']).slice(0, limit) || [];
                const data = @json($dashboardData['stock_composition_qty']['data']).slice(0, limit) || [];
                const stockQtyCtx = document.getElementById('stockCompositionQtyChart')?.getContext('2d');
                if (stockQtyCtx) {
                    stockQtyChart = new Chart(stockQtyCtx, {
                        type: chartType,
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Kuantitas Stok',
                                data: data,
                                backgroundColor: ['#4CAF50', '#2196F3', '#FF9800', '#F44336',
                                    '#9C27B0', '#FFEB3B', '#795548', '#607D8B', '#E91E63',
                                    '#3F51B5'
                                ],
                                borderColor: ['#388E3C', '#1976D2', '#F57C00', '#D32F2F',
                                    '#7B1FA2', '#FBC02D', '#5D4037', '#455A64', '#C2185B',
                                    '#303F9F'
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: chartType === 'bar' ? {
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Item'
                                    }
                                },
                                y: {
                                    title: {
                                        display: true,
                                        text: 'Kuantitas'
                                    },
                                    beginAtZero: true
                                }
                            } : {},
                            plugins: {
                                legend: {
                                    display: chartType === 'pie'
                                },
                                afterDraw: function(chart) {
                                    if (chartType === 'pie') {
                                        const ctx = chart.ctx;
                                        const width = chart.width;
                                        const height = chart.height;
                                        const centerX = width / 2;
                                        const centerY = height / 2;
                                        const total = data.reduce((acc, val) => acc + val, 0);
                                        ctx.save();
                                        ctx.textAlign = 'center';
                                        ctx.textBaseline = 'middle';
                                        ctx.font = 'bold 16px Arial';
                                        ctx.fillStyle = '#000000';
                                        ctx.fillText(`Total: ${total} Items`, centerX, centerY);
                                        ctx.restore();
                                    }
                                }
                            }
                        }
                    });
                }
            };

            // Stock Composition by Amount Chart
            let stockAmountChart = null;
            window.toggleStockAmountChartType = function() {
                if (stockAmountChart) {
                    stockAmountChart.destroy();
                }
                const chartType = document.getElementById('stock_amount_chart_type').value;
                const limit = parseInt(document.getElementById('stock_amount_limit').value);
                const labels = @json($dashboardData['stock_composition_amount']['labels']).slice(0, limit) || [];
                const data = @json($dashboardData['stock_composition_amount']['data']).slice(0, limit) || [];
                const stockAmountCtx = document.getElementById('stockCompositionAmountChart')?.getContext('2d');
                if (stockAmountCtx) {
                    stockAmountChart = new Chart(stockAmountCtx, {
                        type: chartType,
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Nominal Stok (IDR)',
                                data: data,
                                backgroundColor: ['#4CAF50', '#2196F3', '#FF9800', '#F44336',
                                    '#9C27B0', '#FFEB3B', '#795548', '#607D8B', '#E91E63',
                                    '#3F51B5'
                                ],
                                borderColor: ['#388E3C', '#1976D2', '#F57C00', '#D32F2F',
                                    '#7B1FA2', '#FBC02D', '#5D4037', '#455A64', '#C2185B',
                                    '#303F9F'
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: chartType === 'bar' ? {
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Item'
                                    }
                                },
                                y: {
                                    title: {
                                        display: true,
                                        text: 'Nominal (IDR)'
                                    },
                                    beginAtZero: true
                                }
                            } : {},
                            plugins: {
                                legend: {
                                    display: chartType === 'pie'
                                },
                                afterDraw: function(chart) {
                                    if (chartType === 'pie') {
                                        const ctx = chart.ctx;
                                        const width = chart.width;
                                        const height = chart.height;
                                        const centerX = width / 2;
                                        const centerY = height / 2;
                                        const total = data.reduce((acc, val) => acc + val, 0);
                                        ctx.save();
                                        ctx.textAlign = 'center';
                                        ctx.textBaseline = 'middle';
                                        ctx.font = 'bold 16px Arial';
                                        ctx.fillStyle = '#000000';
                                        ctx.fillText(
                                            `Total: ${new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(total)}`,
                                            centerX, centerY);
                                        ctx.restore();
                                    }
                                }
                            }
                        }
                    });
                }
            };

            // Top Selling by Quantity Chart
            let topSellingQtyChart = null;
            window.toggleSalesQtyChartType = function() {
                if (topSellingQtyChart) {
                    topSellingQtyChart.destroy();
                }
                const chartType = document.getElementById('sales_qty_chart_type').value;
                const limit = parseInt(document.getElementById('sales_qty_limit').value);
                const labels = @json($dashboardData['top_selling_qty']['labels']).slice(0, limit) || [];
                const data = @json($dashboardData['top_selling_qty']['data']).slice(0, limit) || [];
                const topSellingQtyCtx = document.getElementById('topSellingQtyChart')?.getContext('2d');
                if (topSellingQtyCtx) {
                    topSellingQtyChart = new Chart(topSellingQtyCtx, {
                        type: chartType,
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Kuantitas Penjualan',
                                data: data,
                                backgroundColor: ['#4CAF50', '#2196F3', '#FF9800', '#F44336',
                                    '#9C27B0', '#FFEB3B', '#795548', '#607D8B', '#E91E63',
                                    '#3F51B5'
                                ],
                                borderColor: ['#388E3C', '#1976D2', '#F57C00', '#D32F2F',
                                    '#7B1FA2', '#FBC02D', '#5D4037', '#455A64', '#C2185B',
                                    '#303F9F'
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: chartType === 'bar' ? {
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Item'
                                    }
                                },
                                y: {
                                    title: {
                                        display: true,
                                        text: 'Kuantitas'
                                    },
                                    beginAtZero: true
                                }
                            } : {},
                            plugins: {
                                legend: {
                                    display: chartType === 'pie'
                                },
                                afterDraw: function(chart) {
                                    if (chartType === 'pie') {
                                        const ctx = chart.ctx;
                                        const width = chart.width;
                                        const height = chart.height;
                                        const centerX = width / 2;
                                        const centerY = height / 2;
                                        const total = data.reduce((acc, val) => acc + val, 0);
                                        ctx.save();
                                        ctx.textAlign = 'center';
                                        ctx.textBaseline = 'middle';
                                        ctx.font = 'bold 16px Arial';
                                        ctx.fillStyle = '#000000';
                                        ctx.fillText(`Total: ${total} Items`, centerX, centerY);
                                        ctx.restore();
                                    }
                                }
                            }
                        }
                    });
                }
            };

            // Top Profitable Chart
            let topProfitableChart = null;
            window.toggleSalesProfitChartType = function() {
                if (topProfitableChart) {
                    topProfitableChart.destroy();
                }
                const chartType = document.getElementById('sales_profit_chart_type').value;
                const limit = parseInt(document.getElementById('sales_profit_limit').value);
                const labels = @json($dashboardData['top_profitable']['labels']).slice(0, limit) || [];
                const data = @json($dashboardData['top_profitable']['data']).slice(0, limit) || [];
                const topProfitableCtx = document.getElementById('topProfitableChart')?.getContext('2d');
                if (topProfitableCtx) {
                    topProfitableChart = new Chart(topProfitableCtx, {
                        type: chartType,
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Untung Penjualan (IDR)',
                                data: data,
                                backgroundColor: ['#4CAF50', '#2196F3', '#FF9800', '#F44336',
                                    '#9C27B0', '#FFEB3B', '#795548', '#607D8B', '#E91E63',
                                    '#3F51B5'
                                ],
                                borderColor: ['#388E3C', '#1976D2', '#F57C00', '#D32F2F',
                                    '#7B1FA2', '#FBC02D', '#5D4037', '#455A64', '#C2185B',
                                    '#303F9F'
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: chartType === 'bar' ? {
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Item'
                                    }
                                },
                                y: {
                                    title: {
                                        display: true,
                                        text: 'Untung (IDR)'
                                    },
                                    beginAtZero: true
                                }
                            } : {},
                            plugins: {
                                legend: {
                                    display: chartType === 'pie'
                                },
                                afterDraw: function(chart) {
                                    if (chartType === 'pie') {
                                        const ctx = chart.ctx;
                                        const width = chart.width;
                                        const height = chart.height;
                                        const centerX = width / 2;
                                        const centerY = height / 2;
                                        const total = data.reduce((acc, val) => acc + val, 0);
                                        ctx.save();
                                        ctx.textAlign = 'center';
                                        ctx.textBaseline = 'middle';
                                        ctx.font = 'bold 16px Arial';
                                        ctx.fillStyle = '#000000';
                                        ctx.fillText(
                                            `Total: ${new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(total)}`,
                                            centerX, centerY);
                                        ctx.restore();
                                    }
                                }
                            }
                        }
                    });
                }
            };

            // Bottom Selling by Quantity Chart
            let bottomSellingQtyChart = null;
            window.toggleBottomSalesQtyChartType = function() {
                if (bottomSellingQtyChart) {
                    bottomSellingQtyChart.destroy();
                }
                const chartType = document.getElementById('sales_qty_chart_type').value;
                const limit = parseInt(document.getElementById('sales_qty_limit').value);
                const labels = @json($dashboardData['bottom_selling_qty']['labels']).slice(0, limit) || [];
                const data = @json($dashboardData['bottom_selling_qty']['data']).slice(0, limit) || [];
                const bottomSellingQtyCtx = document.getElementById('bottomSellingQtyChart')?.getContext('2d');
                if (bottomSellingQtyCtx) {
                    bottomSellingQtyChart = new Chart(bottomSellingQtyCtx, {
                        type: chartType,
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Kuantitas Penjualan',
                                data: data,
                                backgroundColor: ['#F44336', '#FF9800', '#2196F3', '#4CAF50',
                                    '#9C27B0', '#FFEB3B', '#795548', '#607D8B', '#E91E63',
                                    '#3F51B5'
                                ],
                                borderColor: ['#D32F2F', '#F57C00', '#1976D2', '#388E3C',
                                    '#7B1FA2', '#FBC02D', '#5D4037', '#455A64', '#C2185B',
                                    '#303F9F'
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: chartType === 'bar' ? {
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Item'
                                    }
                                },
                                y: {
                                    title: {
                                        display: true,
                                        text: 'Kuantitas'
                                    },
                                    beginAtZero: true
                                }
                            } : {},
                            plugins: {
                                legend: {
                                    display: chartType === 'pie'
                                },
                                afterDraw: function(chart) {
                                    if (chartType === 'pie') {
                                        const ctx = chart.ctx;
                                        const width = chart.width;
                                        const height = chart.height;
                                        const centerX = width / 2;
                                        const centerY = height / 2;
                                        const total = data.reduce((acc, val) => acc + val, 0);
                                        ctx.save();
                                        ctx.textAlign = 'center';
                                        ctx.textBaseline = 'middle';
                                        ctx.font = 'bold 16px Arial';
                                        ctx.fillStyle = '#000000';
                                        ctx.fillText(`Total: ${total} Items`, centerX, centerY);
                                        ctx.restore();
                                    }
                                }
                            }
                        }
                    });
                }
            };

            // Bottom Profitable Chart
            let bottomProfitableChart = null;
            window.toggleBottomSalesProfitChartType = function() {
                if (bottomProfitableChart) {
                    bottomProfitableChart.destroy();
                }
                const chartType = document.getElementById('sales_profit_chart_type').value;
                const limit = parseInt(document.getElementById('sales_profit_limit').value);
                const labels = @json($dashboardData['bottom_profitable']['labels']).slice(0, limit) || [];
                const data = @json($dashboardData['bottom_profitable']['data']).slice(0, limit) || [];
                const bottomProfitableCtx = document.getElementById('bottomProfitableChart')?.getContext('2d');
                if (bottomProfitableCtx) {
                    bottomProfitableChart = new Chart(bottomProfitableCtx, {
                        type: chartType,
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Untung Penjualan (IDR)',
                                data: data,
                                backgroundColor: ['#F44336', '#FF9800', '#2196F3', '#4CAF50',
                                    '#9C27B0', '#FFEB3B', '#795548', '#607D8B', '#E91E63',
                                    '#3F51B5'
                                ],
                                borderColor: ['#D32F2F', '#F57C00', '#1976D2', '#388E3C',
                                    '#7B1FA2', '#FBC02D', '#5D4037', '#455A64', '#C2185B',
                                    '#303F9F'
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: chartType === 'bar' ? {
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Item'
                                    }
                                },
                                y: {
                                    title: {
                                        display: true,
                                        text: 'Untung (IDR)'
                                    },
                                    beginAtZero: true
                                }
                            } : {},
                            plugins: {
                                legend: {
                                    display: chartType === 'pie'
                                },
                                afterDraw: function(chart) {
                                    if (chartType === 'pie') {
                                        const ctx = chart.ctx;
                                        const width = chart.width;
                                        const height = chart.height;
                                        const centerX = width / 2;
                                        const centerY = height / 2;
                                        const total = data.reduce((acc, val) => acc + val, 0);
                                        ctx.save();
                                        ctx.textAlign = 'center';
                                        ctx.textBaseline = 'middle';
                                        ctx.font = 'bold 16px Arial';
                                        ctx.fillStyle = '#000000';
                                        ctx.fillText(
                                            `Total: ${new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(total)}`,
                                            centerX, centerY);
                                        ctx.restore();
                                    }
                                }
                            }
                        }
                    });
                }
            };

            // Profit Trend Chart (Line) with Vertical Lines
            const profitTrendCtx = document.getElementById('profitTrendChart')?.getContext('2d');
            if (profitTrendCtx) {
                const labels = @json($dashboardData['profit_trend']['labels']) || [];
                const data = @json($dashboardData['profit_trend']['data']) || [];
                const extendedLabels = ['', ...labels, ''];
                const extendedData = [null, ...data, null];
                const annotations = {};
                extendedData.forEach((value, index) => {
                    if (value !== null && value !== undefined) {
                        annotations[`line${index}`] = {
                            type: 'line',
                            xMin: index,
                            xMax: index,
                            yMin: 0,
                            yMax: value,
                            borderColor: value < 0 ? '#FF0000' : '#00FF00',
                            borderWidth: 2,
                            borderDash: [5, 5]
                        };
                    }
                });
                new Chart(profitTrendCtx, {
                    type: 'line',
                    data: {
                        labels: extendedLabels,
                        datasets: [{
                            label: 'Laba Bersih (IDR)',
                            data: extendedData,
                            backgroundColor: '#2196F3',
                            borderColor: '#1976D2',
                            fill: false,
                            tension: 0.1,
                            pointRadius: 0
                        }]
                    },
                    options: {
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'Bulan'
                                },
                                ticks: {
                                    padding: 15
                                }
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: 'Laba Bersih (IDR)',
                                    rotation: 90
                                },
                                beginAtZero: true,
                                ticks: {
                                    padding: 15
                                }
                            }
                        },
                        elements: {
                            line: {
                                tension: 0.1
                            },
                            point: {
                                radius: 0,
                                hitRadius: 5
                            }
                        },
                        plugins: {
                            annotation: {
                                annotations: annotations
                            }
                        },
                        interaction: {
                            mode: 'index',
                            intersect: false
                        }
                    }
                });
            }

            // Sales Trend Chart (Dual Line) with Interactions
            const salesTrendCtx = document.getElementById('salesTrendChart')?.getContext('2d');
            if (salesTrendCtx) {
                const labels = @json($dashboardData['sales_trend']['labels']) || [];
                const salesDagangan = @json($dashboardData['sales_trend']['data']['sales_dagangan']) || [];
                const salesJadi = @json($dashboardData['sales_trend']['data']['sales_jadi']) || [];
                const extendedLabels = ['', ...labels, ''];
                const extendedSalesDagangan = [null, ...salesDagangan, null];
                const extendedSalesJadi = [null, ...salesJadi, null];
                const salesTrendChart = new Chart(salesTrendCtx, {
                    type: 'line',
                    data: {
                        labels: extendedLabels,
                        datasets: [{
                            label: 'Penjualan Barang Dagangan (IDR)',
                            data: extendedSalesDagangan,
                            backgroundColor: '#4CAF50',
                            borderColor: '#388E3C',
                            fill: false,
                            tension: 0.1,
                            pointRadius: 0,
                            pointHoverRadius: 5,
                            pointHoverBackgroundColor: '#FFFFFF',
                            pointHoverBorderColor: '#388E3C'
                        }, {
                            label: 'Penjualan Barang Jadi (IDR)',
                            data: extendedSalesJadi,
                            backgroundColor: '#2196F3',
                            borderColor: '#1976D2',
                            fill: false,
                            tension: 0.1,
                            pointRadius: 0,
                            pointHoverRadius: 5,
                            pointHoverBackgroundColor: '#FFFFFF',
                            pointHoverBorderColor: '#1976D2'
                        }]
                    },
                    options: {
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'Bulan'
                                },
                                ticks: {
                                    padding: 15
                                }
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: 'Nominal (IDR)'
                                },
                                beginAtZero: true,
                                ticks: {
                                    padding: 15
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) label += ': ';
                                        label += new Intl.NumberFormat('id-ID', {
                                            style: 'currency',
                                            currency: 'IDR'
                                        }).format(context.raw);
                                        return label;
                                    }
                                }
                            }
                        },
                        interaction: {
                            mode: 'nearest',
                            intersect: false,
                            axis: 'x'
                        },
                        onClick: function(event, elements) {
                            if (elements.length > 0) {
                                const index = elements[0].index;
                                const label = extendedLabels[index];
                                const value = elements[0].dataset.data[index];
                                if (value !== null) {
                                    alert(
                                        `Bulan: ${label || 'Tidak ada label'}, Nilai: ${new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value)}`
                                    );
                                }
                            }
                        },
                        hover: {
                            mode: 'nearest',
                            intersect: false
                        }
                    }
                });
            }

            // Initialize Charts
            @if (
                !empty($dashboardData['stock_composition_qty']['labels']) &&
                    $dashboardData['stock_composition_qty']['labels'][0] != 'No Stock Data')
                toggleStockQtyChartType();
            @endif
            @if (
                !empty($dashboardData['stock_composition_amount']['labels']) &&
                    $dashboardData['stock_composition_amount']['labels'][0] != 'No Stock Data')
                toggleStockAmountChartType();
            @endif
            @if (
                !empty($dashboardData['top_selling_qty']['labels']) &&
                    $dashboardData['top_selling_qty']['labels'][0] != 'No Sales Data')
                toggleSalesQtyChartType();
            @endif
            @if (
                !empty($dashboardData['top_profitable']['labels']) &&
                    $dashboardData['top_profitable']['labels'][0] != 'No Sales Data')
                toggleSalesProfitChartType();
            @endif
            @if (
                !empty($dashboardData['bottom_selling_qty']['labels']) &&
                    $dashboardData['bottom_selling_qty']['labels'][0] != 'No Sales Data')
                toggleBottomSalesQtyChartType();
            @endif
            @if (
                !empty($dashboardData['bottom_profitable']['labels']) &&
                    $dashboardData['bottom_profitable']['labels'][0] != 'No Sales Data')
                toggleBottomSalesProfitChartType();
            @endif
        });
    </script>
@endsection
