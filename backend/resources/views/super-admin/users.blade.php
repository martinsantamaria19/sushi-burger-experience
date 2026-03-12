@extends('layouts.admin')

@section('title', 'Gestión de Usuarios')
@section('page_title', 'Usuarios')

@section('content')
<div class="glass-card p-4">
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0" style="background: transparent;">
            <thead>
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                    <th class="py-3 text-muted fw-medium text-uppercase small" style="background: transparent;">Usuario</th>
                    <th class="py-3 text-muted fw-medium text-uppercase small" style="background: transparent;">Email</th>
                    <th class="py-3 text-muted fw-medium text-uppercase small" style="background: transparent;">Compañía</th>
                    <th class="py-3 text-muted fw-medium text-uppercase small" style="background: transparent;">Estado</th>
                </tr>
            </thead>
            <tbody style="border-top: none;">
                @foreach($users as $user)
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                    <td class="py-3" style="background: transparent;">
                        <div class="d-flex align-items-center gap-3">
                            <div class="profile-circle bg-primary-subtle text-primary" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                            <span class="fw-bold text-white">{{ $user->name }}</span>
                        </div>
                    </td>
                    <td class="py-3 text-gray-300" style="background: transparent;">{{ $user->email }}</td>
                    <td class="py-3 text-gray-300" style="background: transparent;">{{ $user->company->name ?? 'N/A' }}</td>
                    <td class="py-3" style="background: transparent;">
                        @if($user->is_blocked)
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3">Bloqueado</span>
                        @else
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3">Activo</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-3">
        {{ $users->links() }}
    </div>
</div>
@endsection
