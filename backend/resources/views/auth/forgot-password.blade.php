@extends('layouts.app')

@section('title', 'Recuperar Contraseña - Sushi Burger Experience')

@section('content')
<div class="glass-card auth-card">
    <div class="text-center mb-5 staggered-item staggered-1">
        <a href="/" class="logo" style="font-size: 2.5rem;">Cartify<span class="dot">.</span></a>
        <div class="mt-4">
            <h2 class="fw-bold h4 mb-2">Recuperar Contraseña</h2>
            <p class="text-muted small">Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña</p>
        </div>
    </div>

    @if(session('status'))
        <div class="alert alert-success mb-4 staggered-item staggered-1">
            {{ session('status') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger mb-4 staggered-item staggered-1">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-4 staggered-item staggered-2">
            <label for="email" class="form-label">Correo Electrónico</label>
            <input type="email" id="email" name="email" class="form-control-cartify w-100" placeholder="tu@email.com" value="{{ old('email') }}" required autofocus>
            @error('email')
                <div class="error-message mt-1 small text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="text-center mt-4 staggered-item staggered-3">
            <button type="submit" class="btn btn-cartify-primary w-100 py-3 px-5">
                Enviar Enlace de Recuperación
            </button>
        </div>
    </form>

    <div class="text-center mt-4 staggered-item staggered-4">
        <a href="{{ route('login') }}" class="text-muted small" style="text-decoration: none;">
            ← Volver al inicio de sesión
        </a>
    </div>
</div>
@endsection


