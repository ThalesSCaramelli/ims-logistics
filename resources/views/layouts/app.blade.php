<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMS — @yield('title', 'Dashboard')</title>
    <style>
        /* ── Reset ── */
        *{box-sizing:border-box;margin:0;padding:0;font-family:system-ui,sans-serif}
        body{background:#f5f4f0;color:#1a1a18;min-height:100vh}

        /* ── Topbar ── */
        .topbar{background:#185FA5;padding:10px 20px;display:flex;align-items:center;gap:12px;position:sticky;top:0;z-index:100}
        .logo{background:#fff;color:#185FA5;font-size:11px;font-weight:700;padding:3px 10px;border-radius:6px;letter-spacing:0.3px;text-decoration:none;display:inline-block}
        .topbar-nav{font-size:13px;color:rgba(255,255,255,0.7);margin-left:8px;display:flex;align-items:center;gap:6px}
        .topbar-nav a{color:rgba(255,255,255,0.65);text-decoration:none}
        .topbar-nav a:hover{color:#fff}
        .topbar-nav span{color:#fff}
        .topbar-right{margin-left:auto;display:flex;align-items:center;gap:12px}
        .topbar-user{font-size:12px;color:rgba(255,255,255,0.75)}
        .topbar-btn{font-size:12px;color:rgba(255,255,255,0.8);background:rgba(255,255,255,0.15);border:none;border-radius:6px;padding:5px 12px;cursor:pointer;font-family:inherit;text-decoration:none;display:inline-block;line-height:1.4}
        .topbar-btn:hover{background:rgba(255,255,255,0.25);color:#fff}

        /* ── Nav tabs ── */
        .nav-tabs{background:#fff;border-bottom:0.5px solid rgba(0,0,0,0.1);display:flex;padding:0 20px;gap:4px;overflow-x:auto}
        .nav-tab{font-size:13px;padding:11px 16px;cursor:pointer;color:#73726c;border-bottom:2px solid transparent;margin-bottom:-0.5px;text-decoration:none;white-space:nowrap;display:inline-flex;align-items:center;gap:4px}
        .nav-tab:hover{color:#1a1a18}
        .nav-tab.active{color:#185FA5;border-bottom-color:#185FA5;font-weight:500}
        .nav-badge{display:inline-flex;align-items:center;justify-content:center;min-width:16px;height:16px;background:#EF9F27;color:#fff;border-radius:10px;font-size:9px;font-weight:600;padding:0 4px}

        /* ── Flash messages ── */
        .flash{border-radius:8px;padding:10px 14px;font-size:13px;margin-bottom:14px;display:flex;align-items:center;gap:8px}
        .flash-success{background:#EAF3DE;border:0.5px solid #C0DD97;color:#27500A}
        .flash-error{background:#FCEBEB;border:0.5px solid #F09595;color:#A32D2D}
        .flash-info{background:#E6F1FB;border:0.5px solid #B5D4F4;color:#0C447C}
        .flash-warn{background:#FAEEDA;border:0.5px solid #FAC775;color:#854F0B}

        /* ── Cards ── */
        .card{background:#fff;border:0.5px solid rgba(0,0,0,0.1);border-radius:12px;padding:18px;margin-bottom:14px}
        .card-title{font-size:14px;font-weight:500;margin-bottom:14px;padding-bottom:10px;border-bottom:0.5px solid rgba(0,0,0,0.08);display:flex;align-items:center;justify-content:space-between}

        /* ── Form fields ── */
        .field{display:flex;flex-direction:column;gap:4px}
        .field label{font-size:11px;color:#73726c}
        .field input,.field select,.field textarea{font-size:13px;padding:7px 10px;border:0.5px solid #c2c0b6;border-radius:8px;background:#fff;color:#1a1a18;font-family:inherit;width:100%}
        .field input:focus,.field select:focus,.field textarea:focus{outline:none;border-color:#185FA5}
        .field input.error{border-color:#F09595;background:#FFFAFA}
        .field textarea{resize:none}
        .grid-2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
        .grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px}

        /* ── Buttons ── */
        .btn{font-size:12px;padding:7px 14px;border-radius:8px;cursor:pointer;font-family:inherit;border:0.5px solid #c2c0b6;background:#fff;color:#1a1a18;text-decoration:none;display:inline-block;white-space:nowrap;line-height:1.4}
        .btn:hover{background:#f5f4f0}
        .btn-primary{background:#185FA5;color:#fff;border-color:#185FA5}
        .btn-primary:hover{background:#0C447C}
        .btn-success{background:#3B6D11;color:#fff;border-color:#3B6D11}
        .btn-success:hover{background:#27500A}
        .btn-danger{color:#A32D2D;border-color:#F09595}
        .btn-danger:hover{background:#FCEBEB}
        .btn-ghost{color:#73726c;border-color:transparent}
        .btn-ghost:hover{background:#f5f4f0;border-color:#c2c0b6}
        .btn-sm{font-size:11px;padding:4px 9px;border-radius:7px}
        .btn-block{width:100%;text-align:center;display:block}

        /* ── Status pills ── */
        .pill{font-size:10px;padding:2px 8px;border-radius:10px;font-weight:500;white-space:nowrap;display:inline-block}
        .pill-draft,.pill-inactive,.pill-cancelled{background:#F1EFE8;color:#5F5E5A}
        .pill-pending,.pill-suspended,.pill-partial,.pill-in-progress{background:#FAEEDA;color:#854F0B}
        .pill-approved,.pill-scheduled{background:#E6F1FB;color:#185FA5}
        .pill-allocated,.pill-completed,.pill-paid,.pill-active{background:#EAF3DE;color:#3B6D11}
        .pill-rejected{background:#FCEBEB;color:#A32D2D}

        /* ── KPIs ── */
        .kpi-row{display:flex;gap:8px;margin-bottom:14px;flex-wrap:wrap}
        .kpi{background:#fff;border:0.5px solid rgba(0,0,0,0.1);border-radius:8px;padding:10px 14px;flex:1;min-width:120px}
        .kpi-label{font-size:10px;color:#73726c;margin-bottom:4px}
        .kpi-value{font-size:22px;font-weight:500}
        .kpi-sub{font-size:10px;color:#73726c;margin-top:2px}

        /* ── Chips ── */
        .chips{display:flex;gap:6px;flex-wrap:wrap}
        .chip{font-size:12px;padding:4px 12px;border:0.5px solid #c2c0b6;border-radius:20px;cursor:pointer;background:#fff;color:#73726c;white-space:nowrap}
        .chip:hover{border-color:#185FA5;color:#185FA5}
        .chip.active{background:#E6F1FB;color:#185FA5;border-color:#85B7EB}

        /* ── Search ── */
        .search-wrap{position:relative}
        .search-wrap input{width:100%;font-size:13px;padding:7px 10px 7px 30px;border:0.5px solid #c2c0b6;border-radius:8px;font-family:inherit;color:#1a1a18;background:#fff}
        .search-wrap input:focus{outline:none;border-color:#185FA5}
        .search-icon{position:absolute;left:9px;top:50%;transform:translateY(-50%);opacity:0.35;pointer-events:none}

        /* ── Add dashed button ── */
        .add-btn{display:flex;align-items:center;justify-content:center;gap:6px;font-size:12px;color:#185FA5;background:none;border:0.5px dashed #85B7EB;border-radius:8px;padding:8px 14px;cursor:pointer;font-family:inherit;width:100%}
        .add-btn:hover{background:#E6F1FB}

        /* ── Misc ── */
        .divider{height:0.5px;background:rgba(0,0,0,0.08);margin:14px 0}
        .section-label{font-size:11px;font-weight:500;color:#73726c;text-transform:uppercase;letter-spacing:0.6px;margin-bottom:8px}
        .empty-state{text-align:center;padding:48px 20px;color:#73726c}
        .empty-state h3{font-size:15px;font-weight:500;color:#1a1a18;margin-bottom:6px}
        .empty-state p{font-size:13px;margin-bottom:16px}

        /* ── Page layouts ── */
        .page{padding:20px;max-width:1200px;margin:0 auto}
        .page-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:10px}
        .page-title{font-size:18px;font-weight:500}
        .page-sub{font-size:13px;color:#73726c;margin-top:2px}
        .page-2col{display:grid;grid-template-columns:1fr 300px;gap:16px;align-items:start}
        .page-sidebar{position:sticky;top:70px}

        /* ── Accordion ── */
        .acc{border:0.5px solid rgba(0,0,0,0.1);border-radius:12px;margin-bottom:10px;overflow:hidden}
        .acc-hdr{display:flex;align-items:center;justify-content:space-between;padding:13px 16px;cursor:pointer;background:#fff;transition:background .15s}
        .acc-hdr:hover{background:#f9f8f6}
        .acc-title{font-size:14px;font-weight:500}
        .acc-meta{font-size:12px;color:#73726c;margin-left:6px}
        .chevron{width:16px;height:16px;color:#73726c;transition:transform .2s;flex-shrink:0}
        .chevron.open{transform:rotate(180deg)}
        .acc-body{display:none;padding:16px;border-top:0.5px solid rgba(0,0,0,0.08);background:#fff}
        .acc-body.open{display:block}

        /* ── Toggle ── */
        .toggle{width:36px;height:20px;border-radius:10px;position:relative;cursor:pointer;transition:background .2s;border:none;padding:0;flex-shrink:0}
        .toggle.off{background:#c2c0b6}
        .toggle.on{background:#185FA5}
        .toggle::after{content:'';position:absolute;top:2px;left:2px;width:16px;height:16px;background:#fff;border-radius:50%;transition:transform .2s}
        .toggle.on::after{transform:translateX(16px)}

        /* ── Avatar ── */
        .av{display:flex;align-items:center;justify-content:center;border-radius:50%;font-weight:600;flex-shrink:0;background:#E6F1FB;color:#185FA5}
        .av-sm{width:24px;height:24px;font-size:9px}
        .av-md{width:32px;height:32px;font-size:12px}
        .av-lg{width:44px;height:44px;font-size:15px}

        /* ── Date navigation bar ── */
        .date-bar{background:#fff;border-bottom:0.5px solid rgba(0,0,0,0.08);padding:8px 20px;display:flex;align-items:center;gap:10px;flex-wrap:wrap}
        .date-label{font-size:14px;font-weight:500}
        .date-sub{font-size:12px;color:#73726c}
        .nav-btn{background:#fff;border:0.5px solid #c2c0b6;border-radius:6px;cursor:pointer;padding:4px 10px;font-size:16px;color:#73726c;line-height:1;font-family:inherit;text-decoration:none;display:inline-block}
        .nav-btn:hover{background:#f5f4f0}
        .today-btn{font-size:12px;padding:5px 12px;border-radius:8px;border:0.5px solid #c2c0b6;background:#fff;color:#1a1a18;text-decoration:none;cursor:pointer;font-family:inherit}
        .today-btn:hover{background:#f5f4f0}
        .date-input{font-size:13px;padding:5px 9px;border:0.5px solid #c2c0b6;border-radius:8px;font-family:inherit;color:#1a1a18}

        /* ── Responsive ── */
        @media(max-width:800px){
            .page-2col{grid-template-columns:1fr}
            .grid-3{grid-template-columns:1fr 1fr}
            .grid-2{grid-template-columns:1fr}
            .kpi{min-width:calc(50% - 4px)}
        }
    </style>
    @stack('styles')
    @stack('head')
</head>
<body>

{{-- Topbar --}}
<div class="topbar">
    <a href="{{ route('dashboard') }}" class="logo">IMS</a>
    <div class="topbar-nav">
        @yield('breadcrumb')
    </div>
    <div class="topbar-right">
        <span class="topbar-user">{{ Auth::user()->email }}</span>
        <form method="POST" action="{{ route('logout') }}" style="display:inline">
            @csrf
            <button type="submit" class="topbar-btn">Sign out</button>
        </form>
    </div>
</div>

{{-- Nav tabs --}}
@php
    $pendingNotify = \App\Models\Book::where('date', today()->toDateString())
        ->whereNull('notified_at')
        ->where('status','!=','cancelled')
        ->count();
@endphp
<div class="nav-tabs">
    <a href="{{ route('dashboard') }}"
       class="nav-tab {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        Dashboard
    </a>
    <a href="{{ route('planning.index') }}"
       class="nav-tab {{ request()->routeIs('planning.*') ? 'active' : '' }}">
        Day Planning
    </a>
    <a href="{{ route('jobs.history') }}"
       class="nav-tab {{ request()->routeIs('jobs.*') ? 'active' : '' }}">
        Job History
    </a>
    <a href="{{ route('notifications.index') }}"
       class="nav-tab {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
        Notifications
        @if($pendingNotify > 0)
            <span class="nav-badge">{{ $pendingNotify }}</span>
        @endif
    </a>
    <a href="{{ route('worksheets.index') }}"
       class="nav-tab {{ request()->routeIs('worksheets.*') ? 'active' : '' }}">
        Worksheets
    </a>
    <a href="{{ route('payments.index') }}"
       class="nav-tab {{ request()->routeIs('payments.*') ? 'active' : '' }}">
        Payments
    </a>
    <a href="{{ route('workers.index') }}"
       class="nav-tab {{ request()->routeIs('workers.*') ? 'active' : '' }}">
        Workers
    </a>
    <a href="{{ route('clients.index') }}"
       class="nav-tab {{ request()->routeIs('clients.*') ? 'active' : '' }}">
        Clients
    </a>
    <a href="{{ route('products.index') }}"
       class="nav-tab {{ request()->routeIs('products.*') ? 'active' : '' }}">
        Products
    </a>
</div>

{{-- Flash messages -- shown globally --}}
@if(session('success') || session('error') || session('info') || session('warn'))
<div style="padding:12px 20px 0">
    @if(session('success'))
        <div class="flash flash-success">✓ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="flash flash-error">✗ {{ session('error') }}</div>
    @endif
    @if(session('info'))
        <div class="flash flash-info">ℹ {{ session('info') }}</div>
    @endif
    @if(session('warn'))
        <div class="flash flash-warn">⚠ {{ session('warn') }}</div>
    @endif
</div>
@endif

{{-- Page content --}}
@yield('content')

{{-- Common JS --}}
<script>
function toggleAcc(id) {
    var body = document.getElementById(id);
    var chev = document.getElementById('chev-' + id);
    if (body) body.classList.toggle('open');
    if (chev) chev.classList.toggle('open');
}
</script>
@stack('scripts')

</body>
</html>
