@extends('layouts.app')
@section('title', 'Pasang Jasa - DekatSini')
@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">
<h1 class="font-extrabold text-3xl">Pasang jasamu</h1>
<p class="text-slate-500 text-sm">Jangkau lebih banyak pelanggan di sekitarmu.</p>
<form action="/services" method="POST" enctype="multipart/form-data" class="mt-6 bg-white border rounded-xl p-6 space-y-4">
@csrf
@if($errors->any())
<div class="bg-red-50 border border-red-200 text-red-700 p-3 rounded-lg text-sm">
<ul class="list-disc pl-5">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
</div>
@endif
<div><label class="font-semibold text-sm">Nama Jasa</label><input name="title" value="{{ old('title') }}" required placeholder="cth: Servis AC Profesional" class="w-full border rounded-lg px-3 py-2 mt-1"></div>
<div class="grid md:grid-cols-2 gap-4">
<div><label class="font-semibold text-sm">Kategori</label><select name="category_id" required class="w-full border rounded-lg px-3 py-2 mt-1">@foreach($categories as $c)<option value="{{ $c->id }}" @selected(old('category_id') == $c->id)>{{ $c->name }}</option>@endforeach</select></div>
<div><label class="font-semibold text-sm">No. WhatsApp</label><input name="whatsapp" value="{{ old('whatsapp') }}" required placeholder="62812..." class="w-full border rounded-lg px-3 py-2 mt-1"></div>
</div>
<div><label class="font-semibold text-sm">Deskripsi</label><textarea name="description" required rows="4" placeholder="Ceritakan keunggulan jasamu..." class="w-full border rounded-lg px-3 py-2 mt-1">{{ old('description') }}</textarea></div>
<div><label class="font-semibold text-sm">Alamat Lengkap</label><input name="address" value="{{ old('address') }}" required class="w-full border rounded-lg px-3 py-2 mt-1"></div>
<div class="grid md:grid-cols-2 gap-4">
<div><label class="font-semibold text-sm">Kota</label><input name="city" value="{{ old('city', 'Jakarta Selatan') }}" required class="w-full border rounded-lg px-3 py-2 mt-1"></div>
<div><label class="font-semibold text-sm">Harga Mulai (Rp)</label><input name="price_from" type="number" value="{{ old('price_from', 50000) }}" class="w-full border rounded-lg px-3 py-2 mt-1"></div>
</div>
<div><label class="font-semibold text-sm">Titik Lokasi (klik peta)</label><div id="map" class="h-64 rounded-lg border mt-1"></div>
<div class="grid grid-cols-2 gap-2 mt-2"><input name="latitude" id="lat" required readonly value="{{ old('latitude', '-6.260') }}" class="border rounded px-2 py-1 text-sm"><input name="longitude" id="lng" required readonly value="{{ old('longitude', '106.810') }}" class="border rounded px-2 py-1 text-sm"></div></div>
<div><label class="font-semibold text-sm">Foto (maks 2MB)</label><input type="file" name="photos[]" multiple accept="image/*" class="w-full border rounded-lg px-3 py-2 mt-1"></div>
<button class="bg-blue-700 text-white px-6 py-3 rounded-xl font-bold">Kirim Jasa</button>
</form>
</div>
@push('scripts')
<script>
var map = L.map('map').setView([-6.26, 106.81], 12);
L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png',{attribution:'OSM'}).addTo(map);
var m = L.marker([-6.26,106.81],{draggable:true}).addTo(map);
function sync(v){document.getElementById('lat').value=v.lat.toFixed(6);document.getElementById('lng').value=v.lng.toFixed(6);}
map.on('click',e=>{m.setLatLng(e.latlng);sync(e.latlng);});
m.on('dragend',()=>sync(m.getLatLng()));
</script>
@endpush
@endsection
