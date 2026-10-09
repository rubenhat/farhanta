@extends('layouts.app')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header Banner & Filter -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white p-8 rounded-lg shadow-lg mb-8 flex flex-col md:flex-row justify-between items-start md:items-center">
            <div>
                <h1 class="text-4xl font-bold mb-2">Dashboard Manajemen Stok</h1>
                <p class="text-blue-100">Selamat datang di sistem manajemen stok Agen Hendi</p>
            </div>
            
            <div class="mt-4 md:mt-0 bg-white/20 p-4 rounded-lg backdrop-blur-sm">
                <form id="filterForm" class="flex flex-col sm:flex-row gap-3 items-end">
                    <div>
                        <label class="block text-xs text-blue-100 mb-1">Dari Tanggal</label>
                        <input type="date" id="startDate" name="start_date" value="{{ $startDate->format('Y-m-d') }}" class="text-gray-800 rounded px-2 py-1 text-sm border-0 focus:ring-2 focus:ring-blue-400">
                    </div>
                    <div>
                        <label class="block text-xs text-blue-100 mb-1">Sampai Tanggal</label>
                        <input type="date" id="endDate" name="end_date" value="{{ $endDate->format('Y-m-d') }}" class="text-gray-800 rounded px-2 py-1 text-sm border-0 focus:ring-2 focus:ring-blue-400">
                    </div>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-400 text-white font-bold py-1 px-4 rounded text-sm transition">
                        Terapkan
                    </button>
                    <button type="button" id="refreshBtn" class="bg-green-500 hover:bg-green-400 text-white font-bold py-1 px-3 rounded text-sm transition" title="Refresh Data Realtime">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                <h3 class="text-gray-500 text-sm font-bold uppercase">Total Barang Aktif</h3>
                <p class="text-3xl font-bold text-gray-800">{{ $totalBarang }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                <h3 class="text-gray-500 text-sm font-bold uppercase">Total Item Stok</h3>
                <p class="text-3xl font-bold text-gray-800">{{ number_format($totalStok, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
                <h3 class="text-gray-500 text-sm font-bold uppercase">Stok Menipis</h3>
                <p class="text-3xl font-bold text-gray-800">{{ $barangLowStock->count() }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500">
                <h3 class="text-gray-500 text-sm font-bold uppercase">Total Penjualan</h3>
                <p class="text-2xl font-bold text-gray-800" id="valTotalPenjualan">Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</p>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Line Chart: Penjualan Harian -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Tren Penjualan Bulan Ini</h3>
                <canvas id="revenueChart" height="100"></canvas>
            </div>
            
            <!-- Bar Chart: Barang Terlaris -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">5 Barang Terlaris Bulan Ini</h3>
                <canvas id="topProductsChart" height="100"></canvas>
            </div>
        </div>
        
        <!-- Bottom Row Charts & Warnings -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Doughnut Chart: Kategori (Satuan) -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Komposisi Satuan Barang</h3>
                <canvas id="categoryChart" height="200"></canvas>
            </div>
            
            <!-- Stok Rendah List -->
            <div class="bg-red-50 border-l-4 border-red-500 rounded-lg p-6 lg:col-span-2">
                <h3 class="text-lg font-bold text-red-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                     Stok Rendah ({{ $barangLowStock->count() }})
                </h3>
                
                @if($barangLowStock->isEmpty())
                    <p class="text-red-700">Semua barang memiliki stok yang cukup</p>
                @else
                    <div class="space-y-3 max-h-[300px] overflow-y-auto pr-2">
                        @foreach($barangLowStock as $barang)
                            <div class="bg-white p-3 rounded border border-red-200">
                                <p class="font-semibold text-gray-800">{{ $barang->nama_barang }}</p>
                                <div class="flex justify-between text-sm text-gray-600 mt-1">
                                    <span>Stok: <span class="font-bold text-red-600">{{ $barang->stok }} {{ $barang->satuan }}</span></span>
                                    <span>Min: {{ $barang->stok_minimum }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mt-8 bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Akses Cepat</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @if(Auth::user()->role === 'admin')
                <a href="{{ route('stok-masuk.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-4 rounded text-center transition shadow">
                    + Pembelian (In)
                </a>
                @endif
                @if(Auth::user()->role === 'kasir')
                <a href="{{ route('stok-keluar.create') }}" class="bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-4 rounded text-center transition shadow">
                    + Penjualan (POS)
                </a>
                @endif
                @if(Auth::user()->role === 'admin')
                <a href="{{ route('laporan.stok') }}" class="bg-purple-500 hover:bg-purple-600 text-white font-bold py-3 px-4 rounded text-center transition shadow">
                    Laporan Stok
                </a>
                @endif
                <a href="{{ route('laporan.penjualan') }}" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 px-4 rounded text-center transition shadow">
                    Laporan Penjualan
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    // Data Preparation Awal
    let harianData = @json($penjualanHarian);
    let terlarisData = @json($barangTerlaris);
    let kategoriData = @json($kategoriStok);
    
    let revChart, topChart, catChart;

    function initCharts() {
        // 1. Line Chart: Revenue
        const revCtx = document.getElementById('revenueChart').getContext('2d');
        revChart = new Chart(revCtx, {
            type: 'line',
            data: {
                labels: harianData.map(d => d.date),
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: harianData.map(d => d.revenue),
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.3
                }]
            }
        });

        // 2. Bar Chart: Top Products
        const topCtx = document.getElementById('topProductsChart').getContext('2d');
        topChart = new Chart(topCtx, {
            type: 'bar',
            data: {
                labels: terlarisData.map(d => d.nama_barang),
                datasets: [{
                    label: 'Qty Terjual',
                    data: terlarisData.map(d => d.total_terjual),
                    backgroundColor: 'rgba(16, 185, 129, 0.8)',
                }]
            }
        });

        // 3. Doughnut Chart: Categories
        const catCtx = document.getElementById('categoryChart').getContext('2d');
        catChart = new Chart(catCtx, {
            type: 'doughnut',
            data: {
                labels: kategoriData.map(d => d.kategori || 'Uncategorized'),
                datasets: [{
                    data: kategoriData.map(d => d.total),
                    backgroundColor: [
                        '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#64748b'
                    ]
                }]
            }
        });
    }

    initCharts();

    // Fungsi Fetch Realtime/Filter
    function fetchDashboardData() {
        const start = document.getElementById('startDate').value;
        const end = document.getElementById('endDate').value;
        const btn = document.getElementById('refreshBtn');
        
        btn.classList.add('animate-spin');
        
        fetch(`{{ route('dashboard') }}?start_date=${start}&end_date=${end}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            // Update Teks Penjualan
            document.getElementById('valTotalPenjualan').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(data.totalPenjualan);

            // Update Chart 1
            revChart.data.labels = data.penjualanHarian.map(d => d.date);
            revChart.data.datasets[0].data = data.penjualanHarian.map(d => d.revenue);
            revChart.update();

            // Update Chart 2
            topChart.data.labels = data.barangTerlaris.map(d => d.nama_barang);
            topChart.data.datasets[0].data = data.barangTerlaris.map(d => d.total_terjual);
            topChart.update();

            // Update Chart 3
            catChart.data.labels = data.kategoriStok.map(d => d.kategori || 'Uncategorized');
            catChart.data.datasets[0].data = data.kategoriStok.map(d => d.total);
            catChart.update();
            
            setTimeout(() => btn.classList.remove('animate-spin'), 500);
        });
    }

    // Event Listeners
    document.getElementById('filterForm').addEventListener('submit', function(e) {
        e.preventDefault();
        fetchDashboardData();
    });

    document.getElementById('refreshBtn').addEventListener('click', fetchDashboardData);

    // Auto-refresh setiap 60 detik (Realtime)
    setInterval(fetchDashboardData, 60000);

</script>
@endsection