@extends('admin._layout')
@section('title', 'Mi Institución')

@section('panel')
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:48px;text-align:center;">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="#94a3b8" style="width:48px;height:48px;margin:0 auto 16px;display:block;"><path fill-rule="evenodd" d="M1 2.75A.75.75 0 0 1 1.75 2h16.5a.75.75 0 0 1 0 1.5H18v12.5h.25a.75.75 0 0 1 0 1.5H1.75a.75.75 0 0 1 0-1.5H2V3.5h-.25A.75.75 0 0 1 1 2.75ZM10 10a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z" clip-rule="evenodd"/></svg>
    <h3 style="font-size:1.125rem;font-weight:700;color:#0f172a;margin:0 0 8px;">Sin institución asignada</h3>
    <p style="font-size:.875rem;color:#64748b;margin:0;">Contacta al superadmin del sistema para que te asigne una institución.</p>
</div>
@endsection
