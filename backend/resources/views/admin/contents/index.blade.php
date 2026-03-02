@extends('layouts.admin')

@section('title', 'Contenidos del sitio')

@section('content')
<h1 class="text-2xl font-semibold mb-6">Contenidos del sitio</h1>
<p class="text-gray-600 mb-6">Elegí un grupo para editar textos e imágenes.</p>

<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($byGroup as $group => $items)
        <a href="{{ route('admin.contents.edit', $group) }}" class="block p-4 bg-white rounded-lg shadow hover:shadow-md transition border border-gray-100">
            <span class="font-medium capitalize">{{ $group }}</span>
            <span class="text-sm text-gray-500">({{ $items->count() }} campos)</span>
        </a>
    @endforeach
</div>
@endsection
