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
        <!-- Enhanced Date Filter Form -->
        <div class="row mb-4">
            <div class="col-12">
                <form action="{{ route('dashboard_page') }}" method="GET" class="card p-4 shadow-sm">
                    <div class="row align-items-end">
                        <div class="col-md-4 col-sm-6 mb-3">
                            <label for="start_date" class="form-label fw-bold">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control rounded-3"
                                value="{{ $dashboardData['start_date'] }}" required>
                        </div>
                        <div class="col-md-4 col-sm-6 mb-3">
                            <label for="end_date" class="form-label fw-bold">End Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control rounded-3"
                                value="{{ $dashboardData['end_date'] }}" required>
                        </div>
                        <div class="col-md-4 col-sm-12 mb-3 d-flex justify-content-md-start justify-content-center">
                            <button type="submit" class="btn btn-primary me-2 px-4 rounded-3">Filter</button>
                            <button type="button" onclick="resetForm()"
                                class="btn btn-outline-secondary px-4 rounded-3">Reset</button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <label for="chart_selector" class="form-label fw-bold">Pilih Grafik</label>
                            <select id="chart_selector" class="form-select rounded-3" onchange="showChart()">
                                <option value="income_statement" selected>Laporan Laba Rugi</option>
                                <option value="profit_trend">Tren Laba Bersih (Bulanan)</option>
                                <option value="cash_flow">Arus Kas</option>
                                <option value="daily_profit">Laba Bersih Harian</option>
                                <option value="operating_expenses">Distribusi Beban Operasional</option>
                                <option value="pendapatan_vs_beban">Pendapatan vs Beban</option>
                                <option value="saldo_akun_utama">Saldo Akun Utama</option>
                                <option value="transactions_per_category">Transaksi per Kategori</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row">
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Laba Bersih</h5>
                        <p class="card-text">{{ number_format($dashboardData['income_statement']['laba_bersih'], 2) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Total Aset</h5>
                        <p class="card-text">
                            {{ number_format($dashboardData['balance_sheet']['aset_lancar'] + $dashboardData['balance_sheet']['aset_tetap'], 2) }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Piutang Usaha</h5>
                        <p class="card-text">
                            {{ number_format($dashboardData['trial_balance']['key_accounts']['piutang_usaha'], 2) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Utang Usaha</h5>
                        <p class="card-text">
                            {{ number_format($dashboardData['trial_balance']['key_accounts']['utang_usaha'], 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="row">
            <div class="col-md-12">
                <div id="income_statement_chart" class="card shadow-sm chart-container">
                    <div class="card-body">
                        <h3 class="card-title">Laporan Laba Rugi</h3>
                        <canvas id="incomeStatementChart"></canvas>
                    </div>
                </div>
                <div id="profit_trend_chart" class="card shadow-sm chart-container" style="display: none;">
                    <div class="card-body">
                        <h3 class="card-title">Tren Laba Bersih (6 Bulan Terakhir)</h3>
                        <canvas id="profitTrendChart"></canvas>
                    </div>
                </div>
                <div id="daily_profit_chart" class="card shadow-sm chart-container" style="display: none;">
                    <div class="card-body">
                        <h3 class="card-title">Laba Bersih Harian</h3>
                        <canvas id="dailyProfitChart"></canvas>
                    </div>
                </div>
                <div id="operating_expenses_chart" class="card shadow-sm chart-container" style="display: none;">
                    <div class="card-body">
                        <h3 class="card-title">Distribusi Beban Operasional</h3>
                        @if (empty($dashboardData['operating_expenses']['labels']) ||
                                $dashboardData['operating_expenses']['labels'][0] == 'No Data')
                            <p class="text-muted text-center">Tidak ada data beban operasional untuk periode ini.</p>
                        @else
                            <canvas id="operatingExpensesChart"></canvas>
                        @endif
                    </div>
                </div>
                <div id="cash_flow_chart" class="card shadow-sm chart-container" style="display: none;">
                    <div class="card-body">
                        <h3 class="card-title">Arus Kas</h3>
                        @if (empty($dashboardData['cash_flow']['labels']) || $dashboardData['cash_flow']['labels'][0] == 'No Cash Flow Data')
                            <p class="text-muted text-center">Tidak ada data arus kas untuk periode ini.</p>
                        @else
                            <canvas id="cashFlowChart"></canvas>
                        @endif
                    </div>
                </div>
                <div id="pendapatan_vs_beban_chart" class="card shadow-sm chart-container" style="display: none;">
                    <div class="card-body">
                        <h3 class="card-title">Pendapatan vs Beban</h3>
                        <canvas id="pendapatanVsBebanChart"></canvas>
                    </div>
                </div>
                <div id="saldo_akun_utama_chart" class="card shadow-sm chart-container" style="display: none;">
                    <div class="card-body">
                        <h3 class="card-title">Saldo Akun Utama</h3>
                        @if (empty($dashboardData['saldo_akun_utama']['labels']) || $dashboardData['saldo_akun_utama']['labels'][0] == 'No Data')
                            <p class="text-muted text-center">Tidak ada data saldo akun utama untuk periode ini.</p>
                        @else
                            <canvas id="saldoAkunUtamaChart"></canvas>
                        @endif
                    </div>
                </div>
                <div id="transactions_per_category_chart" class="card shadow-sm chart-container" style="display: none;">
                    <div class="card-body">
                        <h3 class="card-title">Transaksi per Kategori</h3>
                        <canvas id="transactionsPerCategoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Transactions Table -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h3 class="card-title">Transaksi Terbaru</h3>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Nama Akun</th>
                                    <th>Debit</th>
                                    <th>Kredit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($dashboardData['recent_transactions'] as $transaction)
                                    <tr>
                                        <td>{{ $transaction['voucher_date'] ? \Carbon\Carbon::parse($transaction['voucher_date'])->format('d M Y') : 'N/A' }}
                                        </td>
                                        <td>{{ $transaction['account_name'] }}</td>
                                        <td>{{ number_format($transaction['debit'], 2) }}</td>
                                        <td>{{ number_format($transaction['credit'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Reset Form Function
        function resetForm() {
            document.getElementById('start_date').value = '';
            document.getElementById('end_date').value = '';
            document.querySelector('form').submit();
        }

        // Chart Filter Function
        function showChart() {
            const chartContainers = document.querySelectorAll('.chart-container');
            chartContainers.forEach(container => {
                container.style.display = 'none';
            });
            const selectedChart = document.getElementById('chart_selector').value;
            document.getElementById(`${selectedChart}_chart`).style.display = 'block';
        }

        // Income Statement Chart
        const incomeStatementCtx = document.getElementById('incomeStatementChart').getContext('2d');
        new Chart(incomeStatementCtx, {
            type: 'bar',
            data: {
                labels: ['Pendapatan Penjualan', 'Laba Kotor', 'Beban Operasional', 'Laba Bersih'],
                datasets: [{
                    label: 'Jumlah (IDR)',
                    data: [
                        {{ $dashboardData['income_statement']['pendapatan_penjualan'] }},
                        {{ $dashboardData['income_statement']['laba_kotor'] }},
                        {{ $dashboardData['income_statement']['total_beban_operasional'] }},
                        {{ $dashboardData['income_statement']['laba_bersih'] }}
                    ],
                    backgroundColor: ['#4CAF50', '#2196F3', '#FF9800', '#F44336'],
                    borderColor: ['#388E3C', '#1976D2', '#F57C00', '#D32F2F'],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Profit Trend Chart (Monthly)
        const profitTrendCtx = document.getElementById('profitTrendChart').getContext('2d');
        new Chart(profitTrendCtx, {
            type: 'line',
            data: {
                labels: @json($dashboardData['profit_trend']['labels']),
                datasets: [{
                    label: 'Laba Bersih (IDR)',
                    data: @json($dashboardData['profit_trend']['data']),
                    backgroundColor: '#2196F3',
                    borderColor: '#1976D2',
                    fill: false,
                    tension: 0.1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Daily Profit Chart
        const dailyProfitCtx = document.getElementById('dailyProfitChart').getContext('2d');
        new Chart(dailyProfitCtx, {
            type: 'line',
            data: {
                labels: @json($dashboardData['daily_profit']['labels']),
                datasets: [{
                    label: 'Laba Bersih Harian (IDR)',
                    data: @json($dashboardData['daily_profit']['data']),
                    backgroundColor: '#4CAF50',
                    borderColor: '#388E3C',
                    fill: false,
                    tension: 0.1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        @if (!empty($dashboardData['cash_flow']['labels']) && $dashboardData['cash_flow']['labels'][0] != 'No Cash Flow Data')
            const cashFlowCtx = document.getElementById('cashFlowChart').getContext('2d');
            new Chart(cashFlowCtx, {
                type: 'bar',
                data: {
                    labels: @json($dashboardData['cash_flow']['labels']),
                    datasets: [{
                        label: 'Arus Kas (IDR)',
                        data: @json($dashboardData['cash_flow']['data']),
                        backgroundColor: ['#4CAF50', '#2196F3'],
                        borderColor: ['#388E3C', '#1976D2'],
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        @endif
        // Operating Expenses Chart
        @if (
            !empty($dashboardData['operating_expenses']['labels']) &&
                $dashboardData['operating_expenses']['labels'][0] != 'No Data')
            const operatingExpensesCtx = document.getElementById('operatingExpensesChart').getContext('2d');
            new Chart(operatingExpensesCtx, {
                type: 'pie',
                data: {
                    labels: @json($dashboardData['operating_expenses']['labels']),
                    datasets: [{
                        label: 'Beban Operasional (IDR)',
                        data: @json($dashboardData['operating_expenses']['data']),
                        backgroundColor: ['#4CAF50', '#2196F3', '#FF9800', '#F44336', '#9C27B0', '#FFEB3B'],
                        borderColor: ['#388E3C', '#1976D2', '#F57C00', '#D32F2F', '#7B1FA2', '#FBC02D'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true
                }
            });
        @endif

        // Pendapatan vs Beban Chart
        const pendapatanVsBebanCtx = document.getElementById('pendapatanVsBebanChart').getContext('2d');
        new Chart(pendapatanVsBebanCtx, {
            type: 'bar',
            data: {
                labels: @json($dashboardData['pendapatan_vs_beban']['labels']),
                datasets: [{
                    label: 'Jumlah (IDR)',
                    data: @json($dashboardData['pendapatan_vs_beban']['data']),
                    backgroundColor: ['#4CAF50', '#66BB6A', '#FF9800', '#F44336', '#D81B60', '#7B1FA2'],
                    borderColor: ['#388E3C', '#4CAF50', '#F57C00', '#D32F2F', '#AD1457', '#6A1B9A'],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Saldo Akun Utama Chart
        @if (
            !empty($dashboardData['saldo_akun_utama']['labels']) &&
                $dashboardData['saldo_akun_utama']['labels'][0] != 'No Data')
            const saldoAkunUtamaCtx = document.getElementById('saldoAkunUtamaChart').getContext('2d');
            new Chart(saldoAkunUtamaCtx, {
                type: 'bar',
                data: {
                    labels: @json($dashboardData['saldo_akun_utama']['labels']),
                    datasets: [{
                        label: 'Saldo (IDR)',
                        data: @json($dashboardData['saldo_akun_utama']['data']),
                        backgroundColor: ['#4CAF50', '#2196F3', '#FF9800', '#F44336'],
                        borderColor: ['#388E3C', '#1976D2', '#F57C00', '#D32F2F'],
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        @endif

        // Transactions per Category Chart
        const transactionsPerCategoryCtx = document.getElementById('transactionsPerCategoryChart').getContext('2d');
        new Chart(transactionsPerCategoryCtx, {
            type: 'bar',
            data: {
                labels: @json($dashboardData['transactions_per_category']['labels']),
                datasets: [{
                    label: 'Total Transaksi (IDR)',
                    data: @json($dashboardData['transactions_per_category']['data']),
                    backgroundColor: ['#4CAF50', '#2196F3', '#FF9800', '#F44336', '#9C27B0'],
                    borderColor: ['#388E3C', '#1976D2', '#F57C00', '#D32F2F', '#7B1FA2'],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Initialize chart visibility
        showChart();
    </script>
@endsection
