@extends('superadmin._layout')
@section('title', 'Editar: ' . $institution->name)

@section('panel')
<a href="{{ route('superadmin.institutions') }}" style="display:inline-flex;align-items:center;gap:6px;font-size:.8125rem;font-weight:600;color:#4f46e5;margin-bottom:20px;">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" style="width:14px;height:14px;"><path fill-rule="evenodd" d="M9.78 4.22a.75.75 0 0 1 0 1.06L7.06 8l2.72 2.72a.75.75 0 1 1-1.06 1.06L5.47 8.53a.75.75 0 0 1 0-1.06l3.25-3.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd"/></svg>
    Volver a instituciones
</a>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;">
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:28px;">
        <h3 style="font-size:1rem;font-weight:700;color:#0f172a;margin:0 0 24px;">Editar institución</h3>
        <form method="POST" action="{{ route('superadmin.institutions.update', $institution) }}" style="display:flex;flex-direction:column;gap:18px;">
            @csrf
            @method('PUT')
            <div>
                <label style="font-size:.75rem;font-weight:600;color:#64748b;display:block;margin-bottom:6px;">Nombre</label>
                <input type="text" name="name" value="{{ old('name', $institution->name) }}" required
                       style="width:100%;padding:12px 16px;border:1px solid #e2e8f0;border-radius:10px;font-size:.875rem;outline:none;"
                       onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='#e2e8f0'">
            </div>
            <div>
                <label style="font-size:.75rem;font-weight:600;color:#64748b;display:block;margin-bottom:6px;">Slug</label>
                <input type="text" name="slug" value="{{ old('slug', $institution->slug) }}" required
                       style="width:100%;padding:12px 16px;border:1px solid #e2e8f0;border-radius:10px;font-size:.875rem;outline:none;font-family:monospace;"
                       onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='#e2e8f0'">
            </div>
            <div>
                <label style="font-size:.75rem;font-weight:600;color:#64748b;display:block;margin-bottom:6px;">Descripción</label>
                <textarea name="description" rows="3" placeholder="Descripción breve..."
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

    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:28px;">
        <h4 style="font-size:.875rem;font-weight:700;color:#0f172a;margin:0 0 16px;">Resumen</h4>
        <div style="display:flex;flex-direction:column;gap:14px;">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <span style="font-size:.8125rem;color:#64748b;">Usuarios asignados</span>
                <span style="font-size:1rem;font-weight:700;color:#4f46e5;">{{ $institution->users_count }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <span style="font-size:.8125rem;color:#64748b;">ID</span>
                <span style="font-size:.8125rem;font-weight:600;color:#334155;">#{{ $institution->id }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <span style="font-size:.8125rem;color:#64748b;">Creada</span>
                <span style="font-size:.8125rem;font-weight:600;color:#334155;">{{ $institution->created_at->format('d/m/Y') }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <span style="font-size:.8125rem;color:#64748b;">Actualizada</span>
                <span style="font-size:.8125rem;font-weight:600;color:#334155;">{{ $institution->updated_at->format('d/m/Y H:i') }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
