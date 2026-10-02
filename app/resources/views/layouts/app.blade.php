<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', 'DekatSini - Temukan Layanan Terbaik di Sekitarmu')</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@vite(['resources/css/app.css', 'resources/js/app.js'])
<style>body{font-family:'Inter',sans-serif}[x-cloak]{display:none!important}</style>
@stack('head')
</head>
<body class="bg-slate-50 text-slate-900">
<header class="sticky top-0 z-50 bg-white/90 backdrop-blur border-b" x-data="{ open: false }">
<div class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between">
<a href="/" class="font-bold text-xl text-blue-700">📍 DekatSini</a>
<nav class="hidden md:flex gap-6 text-sm font-medium">
<a href="/" class="hover:text-blue-700">Beranda</a>
<a href="/services" class="hover:text-blue-700">Jelajah</a>
<a href="/services-new" class="hover:text-blue-700">Pasang Jasa</a>
</nav>
<div class="flex gap-2 items-center">
@auth
<a href="/dashboard" class="hidden sm:inline text-sm font-semibold px-4 py-2 bg-blue-700 text-white rounded-lg">Dashboard</a>
<form method="POST" action="/logout" class="hidden sm:inline">@csrf
<button type="submit" class="text-sm font-semibold px-4 py-2 bg-white border border-blue-700 text-blue-700 rounded-lg">Keluar</button>
</form>
@else
<a href="/login" class="hidden sm:inline text-sm font-semibold px-4 py-2 bg-blue-700 text-white rounded-lg">Masuk</a>
@endauth
<button type="button" @click="open = !open" class="md:hidden p-2 border rounded-lg" aria-label="Menu navigasi" :aria-expanded="open">
<span x-show="!open">☰</span><span x-show="open" x-cloak>✕</span>
</button>
</div>
</div>
<div x-show="open" x-cloak class="md:hidden border-t bg-white px-4 py-3 space-y-1 text-sm font-medium">
<a href="/" class="block py-2 hover:text-blue-700">Beranda</a>
<a href="/services" class="block py-2 hover:text-blue-700">Jelajah</a>
<a href="/services-new" class="block py-2 hover:text-blue-700">Pasang Jasa</a>
@auth
<a href="/dashboard" class="block py-2 hover:text-blue-700">Dashboard</a>
<form method="POST" action="/logout">@csrf
<button type="submit" class="block w-full text-left py-2 text-blue-700">Keluar</button>
</form>
@else
<a href="/login" class="block py-2 text-blue-700 font-bold">Masuk</a>
@endauth
</div>
</header>
<main>@yield('content')</main>
<footer class="mt-16 border-t bg-white">
<div class="max-w-7xl mx-auto px-4 py-10 text-sm text-slate-500 flex flex-col md:flex-row justify-between gap-4">
<p>© 2024 DekatSini. Menghubungkan jasa lokal dengan kebutuhanmu.</p>
<p><a href="#" class="hover:underline">Tentang</a> · <a href="#" class="hover:underline">Bantuan</a> · <a href="#" class="hover:underline">Privasi</a></p>
</div>
</footer>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@stack('scripts')
</body>
</html>
