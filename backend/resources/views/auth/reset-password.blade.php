@extends('layouts.app')

@section('title', 'Restablecer Contraseña - Sushi Burger Experience')

@section('content')
<div class="glass-card auth-card">
    <div class="text-center mb-5 staggered-item staggered-1">
        <a href="/" class="logo" style="font-size: 2.5rem;">Cartify<span class="dot">.</span></a>
        <div class="mt-4">
            <h2 class="fw-bold h4 mb-2">Restablecer Contraseña</h2>
            <p class="text-muted small">Ingresa tu nueva contraseña</p>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger mb-4 staggered-item staggered-1">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ $email }}">

        <div class="mb-3 staggered-item staggered-2">
            <label for="email" class="form-label">Correo Electrónico</label>
            <input type="email" id="email-display" class="form-control-cartify w-100" value="{{ $email }}" disabled>
            <small class="text-muted">Este es el correo asociado a tu cuenta</small>
        </div>

        <div class="mb-3 staggered-item staggered-3">
            <label for="password" class="form-label">Nueva Contraseña</label>
            <input type="password" id="password" name="password" class="form-control-cartify w-100" placeholder="Mínimo 8 caracteres" required>
            @error('password')
                <div class="error-message mt-1 small text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4 staggered-item staggered-4">
            <label for="password_confirmation" class="form-label">Confirmar Nueva Contraseña</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control-cartify w-100" placeholder="Repite tu nueva contraseña" required>
        </div>

        <div class="text-center mt-4 staggered-item staggered-5">
            <button type="submit" class="btn btn-cartify-primary w-100 py-3 px-5">
                Restablecer Contraseña
            </button>
        </div>
    </form>

    <div class="text-center mt-4 staggered-item staggered-6">
        <a href="{{ route('login') }}" class="text-muted small" style="text-decoration: none;">
            ← Volver al inicio de sesión
        </a>
    </div>
</div>
@endsection


