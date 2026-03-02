@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<h1 class="text-2xl font-semibold mb-6">Dashboard</h1>

<div class="grid md:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-medium text-gray-700 mb-2">Mensajes de contacto</h2>
        <p class="text-3xl font-bold text-[var(--bc-red)]">{{ $contactCount }}</p>
        <a href="{{ route('admin.contacts.index') }}" class="text-sm text-blue-600 hover:underline mt-2 inline-block">Ver todos</a>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-medium text-gray-700 mb-2">Cotizaciones solicitadas</h2>
        <p class="text-3xl font-bold text-[var(--bc-red)]">{{ $quoteCount }}</p>
        <a href="{{ route('admin.quotes.index') }}" class="text-sm text-blue-600 hover:underline mt-2 inline-block">Ver todas</a>
    </div>
</div>

<div class="grid md:grid-cols-2 gap-6">
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-medium mb-4">Últimos contactos</h2>
        @forelse($recentContacts as $c)
            <div class="py-2 border-b border-gray-100 last:border-0">
                <p class="font-medium">{{ $c->name }}</p>
                <p class="text-sm text-gray-500">{{ $c->email }}</p>
                <p class="text-sm text-gray-600 mt-1">{{ Str::limit($c->message, 60) }}</p>
            </div>
        @empty
            <p class="text-gray-500 text-sm">Sin mensajes aún.</p>
        @endforelse
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-medium mb-4">Últimas cotizaciones</h2>
        @forelse($recentQuotes as $q)
            <div class="py-2 border-b border-gray-100 last:border-0">
                <p class="font-medium">{{ $q->name }}</p>
                <p class="text-sm text-gray-500">{{ $q->email }} · {{ $q->phone }}</p>
                @if($q->message)
                    <p class="text-sm text-gray-600 mt-1">{{ Str::limit($q->message, 50) }}</p>
                @endif
            </div>
        @empty
            <p class="text-gray-500 text-sm">Sin cotizaciones aún.</p>
        @endforelse
    </div>
</div>
@endsection
