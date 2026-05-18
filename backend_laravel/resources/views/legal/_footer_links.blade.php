{{-- Navegación entre páginas legales --}}
<div style="margin-top:3rem;padding-top:1.5rem;border-top:1px solid #e8edf5;">
    <p style="font-size:.75rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin:0 0 12px;">Otras páginas legales</p>
    <div style="display:flex;flex-wrap:wrap;gap:8px;">
        @php
            $links = [
                ['route' => 'legal.privacy',   'label' => 'Política de privacidad'],
                ['route' => 'legal.data-usage','label' => 'Uso de datos'],
                ['route' => 'legal.cookies',   'label' => 'Cookies'],
                ['route' => 'legal.terms',     'label' => 'Términos de uso'],
                ['route' => 'legal.consent',   'label' => 'Consentimiento informado'],
            ];
        @endphp
        @foreach ($links as $link)
            @if (request()->routeIs($link['route']))
                <span style="font-size:.8125rem;padding:5px 14px;border-radius:9999px;background:#eef2ff;color:#4338ca;border:1.5px solid #c7d2fe;font-weight:600;">
                    {{ $link['label'] }}
                </span>
            @else
                <a href="{{ route($link['route']) }}"
                   style="font-size:.8125rem;padding:5px 14px;border-radius:9999px;background:#f8fafc;color:#64748b;border:1.5px solid #e2e8f0;text-decoration:none;transition:all .15s;"
                   onmouseover="this.style.background='#eef2ff';this.style.color='#4338ca';this.style.borderColor='#c7d2fe';"
                   onmouseout="this.style.background='#f8fafc';this.style.color='#64748b';this.style.borderColor='#e2e8f0';">
                    {{ $link['label'] }}
                </a>
            @endif
        @endforeach
    </div>
</div>
