@extends('superadmin._layout')
@section('title', 'Instituciones')

@section('panel')
{{-- Crear institución --}}
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:28px;margin-bottom:24px;">
    <h3 style="font-size:1rem;font-weight:700;color:#0f172a;margin:0 0 20px;">Nueva institución</h3>
    <form method="POST" action="{{ route('superadmin.institutions.store') }}" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
        @csrf
        <div>
            <label style="font-size:.75rem;font-weight:600;color:#64748b;display:block;margin-bottom:6px;">Nombre</label>
            <input type="text" name="name" required placeholder="Ej: Universidad Tecnológica"
                   style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:10px;font-size:.875rem;outline:none;"
                   onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='#e2e8f0'">
        </div>
        <div>
            <label style="font-size:.75rem;font-weight:600;color:#64748b;display:block;margin-bottom:6px;">Slug (único)</label>
            <input type="text" name="slug" required placeholder="ej: universidad-tecnologica"
                   style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:10px;font-size:.875rem;outline:none;"
                   onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='#e2e8f0'">
        </div>
        <div>
            <label style="font-size:.75rem;font-weight:600;color:#64748b;display:block;margin-bottom:6px;">Email de contacto</label>
            <input type="email" name="contact_email" placeholder="contacto@institucion.edu"
                   style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:10px;font-size:.875rem;outline:none;"
                   onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='#e2e8f0'">
        </div>
        <div>
            <label style="font-size:.75rem;font-weight:600;color:#64748b;display:block;margin-bottom:6px;">Descripción</label>
            <input type="text" name="description" placeholder="Descripción breve (opcional)"
                   style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:10px;font-size:.875rem;outline:none;"
                   onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='#e2e8f0'">
        </div>
        <div style="grid-column:span 2;display:flex;justify-content:flex-end;">
            <button type="submit" style="padding:10px 24px;border:none;border-radius:10px;background:linear-gradient(135deg,#38bdf8,#6366f1,#9333ea);color:#fff;font-size:.875rem;font-weight:700;cursor:pointer;transition:opacity .15s;"
                    onmouseover="this.style.opacity='.9'" onmouseout="this.style.opacity='1'">Crear institución</button>
        </div>
    </form>
    @if($errors->any())
        <div style="margin-top:12px;padding:10px 14px;background:#fef2f2;border:1px solid #fecaca;border-radius:10px;">
            @foreach($errors->all() as $error)
                <p style="font-size:.8125rem;color:#dc2626;margin:0;">{{ $error }}</p>
            @endforeach
        </div>
    @endif
</div>

{{-- Lista de instituciones --}}
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;">
    <div style="padding:20px 24px;border-bottom:1px solid #f1f5f9;">
        <h3 style="font-size:1rem;font-weight:700;color:#0f172a;margin:0;">Instituciones registradas</h3>
    </div>
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                    <th style="text-align:left;padding:14px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Institución</th>
                    <th style="text-align:left;padding:14px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Slug</th>
                    <th style="text-align:left;padding:14px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Contacto</th>
                    <th style="text-align:center;padding:14px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Usuarios</th>
                    <th style="text-align:left;padding:14px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Creada</th>
                    <th style="text-align:center;padding:14px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($institutions as $inst)
                <tr style="border-bottom:1px solid #f1f5f9;" onmouseover="this.style.background='#fafbff'" onmouseout="this.style.background='transparent'">
                    <td style="padding:14px 16px;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:36px;height:36px;border-radius:10px;background:#f5f3ff;border:1px solid #ddd6fe;display:flex;align-items:center;justify-content:center;">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="#7c3aed" style="width:18px;height:18px;"><path fill-rule="evenodd" d="M1 2.75A.75.75 0 0 1 1.75 2h16.5a.75.75 0 0 1 0 1.5H18v12.5h.25a.75.75 0 0 1 0 1.5H1.75a.75.75 0 0 1 0-1.5H2V3.5h-.25A.75.75 0 0 1 1 2.75ZM10 10a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z" clip-rule="evenodd"/></svg>
                            </div>
                            <div>
                                <p style="font-size:.875rem;font-weight:600;color:#1e293b;margin:0;">{{ $inst->name }}</p>
                                @if($inst->description)
                                    <p style="font-size:.75rem;color:#94a3b8;margin:2px 0 0;">{{ Str::limit($inst->description, 50) }}</p>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td style="padding:14px 16px;font-size:.8125rem;color:#64748b;font-family:monospace;">{{ $inst->slug }}</td>
                    <td style="padding:14px 16px;font-size:.8125rem;color:#64748b;">{{ $inst->contact_email ?: '—' }}</td>
                    <td style="padding:14px 16px;text-align:center;">
                        <span style="font-size:.8125rem;font-weight:700;color:#4f46e5;background:#eef2ff;padding:4px 10px;border-radius:9999px;">{{ $inst->users_count }}</span>
                    </td>
                    <td style="padding:14px 16px;font-size:.8125rem;color:#64748b;">{{ $inst->created_at->format('d/m/Y') }}</td>
                    <td style="padding:14px 16px;text-align:center;">
                        <a href="{{ route('superadmin.institutions.edit', $inst) }}" style="font-size:.6875rem;font-weight:600;color:#4f46e5;padding:5px 12px;border-radius:8px;border:1px solid #c7d2fe;background:#eef2ff;">Editar</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="padding:48px;text-align:center;color:#94a3b8;font-size:.875rem;">Sin instituciones registradas. Crea la primera arriba.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
