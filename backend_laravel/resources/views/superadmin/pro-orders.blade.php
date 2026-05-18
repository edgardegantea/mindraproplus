@extends('superadmin._layout')
@section('title', 'Órdenes Pro')

@section('panel')
{{-- Filters --}}
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:16px 24px;margin-bottom:20px;display:flex;gap:8px;">
    @foreach(['pending' => 'Pendientes', 'paid' => 'Pagadas', 'rejected' => 'Rechazadas', '' => 'Todas'] as $val => $label)
        <a href="{{ route('superadmin.pro-orders', ['status' => $val]) }}"
           style="padding:8px 14px;border-radius:8px;font-size:.8125rem;font-weight:600;
           {{ ($status == $val) ? 'background:linear-gradient(135deg,#38bdf8,#6366f1,#9333ea);color:#fff;' : 'background:#f8fafc;color:#64748b;border:1px solid #e2e8f0;' }}">{{ $label }}</a>
    @endforeach
</div>

<div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Fecha</th>
                <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Suscriptor</th>
                <th style="text-align:center;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Periodo</th>
                <th style="text-align:right;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Monto</th>
                <th style="text-align:center;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">MercadoPago</th>
                <th style="text-align:center;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Estado</th>
                <th style="text-align:center;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
            @php
                $statusColors = [
                    'pending'  => ['#d97706', '#fffbeb', '#fde68a'],
                    'paid'     => ['#16a34a', '#f0fdf4', '#bbf7d0'],
                    'rejected' => ['#dc2626', '#fef2f2', '#fecaca'],
                ];
                [$sColor, $sBg, $sBorder] = $statusColors[$order->status] ?? $statusColors['pending'];
                $statusLabels = ['pending' => 'Pendiente', 'paid' => 'Pagada', 'rejected' => 'Rechazada'];
            @endphp
            <tr style="border-bottom:1px solid #f8fafc;" onmouseover="this.style.background='#fafbff'" onmouseout="this.style.background='transparent'">
                <td style="padding:12px 16px;font-size:.8125rem;color:#64748b;white-space:nowrap;">
                    {{ $order->created_at->format('d/m/Y H:i') }}
                    @if($order->paid_at)
                        <div style="font-size:.6875rem;color:#16a34a;">Pagado: {{ $order->paid_at->format('d/m/Y H:i') }}</div>
                    @endif
                </td>
                <td style="padding:12px 16px;">
                    <div style="font-size:.8125rem;font-weight:600;color:#1e293b;">{{ $order->full_name }}</div>
                    <div style="font-size:.6875rem;color:#94a3b8;">{{ $order->email }}</div>
                    @if($order->user)
                        <a href="{{ route('superadmin.users.detail', $order->user) }}" style="font-size:.6875rem;color:#4f46e5;font-weight:600;">Ver usuario</a>
                    @endif
                </td>
                <td style="padding:12px 16px;text-align:center;">
                    <span style="font-size:.75rem;font-weight:700;padding:3px 10px;border-radius:6px;background:#f5f3ff;color:#7c3aed;border:1px solid #ddd6fe;">
                        {{ $order->billing_period === 'annual' ? 'Anual' : 'Mensual' }}
                    </span>
                </td>
                <td style="padding:12px 16px;text-align:right;font-size:.875rem;font-weight:700;color:#1e293b;">
                    ${{ number_format($order->amount_cents / 100, 2) }} {{ $order->currency }}
                </td>
                <td style="padding:12px 16px;text-align:center;">
                    @if($order->mp_payment_id)
                        <div style="font-size:.75rem;font-weight:600;color:#1e293b;">#{{ $order->mp_payment_id }}</div>
                        @if($order->mp_status)
                            <div style="font-size:.6875rem;color:#64748b;">{{ $order->mp_status }}</div>
                        @endif
                        @if($order->mp_payment_type)
                            <div style="font-size:.6875rem;color:#94a3b8;">{{ $order->mp_payment_type }}</div>
                        @endif
                    @else
                        <span style="font-size:.75rem;color:#94a3b8;">Sin pago</span>
                    @endif
                </td>
                <td style="padding:12px 16px;text-align:center;">
                    <span style="font-size:.6875rem;font-weight:700;padding:3px 10px;border-radius:9999px;background:{{ $sBg }};color:{{ $sColor }};border:1px solid {{ $sBorder }};">{{ $statusLabels[$order->status] ?? ucfirst($order->status) }}</span>
                </td>
                <td style="padding:12px 16px;text-align:center;">
                    @if($order->status === 'pending')
                        <div style="display:flex;gap:6px;justify-content:center;">
                            <form method="POST" action="{{ route('superadmin.pro-orders.review', $order) }}" style="display:inline;">
                                @csrf
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" style="font-size:.6875rem;font-weight:600;color:#16a34a;padding:4px 10px;border-radius:8px;border:1px solid #bbf7d0;background:#f0fdf4;cursor:pointer;" title="Aprobar manualmente y activar plan Pro">Aprobar</button>
                            </form>
                            <form method="POST" action="{{ route('superadmin.pro-orders.review', $order) }}" style="display:inline;">
                                @csrf
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" style="font-size:.6875rem;font-weight:600;color:#dc2626;padding:4px 10px;border-radius:8px;border:1px solid #fecaca;background:#fef2f2;cursor:pointer;">Rechazar</button>
                            </form>
                        </div>
                    @elseif($order->status === 'paid')
                        <span style="font-size:.6875rem;color:#16a34a;font-weight:600;">Auto</span>
                    @else
                        <span style="font-size:.6875rem;color:#94a3b8;">—</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="7" style="padding:48px;text-align:center;color:#94a3b8;">Sin órdenes {{ $status === 'pending' ? 'pendientes' : '' }}.</td></tr>
            @endforelse
        </tbody>
    </table>

    @if($orders->hasPages())
    <div style="padding:16px 24px;border-top:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;">
        <span style="font-size:.8125rem;color:#94a3b8;">{{ $orders->firstItem() }}-{{ $orders->lastItem() }} de {{ $orders->total() }}</span>
        <div style="display:flex;gap:6px;">
            @if(!$orders->onFirstPage())<a href="{{ $orders->previousPageUrl() }}" style="padding:6px 12px;border-radius:8px;font-size:.8125rem;color:#4f46e5;background:#eef2ff;font-weight:600;">Anterior</a>@endif
            @if($orders->hasMorePages())<a href="{{ $orders->nextPageUrl() }}" style="padding:6px 12px;border-radius:8px;font-size:.8125rem;color:#4f46e5;background:#eef2ff;font-weight:600;">Siguiente</a>@endif
        </div>
    </div>
    @endif
</div>
@endsection
