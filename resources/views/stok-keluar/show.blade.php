<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Penjualan - {{ $invoiceNumber }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background-color: white !important; }
            .print-area { box-shadow: none !important; max-width: 100% !important; width: 100% !important; margin: 0 !important; padding: 0 !important;}
        }
        body { font-family: 'Courier New', Courier, monospace; background-color: #f3f4f6; }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 5rem;
            font-weight: bold;
            opacity: 0.15;
            pointer-events: none;
            z-index: 0;
            font-family: sans-serif;
            text-align: center;
        }
    </style>
    <!-- Midtrans Snap JS -->
    <script type="text/javascript"
            src="https://app.sandbox.midtrans.com/snap/snap.js"
            data-client-key="{{ config('services.midtrans.client_key') }}"></script>
</head>
<body class="py-8 text-gray-800">
    
    <!-- Controls (Not Printed) -->
    <div class="max-w-md mx-auto mb-6 flex justify-between gap-4 no-print">
        <a href="{{ route('stok-keluar.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded shadow transition flex-1 text-center font-sans font-bold">
            &larr; Kembali
        </a>
        
        @if($stockOuts->first()->status_pembayaran === 'pending' && $stockOuts->first()->payment_token)
        <button id="pay-button" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow transition flex-1 font-sans font-bold">
            Bayar Sekarang
        </button>
        @else
        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow transition flex-1 font-sans font-bold">
            Cetak Struk
        </button>
        @endif
    </div>

    <!-- Receipt Format -->
    <div class="max-w-md mx-auto bg-white p-6 shadow-lg rounded print-area relative overflow-hidden">
        
        <!-- Watermark -->
        @if($stockOuts->first()->status_pembayaran === 'lunas')
            <div class="watermark text-green-600">LUNAS</div>
        @else
            <div class="watermark text-red-600">PENDING</div>
        @endif

        <!-- Header -->
        <div class="text-center mb-6 border-b-2 border-dashed border-gray-300 pb-4 relative z-10">
            <h1 class="text-2xl font-bold font-sans">AGEN HENDI</h1>
            <p class="text-sm">Sistem Kasir & Inventori</p>
        </div>

        <!-- Meta Info -->
        <div class="mb-4 text-sm flex justify-between">
            <div>
                <p><strong>Nota:</strong> {{ $invoiceNumber }}</p>
                <p><strong>Kasir:</strong> {{ $kasir }}</p>
            </div>
            <div class="text-right">
                <p><strong>Tanggal:</strong></p>
                <p>{{ \Carbon\Carbon::parse($tanggal)->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        <div class="border-b-2 border-dashed border-gray-300 mb-4"></div>

        <!-- Items -->
        <div class="mb-4">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-300">
                        <th class="text-left pb-2">Item</th>
                        <th class="text-center pb-2">Qty</th>
                        <th class="text-right pb-2">Harga</th>
                        <th class="text-right pb-2">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stockOuts as $item)
                        @php
                            $hargaSatuan = $item->price_snapshot / $item->jumlah_terjual;
                        @endphp
                        <tr>
                            <td class="py-2 pr-2">{{ Str::limit($item->product_name_snapshot, 20) }}</td>
                            <td class="py-2 text-center">{{ $item->jumlah_terjual }}</td>
                            <td class="py-2 text-right">{{ number_format($hargaSatuan, 0, ',', '.') }}</td>
                            <td class="py-2 text-right">{{ number_format($item->total_harga, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="border-b-2 border-dashed border-gray-300 mb-4"></div>

        <!-- Totals -->
        <div class="flex justify-between items-center text-lg font-bold">
            <p>GRAND TOTAL</p>
            <p>Rp {{ number_format($grandTotal, 0, ',', '.') }}</p>
        </div>

        <div class="border-b-2 border-dashed border-gray-300 mt-4 mb-6"></div>

        <!-- Footer -->
        <div class="text-center text-sm">
            <p>Terima Kasih Atas Pembelian Anda!</p>
            <p class="mt-1 text-gray-500 text-xs">Sistem dikembangkan dengan ❤️</p>
        </div>

    </div>

    @if($stockOuts->first()->status_pembayaran === 'pending' && $stockOuts->first()->payment_token)
    <script type="text/javascript">
      document.getElementById('pay-button').onclick = function(){
        snap.pay('{{ $stockOuts->first()->payment_token }}', {
          onSuccess: function(result){
            window.location.href = "{{ route('payment.success') }}?order_id={{ $invoiceNumber }}";
          },
          onPending: function(result){
            alert("Menunggu pembayaran Anda diselesaikan!");
          },
          onError: function(result){
            window.location.href = "{{ route('payment.failed') }}";
          },
          onClose: function(){
            console.log('Customer closed the popup without finishing the payment');
          }
        })
      };
    </script>
    @endif
</body>
</html>
