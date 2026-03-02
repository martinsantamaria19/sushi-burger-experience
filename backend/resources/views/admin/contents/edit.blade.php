@extends('layouts.admin')

@section('title', 'Editar ' . $group)

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-semibold">Editar: {{ $group }}</h1>
    <a href="{{ route('admin.contents.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Volver a grupos</a>
</div>

<form action="{{ route('admin.contents.update', $group) }}" method="POST" enctype="multipart/form-data" class="space-y-6 max-w-2xl">
    @csrf
    @method('PUT')
    @foreach($settings as $setting)
        <div>
            <label for="key_{{ $setting->id }}" class="block text-sm font-medium text-gray-700 mb-1">
                {{ str_replace('_', ' ', $setting->key) }}
            </label>
            @if($setting->type === 'textarea')
                <textarea name="key_{{ $setting->id }}" id="key_{{ $setting->id }}" rows="4"
                    class="w-full rounded border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-red-500 focus:border-transparent">{{ old('key_'.$setting->id, $setting->value) }}</textarea>
            @elseif($setting->type === 'image')
                @if($setting->value)
                    <div class="mb-2">
                        <img src="{{ Storage::url($setting->value) }}" alt="" class="max-h-32 rounded border object-cover">
                    </div>
                @endif
                <input type="file" name="key_{{ $setting->id }}" id="key_{{ $setting->id }}" accept="image/*"
                    class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-gray-100 file:text-gray-700">
                <p class="text-xs text-gray-500 mt-1">Dejar vacío para mantener la imagen actual.</p>
            @else
                <input type="text" name="key_{{ $setting->id }}" id="key_{{ $setting->id }}"
                    value="{{ old('key_'.$setting->id, $setting->value) }}"
                    class="w-full rounded border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-red-500 focus:border-transparent">
            @endif
        </div>
    @endforeach
    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded">Guardar cambios</button>
</form>
@endsection
