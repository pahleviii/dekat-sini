@extends('layouts.app')
@section('title', 'DekatSini - Temukan Layanan Terbaik di Sekitarmu')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-12">
<div class="grid md:grid-cols-2 gap-10 items-center">
<div>
<h1 class="text-4xl md:text-5xl font-extrabold leading-tight">Temukan Layanan Terbaik di <span class="text-blue-700">Sekitarmu</span></h1>
<p class="mt-4 text-slate-600">Solusi cepat dan terpercaya untuk semua kebutuhan rumah tangga Anda, langsung dari penyedia jasa terdekat.</p>
<form action="/services" method="GET" class="mt-6 flex gap-2 bg-white p-2 rounded-xl shadow border">
<input name="search" placeholder="Cari layanan (misal: AC, Tukang Listrik)" class="flex-1 px-4 py-2 outline-none">
<button class="bg-blue-700 text-white px-5 py-2 rounded-lg font-bold">Cari Sekarang</button>
</form>
<p class="mt-3 text-xs text-slate-500">Populer: Servis AC · Laundry</p>
</div>
<div class="bg-blue-100 rounded-2xl p-10 text-center font-semibold text-blue-800">100% Terverifikasi<br><span class="text-sm font-normal">Penyedia Jasa Berlisensi</span></div>
</div>

<h2 class="mt-16 font-bold text-2xl">Kategori Terpopuler</h2>
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
@foreach($categories as $c)
<a href="/services?category={{ $c->slug }}" class="bg-white border rounded-xl p-4 hover:shadow">
<div class="font-bold">{{ $c->name }}</div>
<div class="text-xs text-slate-500">{{ $c->services_count }} jasa</div>
</a>
@endforeach
</div>

<h2 class="mt-12 font-bold text-2xl">Layanan Unggulan <a href="/services" class="text-sm text-blue-700 font-normal float-right">Lihat Semua →</a></h2>
<div class="grid md:grid-cols-3 gap-4 mt-4">
@foreach($featured as $s)
<a href="/services/{{ $s->slug }}" class="bg-white border rounded-xl p-4 hover:shadow">
<div class="font-bold">{{ $s->title }}</div>
<div class="text-xs text-slate-500">{{ $s->city }} · ★ {{ number_format($s->reviews_avg_rating ?? 0, 1) }}</div>
<div class="text-blue-700 font-bold mt-2">Rp {{ number_format($s->price_from) }}</div>
</a>
@endforeach
</div>

<div class="mt-12 bg-blue-700 text-white rounded-2xl p-8 flex flex-col md:flex-row justify-between items-center gap-4">
<div><h3 class="font-extrabold text-2xl">Punya Keahlian? Bergabunglah sebagai Penyedia Jasa!</h3>
<p class="text-blue-100 text-sm">Tingkatkan penghasilan hingga 40% dengan menjangkau pelanggan di sekitar Anda.</p></div>
<a href="/services-new" class="bg-white text-blue-700 px-6 py-3 rounded-xl font-bold">Daftar Sekarang</a>
</div>
</div>
@endsection
