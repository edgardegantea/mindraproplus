@php
    $colors = [
        'indigo'  => ['bg'=>'#eef2ff','border'=>'#c7d2fe','icon'=>'#4338ca','title'=>'#3730a3'],
        'violet'  => ['bg'=>'#f5f3ff','border'=>'#ddd6fe','icon'=>'#6d28d9','title'=>'#5b21b6'],
        'amber'   => ['bg'=>'#fffbeb','border'=>'#fde68a','icon'=>'#b45309','title'=>'#92400e'],
        'slate'   => ['bg'=>'#f8fafc','border'=>'#e2e8f0','icon'=>'#475569','title'=>'#1e293b'],
        'emerald' => ['bg'=>'#f0fdf4','border'=>'#bbf7d0','icon'=>'#15803d','title'=>'#14532d'],
        'purple'  => ['bg'=>'#faf5ff','border'=>'#e9d5ff','icon'=>'#7e22ce','title'=>'#6b21a8'],
    ];
    $c = $colors[$color ?? 'indigo'];
@endphp

{{-- Breadcrumb --}}
<nav style="display:flex;align-items:center;gap:6px;font-size:.875rem;color:#94a3b8;margin-bottom:1.5rem;max-width:48rem;margin-left:auto;margin-right:auto;margin-bottom:1.5rem;">
    <a href="{{ route('dashboard') }}" style="color:#94a3b8;text-decoration:none;"
       onmouseover="this.style.color='#4f46e5'" onmouseout="this.style.color='#94a3b8'">Inicio</a>
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:12px;height:12px;color:#cbd5e1;">
        <path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/>
    </svg>
    <span style="color:#64748b;">{{ $title }}</span>
</nav>

{{-- Header card --}}
<div style="display:flex;align-items:flex-start;gap:16px;padding:1.5rem;border-radius:18px;border:1px solid {{ $c['border'] }};background:{{ $c['bg'] }};margin-bottom:2.5rem;max-width:48rem;margin-left:auto;margin-right:auto;">
    <div style="flex-shrink:0;width:44px;height:44px;border-radius:12px;background:{{ $c['bg'] }};border:1.5px solid {{ $c['border'] }};display:flex;align-items:center;justify-content:center;">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="{{ $c['icon'] }}" style="width:22px;height:22px;">
            <path fill-rule="evenodd" d="{{ $icon }}" clip-rule="evenodd"/>
        </svg>
    </div>
    <div>
        <h1 style="font-size:1.5rem;font-weight:800;color:{{ $c['title'] }};margin:0 0 4px;">{{ $title }}</h1>
        <p style="font-size:.9375rem;color:#64748b;margin:0;">{{ $subtitle }}</p>
    </div>
</div>
