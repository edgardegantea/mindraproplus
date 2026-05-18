@extends('superadmin._layout')
@section('title', 'Solicitudes de Plan')

@section('panel')
{{-- Filters --}}
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:16px 24px;margin-bottom:20px;display:flex;gap:8px;">
    @foreach(['pending' => 'Pendientes', 'approved' => 'Aprobadas', 'rejected' => 'Rechazadas', '' => 'Todas'] as $val => $label)
        <a href="{{ route('superadmin.plan-requests', ['status' => $val]) }}"
           style="padding:8px 14px;border-radius:8px;font-size:.8125rem;font-weight:600;
           {{ (request('status', 'pending') == $val) ? 'background:linear-gradient(135deg,#38bdf8,#6366f1,#9333ea);color:#fff;' : 'background:#f8fafc;color:#64748b;border:1px solid #e2e8f0;' }}">{{ $label }}</a>
    @endforeach
</div>

<div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Fecha</th>
                <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Usuario</th>
                <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Plan solicitado</th>
                <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Mensaje</th>
                <th style="text-align:center;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Estado</th>
                <th style="text-align:center;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($requests as $req)
            @php
                $statusColors = [
                    'pending' => ['#d97706', '#fffbeb', '#fde68a'],
                    'approved' => ['#16a34a', '#f0fdf4', '#bbf7d0'],
                    'rejected' => ['#dc2626', '#fef2f2', '#fecaca'],
                ];
                [$sColor, $sBg, $sBorder] = $statusColors[$req->status] ?? $statusColors['pending'];
                $statusLabels = ['pending' => 'Pendiente', 'approved' => 'Aprobada', 'rejected' => 'Rechazada'];
            @endphp
            <tr style="border-bottom:1px solid #f8fafc;" onmouseover="this.style.background='#fafbff'" onmouseout="this.style.background='transparent'">
                <td style="padding:12px 16px;font-size:.8125rem;color:#64748b;white-space:nowrap;">{{ $req->created_at->format('d/m/Y H:i') }}</td>
                <td style="padding:12px 16px;">
                    @if($req->user)
                        <a href="{{ route('superadmin.users.detail', $req->user) }}" style="font-size:.8125rem;font-weight:600;color:#4f46e5;">{{ $req->user->name }}</a>
                        <p style="font-size:.6875rem;color:#94a3b8;margin:2px 0 0;">{{ $req->user->email }}</p>
                    @else
                        <span style="font-size:.8125rem;color:#94a3b8;">—</span>
                    @endif
                </td>
                <td style="padding:12px 16px;">
                    <span style="font-size:.75rem;font-weight:700;padding:3px 10px;border-radius:6px;background:#f5f3ff;color:#7c3aed;border:1px solid #ddd6fe;">{{ $req->plan?->name ?? '—' }}</span>
                </td>
                <td style="padding:12px 16px;font-size:.8125rem;color:#334155;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $req->message ?: '—' }}</td>
                <td style="padding:12px 16px;text-align:center;">
                    <span style="font-size:.6875rem;font-weight:700;padding:3px 10px;border-radius:9999px;background:{{ $sBg }};color:{{ $sColor }};border:1px solid {{ $sBorder }};">{{ $statusLabels[$req->status] }}</span>
                </td>
                <td style="padding:12px 16px;text-align:center;">
                    @if($req->status === 'pending')
                        <div style="display:flex;gap:6px;justify-content:center;">
                            <form method="POST" action="{{ route('superadmin.plan-requests.review', $req) }}" style="display:inline;">
                                @csrf
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" style="font-size:.6875rem;font-weight:600;color:#16a34a;padding:4px 10px;border-radius:8px;border:1px solid #bbf7d0;background:#f0fdf4;cursor:pointer;">Aprobar</button>
                            </form>
                            <form method="POST" action="{{ route('superadmin.plan-requests.review', $req) }}" style="display:inline;">
                                @csrf
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" style="font-size:.6875rem;font-weight:600;color:#dc2626;padding:4px 10px;border-radius:8px;border:1px solid #fecaca;background:#fef2f2;cursor:pointer;">Rechazar</button>
                            </form>
                        </div>
                    @else
                        <span style="font-size:.6875rem;color:#94a3b8;">{{ $req->reviewer?->name ?? '—' }}</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="6" style="padding:48px;text-align:center;color:#94a3b8;">Sin solicitudes {{ request('status', 'pending') === 'pending' ? 'pendientes' : '' }}.</td></tr>
            @endforelse
        </tbody>
    </table>

    @if($requests->hasPages())
    <div style="padding:16px 24px;border-top:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;">
        <span style="font-size:.8125rem;color:#94a3b8;">{{ $requests->firstItem() }}-{{ $requests->lastItem() }} de {{ $requests->total() }}</span>
        <div style="display:flex;gap:6px;">
            @if(!$requests->onFirstPage())<a href="{{ $requests->previousPageUrl() }}" style="padding:6px 12px;border-radius:8px;font-size:.8125rem;color:#4f46e5;background:#eef2ff;font-weight:600;">Anterior</a>@endif
            @if($requests->hasMorePages())<a href="{{ $requests->nextPageUrl() }}" style="padding:6px 12px;border-radius:8px;font-size:.8125rem;color:#4f46e5;background:#eef2ff;font-weight:600;">Siguiente</a>@endif
        </div>
    </div>
    @endif
</div>
@endsection
