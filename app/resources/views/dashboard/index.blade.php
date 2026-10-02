@extends('layouts.app')
@section('title', 'Dashboard Provider - DekatSini')
@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
<h1 class="font-extrabold text-3xl">Selamat datang, {{ auth()->user()->name }}!</h1>
<p class="text-slate-500 text-sm">Performa listing jasamu hari ini.</p>
@if(session('success'))<div class="mt-4 bg-green-100 text-green-800 p-3 rounded-lg text-sm">{{ session('success') }}</div>@endif
<div class="grid md:grid-cols-3 gap-4 mt-6">
<div class="bg-white border rounded-xl p-5"><p class="text-sm text-slate-500">Total Jasa</p><p class="text-3xl font-extrabold">{{ $totalServices }}</p></div>
<div class="bg-white border rounded-xl p-5"><p class="text-sm text-slate-500">Dilihat Hari Ini</p><p class="text-3xl font-extrabold">{{ $viewsToday }}</p></div>
<div class="bg-white border rounded-xl p-5"><p class="text-sm text-slate-500">Kontak Baru</p><p class="text-3xl font-extrabold">{{ $newContacts }}</p></div>
</div>
<div class="mt-6 bg-white border rounded-xl p-5">
<div class="flex justify-between items-center"><h2 class="font-bold">Jasa Saya</h2><a href="/services-new" class="bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-bold">+ Jasa Baru</a></div>
<table class="w-full text-sm mt-4">
<tr class="text-left text-slate-400"><th class="py-2">Nama</th><th>Kategori</th><th>Status</th><th>Leads</th><th>Aksi</th></tr>
@foreach($services as $s)
<tr class="border-t">
<td class="py-3 font-semibold">{{ $s->title }}</td>
<td>{{ $s->category->name }}</td>
<td><span class="px-2 py-1 rounded-full text-xs {{ $s->status=='active'?'bg-green-100 text-green-700':'bg-yellow-100 text-yellow-700' }}">{{ $s->status }}</span></td>
<td>{{ $s->leads_count }} · {{ $s->views_count }} views</td>
<td class="flex gap-2"><a href="/services/{{ $s->id }}/edit" class="text-blue-700">Edit</a>
<form action="/services/{{ $s->id }}" method="POST" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-red-600">Hapus</button></form></td>
</tr>
@endforeach
</table>
<div class="mt-4">{{ $services->links() }}</div>
</div>
</div>
@endsection
