@extends('layouts.app')
@section('title', 'Edit Jasa - DekatSini')
@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
<h1 class="font-extrabold text-2xl">Edit: {{ $service->title }}</h1>
<form action="/services/{{ $service->id }}" method="POST" class="mt-4 bg-white border rounded-xl p-6 space-y-4">
@csrf @method('PATCH')
@if($errors->any())
<div class="bg-red-50 border border-red-200 text-red-700 p-3 rounded-lg text-sm">
<ul class="list-disc pl-5">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
</div>
@endif
<div><label class="font-semibold text-sm">Nama Jasa</label><input name="title" value="{{ $service->title }}" required class="w-full border rounded-lg px-3 py-2 mt-1"></div>
<div><label class="font-semibold text-sm">Kategori</label><select name="category_id" class="w-full border rounded-lg px-3 py-2 mt-1">@foreach($categories as $c)<option value="{{ $c->id }}" @selected($c->id==$service->category_id)>{{ $c->name }}</option>@endforeach</select></div>
<div><label class="font-semibold text-sm">Deskripsi</label><textarea name="description" rows="4" required class="w-full border rounded-lg px-3 py-2 mt-1">{{ $service->description }}</textarea></div>
<div class="grid grid-cols-2 gap-3">
<div><label class="font-semibold text-sm">WA</label><input name="whatsapp" value="{{ $service->whatsapp }}" required class="w-full border rounded-lg px-3 py-2 mt-1"></div>
<div><label class="font-semibold text-sm">Harga</label><input name="price_from" type="number" value="{{ $service->price_from }}" class="w-full border rounded-lg px-3 py-2 mt-1"></div>
</div>
<div><label class="font-semibold text-sm">Status</label><select name="status" class="w-full border rounded-lg px-3 py-2 mt-1"><option value="active" @selected($service->status=='active')>Aktif</option><option value="paused" @selected($service->status=='paused')>Jeda</option></select></div>
<button class="bg-blue-700 text-white px-6 py-2 rounded-lg font-bold">Simpan</button>
</form>
</div>
@endsection
