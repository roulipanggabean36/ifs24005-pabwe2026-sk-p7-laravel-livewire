<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>
    <link rel="stylesheet" href="/css/app.css">
    @livewireStyles
    <meta name="description" content="Stock Barang: aplikasi untuk mengelola stok barang, mencari, memfilter, dan mengekspor data stok.">
</head>
<body class="bg-gray-100 text-gray-800 min-h-screen">
    @auth
        <nav class="bg-white shadow">
            <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
                <div class="flex items-center gap-6">
                    <a href="{{ route('home') }}" class="font-bold text-lg">{{ config('app.name') }}</a>
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('admin.users') }}" class="text-sm underline">Pengguna</a>
                    @else
                        <a href="{{ route('stocks.index') }}" class="text-sm underline">Stok</a>
                    @endif
                    <a href="{{ route('profile') }}" class="text-sm underline">Profil</a>
                </div>
                <div class="flex items-center gap-3 text-sm">
                    <span>{{ auth()->user()->name }} ({{ auth()->user()->role }})</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-red-600 hover:underline">Keluar</button>
                    </form>
                </div>
            </div>
        </nav>
    @endauth

    <main class="max-w-6xl mx-auto px-4 py-6">
        {{ $slot }}
    </main>

    @livewireScripts
<script>document.addEventListener("DOMContentLoaded",function(){var f=function(){document.querySelectorAll(".tab-item").forEach(function(e){e.removeAttribute("aria-role");e.removeAttribute("aria-selected");e.removeAttribute("aria-label")});document.querySelectorAll(".toastui-editor-pseudo-clipboard").forEach(function(e){e.setAttribute("aria-label","Clipboard editor")})};f();new MutationObserver(f).observe(document.body,{childList:true,subtree:true})});</script>
</body>
</html>