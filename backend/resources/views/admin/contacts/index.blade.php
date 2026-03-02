@extends('layouts.admin')

@section('title', 'Mensajes de contacto')

@section('content')
<h1 class="text-2xl font-semibold mb-6">Mensajes de contacto</h1>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Teléfono</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Mensaje</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($submissions as $s)
                <tr>
                    <td class="px-4 py-3 text-sm">{{ $s->name }}</td>
                    <td class="px-4 py-3 text-sm">{{ $s->email }}</td>
                    <td class="px-4 py-3 text-sm">{{ $s->phone ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm max-w-xs truncate">{{ Str::limit($s->message, 50) }}</td>
                    <td class="px-4 py-3 text-sm text-gray-500">{{ $s->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">No hay mensajes.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-4 py-2 border-t">{{ $submissions->links() }}</div>
</div>
@endsection
