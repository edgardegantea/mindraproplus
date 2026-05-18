@extends('admin._layout')
@section('title', 'Mi Institución')

@section('panel')
<div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;">
    {{-- Formulario de edición --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:28px;">
        <h3 style="font-size:1rem;font-weight:700;color:#0f172a;margin:0 0 24px;">Información de la institución</h3>
        <form method="POST" action="{{ route('admin.institution.update') }}" style="display:flex;flex-direction:column;gap:18px;">
            @csrf
            <div>
                <label style="font-size:.75rem;font-weight:600;color:#64748b;display:block;margin-bottom:6px;">Nombre de la institución</label>
                <input type="text" name="name" value="{{ old('name', $institution->name) }}" required
                       style="width:100%;padding:12px 16px;border:1px solid #e2e8f0;border-radius:10px;font-size:.875rem;outline:none;"
                       onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='#e2e8f0'">
            </div>
            <div>
                <label style="font-size:.75rem;font-weight:600;color:#64748b;display:block;margin-bottom:6px;">Descripción</label>
                <textarea name="description" rows="3" placeholder="Descripción breve de la institución..."
                          style="width:100%;padding:12px 16px;border:1px solid #e2e8f0;border-radius:10px;font-size:.875rem;outline:none;resize:vertical;"
                          onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='#e2e8f0'">{{ old('description', $institution->description) }}</textarea>
            </div>
            <div>
                <label style="font-size:.75rem;font-weight:600;color:#64748b;display:block;margin-bottom:6px;">Email de contacto</label>
                <input type="email" name="contact_email" value="{{ old('contact_email', $institution->contact_email) }}" placeholder="contacto@institucion.edu"
                       style="width:100%;padding:12px 16px;border:1px solid #e2e8f0;border-radius:10px;font-size:.875rem;outline:none;"
                       onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='#e2e8f0'">
            </div>
            <div style="display:flex;justify-content:flex-end;">
                <button type="submit" style="padding:10px 24px;border:none;border-radius:10px;background:linear-gradient(135deg,#38bdf8,#6366f1,#9333ea);color:#fff;font-size:.875rem;font-weight:700;cursor:pointer;">
                    Guardar cambios
                </button>
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

    {{-- Info card --}}
    <div style="display:flex;flex-direction:column;gap:20px;">
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:28px;text-align:center;">
            <div style="width:64px;height:64px;border-radius:16px;background:#f5f3ff;border:1px solid #ddd6fe;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="#7c3aed" style="width:28px;height:28px;"><path fill-rule="evenodd" d="M1 2.75A.75.75 0 0 1 1.75 2h16.5a.75.75 0 0 1 0 1.5H18v12.5h.25a.75.75 0 0 1 0 1.5H1.75a.75.75 0 0 1 0-1.5H2V3.5h-.25A.75.75 0 0 1 1 2.75ZM10 10a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z" clip-rule="evenodd"/></svg>
            </div>
            <h4 style="font-size:1.125rem;font-weight:700;color:#0f172a;margin:0;">{{ $institution->name }}</h4>
            <p style="font-size:.8125rem;color:#64748b;margin:4px 0 0;">{{ $institution->slug }}</p>
        </div>

        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:24px;">
            <div style="display:flex;flex-direction:column;gap:14px;">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:.8125rem;color:#64748b;">Usuarios</span>
                    <span style="font-size:1rem;font-weight:700;color:#4f46e5;">{{ $institution->users_count }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:.8125rem;color:#64748b;">Email contacto</span>
                    <span style="font-size:.8125rem;font-weight:600;color:#334155;">{{ $institution->contact_email ?: '—' }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:.8125rem;color:#64748b;">Creada</span>
                    <span style="font-size:.8125rem;font-weight:600;color:#334155;">{{ $institution->created_at->format('d/m/Y') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
