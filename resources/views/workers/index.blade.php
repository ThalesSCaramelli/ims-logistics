<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMS — Workers</title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0;font-family:system-ui,sans-serif}
        body{background:#f5f4f0;color:#1a1a18}
        .topbar{background:#185FA5;padding:10px 20px;display:flex;align-items:center;gap:12px;position:sticky;top:0;z-index:100}
        .logo{background:#fff;color:#185FA5;font-size:11px;font-weight:700;padding:3px 10px;border-radius:6px}
        .topbar-nav{font-size:13px;color:rgba(255,255,255,0.7);margin-left:8px}
        .topbar-nav span{color:#fff}
        .topbar-right{margin-left:auto;display:flex;align-items:center;gap:12px}
        .topbar-user{font-size:12px;color:rgba(255,255,255,0.75)}
        .topbar-btn{font-size:12px;color:rgba(255,255,255,0.8);background:rgba(255,255,255,0.15);border:none;border-radius:6px;padding:5px 12px;cursor:pointer;font-family:inherit;text-decoration:none;display:inline-block}
        .topbar-btn:hover{background:rgba(255,255,255,0.25);color:#fff}
        .page{padding:20px;max-width:1100px;margin:0 auto}
        .page-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px}
        .page-title{font-size:18px;font-weight:500}
        .page-sub{font-size:13px;color:#73726c;margin-top:2px}
        .btn{font-size:12px;padding:7px 14px;border-radius:8px;cursor:pointer;font-family:inherit;border:0.5px solid #c2c0b6;background:#fff;color:#1a1a18;text-decoration:none;display:inline-block;white-space:nowrap}
        .btn:hover{background:#f5f4f0}
        .btn-primary{background:#185FA5;color:#fff;border-color:#185FA5}
        .btn-primary:hover{background:#0C447C}

        .filters{background:#fff;border:0.5px solid rgba(0,0,0,0.1);border-radius:12px;padding:12px 16px;margin-bottom:14px;display:flex;gap:10px;align-items:center;flex-wrap:wrap}
        .search-wrap{position:relative;flex:1;min-width:200px}
        .search-wrap input{width:100%;font-size:13px;padding:7px 10px 7px 30px;border:0.5px solid #c2c0b6;border-radius:8px;font-family:inherit;color:#1a1a18}
        .search-wrap input:focus{outline:none;border-color:#185FA5}
        .search-icon{position:absolute;left:9px;top:50%;transform:translateY(-50%);opacity:0.35;pointer-events:none}
        .chips{display:flex;gap:6px;flex-wrap:wrap}
        .chip{font-size:12px;padding:4px 12px;border:0.5px solid #c2c0b6;border-radius:20px;cursor:pointer;background:#fff;color:#73726c;white-space:nowrap}
        .chip:hover{border-color:#185FA5;color:#185FA5}
        .chip.active{background:#E6F1FB;color:#185FA5;border-color:#85B7EB}
        .chip.ca.active{background:#FCEBEB;color:#A32D2D;border-color:#F09595}

        .workers-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:10px}
        .worker-card{background:#fff;border:0.5px solid rgba(0,0,0,0.1);border-radius:12px;padding:14px 16px;text-decoration:none;color:inherit;display:block;transition:border-color .15s}
        .worker-card:hover{border-color:#185FA5}
        .worker-card.inactive{opacity:0.5}
        .wc-top{display:flex;align-items:center;gap:10px;margin-bottom:10px}
        .wc-av{width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:600;flex-shrink:0;background:#E6F1FB;color:#185FA5}
        .wc-name{font-size:14px;font-weight:500;flex:1}
        .status-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0}
        .dot-active{background:#3B6D11}
        .dot-suspended{background:#EF9F27}
        .dot-inactive{background:#c2c0b6}
        .wc-meta{font-size:12px;color:#73726c;margin-bottom:8px}
        .wc-tags{display:flex;gap:5px;flex-wrap:wrap}
        .tag{font-size:10px;padding:2px 8px;border-radius:10px;font-weight:500}
        .tag-active{background:#EAF3DE;color:#3B6D11}
        .tag-suspended{background:#FAEEDA;color:#854F0B}
        .tag-inactive{background:#F1EFE8;color:#5F5E5A}
        .tag-lf{background:#EEEDFE;color:#3C3489}
        .tag-min{background:#E6F1FB;color:#185FA5}
        .tag-visa{background:#FCEBEB;color:#A32D2D}

        .empty-state{text-align:center;padding:48px 20px;background:#fff;border:0.5px solid rgba(0,0,0,0.1);border-radius:12px;color:#73726c}
        .empty-state h3{font-size:15px;font-weight:500;color:#1a1a18;margin-bottom:6px}
    </style>
</head>
<body>
<div class="topbar">
    <div class="logo">IMS</div>
    <div class="topbar-nav"><span>Workers</span></div>
    <div class="topbar-right">
        <span class="topbar-user">{{ Auth::user()->email }}</span>
        <form method="POST" action="{{ route('logout') }}" style="display:inline">
            @csrf
            <button type="submit" class="topbar-btn">Sign out</button>
        </form>
    </div>
</div>

@include('layouts._nav')

<div class="page">
    <div class="page-header">
        <div>
            <div class="page-title">Workers</div>
            <div class="page-sub">{{ $workers->total() }} workers registered</div>
        </div>
        <a href="{{ route('workers.create') }}" class="btn btn-primary">+ New worker</a>
    </div>

    @if(session('success'))
        <div style="background:#EAF3DE;border:0.5px solid #C0DD97;border-radius:8px;padding:10px 14px;font-size:13px;color:#27500A;margin-bottom:14px">✓ {{ session('success') }}</div>
    @endif

    <div class="filters">
        <div class="search-wrap">
            <svg class="search-icon" width="13" height="13" viewBox="0 0 14 14" fill="none"><circle cx="6" cy="6" r="4.5" stroke="#1a1a18" stroke-width="1.2"/><path d="M9.5 9.5L12.5 12.5" stroke="#1a1a18" stroke-width="1.2" stroke-linecap="round"/></svg>
            <input type="text" placeholder="Search by name or ABN..." id="search-input" oninput="filterCards(this.value)">
        </div>
        <div class="chips">
            <span class="chip active" onclick="chipFilter('all',this)">All</span>
            <span class="chip" onclick="chipFilter('active',this)">Active</span>
            <span class="chip" onclick="chipFilter('suspended',this)">Suspended</span>
            <span class="chip ca" onclick="chipFilter('inactive',this)">Inactive</span>
            <span class="chip" onclick="chipFilter('forklift',this)">Forklift LF</span>
        </div>
    </div>

    @if($workers->isEmpty())
        <div class="empty-state">
            <h3>No workers registered yet</h3>
            <p>Add your first worker to start allocating them to books.</p>
        </div>
    @else
    <div class="workers-grid" id="workers-grid">
        @foreach($workers as $worker)
        @php
            $initials = collect(explode(' ',$worker->name))->map(fn($p)=>strtoupper(substr($p,0,1)))->take(2)->join('');
            $visaExpiring = !$worker->is_australian && $worker->visa?->isExpiredOrExpiringSoon();
        @endphp
        <a href="{{ route('workers.show', $worker) }}"
           class="worker-card {{ $worker->status->value === 'inactive' ? 'inactive' : '' }}"
           data-status="{{ $worker->status->value }}"
           data-name="{{ strtolower($worker->name) }}"
           data-abn="{{ $worker->abn }}"
           data-forklift="{{ $worker->has_forklift ? '1' : '0' }}">
            <div class="wc-top">
                <div class="wc-av">{{ $initials }}</div>
                <div class="wc-name">{{ $worker->name }}</div>
                <div class="status-dot dot-{{ $worker->status->value }}"></div>
            </div>
            <div class="wc-meta">
                {{ $worker->phone }}
                @if($worker->abn) · ABN {{ $worker->abn }} @endif
            </div>
            <div class="wc-tags">
                <span class="tag tag-{{ $worker->status->value }}">{{ ucfirst($worker->status->value) }}</span>
                @if($worker->has_forklift) <span class="tag tag-lf">Driver LF</span> @endif
                @if($worker->min_weekly) <span class="tag tag-min">Min ${{ number_format($worker->min_weekly,0) }}/wk</span> @endif
                @if($visaExpiring) <span class="tag tag-visa">Visa expiring</span> @endif
            </div>
        </a>
        @endforeach
    </div>
    <div style="margin-top:14px">{{ $workers->links() }}</div>
    @endif
</div>

<script>
var activeChip = 'all';
function chipFilter(type, el) {
    activeChip = type;
    document.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
    el.classList.add('active');
    filterCards(document.getElementById('search-input').value);
}
function filterCards(q) {
    q = (q || '').toLowerCase();
    document.querySelectorAll('.worker-card').forEach(function(card) {
        var matchQ = !q || card.dataset.name.includes(q) || (card.dataset.abn||'').includes(q);
        var matchC = activeChip === 'all' ||
                     activeChip === 'forklift' ? card.dataset.forklift === '1' : card.dataset.status === activeChip;
        card.style.display = (matchQ && matchC) ? '' : 'none';
    });
}
</script>
</body>
</html>
