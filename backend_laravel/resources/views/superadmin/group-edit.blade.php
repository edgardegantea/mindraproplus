@extends('superadmin._layout')
@section('title', 'Grupo: ' . $group->name)

@section('panel')
<a href="{{ route('superadmin.groups') }}" style="display:inline-flex;align-items:center;gap:6px;font-size:.8125rem;font-weight:600;color:#4f46e5;margin-bottom:20px;">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" style="width:14px;height:14px;"><path fill-rule="evenodd" d="M9.78 4.22a.75.75 0 0 1 0 1.06L7.06 8l2.72 2.72a.75.75 0 1 1-1.06 1.06L5.47 8.53a.75.75 0 0 1 0-1.06l3.25-3.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd"/></svg>
    Volver a grupos
</a>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
    {{-- Group info --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:28px;">
        <h3 style="font-size:1rem;font-weight:700;color:#0f172a;margin:0 0 20px;">Información del grupo</h3>
        <form method="POST" action="{{ route('superadmin.groups.update', $group) }}" style="display:flex;flex-direction:column;gap:14px;">
            @csrf
            <input type="hidden" name="action" value="update_info">
            <div>
                <label style="font-size:.75rem;font-weight:600;color:#64748b;display:block;margin-bottom:6px;">Nombre</label>
                <input type="text" name="name" value="{{ old('name', $group->name) }}" required
                       style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:10px;font-size:.875rem;outline:none;">
            </div>
            <div>
                <label style="font-size:.75rem;font-weight:600;color:#64748b;display:block;margin-bottom:6px;">Descripción</label>
                <textarea name="description" rows="3"
                          style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:10px;font-size:.875rem;outline:none;resize:vertical;">{{ old('description', $group->description) }}</textarea>
            </div>
            <div>
                <span style="font-size:.75rem;color:#64748b;">Institución: <strong>{{ $group->institution?->name }}</strong></span>
            </div>
            <button type="submit" style="align-self:flex-end;padding:8px 20px;border:none;border-radius:10px;background:linear-gradient(135deg,#38bdf8,#6366f1,#9333ea);color:#fff;font-size:.8125rem;font-weight:700;cursor:pointer;">Guardar</button>
        </form>
    </div>

    {{-- Add users --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:28px;">
        <h3 style="font-size:1rem;font-weight:700;color:#0f172a;margin:0 0 20px;">Agregar usuarios</h3>
        @if($availableUsers->isNotEmpty())
            <form method="POST" action="{{ route('superadmin.groups.update', $group) }}">
                @csrf
                <input type="hidden" name="action" value="add_users">
                <div style="max-height:280px;overflow-y:auto;border:1px solid #e2e8f0;border-radius:10px;margin-bottom:12px;">
                    @foreach($availableUsers as $u)
                        <label style="display:flex;align-items:center;gap:10px;padding:10px 14px;cursor:pointer;{{ !$loop->last ? 'border-bottom:1px solid #f1f5f9;' : '' }}"
                               onmouseover="this.style.background='#fafbff'" onmouseout="this.style.background='transparent'">
                            <input type="checkbox" name="user_ids[]" value="{{ $u->id }}" style="accent-color:#4f46e5;">
                            <div>
                                <p style="font-size:.8125rem;font-weight:600;color:#1e293b;margin:0;">{{ $u->name }}</p>
                                <p style="font-size:.6875rem;color:#94a3b8;margin:0;">{{ $u->email }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>
                <button type="submit" style="padding:8px 16px;border:none;border-radius:8px;background:#4f46e5;color:#fff;font-size:.8125rem;font-weight:600;cursor:pointer;">Agregar seleccionados</button>
            </form>
        @else
            <p style="font-size:.875rem;color:#94a3b8;text-align:center;">Todos los usuarios de la institución ya están en este grupo.</p>
        @endif
    </div>
</div>

{{-- Current members --}}
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;margin-top:24px;">
    <div style="padding:20px 24px;border-bottom:1px solid #f1f5f9;">
        <h3 style="font-size:1rem;font-weight:700;color:#0f172a;margin:0;">Miembros del grupo ({{ $group->users->count() }})</h3>
    </div>
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Usuario</th>
                <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Email</th>
                <th style="text-align:center;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Rol</th>
                <th style="text-align:center;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Acción</th>
            </tr>
        </thead>
        <tbody>
            @forelse($group->users as $member)
            <tr style="border-bottom:1px solid #f8fafc;">
                <td style="padding:12px 16px;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:28px;height:28px;border-radius:9999px;background:linear-gradient(135deg,#38bdf8,#6366f1);display:flex;align-items:center;justify-content:center;color:#fff;font-size:.6875rem;font-weight:700;">{{ strtoupper(substr($member->name, 0, 1)) }}</div>
                        <span style="font-size:.8125rem;font-weight:600;color:#1e293b;">{{ $member->name }}</span>
                    </div>
                </td>
                <td style="padding:12px 16px;font-size:.8125rem;color:#64748b;">{{ $member->email }}</td>
                <td style="padding:12px 16px;text-align:center;">
                    <span style="font-size:.6875rem;font-weight:700;padding:3px 8px;border-radius:6px;background:#f8fafc;color:#64748b;border:1px solid #e2e8f0;">{{ $member->role }}</span>
                </td>
                <td style="padding:12px 16px;text-align:center;">
                    <form method="POST" action="{{ route('superadmin.groups.update', $group) }}" style="display:inline;">
                        @csrf
                        <input type="hidden" name="action" value="remove_user">
                        <input type="hidden" name="remove_user_id" value="{{ $member->id }}">
                        <button type="submit" style="font-size:.6875rem;font-weight:600;color:#dc2626;padding:4px 10px;border-radius:8px;border:1px solid #fecaca;background:#fef2f2;cursor:pointer;">Quitar</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="4" style="padding:32px;text-align:center;color:#94a3b8;">Sin miembros en este grupo.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
