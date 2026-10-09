<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Gagal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #fef2f2; } /* Light red background */
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">

    <div class="bg-white p-10 rounded-2xl shadow-xl text-center max-w-md w-full border-t-8 border-red-500">
        <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </div>
        
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Pembayaran Gagal</h1>
        <p class="text-gray-600 mb-8">Maaf, transaksi Anda gagal diproses atau dibatalkan. Silakan coba kembali atau hubungi Administrator.</p>
        
        <a href="{{ route('stok-keluar.index') }}" class="inline-block bg-red-500 hover:bg-red-600 text-white font-bold py-3 px-8 rounded-full transition shadow-lg w-full">
            Kembali ke Halaman Penjualan
        </a>
    </div>

</body>
</html>
