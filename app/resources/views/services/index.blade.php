@extends('layouts.app')
@section('title', 'Jelajah Jasa - DekatSini')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-8 flex flex-col md:flex-row gap-8">
<button type="button" onclick="document.getElementById('filterPanel').classList.toggle('hidden')" class="md:hidden w-full bg-white border rounded-xl px-4 py-3 text-left font-bold text-sm">⚙ Buka Filter</button>
<aside id="filterPanel" class="w-full md:w-64 hidden md:block bg-white border rounded-xl p-5 h-fit">
<h3 class="font-bold">Filter</h3>
<form method="GET" class="mt-4 space-y-4 text-sm">
<input name="search" value="{{ request('search') }}" placeholder="Cari..." class="w-full border rounded-lg px-3 py-2">
<div><p class="font-semibold mb-1">Kategori</p>
@foreach($categories as $c)
<label class="flex gap-2 py-1"><input type="radio" name="category" value="{{ $c->slug }}" @checked(request('category')==$c->slug)> {{ $c->name }}</label>
@endforeach</div>
<div><p class="font-semibold mb-1">Rating Min</p>
<select name="min_rating" class="w-full border rounded-lg px-2 py-2">
<option value="0">Semua</option>
<option value="3" @selected(request('min_rating')=='3')>3+ Bintang</option>
<option value="4" @selected(request('min_rating')=='4')>4+ Bintang</option>
<option value="5" @selected(request('min_rating')=='5')>5 Bintang</option>
</select></div>
<div><p class="font-semibold mb-1">Urutkan</p>
<select name="sort" class="w-full border rounded-lg px-2 py-2">
<option value="recommended" @selected(request('sort','recommended')=='recommended')>Rekomendasi</option>
<option value="price_asc" @selected(request('sort')=='price_asc')>Harga Terendah</option>
<option value="rating" @selected(request('sort')=='rating')>Rating Tertinggi</option>
<option value="nearest" @selected(request('sort')=='nearest')>Terdekat</option>
</select></div>
<div><p class="font-semibold mb-1">Radius: <span id="rv">{{ request('radius',20) }}</span> km</p>
<input type="range" name="radius" min="1" max="20" value="{{ request('radius',20) }}" class="w-full" oninput="document.getElementById('rv').innerText=this.value">
<p class="text-xs text-slate-400">Gunakan lokasi untuk filter jarak akurat.</p></div>
<button class="w-full bg-blue-700 text-white py-2 rounded-lg font-bold">Terapkan</button>
</form>
</aside>
<div class="flex-1">
<h1 class="font-extrabold text-2xl">Jasa Terbaik di Dekatmu</h1>
<p class="text-sm text-slate-500">{{ $services->total() }} layanan ditemukan</p>
<div class="grid md:grid-cols-3 gap-4 mt-4">
@foreach($services as $s)
<div class="bg-white border rounded-xl overflow-hidden">
<div class="bg-slate-200 h-36 flex items-center justify-center text-xs">{{ $s->category->name }}</div>
<div class="p-4">
<div class="font-bold">{{ $s->title }}</div>
<div class="text-xs text-slate-500">{{ $s->city }} · ★ {{ number_format($s->reviews_avg_rating ?? 0,1) }} @if(isset($s->distance)) · {{ number_format($s->distance,1) }} km @endif</div>
<div class="text-blue-700 font-bold mt-1">Rp {{ number_format($s->price_from) }}</div>
<a href="/services/{{ $s->slug }}" class="mt-3 block text-center bg-blue-700 text-white py-2 rounded-lg font-bold text-sm">Lihat Detail</a>
</div>
</div>
@endforeach
</div>
<div class="mt-6">{{ $services->links() }}</div>
</div>
</div>
@endsection
