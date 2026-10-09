<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Berhasil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #f0fdf4; } /* Light green background */
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">

    <div class="bg-white p-10 rounded-2xl shadow-xl text-center max-w-md w-full border-t-8 border-green-500">
        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Pembayaran Berhasil!</h1>
        <p class="text-gray-600 mb-8">Terima kasih, transaksi Anda telah dikonfirmasi dan lunas. Stok telah berhasil dikurangi.</p>
        
        <a href="{{ route('stok-keluar.index') }}" class="inline-block bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-8 rounded-full transition shadow-lg w-full">
            Kembali ke Halaman Penjualan
        </a>
    </div>

</body>
</html>
