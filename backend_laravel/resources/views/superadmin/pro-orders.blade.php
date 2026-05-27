@extends('superadmin._layout')
@section('title', 'Solicitudes Plan Plus')

@section('panel')

@if(session('success'))
<div style="background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:12px;padding:14px 18px;margin-bottom:16px;color:#15803d;font-size:.875rem;font-weight:600;">
    ✅ {{ session('success') }}
</div>
@endif

{{-- Filters --}}
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:14px 20px;margin-bottom:20px;display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
    @foreach([
        'inquiry'   => '🔵 Nuevas',
        'in_review' => '🟡 En revisión',
        'paid'      => '🟢 Aprobadas',
        'rejected'  => '🔴 Rechazadas',
        ''          => 'Todas',
    ] as $val => $label)
        <a href="{{ route('superadmin.pro-orders', ['status' => $val]) }}"
           style="padding:7px 14px;border-radius:8px;font-size:.8125rem;font-weight:600;text-decoration:none;
           {{ ($status === $val) ? 'background:linear-gradient(135deg,#7c3aed,#4f46e5);color:#fff;' : 'background:#f8fafc;color:#64748b;border:1px solid #e2e8f0;' }}">
           {{ $label }}
        </a>
    @endforeach
</div>

<div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Fecha</th>
                <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Solicitante</th>
                <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Institución</th>
                <th style="text-align:center;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Estado</th>
                <th style="text-align:center;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Admin asignado</th>
                <th style="text-align:center;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
            @php
                $statusMap = [
                    'inquiry'   => ['#2563eb','#eff6ff','#bfdbfe','🔵 Nueva solicitud'],
                    'in_review' => ['#d97706','#fffbeb','#fde68a','🟡 En revisión'],
                    'paid'      => ['#16a34a','#f0fdf4','#bbf7d0','🟢 Aprobada'],
                    'rejected'  => ['#dc2626','#fef2f2','#fecaca','🔴 Rechazada'],
                    'pending'   => ['#9333ea','#faf5ff','#ddd6fe','⚪ Pendiente pago'],
                ];
                [$sColor, $sBg, $sBorder, $sLabel] = $statusMap[$order->status] ?? ['#64748b','#f8fafc','#e2e8f0', ucfirst($order->status)];
                $notes = is_array($order->notes) ? $order->notes : [];
                $orgName = $notes['org_name'] ?? '—';
            @endphp
            <tr style="border-bottom:1px solid #f1f5f9;" onmouseover="this.style.background='#fafbff'" onmouseout="this.style.background='transparent'">
                <td style="padding:12px 16px;font-size:.8125rem;color:#64748b;white-space:nowrap;">
                    {{ $order->created_at->format('d/m/Y') }}<br>
                    <span style="font-size:.6875rem;">{{ $order->created_at->format('H:i') }}</span>
                </td>
                <td style="padding:12px 16px;">
                    <div style="font-size:.8125rem;font-weight:600;color:#1e293b;">{{ $order->full_name }}</div>
                    <div style="font-size:.6875rem;color:#94a3b8;">{{ $order->email }}</div>
                    @if($notes['requester_position'] ?? null)
                        <div style="font-size:.6875rem;color:#64748b;">{{ $notes['requester_position'] }}</div>
                    @endif
                </td>
                <td style="padding:12px 16px;">
                    <div style="font-size:.8125rem;font-weight:600;color:#1e293b;">{{ $orgName }}</div>
                    @if($notes['org_type_label'] ?? null)
                        <div style="font-size:.6875rem;color:#64748b;">{{ $notes['org_type_label'] }}</div>
                    @endif
                    @if($notes['org_country'] ?? null)
                        <div style="font-size:.6875rem;color:#94a3b8;">{{ $notes['org_country'] }}{{ ($notes['org_state'] ?? null) ? ', '.$notes['org_state'] : '' }}</div>
                    @endif
                </td>
                <td style="padding:12px 16px;text-align:center;">
                    <span style="font-size:.6875rem;font-weight:700;padding:4px 10px;border-radius:9999px;
                        background:{{ $sBg }};color:{{ $sColor }};border:1px solid {{ $sBorder }};">
                        {{ $sLabel }}
                    </span>
                    @if($order->reviewed_at)
                        <div style="font-size:.625rem;color:#94a3b8;margin-top:3px;">{{ $order->reviewed_at->format('d/m/Y') }}</div>
                    @endif
                </td>
                <td style="padding:12px 16px;text-align:center;">
                    @if($order->assignedAdmin)
                        <div style="font-size:.75rem;font-weight:600;color:#4f46e5;">{{ $order->assignedAdmin->name }}</div>
                        <div style="font-size:.625rem;color:#94a3b8;">{{ $order->assignedAdmin->email }}</div>
                    @else
                        <span style="font-size:.75rem;color:#cbd5e1;">Sin asignar</span>
                    @endif
                </td>
                <td style="padding:12px 16px;text-align:center;">
                    <a href="{{ route('superadmin.pro-orders.show', $order) }}"
                       style="font-size:.75rem;font-weight:600;color:#4f46e5;padding:6px 14px;border-radius:8px;border:1px solid #c7d2fe;background:#eef2ff;text-decoration:none;white-space:nowrap;">
                        Ver detalle →
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="padding:48px;text-align:center;color:#94a3b8;">
                    Sin órdenes con estado "{{ $status ?: 'todos' }}".
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($orders->hasPages())
    <div style="padding:16px 24px;border-top:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;">
        <span style="font-size:.8125rem;color:#94a3b8;">{{ $orders->firstItem() }}-{{ $orders->lastItem() }} de {{ $orders->total() }}</span>
        <div style="display:flex;gap:6px;">
            @if(!$orders->onFirstPage())<a href="{{ $orders->previousPageUrl() }}" style="padding:6px 12px;border-radius:8px;font-size:.8125rem;color:#4f46e5;background:#eef2ff;font-weight:600;text-decoration:none;">Anterior</a>@endif
            @if($orders->hasMorePages())<a href="{{ $orders->nextPageUrl() }}" style="padding:6px 12px;border-radius:8px;font-size:.8125rem;color:#4f46e5;background:#eef2ff;font-weight:600;text-decoration:none;">Siguiente</a>@endif
        </div>
    </div>
    @endif
</div>
@endsection
