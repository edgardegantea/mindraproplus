@extends('admin._layout')
@section('title', 'Gestión de Usuarios')

@push('styles')
<style>
    .cb-user { width:16px; height:16px; accent-color:#4f46e5; cursor:pointer; }
</style>
@endpush

@section('panel')
{{-- Filtros --}}
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:20px 24px;margin-bottom:24px;display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
    <form method="GET" action="{{ route('admin.users') }}" style="display:flex;align-items:center;gap:12px;flex:1;flex-wrap:wrap;">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar nombre o email..."
               style="flex:1;min-width:180px;padding:10px 14px;border:1px solid #e2e8f0;border-radius:10px;font-size:.875rem;outline:none;"
               onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='#e2e8f0'">
        <select name="status" style="padding:10px 14px;border:1px solid #e2e8f0;border-radius:10px;font-size:.875rem;background:#fff;">
            <option value="">Todos</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Activos (7d)</option>
            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactivos</option>
        </select>
        <button type="submit" style="padding:10px 20px;border:none;border-radius:10px;background:linear-gradient(135deg,#38bdf8,#6366f1,#9333ea);color:#fff;font-size:.875rem;font-weight:600;cursor:pointer;">Filtrar</button>
    </form>
</div>

{{-- Acciones grupales --}}
<form method="POST" action="{{ route('admin.users.group') }}" id="groupForm">
    @csrf
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;">
        <div style="padding:16px 24px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;">
            <div style="display:flex;align-items:center;gap:12px;">
                <input type="checkbox" id="selectAll" class="cb-user" onclick="toggleAll(this)">
                <label for="selectAll" style="font-size:.8125rem;color:#64748b;cursor:pointer;">Seleccionar todos</label>
            </div>
            <div style="display:flex;gap:8px;">
                <button type="submit" name="action" value="export" style="padding:6px 14px;border:1px solid #e2e8f0;border-radius:8px;background:#fff;font-size:.75rem;font-weight:600;color:#334155;cursor:pointer;">Exportar CSV</button>
            </div>
        </div>

        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                    <th style="width:40px;padding:12px 16px;"></th>
                    <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Usuario</th>
                    <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Rol</th>
                    <th style="text-align:center;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Sesiones</th>
                    <th style="text-align:center;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Ansiedad</th>
                    <th style="text-align:center;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                @php $uAvg = $user->inference_records_avg_predicted_probability ? round($user->inference_records_avg_predicted_probability * 100) : null; @endphp
                <tr style="border-bottom:1px solid #f8fafc;" onmouseover="this.style.background='#fafbff'" onmouseout="this.style.background='transparent'">
                    <td style="padding:12px 16px;"><input type="checkbox" name="user_ids[]" value="{{ $user->id }}" class="cb-user"></td>
                    <td style="padding:12px 16px;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:32px;height:32px;border-radius:9999px;background:linear-gradient(135deg,#38bdf8,#6366f1);display:flex;align-items:center;justify-content:center;color:#fff;font-size:.75rem;font-weight:700;">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                            <div>
                                <p style="font-size:.875rem;font-weight:600;color:#1e293b;margin:0;">{{ $user->name }}</p>
                                <p style="font-size:.6875rem;color:#94a3b8;margin:0;">{{ $user->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td style="padding:12px 16px;">
                        <span style="font-size:.6875rem;font-weight:700;padding:3px 8px;border-radius:6px;background:#f8fafc;color:#64748b;border:1px solid #e2e8f0;">{{ $user->role }}</span>
                    </td>
                    <td style="padding:12px 16px;text-align:center;font-size:.875rem;font-weight:600;color:#334155;">{{ $user->inference_records_count }}</td>
                    <td style="padding:12px 16px;text-align:center;">
                        @if($uAvg !== null)
                            <span style="font-size:.75rem;font-weight:700;padding:3px 10px;border-radius:9999px;background:{{ $uAvg > 65 ? '#fef2f2' : ($uAvg > 40 ? '#fffbeb' : '#f0fdf4') }};color:{{ $uAvg > 65 ? '#dc2626' : ($uAvg > 40 ? '#d97706' : '#16a34a') }};">{{ $uAvg }}%</span>
                        @else
                            <span style="color:#cbd5e1;font-size:.75rem;">—</span>
                        @endif
                    </td>
                    <td style="padding:12px 16px;text-align:center;">
                        <div style="display:flex;align-items:center;justify-content:center;gap:6px;">
                            <a href="{{ route('admin.user', $user) }}" style="font-size:.6875rem;font-weight:600;color:#4f46e5;padding:5px 10px;border-radius:8px;border:1px solid #c7d2fe;background:#eef2ff;">Seguimiento</a>
                            <form method="POST" action="{{ route('admin.user.update', $user) }}" style="display:inline;">
                                @csrf
                                <input type="hidden" name="action" value="toggle_access">
                                <button type="submit" style="font-size:.6875rem;font-weight:600;padding:5px 10px;border-radius:8px;border:1px solid;cursor:pointer;
                                    {{ $user->role === 'user' ? 'color:#16a34a;border-color:#bbf7d0;background:#f0fdf4;' : 'color:#d97706;border-color:#fde68a;background:#fffbeb;' }}">
                                    {{ $user->role === 'user' ? 'Activar' : 'Restringir' }}
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="padding:48px;text-align:center;color:#94a3b8;">No se encontraron usuarios.</td></tr>
                @endforelse
            </tbody>
        </table>

        @if($users->hasPages())
        <div style="padding:16px 24px;border-top:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;">
            <span style="font-size:.8125rem;color:#94a3b8;">{{ $users->firstItem() }}-{{ $users->lastItem() }} de {{ $users->total() }}</span>
            <div style="display:flex;gap:6px;">
                @if(!$users->onFirstPage())<a href="{{ $users->previousPageUrl() }}" style="padding:6px 12px;border-radius:8px;font-size:.8125rem;color:#4f46e5;background:#eef2ff;font-weight:600;">Anterior</a>@endif
                @if($users->hasMorePages())<a href="{{ $users->nextPageUrl() }}" style="padding:6px 12px;border-radius:8px;font-size:.8125rem;color:#4f46e5;background:#eef2ff;font-weight:600;">Siguiente</a>@endif
            </div>
        </div>
        @endif
    </div>
</form>

<script>
function toggleAll(el) {
    document.querySelectorAll('.cb-user').forEach(cb => cb.checked = el.checked);
}
</script>
@endsection
