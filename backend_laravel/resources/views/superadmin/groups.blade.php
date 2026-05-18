@extends('superadmin._layout')
@section('title', 'Grupos')

@section('panel')
{{-- Create group --}}
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:28px;margin-bottom:24px;">
    <h3 style="font-size:1rem;font-weight:700;color:#0f172a;margin:0 0 20px;">Nuevo grupo</h3>
    <form method="POST" action="{{ route('superadmin.groups.store') }}" style="display:flex;align-items:flex-end;gap:12px;flex-wrap:wrap;">
        @csrf
        <div style="flex:1;min-width:160px;">
            <label style="font-size:.75rem;font-weight:600;color:#64748b;display:block;margin-bottom:6px;">Nombre</label>
            <input type="text" name="name" required placeholder="Ej: Grupo A - Psicología"
                   style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:10px;font-size:.875rem;outline:none;">
        </div>
        <div style="flex:1;min-width:160px;">
            <label style="font-size:.75rem;font-weight:600;color:#64748b;display:block;margin-bottom:6px;">Institución</label>
            <select name="institution_id" required style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:10px;font-size:.875rem;background:#fff;">
                <option value="">Seleccionar...</option>
                @foreach($institutions as $inst)
                    <option value="{{ $inst->id }}">{{ $inst->name }}</option>
                @endforeach
            </select>
        </div>
        <div style="flex:1;min-width:160px;">
            <label style="font-size:.75rem;font-weight:600;color:#64748b;display:block;margin-bottom:6px;">Descripción</label>
            <input type="text" name="description" placeholder="Descripción breve (opcional)"
                   style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:10px;font-size:.875rem;outline:none;">
        </div>
        <button type="submit" style="padding:10px 20px;border:none;border-radius:10px;background:linear-gradient(135deg,#38bdf8,#6366f1,#9333ea);color:#fff;font-size:.875rem;font-weight:700;cursor:pointer;">Crear</button>
    </form>
    @if($errors->any())
        <div style="margin-top:12px;padding:10px 14px;background:#fef2f2;border:1px solid #fecaca;border-radius:10px;">
            @foreach($errors->all() as $error)
                <p style="font-size:.8125rem;color:#dc2626;margin:0;">{{ $error }}</p>
            @endforeach
        </div>
    @endif
</div>

{{-- Groups list --}}
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;">
    <div style="padding:20px 24px;border-bottom:1px solid #f1f5f9;">
        <h3 style="font-size:1rem;font-weight:700;color:#0f172a;margin:0;">Grupos registrados</h3>
    </div>
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Grupo</th>
                <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Institución</th>
                <th style="text-align:center;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Miembros</th>
                <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Creado</th>
                <th style="text-align:center;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($groups as $group)
            <tr style="border-bottom:1px solid #f8fafc;" onmouseover="this.style.background='#fafbff'" onmouseout="this.style.background='transparent'">
                <td style="padding:12px 16px;">
                    <p style="font-size:.875rem;font-weight:600;color:#1e293b;margin:0;">{{ $group->name }}</p>
                    @if($group->description)
                        <p style="font-size:.6875rem;color:#94a3b8;margin:2px 0 0;">{{ Str::limit($group->description, 60) }}</p>
                    @endif
                </td>
                <td style="padding:12px 16px;font-size:.8125rem;color:#64748b;">{{ $group->institution?->name ?? '—' }}</td>
                <td style="padding:12px 16px;text-align:center;">
                    <span style="font-size:.8125rem;font-weight:700;color:#4f46e5;background:#eef2ff;padding:4px 10px;border-radius:9999px;">{{ $group->users_count }}</span>
                </td>
                <td style="padding:12px 16px;font-size:.8125rem;color:#64748b;">{{ $group->created_at->format('d/m/Y') }}</td>
                <td style="padding:12px 16px;text-align:center;">
                    <div style="display:flex;gap:6px;justify-content:center;">
                        <a href="{{ route('superadmin.groups.edit', $group) }}" style="font-size:.6875rem;font-weight:600;color:#4f46e5;padding:4px 10px;border-radius:8px;border:1px solid #c7d2fe;background:#eef2ff;">Editar</a>
                        <form method="POST" action="{{ route('superadmin.groups.delete', $group) }}" onsubmit="return confirm('¿Eliminar este grupo?');" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="font-size:.6875rem;font-weight:600;color:#dc2626;padding:4px 10px;border-radius:8px;border:1px solid #fecaca;background:#fef2f2;cursor:pointer;">Eliminar</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" style="padding:48px;text-align:center;color:#94a3b8;">Sin grupos. Crea el primero arriba.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
