<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\StockOut;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Tentukan rentang waktu filter
        $startDate = $request->input('start_date') 
            ? Carbon::parse($request->input('start_date'))->startOfDay() 
            : Carbon::now()->startOfMonth();
            
        $endDate = $request->input('end_date') 
            ? Carbon::parse($request->input('end_date'))->endOfDay() 
            : Carbon::now()->endOfDay();

        // Data untuk peringatan stok (tidak terpengaruh waktu)
        $barangLowStock = Barang::active()->lowStock()->get();
        $barangExpiringSoon = Barang::active()->expiringSoon()->get();

        // Total data untuk overview
        $totalBarang = Barang::active()->count();
        $totalStok = Barang::active()->sum('stok');
        
        // Total penjualan sesuai filter
        $totalPenjualan = StockOut::whereBetween('created_at', [$startDate, $endDate])
                            ->sum('total_harga');
        
        // 1. Chart: Penjualan Harian (Line Chart)
        $penjualanHarian = StockOut::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_harga) as revenue')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // 2. Chart: Barang Terlaris (Bar Chart)
        $barangTerlaris = StockOut::join('barangs', 'stock_outs.barang_id', '=', 'barangs.id')
            ->select('barangs.nama_barang', DB::raw('SUM(stock_outs.jumlah_terjual) as total_terjual'))
            ->whereBetween('stock_outs.created_at', [$startDate, $endDate])
            ->groupBy('barangs.nama_barang')
            ->orderByDesc('total_terjual')
            ->limit(5)
            ->get();

        // 3. Chart: Kategori Stok (Pie/Doughnut Chart) - kita ganti menggunakan satuan
        $kategoriStok = Barang::active()
            ->select('satuan as kategori', DB::raw('COUNT(*) as total'))
            ->groupBy('satuan')
            ->get();

        // Jika request via AJAX (fetch realtime / filter)
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'totalPenjualan' => $totalPenjualan,
                'penjualanHarian' => $penjualanHarian,
                'barangTerlaris' => $barangTerlaris,
                'kategoriStok' => $kategoriStok,
            ]);
        }

        return view('dashboard', compact(
            'barangLowStock',
            'barangExpiringSoon',
            'totalBarang',
            'totalStok',
            'totalPenjualan',
            'penjualanHarian',
            'barangTerlaris',
            'kategoriStok',
            'startDate',
            'endDate'
        ));
    }
}
