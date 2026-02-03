@extends('layouts.app')

@section('title', 'Registro - Cartify')

@section('content')
<div class="glass-card">
    <div class="text-center mb-4">
        <a href="/" class="logo">Cartify<span class="dot">.</span></a>
        <h2 class="mt-3 fw-bold">Crea tu cuenta</h2>
        <p class="text-muted small">Únete a la revolución de las cartas digitales</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf
        
        <div class="mb-3">
            <label for="name" class="form-label">Nombre Completo</label>
            <input type="text" id="name" name="name" class="form-control-cartify w-100" placeholder="Tu nombre" value="{{ old('name') }}" required autofocus>
            @error('name')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Correo Electrónico</label>
            <input type="email" id="email" name="email" class="form-control-cartify w-100" placeholder="tu@email.com" value="{{ old('email') }}" required>
            @error('email')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Contraseña</label>
            <input type="password" id="password" name="password" class="form-control-cartify w-100" placeholder="••••••••" required>
            @error('password')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="password_confirmation" class="form-label">Confirmar Contraseña</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control-cartify w-100" placeholder="••••••••" required>
        </div>

        <button type="submit" class="btn btn-cartify-primary w-100 py-3 mb-4">
            Crear cuenta
        </button>

        <p class="text-center text-muted small mb-0">
            ¿Ya tienes una cuenta? <a href="{{ route('login') }}" class="text-white fw-bold text-decoration-none">Inicia sesión</a>
        </p>
    </form>
</div>
@endsection
