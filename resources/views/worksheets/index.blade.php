<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMS — Worksheets</title>
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
        .topbar-btn:hover{background:rgba(255,255,255,0.25)}

        .nav-tabs{background:#fff;border-bottom:0.5px solid rgba(0,0,0,0.1);display:flex;padding:0 20px;gap:4px;overflow-x:auto}
        .nav-tab{font-size:13px;padding:11px 16px;cursor:pointer;color:#73726c;border-bottom:2px solid transparent;margin-bottom:-0.5px;text-decoration:none;white-space:nowrap;display:inline-block}
        .nav-tab:hover{color:#1a1a18}
        .nav-tab.active{color:#185FA5;border-bottom-color:#185FA5;font-weight:500}
        .nav-tab .badge{display:inline-flex;align-items:center;justify-content:center;min-width:16px;height:16px;background:#EF9F27;color:#fff;border-radius:10px;font-size:9px;font-weight:600;margin-left:5px;padding:0 4px}

        .page{padding:20px;max-width:1100px;margin:0 auto}
        .page-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:10px}
        .page-title{font-size:18px;font-weight:500}
        .page-sub{font-size:13px;color:#73726c;margin-top:2px}

        .filters{background:#fff;border:0.5px solid rgba(0,0,0,0.1);border-radius:12px;padding:12px 16px;margin-bottom:14px;display:flex;gap:10px;align-items:center;flex-wrap:wrap}
        .search-wrap{position:relative;flex:1;min-width:180px}
        .search-wrap input{width:100%;font-size:13px;padding:7px 10px 7px 30px;border:0.5px solid #c2c0b6;border-radius:8px;font-family:inherit;color:#1a1a18}
        .search-wrap input:focus{outline:none;border-color:#185FA5}
        .si{position:absolute;left:9px;top:50%;transform:translateY(-50%);opacity:0.35;pointer-events:none}
        .chips{display:flex;gap:6px;flex-wrap:wrap}
        .chip{font-size:12px;padding:4px 12px;border:0.5px solid #c2c0b6;border-radius:20px;cursor:pointer;background:#fff;color:#73726c;white-space:nowrap}
        .chip:hover{border-color:#185FA5;color:#185FA5}
        .chip.active{background:#E6F1FB;color:#185FA5;border-color:#85B7EB}
        .chip.cp.active{background:#FAEEDA;color:#854F0B;border-color:#FAC775}
        .chip.ca.active{background:#EAF3DE;color:#3B6D11;border-color:#97C459}

        .kpi-row{display:flex;gap:8px;margin-bottom:14px}
        .kpi{background:#fff;border:0.5px solid rgba(0,0,0,0.1);border-radius:8px;padding:10px 14px;flex:1}
        .kpi-label{font-size:10px;color:#73726c;margin-bottom:4px}
        .kpi-value{font-size:22px;font-weight:500}

        .ws-table{background:#fff;border:0.5px solid rgba(0,0,0,0.1);border-radius:12px;overflow:hidden}
        .ws-head{display:grid;grid-template-columns:100px 1fr 140px 110px 100px 120px;gap:0;padding:9px 16px;background:#f5f4f0;border-bottom:0.5px solid rgba(0,0,0,0.1)}
        .ws-th{font-size:11px;font-weight:500;color:#73726c;text-transform:uppercase;letter-spacing:0.4px}
        .ws-row{display:grid;grid-template-columns:100px 1fr 140px 110px 100px 120px;gap:0;padding:12px 16px;border-bottom:0.5px solid rgba(0,0,0,0.07);align-items:center;transition:background .1s;text-decoration:none;color:inherit;cursor:pointer}
        .ws-row:last-child{border-bottom:none}
        .ws-row:hover{background:#f9f8f6}
        .ws-date{font-size:12px;color:#73726c}
        .ws-job{font-size:13px;font-weight:500}
        .ws-site{font-size:11px;color:#73726c;margin-top:2px}
        .ws-workers{display:flex;flex-wrap:wrap;gap:3px}
        .wp{font-size:10px;background:#f5f4f0;border:0.5px solid rgba(0,0,0,0.1);border-radius:8px;padding:1px 7px;color:#1a1a18}
        .ws-total{font-size:13px;font-weight:500}
        .status-pill{font-size:10px;padding:3px 9px;border-radius:10px;font-weight:500;white-space:nowrap;display:inline-block}
        .sp-draft{background:#F1EFE8;color:#5F5E5A}
        .sp-pending{background:#FAEEDA;color:#854F0B}
        .sp-approved{background:#E6F1FB;color:#185FA5}
        .sp-paid{background:#EAF3DE;color:#3B6D11}
        .sp-rejected{background:#FCEBEB;color:#A32D2D}
        .ws-actions{display:flex;gap:5px;justify-content:flex-end}
        .act-btn{font-size:11px;padding:4px 9px;border-radius:7px;cursor:pointer;font-family:inherit;border:0.5px solid #c2c0b6;background:#fff;color:#1a1a18;white-space:nowrap;text-decoration:none;display:inline-block}
        .act-btn:hover{background:#f5f4f0}
        .act-btn.primary{background:#185FA5;color:#fff;border-color:#185FA5}
        .act-btn.primary:hover{background:#0C447C}

        .empty-state{text-align:center;padding:48px 20px;color:#73726c}
        .empty-state h3{font-size:15px;font-weight:500;color:#1a1a18;margin-bottom:6px}

        .flash{border-radius:8px;padding:10px 14px;font-size:13px;margin-bottom:14px}
        .flash-success{background:#EAF3DE;border:0.5px solid #C0DD97;color:#27500A}

        @media(max-width:800px){
            .ws-head,.ws-row{grid-template-columns:80px 1fr 90px 90px}
            .ws-th:nth-child(3),.ws-th:nth-child(5),
            [class*="ws-workers"]{display:none}
        }
    </style>
</head>
<body>
<div class="topbar">
    <div class="logo">IMS</div>
    <div class="topbar-nav"><span>Worksheets</span></div>
    <div class="topbar-right">
        <span class="topbar-user">{{ Auth::user()->email }}</span>
        <form method="POST" action="{{ route('logout') }}" style="display:inline">
            @csrf<button type="submit" class="topbar-btn">Sign out</button>
        </form>
    </div>
</div>

@include('layouts._nav')

<div class="page">
    <div class="page-header">
        <div>
            <div class="page-title">Worksheets</div>
            <div class="page-sub">Review and approve worker timesheets</div>
        </div>
    </div>

    @if(session('success'))
        <div class="flash flash-success">✓ {{ session('success') }}</div>
    @endif

    <div class="kpi-row">
        <div class="kpi">
            <div class="kpi-label">Pending review</div>
            <div class="kpi-value" style="color:#854F0B">{{ $kpis['pending'] }}</div>
        </div>
        <div class="kpi">
            <div class="kpi-label">Approved this week</div>
            <div class="kpi-value" style="color:#185FA5">{{ $kpis['approved_week'] }}</div>
        </div>
        <div class="kpi">
            <div class="kpi-label">Paid this week</div>
            <div class="kpi-value" style="color:#3B6D11">{{ $kpis['paid_week'] }}</div>
        </div>
        <div class="kpi">
            <div class="kpi-label">Total value pending</div>
            <div class="kpi-value">${{ number_format($kpis['pending_value'], 0) }}</div>
        </div>
    </div>

    <div class="filters">
        <div class="search-wrap">
            <svg class="si" width="13" height="13" viewBox="0 0 14 14" fill="none"><circle cx="6" cy="6" r="4.5" stroke="#1a1a18" stroke-width="1.2"/><path d="M9.5 9.5L12.5 12.5" stroke="#1a1a18" stroke-width="1.2" stroke-linecap="round"/></svg>
            <input type="text" placeholder="Search by client or worker..." id="ws-search" oninput="filterRows(this.value)">
        </div>
        <div class="chips">
            <span class="chip active" onclick="chipFilter('all',this)">All</span>
            <span class="chip cp" onclick="chipFilter('pending',this)">Pending</span>
            <span class="chip" onclick="chipFilter('draft',this)">Draft</span>
            <span class="chip ca" onclick="chipFilter('approved',this)">Approved</span>
            <span class="chip ca" onclick="chipFilter('paid',this)">Paid</span>
        </div>
        <div style="margin-left:auto;display:flex;gap:8px;align-items:center">
            <label style="font-size:12px;color:#73726c">Week:</label>
            <form method="GET" action="{{ route('worksheets.index') }}" style="display:inline">
                <input type="week" name="week" value="{{ $week }}" style="font-size:12px;padding:5px 9px;border:0.5px solid #c2c0b6;border-radius:8px;font-family:inherit" onchange="this.form.submit()">
            </form>
        </div>
    </div>

    @if($worksheets->isEmpty())
        <div class="empty-state">
            <h3>No worksheets for this period</h3>
            <p>Worksheets are created when workers complete their jobs.</p>
        </div>
    @else
    <div class="ws-table">
        <div class="ws-head">
            <span class="ws-th">Date</span>
            <span class="ws-th">Job</span>
            <span class="ws-th">Workers</span>
            <span class="ws-th">Total value</span>
            <span class="ws-th">Status</span>
            <span class="ws-th"></span>
        </div>
        @foreach($worksheets as $ws)
        @php $statusVal = $ws->sync_status->value ?? 'draft'; @endphp
        <a href="{{ route('worksheets.show', $ws) }}"
           class="ws-row"
           data-status="{{ $statusVal }}"
           data-search="{{ strtolower($ws->job->site->client->name . ' ' . $ws->job->site->name) }}">
            <div class="ws-date">{{ $ws->job->date->format('d M Y') }}</div>
            <div>
                <div class="ws-job">{{ $ws->job->site->client->name }}</div>
                <div class="ws-site">{{ $ws->job->site->name }} · {{ \Carbon\Carbon::parse($ws->job->start_time)->format('H:i') }}</div>
            </div>
            <div class="ws-workers">
                @foreach($ws->job->book->workers->take(3) as $w)
                    <span class="wp">{{ collect(explode(' ',$w->name))->map(fn($p)=>strtoupper(substr($p,0,1)))->take(2)->join('') }}</span>
                @endforeach
                @if($ws->job->book->workers->count() > 3)
                    <span class="wp">+{{ $ws->job->book->workers->count() - 3 }}</span>
                @endif
            </div>
            <div class="ws-total">
                @if($ws->total_amount)
                    ${{ number_format($ws->total_amount, 2) }}
                @else
                    <span style="color:#73726c">—</span>
                @endif
            </div>
            <div><span class="status-pill sp-{{ $statusVal }}">{{ ucfirst($statusVal) }}</span></div>
            <div class="ws-actions" onclick="event.stopPropagation()">
                <a href="{{ route('worksheets.show', $ws) }}" class="act-btn {{ $statusVal === 'pending' ? 'primary' : '' }}">
                    {{ $statusVal === 'pending' ? 'Review' : 'View' }}
                </a>
            </div>
        </a>
        @endforeach
    </div>
    @endif
</div>

<script>
var activeChip = 'all';
function chipFilter(type, el) {
    activeChip = type;
    document.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
    el.classList.add('active');
    filterRows(document.getElementById('ws-search').value);
}
function filterRows(q) {
    q = (q||'').toLowerCase();
    document.querySelectorAll('.ws-row').forEach(function(row) {
        var matchQ = !q || row.dataset.search.includes(q);
        var matchC = activeChip === 'all' || row.dataset.status === activeChip;
        row.style.display = (matchQ && matchC) ? '' : 'none';
    });
}
</script>
</body>
</html>
