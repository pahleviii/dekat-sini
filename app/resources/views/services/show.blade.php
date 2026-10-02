@extends('layouts.app')
@section('title', $service->title.' - DekatSini')
@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
<p class="text-xs text-slate-500"><a href="/">Beranda</a> › <a href="/services">Jasa</a> › {{ $service->title }}</p>
<div class="mt-4 bg-slate-900 text-white rounded-2xl p-8 flex flex-col md:flex-row justify-between gap-4">
<div>
<h1 class="text-3xl font-extrabold">{{ $service->title }}</h1>
<p class="text-sm mt-2">★ {{ number_format($service->reviews_avg_rating ?? 0,1) }} · {{ $service->city }} · @if($service->is_verified)<span class="bg-blue-700 px-2 py-1 rounded text-xs font-bold">TERVERIFIKASI</span>@endif</p>
<p class="mt-3 text-xl font-extrabold text-blue-300">Mulai Rp {{ number_format((float) ($service->price_from ?? 0)) }}</p>
</div>
<div class="flex gap-2">
<a href="tel:{{ $service->phone }}" onclick="logLead('phone')" class="bg-white text-slate-900 px-5 py-2 rounded-lg font-bold">Hubungi</a>
<a href="{{ $waLink }}" target="_blank" onclick="logLead('whatsapp')" class="bg-green-500 px-5 py-2 rounded-lg font-bold">WhatsApp</a>
</div>
</div>
@if($service->photos->isNotEmpty())
<div class="bg-white border rounded-xl p-4 mt-6">
<h2 class="font-bold mb-3">Galeri Foto</h2>
<div class="grid grid-cols-3 gap-3">
@foreach($service->photos as $p)
<img src="/storage/{{ $p->path }}" alt="{{ $service->title }}" class="w-full h-32 object-cover rounded-lg">
@endforeach
</div>
</div>
@endif
<div class="grid md:grid-cols-3 gap-6 mt-6">
<div class="md:col-span-2 space-y-6">
<div class="bg-white border rounded-xl p-6">
<h2 class="font-bold">Tentang Layanan Ini</h2>
<p class="text-sm text-slate-600 mt-2">{{ $service->description }}</p>
<p class="mt-3 text-sm">✅ Garansi · ✅ Teknisi Bersertifikat · ✅ Harga Transparan</p>
</div>
<div class="bg-white border rounded-xl p-6">
<h2 class="font-bold">Ulasan Pelanggan ({{ $service->reviews->count() }})</h2>
@foreach($service->reviews as $r)
<div class="border-t mt-3 pt-3 text-sm"><b>★ {{ $r->rating }}</b> — {{ $r->comment }}</div>
@endforeach
</div>
</div>
<div class="space-y-4">
<div class="bg-white border rounded-xl overflow-hidden">
<div id="map" class="h-48"></div>
<div class="p-4 text-sm"><b>Kontak</b><br>WA: {{ $service->whatsapp }}<br>{{ $service->address }}, {{ $service->city }}</div>
<form onsubmit="event.preventDefault();logLead('quote');alert('Permintaan penawaran terkirim!')" class="p-4">
<textarea id="quote-message" name="message" rows="3" placeholder="Tulis pesan atau kebutuhan Anda..." class="w-full border rounded-lg px-3 py-2 text-sm mb-2"></textarea>
<button class="w-full bg-slate-100 py-2 rounded-lg font-bold">Minta Penawaran</button>
</form>
</div>
<div class="bg-white border rounded-xl p-4">
<h3 class="font-bold text-sm">Jasa Terkait</h3>
@foreach($related as $rel)
<a href="/services/{{ $rel->slug }}" class="block text-sm mt-2 hover:underline">{{ $rel->title }} — <span class="text-blue-700 font-bold">Rp {{ number_format($rel->price_from) }}</span></a>
@endforeach
</div>
</div>
</div>
</div>
@push('scripts')
<script>
function logLead(channel){
  var el = document.getElementById('quote-message');
  var message = el ? el.value : '';
  fetch('/leads',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify({service_id:{{ $service->id }},channel:channel,message:message})});
}
function initMap(){
  var map = L.map('map').setView([{{ $service->latitude }}, {{ $service->longitude }}], 14);
  L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png',{attribution:'OSM'}).addTo(map);
  L.marker([{{ $service->latitude }}, {{ $service->longitude }}]).addTo(map);
}
var ready = 0;
function leafletReady(){ if (++ready === 2) initMap(); }
var leafletCss = document.createElement('link');
leafletCss.rel = 'stylesheet';
leafletCss.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
leafletCss.onload = leafletReady;
document.head.appendChild(leafletCss);
var leafletJs = document.createElement('script');
leafletJs.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
leafletJs.onload = leafletReady;
document.body.appendChild(leafletJs);
</script>
@endpush
@endsection
