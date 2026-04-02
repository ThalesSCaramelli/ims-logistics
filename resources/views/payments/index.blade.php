<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMS — Payments</title>
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
        .page{padding:20px;max-width:1100px;margin:0 auto}

        .page-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:10px}
        .page-title{font-size:18px;font-weight:500}
        .page-sub{font-size:13px;color:#73726c;margin-top:2px}

        .week-bar{background:#fff;border:0.5px solid rgba(0,0,0,0.1);border-radius:12px;padding:12px 16px;margin-bottom:14px;display:flex;align-items:center;gap:12px;flex-wrap:wrap}
        .week-label{font-size:14px;font-weight:500}
        .week-sub{font-size:12px;color:#73726c;margin-left:4px}
        .nav-btn{background:#fff;border:0.5px solid #c2c0b6;border-radius:6px;cursor:pointer;padding:4px 10px;font-size:16px;color:#73726c;line-height:1;font-family:inherit;text-decoration:none;display:inline-block}
        .nav-btn:hover{background:#f5f4f0}
        .week-input{font-size:13px;padding:5px 9px;border:0.5px solid #c2c0b6;border-radius:8px;font-family:inherit}
        .export-btn{margin-left:auto;font-size:12px;padding:6px 14px;border-radius:8px;background:#3B6D11;color:#fff;border:none;cursor:pointer;font-family:inherit;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
        .export-btn:hover{background:#27500A}

        .kpi-row{display:flex;gap:8px;margin-bottom:14px}
        .kpi{background:#fff;border:0.5px solid rgba(0,0,0,0.1);border-radius:8px;padding:10px 14px;flex:1}
        .kpi-label{font-size:10px;color:#73726c;margin-bottom:4px}
        .kpi-value{font-size:22px;font-weight:500}
        .kpi-sub{font-size:10px;color:#73726c;margin-top:2px}

        /* Worker payment cards */
        .worker-payment{background:#fff;border:0.5px solid rgba(0,0,0,0.1);border-radius:12px;margin-bottom:10px;overflow:hidden}
        .wp-header{display:flex;align-items:center;gap:12px;padding:14px 16px;cursor:pointer;transition:background .1s}
        .wp-header:hover{background:#f9f8f6}
        .wp-av{width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:600;background:#E6F1FB;color:#185FA5;flex-shrink:0}
        .wp-name{font-size:14px;font-weight:500;flex:1}
        .wp-meta{font-size:12px;color:#73726c;margin-top:1px}
        .wp-total{font-size:16px;font-weight:500;text-align:right}
        .wp-total-sub{font-size:11px;color:#73726c;text-align:right;margin-top:1px}
        .pay-status{font-size:10px;padding:3px 9px;border-radius:10px;font-weight:500;margin-left:8px;white-space:nowrap}
        .ps-unpaid{background:#FAEEDA;color:#854F0B}
        .ps-paid{background:#EAF3DE;color:#3B6D11}
        .chevron{width:14px;height:14px;color:#73726c;transition:transform .2s;flex-shrink:0}
        .chevron.open{transform:rotate(180deg)}

        .wp-body{display:none;border-top:0.5px solid rgba(0,0,0,0.08);background:#fafaf8}
        .wp-body.open{display:block}

        /* Jobs detail table */
        .jobs-table{width:100%;border-collapse:collapse;font-size:12px}
        .jobs-table th{text-align:left;font-size:10px;font-weight:500;color:#73726c;text-transform:uppercase;letter-spacing:0.4px;padding:8px 16px;border-bottom:0.5px solid rgba(0,0,0,0.1)}
        .jobs-table td{padding:8px 16px;border-bottom:0.5px solid rgba(0,0,0,0.06)}
        .jobs-table tr:last-child td{border-bottom:none}
        .jt-client{font-size:13px;font-weight:500}
        .jt-site{font-size:11px;color:#73726c;margin-top:1px}

        .wp-footer{display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-top:0.5px solid rgba(0,0,0,0.08);background:#fafaf8}
        .wp-footer-total{font-size:14px;font-weight:500}
        .wp-footer-sub{font-size:12px;color:#73726c}
        .pay-btn{font-size:12px;padding:7px 16px;border-radius:8px;cursor:pointer;font-family:inherit;background:#185FA5;color:#fff;border:none;white-space:nowrap}
        .pay-btn:hover{background:#0C447C}
        .pay-btn.paid{background:#3B6D11}
        .pay-btn.paid:hover{background:#27500A}

        .flash{border-radius:8px;padding:10px 14px;font-size:13px;margin-bottom:14px}
        .flash-success{background:#EAF3DE;border:0.5px solid #C0DD97;color:#27500A}

        .empty-state{text-align:center;padding:48px 20px;background:#fff;border:0.5px solid rgba(0,0,0,0.1);border-radius:12px;color:#73726c}
        .empty-state h3{font-size:15px;font-weight:500;color:#1a1a18;margin-bottom:6px}
    </style>
</head>
<body>
<div class="topbar">
    <div class="logo">IMS</div>
    <div class="topbar-nav"><span>Payments</span></div>
    <div class="topbar-right">
        <span class="topbar-user">{{ Auth::user()->email }}</span>
        <form method="POST" action="{{ route('logout') }}" style="display:inline">
            @csrf<button type="submit" class="topbar-btn">Sign out</button>
        </form>
    </div>
</div>

@include('layouts._nav')

@php
    $weekStart = \Carbon\Carbon::parse($week . '-1');
    $weekEnd   = $weekStart->copy()->endOfWeek();
    $prevWeek  = $weekStart->copy()->subWeek()->format('Y-W');
    $nextWeek  = $weekStart->copy()->addWeek()->format('Y-W');
@endphp

<div class="page">
    <div class="page-header">
        <div>
            <div class="page-title">Payments</div>
            <div class="page-sub">Weekly worker payment consolidation</div>
        </div>
    </div>

    @if(session('success'))
        <div class="flash flash-success">✓ {{ session('success') }}</div>
    @endif

    {{-- Week bar --}}
    <div class="week-bar">
        <a href="{{ route('payments.index', ['week' => $prevWeek]) }}" class="nav-btn">&#8249;</a>
        <div>
            <span class="week-label">{{ $weekStart->format('d M') }} — {{ $weekEnd->format('d M Y') }}</span>
            @if($week === now()->format('Y-W')) <span class="week-sub">This week</span> @endif
        </div>
        <a href="{{ route('payments.index', ['week' => $nextWeek]) }}" class="nav-btn">&#8250;</a>
        <form method="GET" action="{{ route('payments.index') }}">
            <input type="week" name="week" value="{{ $week }}" class="week-input" onchange="this.form.submit()">
        </form>
        <a href="{{ route('payments.export', ['week' => $week]) }}" class="export-btn">
            <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M6 1v7M3 5l3 3 3-3M2 9h8" stroke="#fff" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Export CSV
        </a>
    </div>

    {{-- KPIs --}}
    <div class="kpi-row">
        <div class="kpi">
            <div class="kpi-label">Total payroll</div>
            <div class="kpi-value">${{ number_format($kpis['total'], 0) }}</div>
            <div class="kpi-sub">this week</div>
        </div>
        <div class="kpi">
            <div class="kpi-label">Workers paid</div>
            <div class="kpi-value" style="color:#3B6D11">{{ $kpis['paid_count'] }}</div>
            <div class="kpi-sub">of {{ $kpis['total_workers'] ?? 0 }}</div>
        </div>
        <div class="kpi">
            <div class="kpi-label">Outstanding</div>
            <div class="kpi-value" style="color:#854F0B">${{ number_format($kpis['outstanding'], 0) }}</div>
            <div class="kpi-sub">{{ $kpis['unpaid_count'] }} workers</div>
        </div>
        <div class="kpi">
            <div class="kpi-label">Jobs this week</div>
            <div class="kpi-value">{{ $kpis['jobs_count'] }}</div>
            <div class="kpi-sub">completed</div>
        </div>
    </div>

    @if($workerPayments->isEmpty())
        <div class="empty-state">
            <h3>No payments for this week</h3>
            <p>Payments are generated from approved worksheets.</p>
        </div>
    @else

    {{-- Pay all pending --}}
    @if($kpis['unpaid_count'] > 0)
    <div style="display:flex;justify-content:flex-end;margin-bottom:10px">
        <form method="POST" action="{{ route('payments.markAllPaid') }}">
            @csrf
            <input type="hidden" name="week" value="{{ $week }}">
            <button type="submit" class="pay-btn" onclick="return confirm('Mark all workers as paid for this week?')">
                Mark all paid ({{ $kpis['unpaid_count'] }} workers)
            </button>
        </form>
    </div>
    @endif

    @foreach($workerPayments as $wp)
    @php
        $initials = collect(explode(' ',$wp['worker']->name))->map(fn($p)=>strtoupper(substr($p,0,1)))->take(2)->join('');
        $isPaid   = $wp['is_paid'];
    @endphp
    <div class="worker-payment">
        <div class="wp-header" onclick="toggleWP({{ $wp['worker']->id }})">
            <div class="wp-av">{{ $initials }}</div>
            <div style="flex:1">
                <div class="wp-name">
                    {{ $wp['worker']->name }}
                    <span class="pay-status {{ $isPaid?'ps-paid':'ps-unpaid' }}">{{ $isPaid?'Paid':'Unpaid' }}</span>
                </div>
                <div class="wp-meta">{{ $wp['jobs_count'] }} job{{ $wp['jobs_count']!==1?'s':'' }} · {{ $wp['total_hours'] }}h total</div>
            </div>
            <div>
                <div class="wp-total">${{ number_format($wp['total_amount'], 2) }}</div>
                @if($wp['worker']->min_weekly && $wp['total_amount'] < $wp['worker']->min_weekly)
                    <div class="wp-total-sub" style="color:#854F0B">Below min ${{ number_format($wp['worker']->min_weekly, 0) }}</div>
                @endif
            </div>
            <svg class="chevron" id="chev-{{ $wp['worker']->id }}" viewBox="0 0 16 16" fill="none"><path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>

        <div class="wp-body" id="wpb-{{ $wp['worker']->id }}">
            <table class="jobs-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Job</th>
                        <th>Hours</th>
                        <th>Rate</th>
                        <th>Labour</th>
                        <th>Additionals</th>
                        <th>Deductions</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($wp['lines'] as $line)
                    <tr>
                        <td>{{ $line['date']->format('d M') }}</td>
                        <td>
                            <div class="jt-client">{{ $line['client'] }}</div>
                            <div class="jt-site">{{ $line['site'] }}</div>
                        </td>
                        <td>{{ $line['hours'] }}h</td>
                        <td>${{ number_format($line['rate'], 2) }}/h</td>
                        <td>${{ number_format($line['labour'], 2) }}</td>
                        <td style="color:#3B6D11">{{ $line['additionals'] > 0 ? '+$'.number_format($line['additionals'],2) : '—' }}</td>
                        <td style="color:#A32D2D">{{ $line['deductions'] > 0 ? '−$'.number_format($line['deductions'],2) : '—' }}</td>
                        <td style="font-weight:500">${{ number_format($line['total'], 2) }}</td>
                    </tr>
                    @endforeach
                    {{-- Min weekly top-up --}}
                    @if($wp['worker']->min_weekly && $wp['total_amount'] < $wp['worker']->min_weekly)
                    <tr style="background:#FFF9F0">
                        <td colspan="7" style="font-size:12px;color:#854F0B;font-style:italic">Minimum weekly guarantee top-up</td>
                        <td style="font-weight:500;color:#854F0B">+${{ number_format($wp['worker']->min_weekly - $wp['total_amount'], 2) }}</td>
                    </tr>
                    @endif
                </tbody>
            </table>

            <div class="wp-footer">
                <div>
                    <div class="wp-footer-total">${{ number_format($wp['payable_amount'], 2) }}</div>
                    <div class="wp-footer-sub">Total payable{{ $wp['worker']->abn ? ' · ABN '.$wp['worker']->abn : ' · No ABN' }}</div>
                </div>
                @if(!$isPaid)
                <form method="POST" action="{{ route('payments.markPaid', $wp['worker']) }}">
                    @csrf
                    <input type="hidden" name="week" value="{{ $week }}">
                    <button type="submit" class="pay-btn">Mark as paid</button>
                </form>
                @else
                <form method="POST" action="{{ route('payments.markUnpaid', $wp['worker']) }}">
                    @csrf
                    <input type="hidden" name="week" value="{{ $week }}">
                    <button type="submit" class="pay-btn paid">✓ Paid — Undo</button>
                </form>
                @endif
            </div>
        </div>
    </div>
    @endforeach
    @endif
</div>

<script>
function toggleWP(id) {
    document.getElementById('wpb-'+id).classList.toggle('open');
    document.getElementById('chev-'+id).classList.toggle('open');
}
</script>
</body>
</html>
