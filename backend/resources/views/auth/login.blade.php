@extends('layouts.app')

@section('title', 'Login - Sushi Burger Experience')

@section('content')
<div class="glass-card auth-card">
    <div class="text-center mb-5 staggered-item staggered-1">
        <a href="/" class="logo" style="font-size: 2.5rem;">Cartify<span class="dot">.</span></a>
        <div class="mt-4">
            <h2 class="fw-bold h4 mb-2">Bienvenido de nuevo</h2>
            <p class="text-muted small">Ingresa tus credenciales para acceder al panel</p>
        </div>
    </div>

    @if(session('message'))
        <div class="alert alert-success mb-4 staggered-item staggered-1">
            {{ session('message') }}
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success mb-4 staggered-item staggered-1">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger mb-4 staggered-item staggered-1">
            {{ session('error') }}
        </div>
    @endif

    @if(session('verification_required'))
        <div class="alert alert-warning mb-4 staggered-item staggered-1">
            <p class="mb-2"><strong>Correo no verificado</strong></p>
            <p class="small mb-3">Tu correo electrónico no ha sido verificado. Por favor, verifica tu correo antes de iniciar sesión.</p>
            <form method="POST" action="{{ route('email.resend') }}" class="d-inline">
                @csrf
                <input type="hidden" name="email" value="{{ session('user_email') }}">
                <button type="submit" class="btn btn-sm btn-warning">
                    Reenviar enlace de verificación
                </button>
            </form>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3 staggered-item staggered-2">
            <label for="email" class="form-label">Correo Electrónico</label>
            <input type="email" id="email" name="email" class="form-control-cartify w-100" placeholder="tu@email.com" value="{{ old('email', session('user_email')) }}" required autofocus>
            @error('email')
                <div class="error-message mt-1 small text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4 staggered-item staggered-3">
            <label for="password" class="form-label">Contraseña</label>
            <input type="password" id="password" name="password" class="form-control-cartify w-100" placeholder="••••••••" required>
            @error('password')
                <div class="error-message mt-1 small text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex align-items-center justify-content-between mb-4 staggered-item staggered-4">
            <div class="form-check">
                <input class="form-check-input bg-dark border-secondary" type="checkbox" name="remember" id="remember">
                <label class="form-check-label small text-muted" for="remember">
                    Recordarme
                </label>
            </div>
            <a href="{{ route('password.request') }}" class="small text-muted text-decoration-none hover-white">¿Olvidaste tu contraseña?</a>
        </div>

        <button type="submit" class="btn btn-cartify-primary w-100 py-3 mb-4 staggered-item staggered-5">
            Iniciar Sesión
        </button>

        <p class="text-center text-muted small mb-0 staggered-item staggered-6">
            ¿No tienes una cuenta? <a href="{{ route('register') }}" class="text-white fw-bold text-decoration-none border-bottom border-light">Regístrate</a>
        </p>
    </form>
</div>
@endsection

@section('styles')
<style>
    .alert {
        background: rgba(0, 0, 0, 0.3);
        border: 1px solid var(--color-border);
        border-radius: 12px;
        padding: 1rem;
        color: white;
    }
    .alert-success {
        border-color: rgba(34, 197, 94, 0.3);
        background: rgba(34, 197, 94, 0.1);
    }
    .alert-danger {
        border-color: rgba(239, 68, 68, 0.3);
        background: rgba(239, 68, 68, 0.1);
    }
    .alert-warning {
        border-color: rgba(251, 191, 36, 0.3);
        background: rgba(251, 191, 36, 0.1);
    }
    .alert-warning .btn-warning {
        background: rgba(251, 191, 36, 0.2);
        border-color: rgba(251, 191, 36, 0.5);
        color: #fbbf24;
        transition: all 0.2s ease;
    }
    .alert-warning .btn-warning:hover {
        background: rgba(251, 191, 36, 0.3);
        color: white;
    }
</style>
@endsection
