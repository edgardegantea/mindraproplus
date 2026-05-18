{{-- ══════════════════════════════════════════════════════════════════════════
     Accessibility & Theme System — include in every layout
     Usage: @include('partials.accessibility')
     ══════════════════════════════════════════════════════════════════════════ --}}

<style>
/* ── CSS Custom Properties (Light — default) ────────────────────────────── */
:root, [data-theme="light"] {
    --bg-body: #f0f2f5;
    --bg-page: #fff;
    --bg-surface: #f8fafc;
    --bg-surface-hover: #fff;
    --bg-card: #fff;
    --bg-input: #f8fafc;
    --bg-nav: rgba(255,255,255,.85);
    --bg-footer: #fff;
    --bg-code: #f1f5f9;

    --border-default: #e8edf5;
    --border-light: #f1f5f9;
    --border-input: #e2e8f0;
    --border-accent: #c7d2fe;

    --text-primary: #0f172a;
    --text-secondary: #334155;
    --text-muted: #64748b;
    --text-faint: #94a3b8;
    --text-inverse: #fff;

    --accent: #4f46e5;
    --accent-light: #eef2ff;
    --accent-border: #c7d2fe;

    --shadow-sm: 0 1px 3px rgba(0,0,0,.04);
    --shadow-md: 0 4px 20px rgba(0,0,0,.06);
    --shadow-lg: 0 8px 30px rgba(0,0,0,.08);

    --font-scale: 1;
}

/* ── Dark Theme ──────────────────────────────────────────────────────────── */
[data-theme="dark"] {
    --bg-body: #0b1120;
    --bg-page: #151d2e;
    --bg-surface: #1a2538;
    --bg-surface-hover: #243044;
    --bg-card: #1e293b;
    --bg-input: #243044;
    --bg-nav: rgba(11,17,32,.95);
    --bg-footer: #0b1120;
    --bg-code: #243044;

    --border-default: #2d3b50;
    --border-light: #1e293b;
    --border-input: #3d4f66;
    --border-accent: #4f46e5;

    --text-primary: #f8fafc;
    --text-secondary: #e2e8f0;
    --text-muted: #a8b8cc;
    --text-faint: #7a8da4;
    --text-inverse: #0b1120;

    --accent: #a5b4fc;
    --accent-light: rgba(99,102,241,.18);
    --accent-border: #6366f1;

    --shadow-sm: 0 1px 3px rgba(0,0,0,.3);
    --shadow-md: 0 4px 20px rgba(0,0,0,.4);
    --shadow-lg: 0 8px 30px rgba(0,0,0,.5);
}

/* ── High Contrast ───────────────────────────────────────────────────────── */
[data-contrast="high"] {
    --text-primary: #000;
    --text-secondary: #1a1a1a;
    --text-muted: #333;
    --text-faint: #555;
    --border-default: #666;
    --border-input: #333;
}
[data-theme="dark"][data-contrast="high"] {
    --text-primary: #fff;
    --text-secondary: #f0f0f0;
    --text-muted: #ddd;
    --text-faint: #bbb;
    --border-default: #999;
    --border-input: #ccc;
}

/* ── Font Scale ──────────────────────────────────────────────────────────── */
[data-font="small"]  { --font-scale: 0.875; }
[data-font="normal"] { --font-scale: 1; }
[data-font="large"]  { --font-scale: 1.125; }
[data-font="xl"]     { --font-scale: 1.25; }

/* ── Reduced Motion ──────────────────────────────────────────────────────── */
[data-motion="reduced"] *,
[data-motion="reduced"] *::before,
[data-motion="reduced"] *::after {
    animation-duration: 0.01ms !important;
    transition-duration: 0.01ms !important;
}

/* ── Global Overrides (theme-aware) ──────────────────────────────────────── */
html { font-size: calc(16px * var(--font-scale, 1)); }

[data-theme="dark"] body {
    background: var(--bg-body) !important;
    color: var(--text-primary) !important;
}

[data-theme="dark"] .glass-nav,
[data-theme="dark"] nav.bg-white,
[data-theme="dark"] .chat-topbar {
    background: var(--bg-nav) !important;
    border-color: var(--border-default) !important;
}

[data-theme="dark"] .bg-white,
[data-theme="dark"] .bg-slate-50 {
    background: var(--bg-body) !important;
}

/* Cards, surfaces */
[data-theme="dark"] .form-card,
[data-theme="dark"] .summary-card,
[data-theme="dark"] .welcome-card,
[data-theme="dark"] .anxiety-card,
[data-theme="dark"] .bubble-mindra,
[data-theme="dark"] .input-card {
    background: var(--bg-card) !important;
    border-color: var(--border-default) !important;
    color: var(--text-primary) !important;
}

[data-theme="dark"] .chat-sidebar {
    background: var(--bg-page) !important;
    border-color: var(--border-default) !important;
}

[data-theme="dark"] .chat-main {
    background: var(--bg-body) !important;
}

[data-theme="dark"] .chat-feed {
    background: var(--bg-body) !important;
}

/* Inputs */
[data-theme="dark"] .form-input,
[data-theme="dark"] input[type="text"],
[data-theme="dark"] input[type="email"],
[data-theme="dark"] input[type="password"],
[data-theme="dark"] input[type="tel"],
[data-theme="dark"] textarea,
[data-theme="dark"] .input-textarea {
    background: var(--bg-input) !important;
    border-color: var(--border-input) !important;
    color: var(--text-primary) !important;
}

/* Text overrides — force override inline styles */
[data-theme="dark"] h1, [data-theme="dark"] h2, [data-theme="dark"] h3, [data-theme="dark"] h4,
[data-theme="dark"] h1[style], [data-theme="dark"] h2[style], [data-theme="dark"] h3[style], [data-theme="dark"] h4[style] {
    color: var(--text-primary) !important;
}

[data-theme="dark"] p, [data-theme="dark"] span, [data-theme="dark"] li, [data-theme="dark"] label, [data-theme="dark"] a,
[data-theme="dark"] p[style], [data-theme="dark"] span[style], [data-theme="dark"] li[style], [data-theme="dark"] label[style] {
    color: var(--text-secondary) !important;
}

/* Preserve specific accent/status colors */
[data-theme="dark"] [style*="color:#4f46e5"], [data-theme="dark"] [style*="color: #4f46e5"],
[data-theme="dark"] [style*="color:#4338ca"], [data-theme="dark"] [style*="color: #4338ca"] {
    color: var(--accent) !important;
}
[data-theme="dark"] [style*="color:#16a34a"], [data-theme="dark"] [style*="color: #16a34a"],
[data-theme="dark"] [style*="color:#15803d"], [data-theme="dark"] [style*="color: #15803d"],
[data-theme="dark"] [style*="color:#22c55e"] {
    color: #4ade80 !important;
}
[data-theme="dark"] [style*="color:#dc2626"], [data-theme="dark"] [style*="color:#e11d48"],
[data-theme="dark"] [style*="color:#be123c"], [data-theme="dark"] [style*="color:#991b1b"] {
    color: #fb7185 !important;
}
[data-theme="dark"] [style*="color:#d97706"] {
    color: #fbbf24 !important;
}
[data-theme="dark"] [style*="color:#7c3aed"] {
    color: #a78bfa !important;
}

/* White text on gradient buttons must stay white */
[data-theme="dark"] .btn-primary,
[data-theme="dark"] .btn-primary span,
[data-theme="dark"] .btn-primary a,
[data-theme="dark"] .bubble-user,
[data-theme="dark"] .btn-dark,
[data-theme="dark"] [style*="background:linear-gradient(135deg,#38bdf8"] span,
[data-theme="dark"] [style*="background:linear-gradient(135deg,#38bdf8"] {
    color: #fff !important;
}

/* Dark plan card (Full) text stays light */
[data-theme="dark"] div[style*="background:#0f172a"] h3,
[data-theme="dark"] div[style*="background:#0f172a"] p,
[data-theme="dark"] div[style*="background:#0f172a"] li,
[data-theme="dark"] div[style*="background:#0f172a"] span {
    color: inherit !important;
}

/* Muted/faint text that uses inline styles */
[data-theme="dark"] .form-section-title,
[data-theme="dark"] .anxiety-label,
[data-theme="dark"] .a11y-label {
    color: var(--text-faint) !important;
}

/* Links in nav should inherit hover behavior */
[data-theme="dark"] .nav-links a {
    color: var(--text-muted) !important;
}
[data-theme="dark"] .nav-links a:hover {
    color: var(--accent) !important;
}

/* CTA section text stays white */
[data-theme="dark"] div[style*="border-radius:32px"] h2,
[data-theme="dark"] div[style*="border-radius:32px"] p {
    color: inherit !important;
}

[data-theme="dark"] .btn-secondary {
    background: var(--bg-card) !important;
    border-color: var(--border-default) !important;
    color: var(--text-primary) !important;
}

/* Sections with inline bg */
[data-theme="dark"] section {
    background: var(--bg-body) !important;
    border-color: var(--border-default) !important;
}

[data-theme="dark"] footer {
    background: var(--bg-footer) !important;
    border-color: var(--border-default) !important;
}

/* ── Inline-styled cards & divs (home page, plans, etc.) ─────────────── */
[data-theme="dark"] div[style*="background:#fff"],
[data-theme="dark"] div[style*="background: #fff"],
[data-theme="dark"] div[style*="background:#f8fafc"],
[data-theme="dark"] div[style*="background: #f8fafc"] {
    background: var(--bg-card) !important;
    border-color: var(--border-default) !important;
}

[data-theme="dark"] div[style*="background:linear-gradient(160deg,#eef2ff"],
[data-theme="dark"] div[style*="background:linear-gradient(135deg,#eef2ff"],
[data-theme="dark"] div[style*="background:linear-gradient(135deg,#f5f3ff"] {
    background: var(--bg-surface) !important;
}

/* Inline text color overrides */
[data-theme="dark"] [style*="color:#0f172a"],
[data-theme="dark"] [style*="color: #0f172a"],
[data-theme="dark"] [style*="color:#1e293b"],
[data-theme="dark"] [style*="color: #1e293b"],
[data-theme="dark"] [style*="color:#1e1b4b"] {
    color: var(--text-primary) !important;
}

[data-theme="dark"] [style*="color:#334155"],
[data-theme="dark"] [style*="color: #334155"],
[data-theme="dark"] [style*="color:#475569"],
[data-theme="dark"] [style*="color: #475569"] {
    color: var(--text-secondary) !important;
}

[data-theme="dark"] [style*="color:#64748b"],
[data-theme="dark"] [style*="color: #64748b"] {
    color: var(--text-muted) !important;
}

[data-theme="dark"] [style*="color:#94a3b8"],
[data-theme="dark"] [style*="color: #94a3b8"],
[data-theme="dark"] [style*="color:#cbd5e1"],
[data-theme="dark"] [style*="color: #cbd5e1"] {
    color: var(--text-faint) !important;
}

/* Inline bg surfaces */
[data-theme="dark"] [style*="background:#f8fafc"],
[data-theme="dark"] [style*="background: #f8fafc"],
[data-theme="dark"] [style*="background:#f1f5f9"],
[data-theme="dark"] [style*="background: #f1f5f9"],
[data-theme="dark"] [style*="background:#f0f2f5"],
[data-theme="dark"] [style*="background: #f0f2f5"] {
    background: var(--bg-surface) !important;
}

/* Inline border overrides */
[data-theme="dark"] [style*="border:1px solid #e8edf5"],
[data-theme="dark"] [style*="border: 1px solid #e8edf5"],
[data-theme="dark"] [style*="border:1px solid #f1f5f9"],
[data-theme="dark"] [style*="border: 1px solid #f1f5f9"],
[data-theme="dark"] [style*="border:1px solid #e2e8f0"],
[data-theme="dark"] [style*="border: 1px solid #e2e8f0"],
[data-theme="dark"] [style*="border-bottom:1px solid #f1f5f9"],
[data-theme="dark"] [style*="border-top:1px solid #e8edf5"],
[data-theme="dark"] [style*="border-top:1px solid #f1f5f9"],
[data-theme="dark"] [style*="border-bottom:1px solid #e8edf5"] {
    border-color: var(--border-default) !important;
}

/* Hero section gradient */
[data-theme="dark"] section[style*="background:linear-gradient(160deg,#f8faff"] {
    background: linear-gradient(160deg, #0b1120 0%, #111827 40%, #0f172a 100%) !important;
}

/* Chat mockup in hero */
[data-theme="dark"] div[style*="background:#fff;border-radius:24px;border:1px solid #e8edf5"] {
    background: var(--bg-card) !important;
    border-color: var(--border-default) !important;
}

/* Feature cards hover-styled divs */
[data-theme="dark"] div[style*="border-radius:24px;background:#f8fafc;border:1px solid #f1f5f9"],
[data-theme="dark"] div[style*="border-radius:18px;background:#fff;border:1px solid #e8edf5"] {
    background: var(--bg-card) !important;
    border-color: var(--border-default) !important;
}

/* Plan cards */
[data-theme="dark"] div[style*="border-radius:24px;background:#f8fafc;border:1px solid #e8edf5"] {
    background: var(--bg-card) !important;
    border-color: var(--border-default) !important;
}

/* Pro plan card keeps accent border */
[data-theme="dark"] div[style*="border:2px solid #4f46e5"] {
    background: var(--bg-surface) !important;
}

/* Privacy banner */
[data-theme="dark"] div[style*="background:linear-gradient(135deg,#eef2ff,#f5f3ff);border:1px solid #c7d2fe"] {
    background: var(--bg-surface) !important;
    border-color: var(--border-accent) !important;
}

/* CTA dark section — keep dark but adjust */
[data-theme="dark"] div[style*="background:#0f172a;border-radius:32px"] {
    background: #0b1120 !important;
    border: 1px solid var(--border-default) !important;
}

/* Icon boxes */
[data-theme="dark"] div[style*="background:linear-gradient(135deg,#eef2ff,#e0e7ff)"],
[data-theme="dark"] div[style*="background:linear-gradient(135deg,#f5f3ff,#ede9fe)"],
[data-theme="dark"] div[style*="background:linear-gradient(135deg,#f0fdf4,#dcfce7)"],
[data-theme="dark"] div[style*="background:#eef2ff"],
[data-theme="dark"] div[style*="background:#f0fdf4"],
[data-theme="dark"] div[style*="background:#f5f3ff"],
[data-theme="dark"] div[style*="background:#fffbeb"] {
    background: var(--bg-surface) !important;
    border-color: var(--border-default) !important;
}

/* Badges and small tags */
[data-theme="dark"] span[style*="background:#f0fdf4"],
[data-theme="dark"] span[style*="background:#eef2ff"],
[data-theme="dark"] span[style*="background:#f5f3ff"] {
    background: var(--bg-surface) !important;
    border-color: var(--border-default) !important;
}

/* Footer bottom bar */
[data-theme="dark"] div[style*="border-top:1px solid #f1f5f9;background:#f8fafc"] {
    background: var(--bg-surface) !important;
    border-color: var(--border-default) !important;
}

/* Auth forms card */
[data-theme="dark"] .bg-white.rounded-2xl,
[data-theme="dark"] div.bg-white {
    background: var(--bg-card) !important;
    border-color: var(--border-default) !important;
}

/* SuperAdmin / Admin layout cards */
[data-theme="dark"] .rounded-2xl.shadow-sm,
[data-theme="dark"] .rounded-xl,
[data-theme="dark"] .rounded-lg {
    background: var(--bg-card);
    border-color: var(--border-default);
}

/* Table headers and rows */
[data-theme="dark"] th {
    background: var(--bg-surface) !important;
    color: var(--text-muted) !important;
    border-color: var(--border-default) !important;
}
[data-theme="dark"] td {
    border-color: var(--border-default) !important;
    color: var(--text-secondary) !important;
}
[data-theme="dark"] tr:hover td {
    background: var(--bg-surface-hover) !important;
}

/* Nav pills (chat sidebar) */
[data-theme="dark"] .nav-pill {
    color: var(--text-muted) !important;
}
[data-theme="dark"] .nav-pill:hover {
    background: var(--bg-surface-hover) !important;
    color: var(--text-primary) !important;
}
[data-theme="dark"] .nav-pill.active {
    background: var(--accent-light) !important;
    color: var(--accent) !important;
}

/* Quick prompts */
[data-theme="dark"] .quick-prompt {
    background: var(--bg-card) !important;
    border-color: var(--border-default) !important;
    color: var(--text-muted) !important;
}
[data-theme="dark"] .quick-prompt:hover {
    background: var(--accent-light) !important;
    border-color: var(--accent-border) !important;
    color: var(--accent) !important;
}

/* Tailwind overrides */
[data-theme="dark"] .text-slate-800, [data-theme="dark"] .text-slate-700 { color: var(--text-primary) !important; }
[data-theme="dark"] .text-slate-600, [data-theme="dark"] .text-slate-500 { color: var(--text-muted) !important; }
[data-theme="dark"] .text-slate-400, [data-theme="dark"] .text-slate-300 { color: var(--text-faint) !important; }
[data-theme="dark"] .border-slate-200, [data-theme="dark"] .border-slate-300 { border-color: var(--border-default) !important; }
[data-theme="dark"] .bg-slate-50 { background: var(--bg-body) !important; }

/* Auth card */
[data-theme="dark"] .rounded-2xl.shadow-sm {
    background: var(--bg-card) !important;
    border-color: var(--border-default) !important;
}

/* ── Chat-specific dark overrides ───────────────────────────────────────── */

/* Camera consent modal */
[data-theme="dark"] .cam-modal {
    background: var(--bg-card) !important;
    box-shadow: 0 20px 60px rgba(0,0,0,.5) !important;
}
[data-theme="dark"] .cam-modal h3 {
    color: var(--text-primary) !important;
}
[data-theme="dark"] .cam-modal p {
    color: var(--text-muted) !important;
}
[data-theme="dark"] .cam-modal-icon {
    background: var(--accent-light) !important;
    border-color: var(--accent-border) !important;
}
[data-theme="dark"] .cam-btn-skip {
    background: var(--bg-surface) !important;
    border-color: var(--border-default) !important;
    color: var(--text-muted) !important;
}
[data-theme="dark"] .cam-btn-skip:hover {
    background: var(--bg-surface-hover) !important;
    color: var(--text-secondary) !important;
}
[data-theme="dark"] .cam-btn-accept span {
    color: #fff !important;
}

/* Camera preview */
[data-theme="dark"] .cam-preview {
    border-color: var(--accent-border) !important;
}

/* New chat button */
[data-theme="dark"] .new-chat-btn {
    background: var(--accent-light) !important;
    border-color: var(--accent-border) !important;
    color: var(--accent) !important;
}
[data-theme="dark"] .new-chat-btn:hover {
    background: rgba(99,102,241,.25) !important;
}

/* Sidebar footer */
[data-theme="dark"] .sidebar-footer {
    border-color: var(--border-default) !important;
}
[data-theme="dark"] .sidebar-header {
    border-color: var(--border-default) !important;
}

/* Sidebar footer inline-styled elements */
[data-theme="dark"] .sidebar-footer p[style*="color:#1e293b"] {
    color: var(--text-primary) !important;
}
[data-theme="dark"] .sidebar-footer p[style*="color:#94a3b8"] {
    color: var(--text-faint) !important;
}
[data-theme="dark"] .sidebar-footer button[style] {
    background: var(--bg-surface) !important;
    border-color: var(--border-default) !important;
    color: var(--text-faint) !important;
}

/* Topbar */
[data-theme="dark"] .chat-topbar {
    background: var(--bg-nav) !important;
    border-color: var(--border-default) !important;
}
[data-theme="dark"] .chat-topbar p[style*="color:#1e293b"] {
    color: var(--text-primary) !important;
}

/* Camera toggle button in topbar */
[data-theme="dark"] .cam-toggle-btn[style*="background:#f8fafc"] {
    background: var(--bg-surface) !important;
    border-color: var(--border-default) !important;
    color: var(--text-faint) !important;
}
[data-theme="dark"] .cam-toggle-btn[style*="background:#f0fdf4"] {
    background: rgba(74,222,128,.12) !important;
    border-color: rgba(74,222,128,.4) !important;
    color: #4ade80 !important;
}

/* Topbar anxiety toggle */
[data-theme="dark"] .chat-topbar button[style*="background:#f8fafc"] {
    background: var(--bg-surface) !important;
    border-color: var(--border-default) !important;
    color: var(--text-faint) !important;
}
[data-theme="dark"] .chat-topbar button[style*="background:#eef2ff"] {
    background: var(--accent-light) !important;
    border-color: var(--accent-border) !important;
    color: var(--accent) !important;
}

/* Welcome badges */
[data-theme="dark"] .welcome-badge[style*="background:#f0fdf4"] {
    background: rgba(74,222,128,.12) !important;
    border-color: rgba(74,222,128,.3) !important;
    color: #4ade80 !important;
}
[data-theme="dark"] .welcome-badge[style*="background:#eef2ff"] {
    background: var(--accent-light) !important;
    border-color: var(--accent-border) !important;
    color: var(--accent) !important;
}
[data-theme="dark"] .welcome-badge[style*="background:#f5f3ff"] {
    background: rgba(167,139,250,.12) !important;
    border-color: rgba(167,139,250,.3) !important;
    color: #a78bfa !important;
}

/* Error banner */
[data-theme="dark"] .error-banner {
    background: rgba(239,68,68,.1) !important;
    border-color: rgba(239,68,68,.3) !important;
    color: #fb7185 !important;
}

/* Input area */
[data-theme="dark"] .input-area {
    background: linear-gradient(180deg, transparent, var(--bg-body) 20%) !important;
}
[data-theme="dark"] .input-card {
    background: var(--bg-card) !important;
    border-color: var(--border-default) !important;
}
[data-theme="dark"] .input-card:focus-within {
    border-color: var(--accent-border) !important;
}
[data-theme="dark"] .input-textarea {
    color: var(--text-primary) !important;
}
[data-theme="dark"] .input-textarea::placeholder {
    color: var(--text-faint) !important;
}
[data-theme="dark"] .input-hint {
    color: var(--text-faint) !important;
}

/* Mic button */
[data-theme="dark"] .btn-mic {
    background: var(--bg-surface) !important;
    color: var(--text-faint) !important;
}
[data-theme="dark"] .btn-mic:hover {
    background: var(--accent-light) !important;
    color: var(--accent) !important;
}

/* Audio ready indicator */
[data-theme="dark"] .audio-ready {
    color: var(--text-muted) !important;
}
[data-theme="dark"] .audio-remove {
    color: var(--text-faint) !important;
}
[data-theme="dark"] .audio-remove:hover {
    color: #fb7185 !important;
}

/* Anxiety card — inline-styled badges */
[data-theme="dark"] .anxiety-badge[style*="background:#fff1f2"] {
    background: rgba(239,68,68,.15) !important;
    color: #fb7185 !important;
}
[data-theme="dark"] .anxiety-badge[style*="background:#fffbeb"] {
    background: rgba(251,191,36,.12) !important;
    color: #fbbf24 !important;
}
[data-theme="dark"] .anxiety-badge[style*="background:#f0fdf4"] {
    background: rgba(74,222,128,.12) !important;
    color: #4ade80 !important;
}

/* Anxiety bar background */
[data-theme="dark"] .anxiety-bar-bg {
    background: var(--bg-surface) !important;
}

/* Facial emotion row inside anxiety card */
[data-theme="dark"] .anxiety-card div[style*="border-top:1px solid #f1f5f9"] {
    border-color: var(--border-default) !important;
}
[data-theme="dark"] .anxiety-card span[style*="color:#475569"] {
    color: var(--text-secondary) !important;
}
[data-theme="dark"] .anxiety-card span[style*="color:#94a3b8"] {
    color: var(--text-faint) !important;
}

/* Congruence badges in anxiety card */
[data-theme="dark"] .anxiety-card span[style*="background:#f0fdf4;color:#16a34a"] {
    background: rgba(74,222,128,.12) !important;
    color: #4ade80 !important;
}
[data-theme="dark"] .anxiety-card span[style*="background:#fef2f2;color:#dc2626"] {
    background: rgba(239,68,68,.12) !important;
    color: #fb7185 !important;
}

/* Sidebar navigation label */
[data-theme="dark"] .sidebar-body p[style*="color:#94a3b8"] {
    color: var(--text-faint) !important;
}

/* Welcome card text */
[data-theme="dark"] .welcome-card h2[style] {
    color: var(--text-primary) !important;
}
[data-theme="dark"] .welcome-card p[style*="color:#64748b"] {
    color: var(--text-muted) !important;
}

/* Topbar avatar border */
[data-theme="dark"] .topbar-avatar {
    border-color: var(--accent-border) !important;
}

/* ── Dashboard dark overrides ───────────────────────────────────────────── */

/* Calendar cells with anxiety-level backgrounds */
[data-theme="dark"] .cal-cell[style*="background:#fff1f2"] {
    background: rgba(251,113,133,.15) !important;
}
[data-theme="dark"] .cal-cell[style*="background:#fffbeb"] {
    background: rgba(251,191,36,.12) !important;
}
[data-theme="dark"] .cal-cell[style*="background:#f0fdf4"] {
    background: rgba(74,222,128,.12) !important;
}

/* Calendar navigation buttons */
[data-theme="dark"] .cal-cell .today-ring {
    outline-color: var(--accent) !important;
}

/* Date filter banner */
[data-theme="dark"] div[style*="background:#eef2ff;border:1px solid #c7d2fe"] {
    background: var(--accent-light) !important;
    border-color: var(--accent-border) !important;
}

/* Level filter buttons */
[data-theme="dark"] button[style*="background:#f8fafc;border-color:#e2e8f0"] {
    background: var(--bg-surface) !important;
    border-color: var(--border-default) !important;
    color: var(--text-muted) !important;
}

/* Session card hover buttons */
[data-theme="dark"] button[style*="background:none"]:hover {
    background: var(--bg-surface-hover) !important;
}

/* Recommendation boxes — keep semantic colors but darken backgrounds */
[data-theme="dark"] div[style*="background:#fff1f2;"] {
    background: rgba(251,113,133,.1) !important;
    border-color: rgba(251,113,133,.25) !important;
}
[data-theme="dark"] div[style*="background:#fffbeb;"] {
    background: rgba(251,191,36,.08) !important;
    border-color: rgba(251,191,36,.2) !important;
}
[data-theme="dark"] div[style*="background:#f0fdf4;"] {
    background: rgba(74,222,128,.08) !important;
    border-color: rgba(74,222,128,.2) !important;
}

/* Progress bar backgrounds in dashboard */
[data-theme="dark"] div[style*="background:#f1f5f9"] {
    background: var(--bg-surface) !important;
}
[data-theme="dark"] div[style*="background:#e2e8f0"] {
    background: var(--bg-surface) !important;
}

/* Dashboard bubble-mindra (redefined in push styles) */
[data-theme="dark"] .bubble-mindra {
    background: var(--bg-card) !important;
    border-color: var(--border-default) !important;
    color: var(--text-secondary) !important;
}

/* Calendar legend border */
[data-theme="dark"] div[style*="border-top:1px solid #f1f5f9"] {
    border-color: var(--border-default) !important;
}

/* Session detail border */
[data-theme="dark"] div[style*="border-top:1px solid #f1f5f9;"] {
    border-color: var(--border-default) !important;
}

/* Anxiety-level inline badges in dashboard sessions */
[data-theme="dark"] span[style*="background:#fff1f2"] {
    background: rgba(251,113,133,.12) !important;
}
[data-theme="dark"] span[style*="background:#fffbeb"] {
    background: rgba(251,191,36,.1) !important;
}

/* ── Accessibility Panel ─────────────────────────────────────────────────── */
.a11y-fab {
    position: fixed; bottom: 20px; left: 20px; z-index: 9999;
    width: 48px; height: 48px; border-radius: 14px;
    background: var(--accent, #4f46e5); color: #fff;
    border: none; cursor: pointer; display: flex;
    align-items: center; justify-content: center;
    box-shadow: 0 4px 16px rgba(79,70,229,.35);
    transition: all .2s;
}
.a11y-fab:hover { transform: scale(1.08); box-shadow: 0 6px 24px rgba(79,70,229,.45); }

.a11y-panel {
    position: fixed; bottom: 78px; left: 20px; z-index: 9999;
    width: 320px; background: var(--bg-card, #fff);
    border: 1px solid var(--border-default, #e8edf5);
    border-radius: 20px; padding: 24px;
    box-shadow: 0 12px 48px rgba(0,0,0,.12);
    display: none;
}
.a11y-panel.open { display: block; }
.a11y-panel h4 {
    font-size: .875rem; font-weight: 800; margin-bottom: 20px;
    color: var(--text-primary) !important;
    display: flex; align-items: center; gap: 8px;
}
.a11y-section {
    margin-bottom: 18px; padding-bottom: 16px;
    border-bottom: 1px solid var(--border-light, #f1f5f9);
}
.a11y-section:last-child { margin-bottom: 0; padding-bottom: 0; border-bottom: none; }
.a11y-label {
    font-size: .6875rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .08em; margin-bottom: 10px; display: block;
    color: var(--text-faint) !important;
}

/* Theme switcher */
.theme-options {
    display: flex; gap: 8px;
}
.theme-opt {
    flex: 1; padding: 10px 12px; border-radius: 12px;
    border: 1.5px solid var(--border-default); cursor: pointer;
    font-size: .75rem; font-weight: 700; text-align: center;
    transition: all .15s; font-family: inherit;
    background: var(--bg-surface); color: var(--text-muted);
}
.theme-opt:hover { border-color: var(--accent-border); }
.theme-opt.active {
    border-color: var(--accent); background: var(--accent-light);
    color: var(--accent);
}

/* Font size buttons */
.font-options { display: flex; gap: 6px; }
.font-opt {
    flex: 1; padding: 8px; border-radius: 10px;
    border: 1.5px solid var(--border-default); cursor: pointer;
    font-weight: 700; text-align: center; transition: all .15s;
    font-family: inherit;
    background: var(--bg-surface); color: var(--text-muted);
}
.font-opt:hover { border-color: var(--accent-border); }
.font-opt.active { border-color: var(--accent); background: var(--accent-light); color: var(--accent); }

/* Toggle switch */
.a11y-toggle {
    display: flex; align-items: center; justify-content: space-between;
    padding: 8px 0; cursor: pointer;
}
.a11y-toggle-label { font-size: .8125rem; font-weight: 600; color: var(--text-secondary); }
.toggle-track {
    width: 44px; height: 24px; border-radius: 12px;
    background: var(--border-default); position: relative;
    transition: background .2s; flex-shrink: 0;
}
.toggle-track.on { background: var(--accent); }
.toggle-knob {
    position: absolute; top: 2px; left: 2px;
    width: 20px; height: 20px; border-radius: 10px;
    background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,.15);
    transition: transform .2s;
}
.toggle-track.on .toggle-knob { transform: translateX(20px); }

@media (max-width: 768px) {
    .a11y-panel { width: calc(100vw - 40px); left: 20px; }
}
</style>

{{-- Floating Accessibility Button --}}
<button class="a11y-fab" onclick="toggleA11yPanel()" aria-label="Opciones de accesibilidad" title="Accesibilidad">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:24px;height:24px;">
        <path d="M12 2a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3ZM6.5 7.5a.75.75 0 0 0 0 1.5h2.579l-.718 5.03A.75.75 0 0 0 9 14.78l.393-.262a3.75 3.75 0 0 1 5.214 0l.393.262a.75.75 0 0 0 .639.033.75.75 0 0 0 .361-.783L15.921 9H17.5a.75.75 0 0 0 0-1.5h-11Z"/>
        <path d="M9.256 15.996a.75.75 0 0 0-1.012.348l-1.5 3a.75.75 0 1 0 1.342.67l1.5-3a.75.75 0 0 0-.33-1.018ZM14.744 15.996a.75.75 0 0 1 1.012.348l1.5 3a.75.75 0 0 1-1.342.67l-1.5-3a.75.75 0 0 1 .33-1.018Z"/>
    </svg>
</button>

{{-- Accessibility Panel --}}
<div class="a11y-panel" id="a11yPanel">
    <h4>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="var(--accent)" style="width:20px;height:20px;">
            <path fill-rule="evenodd" d="M11.078 2.25c-.917 0-1.699.663-1.85 1.567L9.05 4.889c-.02.12-.115.26-.297.348a7.493 7.493 0 0 0-.986.57c-.166.115-.334.126-.45.083L6.3 5.508a1.875 1.875 0 0 0-2.282.819l-.922 1.597a1.875 1.875 0 0 0 .432 2.385l.84.692c.095.078.17.229.154.43a7.598 7.598 0 0 0 0 1.139c.015.2-.059.352-.153.43l-.841.692a1.875 1.875 0 0 0-.432 2.385l.922 1.597a1.875 1.875 0 0 0 2.282.818l1.019-.382c.115-.043.283-.031.45.082.312.214.641.405.985.57.182.088.277.228.297.35l.178 1.071c.151.904.933 1.567 1.85 1.567h1.844c.916 0 1.699-.663 1.85-1.567l.178-1.072c.02-.12.114-.26.297-.349.344-.165.673-.356.985-.57.167-.114.335-.125.45-.082l1.02.382a1.875 1.875 0 0 0 2.28-.819l.923-1.597a1.875 1.875 0 0 0-.432-2.385l-.84-.692c-.095-.078-.17-.229-.154-.43a7.614 7.614 0 0 0 0-1.139c-.016-.2.059-.352.153-.43l.84-.692c.708-.582.891-1.59.433-2.385l-.922-1.597a1.875 1.875 0 0 0-2.282-.818l-1.02.382c-.114.043-.282.031-.449-.083a7.49 7.49 0 0 0-.985-.57c-.183-.087-.277-.227-.297-.348l-.179-1.072a1.875 1.875 0 0 0-1.85-1.567h-1.843ZM12 15.75a3.75 3.75 0 1 0 0-7.5 3.75 3.75 0 0 0 0 7.5Z" clip-rule="evenodd"/>
        </svg>
        Accesibilidad
    </h4>

    {{-- Theme --}}
    <div class="a11y-section">
        <span class="a11y-label">Tema</span>
        <div class="theme-options">
            <button class="theme-opt" data-theme-val="light" onclick="setTheme('light')">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:16px;height:16px;display:block;margin:0 auto 4px;">
                    <path d="M10 2a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-1.5 0v-1.5A.75.75 0 0 1 10 2ZM10 15a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-1.5 0v-1.5A.75.75 0 0 1 10 15ZM10 7a3 3 0 1 0 0 6 3 3 0 0 0 0-6ZM15.657 5.404a.75.75 0 1 0-1.06-1.06l-1.061 1.06a.75.75 0 0 0 1.06 1.06l1.06-1.06ZM6.464 14.596a.75.75 0 1 0-1.06-1.06l-1.06 1.06a.75.75 0 0 0 1.06 1.06l1.06-1.06ZM18 10a.75.75 0 0 1-.75.75h-1.5a.75.75 0 0 1 0-1.5h1.5A.75.75 0 0 1 18 10ZM5 10a.75.75 0 0 1-.75.75h-1.5a.75.75 0 0 1 0-1.5h1.5A.75.75 0 0 1 5 10ZM14.596 15.657a.75.75 0 0 0 1.06-1.06l-1.06-1.061a.75.75 0 1 0-1.06 1.06l1.06 1.06ZM5.404 6.464a.75.75 0 0 0 1.06-1.06l-1.06-1.06a.75.75 0 1 0-1.06 1.06l1.06 1.06Z"/>
                </svg>
                Claro
            </button>
            <button class="theme-opt" data-theme-val="dark" onclick="setTheme('dark')">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:16px;height:16px;display:block;margin:0 auto 4px;">
                    <path fill-rule="evenodd" d="M7.455 2.004a.75.75 0 0 1 .26.77 7 7 0 0 0 9.958 7.967.75.75 0 0 1 1.067.853A8.5 8.5 0 1 1 6.647 1.921a.75.75 0 0 1 .808.083Z" clip-rule="evenodd"/>
                </svg>
                Oscuro
            </button>
            <button class="theme-opt" data-theme-val="auto" onclick="setTheme('auto')">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:16px;height:16px;display:block;margin:0 auto 4px;">
                    <path fill-rule="evenodd" d="M2 4.25A2.25 2.25 0 0 1 4.25 2h11.5A2.25 2.25 0 0 1 18 4.25v8.5A2.25 2.25 0 0 1 15.75 15h-3.105a3.501 3.501 0 0 0 1.1 1.677A.75.75 0 0 1 13.26 18H6.74a.75.75 0 0 1-.484-1.323A3.501 3.501 0 0 0 7.355 15H4.25A2.25 2.25 0 0 1 2 12.75v-8.5Zm1.5 0a.75.75 0 0 1 .75-.75h11.5a.75.75 0 0 1 .75.75v7.5a.75.75 0 0 1-.75.75H4.25a.75.75 0 0 1-.75-.75v-7.5Z" clip-rule="evenodd"/>
                </svg>
                Auto
            </button>
        </div>
    </div>

    {{-- Font Size --}}
    <div class="a11y-section">
        <span class="a11y-label">Tamano de texto</span>
        <div class="font-options">
            <button class="font-opt" data-font-val="small" onclick="setFontSize('small')" style="font-size:.6875rem;">A</button>
            <button class="font-opt" data-font-val="normal" onclick="setFontSize('normal')" style="font-size:.8125rem;">A</button>
            <button class="font-opt" data-font-val="large" onclick="setFontSize('large')" style="font-size:1rem;">A</button>
            <button class="font-opt" data-font-val="xl" onclick="setFontSize('xl')" style="font-size:1.25rem;">A</button>
        </div>
    </div>

    {{-- High Contrast --}}
    <div class="a11y-section">
        <div class="a11y-toggle" onclick="toggleContrast()">
            <span class="a11y-toggle-label">Alto contraste</span>
            <div class="toggle-track" id="contrastToggle">
                <div class="toggle-knob"></div>
            </div>
        </div>
    </div>

    {{-- Reduced Motion --}}
    <div class="a11y-section">
        <div class="a11y-toggle" onclick="toggleMotion()">
            <span class="a11y-toggle-label">Reducir animaciones</span>
            <div class="toggle-track" id="motionToggle">
                <div class="toggle-knob"></div>
            </div>
        </div>
    </div>

    {{-- Reset --}}
    <button onclick="resetA11y()" style="width:100%;padding:10px;border-radius:10px;border:1.5px solid var(--border-default);background:var(--bg-surface);color:var(--text-muted);font-size:.75rem;font-weight:700;cursor:pointer;font-family:inherit;transition:all .15s;"
            onmouseover="this.style.borderColor='var(--accent)';this.style.color='var(--accent)'"
            onmouseout="this.style.borderColor='var(--border-default)';this.style.color='var(--text-muted)'">
        Restablecer valores predeterminados
    </button>
</div>

<script>
(function() {
    var html = document.documentElement;

    function applyStored() {
        var theme = localStorage.getItem('mindra_theme') || 'light';
        var font = localStorage.getItem('mindra_font') || 'normal';
        var contrast = localStorage.getItem('mindra_contrast') === '1';
        var motion = localStorage.getItem('mindra_motion') === '1';

        if (theme === 'auto') {
            var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            html.setAttribute('data-theme', prefersDark ? 'dark' : 'light');
        } else {
            html.setAttribute('data-theme', theme);
        }

        html.setAttribute('data-font', font);
        html.setAttribute('data-contrast', contrast ? 'high' : 'normal');
        html.setAttribute('data-motion', motion ? 'reduced' : 'normal');

        updatePanelUI(theme, font, contrast, motion);
    }

    function updatePanelUI(theme, font, contrast, motion) {
        document.querySelectorAll('.theme-opt').forEach(function(btn) {
            btn.classList.toggle('active', btn.getAttribute('data-theme-val') === theme);
        });
        document.querySelectorAll('.font-opt').forEach(function(btn) {
            btn.classList.toggle('active', btn.getAttribute('data-font-val') === font);
        });
        var ct = document.getElementById('contrastToggle');
        if (ct) ct.classList.toggle('on', contrast);
        var mt = document.getElementById('motionToggle');
        if (mt) mt.classList.toggle('on', motion);
    }

    window.toggleA11yPanel = function() {
        var panel = document.getElementById('a11yPanel');
        panel.classList.toggle('open');
    };

    window.setTheme = function(val) {
        localStorage.setItem('mindra_theme', val);
        applyStored();
    };

    window.setFontSize = function(val) {
        localStorage.setItem('mindra_font', val);
        applyStored();
    };

    window.toggleContrast = function() {
        var current = localStorage.getItem('mindra_contrast') === '1';
        localStorage.setItem('mindra_contrast', current ? '0' : '1');
        applyStored();
    };

    window.toggleMotion = function() {
        var current = localStorage.getItem('mindra_motion') === '1';
        localStorage.setItem('mindra_motion', current ? '0' : '1');
        applyStored();
    };

    window.resetA11y = function() {
        localStorage.removeItem('mindra_theme');
        localStorage.removeItem('mindra_font');
        localStorage.removeItem('mindra_contrast');
        localStorage.removeItem('mindra_motion');
        applyStored();
    };

    // Close panel on outside click
    document.addEventListener('click', function(e) {
        var panel = document.getElementById('a11yPanel');
        var fab = document.querySelector('.a11y-fab');
        if (panel && fab && !panel.contains(e.target) && !fab.contains(e.target)) {
            panel.classList.remove('open');
        }
    });

    // Listen for system theme change
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function() {
        if (localStorage.getItem('mindra_theme') === 'auto') applyStored();
    });

    applyStored();
})();
</script>
