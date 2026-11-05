<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://uicdn.toast.com/editor/latest/toastui-editor.min.css">
    @livewireStyles
</head>
<body class="bg-gray-100 text-gray-800 min-h-screen">
    @auth
        <nav class="bg-white shadow">
            <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
                <div class="flex items-center gap-6">
                    <a href="{{ route('home') }}" class="font-bold text-lg">{{ config('app.name') }}</a>
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('admin.users') }}" class="text-sm hover:underline">Pengguna</a>
                    @else
                        <a href="{{ route('stocks.index') }}" class="text-sm hover:underline">Stok</a>
                    @endif
                    <a href="{{ route('profile') }}" class="text-sm hover:underline">Profil</a>
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
</body>
</html>