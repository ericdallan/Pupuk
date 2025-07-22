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
                        <h3 class="card-title">Tren Laba Bersih (12 Bulan Terakhir)</h3>
                        <canvas id="profitTrendChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            <!-- Row 1, Column 2: Sales Trend -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm chart-container">
                    <div class="card-body">
                        <h3 class="card-title">Tren Penjualan (12 Bulan Terakhir)</h3>
                        <canvas id="salesTrendChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            <!-- Row 2, Column 1: Stock Composition by Quantity -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm chart-container">
                    <div class="card-body">
                        <h3 class="card-title">Komposisi Stok Berdasarkan Kuantitas</h3>
                        <form action="{{ route('dashboard_page') }}" method="GET" class="mb-3">
                            <div class="row align-items-end">
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <label for="stock_qty_month" class="form-label fw-bold">Bulan</label>
                                    <select name="stock_qty_month" id="stock_qty_month" class="form-select rounded-3"
                                        required>
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
                                        required>
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
                                        onchange="toggleStockQtyChartType()">
                                        <option value="5" selected>Top 5</option>
                                        <option value="10">Top 10</option>
                                    </select>
                                </div>
                                <div class="col-md-6 col-sm-12">
                                    <label for="stock_qty_chart_type" class="form-label fw-bold">Tipe Grafik</label>
                                    <select id="stock_qty_chart_type" class="form-select rounded-3"
                                        onchange="toggleStockQtyChartType()">
                                        <option value="bar" selected>Bar</option>
                                        <option value="pie">Pie</option>
                                    </select>
                                </div>
                            </div>
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
                        <h3 class="card-title">Komposisi Stok Berdasarkan Nominal</h3>
                        <form action="{{ route('dashboard_page') }}" method="GET" class="mb-3">
                            <div class="row align-items-end">
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <label for="stock_amount_month" class="form-label fw-bold">Bulan</label>
                                    <select name="stock_amount_month" id="stock_amount_month" class="form-select rounded-3"
                                        required>
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
                                        required>
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
                                        onchange="toggleStockAmountChartType()">
                                        <option value="5" selected>Top 5</option>
                                        <option value="10">Top 10</option>
                                    </select>
                                </div>
                                <div class="col-md-6 col-sm-12">
                                    <label for="stock_amount_chart_type" class="form-label fw-bold">Tipe Grafik</label>
                                    <select id="stock_amount_chart_type" class="form-select rounded-3"
                                        onchange="toggleStockAmountChartType()">
                                        <option value="bar" selected>Bar</option>
                                        <option value="pie">Pie</option>
                                    </select>
                                </div>
                            </div>
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
        </div>
    </div>
    <script>
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
                const stockQtyCtx = document.getElementById('stockCompositionQtyChart').getContext('2d');
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
                const stockAmountCtx = document.getElementById('stockCompositionAmountChart').getContext('2d');
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

            // < -- Profit Trend Chart(Line) with Vertical Lines -- >
            const profitTrendCtx = document.getElementById('profitTrendChart').getContext('2d');
            if (profitTrendCtx) {
                const labels = @json($dashboardData['profit_trend']['labels']) || [];
                const data = @json($dashboardData['profit_trend']['data']) || [];
                // Tambahkan bulan kosong di awal dan akhir
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
                            borderColor: value < 0 ? '#FF0000' :
                            '#00FF00', // Merah untuk profit negatif, hijau untuk positif/nol
                            borderWidth: 2,
                            borderDash: [5, 5] // Membuat garis putus-putus (5px garis, 5px spasi)
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
                        },
                    }
                });
            }
            // < -- Sales Trend Chart(Dual Line) with Interactions -- >
            const salesTrendCtx = document.getElementById('salesTrendChart').getContext('2d');
            if (salesTrendCtx) {
                console.log('Sales Trend Data:', {
                    labels: @json($dashboardData['sales_trend']['labels']) || [],
                    sales_dagangan: @json($dashboardData['sales_trend']['data']['sales_dagangan']) || [],
                    sales_jadi: @json($dashboardData['sales_trend']['data']['sales_jadi']) || []
                });
                const labels = @json($dashboardData['sales_trend']['labels']) || [];
                const salesDagangan = @json($dashboardData['sales_trend']['data']['sales_dagangan']) || [];
                const salesJadi = @json($dashboardData['sales_trend']['data']['sales_jadi']) || [];
                // Tambahkan bulan kosong di awal dan akhir
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
                                        if (label) {
                                            label += ': ';
                                        }
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

            // Initialize Stock Charts
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
        });
    </script>
@endsection
