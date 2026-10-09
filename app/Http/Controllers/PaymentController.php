<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockOut;
use App\Models\Barang;
use App\Models\StockIn;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    private function processPaymentSuccess($orderId)
    {
        $stockOuts = StockOut::where('invoice_number', $orderId)->get();
        if ($stockOuts->isEmpty()) return false;
        if ($stockOuts->first()->status_pembayaran === 'lunas') return true;

        DB::transaction(function () use ($stockOuts) {
            foreach ($stockOuts as $stockOut) {
                $stockOut->update(['status_pembayaran' => 'lunas']);
                $barang = Barang::find($stockOut->barang_id);
                if ($barang) {
                    $barang->decrement('stok', $stockOut->jumlah_terjual);
                    $need = $stockOut->jumlah_terjual;
                    $stockIns = StockIn::where('barang_id', $barang->id)
                        ->where('sisa', '>', 0)
                        ->orderByRaw('ISNULL(tanggal_kedaluwarsa), tanggal_kedaluwarsa ASC, created_at ASC')
                        ->get();
                    foreach ($stockIns as $si) {
                        if ($need <= 0) break;
                        $take = min($si->sisa, $need);
                        $si->decrement('sisa', $take);
                        $need -= $take;
                    }
                }
            }
        });
        return true;
    }

    public function success(Request $request)
    {
        $orderId = $request->query('order_id');
        if ($orderId) {
            \Midtrans\Config::$serverKey = config('services.midtrans.server_key');
            \Midtrans\Config::$isProduction = false;
            try {
                $status = \Midtrans\Transaction::status($orderId);
                if ($status->transaction_status == 'capture' || $status->transaction_status == 'settlement') {
                    $this->processPaymentSuccess($orderId);
                }
            } catch (\Exception $e) {
                // Abaikan error jika Midtrans belum mengupdate
            }
        }
        return view('payment.success');
    }

    public function failed(Request $request)
    {
        return view('payment.failed');
    }

    public function callback(Request $request)
    {
        \Midtrans\Config::$serverKey = config('services.midtrans.server_key');
        \Midtrans\Config::$isProduction = false;
        
        try {
            $notification = new \Midtrans\Notification();
            $transaction = $notification->transaction_status;
            $orderId = $notification->order_id;

            if ($transaction == 'capture' || $transaction == 'settlement') {
                $this->processPaymentSuccess($orderId);
            }
            return response()->json(['message' => 'Success']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
