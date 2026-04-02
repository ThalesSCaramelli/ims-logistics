<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMS — Notifications</title>
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
        .nav-tabs{background:#fff;border-bottom:0.5px solid rgba(0,0,0,0.1);display:flex;padding:0 20px;gap:4px;overflow-x:auto}
        .nav-tab{font-size:13px;padding:11px 16px;cursor:pointer;color:#73726c;border-bottom:2px solid transparent;margin-bottom:-0.5px;text-decoration:none;white-space:nowrap;display:inline-block}
        .nav-tab:hover{color:#1a1a18}
        .nav-tab.active{color:#185FA5;border-bottom-color:#185FA5;font-weight:500}
        .nav-tab .badge{display:inline-flex;align-items:center;justify-content:center;min-width:16px;height:16px;background:#EF9F27;color:#fff;border-radius:10px;font-size:9px;font-weight:600;margin-left:5px;padding:0 4px}

        .page{padding:20px;max-width:1000px;margin:0 auto}

        /* Date bar */
        .date-bar{background:#fff;border-bottom:0.5px solid rgba(0,0,0,0.08);padding:8px 20px;display:flex;align-items:center;gap:10px;margin-bottom:0}
        .date-label{font-size:14px;font-weight:500}
        .date-sub{font-size:12px;color:#73726c;margin-left:4px}
        .nav-btn{background:#fff;border:0.5px solid #c2c0b6;border-radius:6px;cursor:pointer;padding:4px 10px;font-size:16px;color:#73726c;line-height:1;font-family:inherit;text-decoration:none;display:inline-block}
        .nav-btn:hover{background:#f5f4f0}
        .today-btn{font-size:12px;padding:5px 12px;border-radius:8px;cursor:pointer;font-family:inherit;border:0.5px solid #c2c0b6;background:#fff;color:#1a1a18;text-decoration:none}
        .today-btn:hover{background:#f5f4f0}
        .date-input{font-size:13px;padding:5px 9px;border:0.5px solid #c2c0b6;border-radius:8px;font-family:inherit;color:#1a1a18}

        /* Alert flash */
        .flash{padding:10px 16px;border-radius:8px;margin-bottom:14px;font-size:13px;display:flex;align-items:center;gap:8px}
        .flash-success{background:#EAF3DE;border:0.5px solid #C0DD97;color:#27500A}
        .flash-info{background:#E6F1FB;border:0.5px solid #B5D4F4;color:#0C447C}

        /* Stats row */
        .stats-row{display:flex;gap:10px;margin-bottom:16px}
        .stat{background:#fff;border:0.5px solid rgba(0,0,0,0.1);border-radius:8px;padding:10px 14px;flex:1}
        .stat-label{font-size:10px;color:#73726c;margin-bottom:4px}
        .stat-value{font-size:20px;font-weight:500}

        /* Bulk bar */
        .bulk-bar{background:#185FA5;border-radius:12px;padding:10px 16px;margin-bottom:12px;display:none;align-items:center;gap:10px}
        .bulk-bar.on{display:flex}
        .bulk-label{font-size:13px;color:#fff;flex:1}
        .bbtn{font-size:12px;padding:6px 14px;border-radius:8px;cursor:pointer;font-family:inherit;border:0.5px solid rgba(255,255,255,0.35);background:rgba(255,255,255,0.15);color:#fff}
        .bbtn.green{background:rgba(59,109,17,0.9);border-color:rgba(255,255,255,0.3)}
        .bbtn:hover{background:rgba(255,255,255,0.25)}

        /* Toolbar */
        .toolbar{display:flex;align-items:center;gap:10px;margin-bottom:12px;flex-wrap:wrap}
        .chips{display:flex;gap:6px}
        .chip{font-size:12px;padding:4px 12px;border:0.5px solid #c2c0b6;border-radius:20px;cursor:pointer;background:#fff;color:#73726c;white-space:nowrap}
        .chip:hover{border-color:#185FA5;color:#185FA5}
        .chip.active{background:#E6F1FB;color:#185FA5;border-color:#85B7EB}
        .chip.cn.active{background:#FAEEDA;color:#854F0B;border-color:#FAC775}
        .chip.cs.active{background:#EAF3DE;color:#3B6D11;border-color:#97C459}
        .send-all-btn{margin-left:auto;font-size:12px;padding:7px 16px;border-radius:8px;background:#3B6D11;color:#fff;border:none;cursor:pointer;font-family:inherit;white-space:nowrap}
        .send-all-btn:hover{background:#27500A}
        .send-all-btn:disabled{opacity:0.5;cursor:not-allowed}

        /* Books table */
        .books-table{background:#fff;border:0.5px solid rgba(0,0,0,0.1);border-radius:12px;overflow:hidden}
        .bt-head{display:grid;grid-template-columns:36px 1fr 160px 140px 160px 120px;gap:0;padding:9px 16px;background:#f5f4f0;border-bottom:0.5px solid rgba(0,0,0,0.1)}
        .bt-th{font-size:11px;font-weight:500;color:#73726c;text-transform:uppercase;letter-spacing:0.5px}

        .book-row{border-bottom:0.5px solid rgba(0,0,0,0.07)}
        .book-row:last-child{border-bottom:none}

        .book-main{display:grid;grid-template-columns:36px 1fr 160px 140px 160px 120px;gap:0;padding:12px 16px;align-items:start;cursor:pointer;transition:background .15s}
        .book-main:hover{background:#f9f8f6}

        .book-check{display:flex;align-items:center;justify-content:center;padding-top:2px}
        .cb{width:15px;height:15px;border:0.5px solid #c2c0b6;border-radius:3px;cursor:pointer;accent-color:#185FA5}
        .cb:disabled{opacity:0.4;cursor:not-allowed}

        .book-jobs{min-width:0}
        .job-line{font-size:13px;margin-bottom:3px;display:flex;align-items:center;gap:6px;flex-wrap:wrap}
        .job-line:last-child{margin-bottom:0}
        .job-time{font-weight:500;color:#1a1a18;white-space:nowrap}
        .job-client{color:#1a1a18}
        .job-site{font-size:11px;color:#73726c}
        .job-containers{font-size:10px;background:#E6F1FB;color:#185FA5;padding:1px 7px;border-radius:10px;white-space:nowrap}

        .book-workers{min-width:0}
        .worker-pills{display:flex;flex-wrap:wrap;gap:3px}
        .wp{font-size:11px;background:#f5f4f0;border:0.5px solid rgba(0,0,0,0.1);border-radius:10px;padding:2px 8px;color:#1a1a18;white-space:nowrap}

        .book-status{display:flex;align-items:flex-start;padding-top:2px}
        .status-pill{font-size:10px;padding:3px 9px;border-radius:10px;font-weight:500;white-space:nowrap}
        .sp-pending{background:#FAEEDA;color:#854F0B}
        .sp-sent{background:#EAF3DE;color:#3B6D11}
        .sp-failed{background:#FCEBEB;color:#A32D2D}

        .book-sent-info{font-size:11px;color:#73726c;line-height:1.5}
        .book-sent-info strong{color:#1a1a18;display:block;font-size:12px}

        .book-actions{display:flex;align-items:flex-start;gap:6px;flex-wrap:wrap}
        .act-btn{font-size:11px;padding:5px 10px;border-radius:7px;cursor:pointer;font-family:inherit;border:0.5px solid #c2c0b6;background:#fff;color:#1a1a18;white-space:nowrap}
        .act-btn:hover{background:#f5f4f0}
        .act-btn.send{background:#185FA5;color:#fff;border-color:#185FA5}
        .act-btn.send:hover{background:#0C447C}
        .act-btn.resend{background:#854F0B;color:#fff;border-color:#854F0B}
        .act-btn.resend:hover{background:#633806}

        /* Expand detail */
        .book-detail{display:none;padding:12px 16px 14px 52px;border-top:0.5px solid rgba(0,0,0,0.07);background:#fafaf8}
        .book-detail.open{display:block}
        .detail-section{margin-bottom:12px}
        .detail-title{font-size:11px;font-weight:500;color:#73726c;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px}
        .detail-workers{display:flex;flex-wrap:wrap;gap:6px}
        .dw{display:flex;align-items:center;gap:6px;padding:5px 10px;background:#fff;border:0.5px solid rgba(0,0,0,0.1);border-radius:8px;font-size:12px}
        .dw-av{width:22px;height:22px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:600;background:#E6F1FB;color:#185FA5}
        .dw-status{font-size:10px;margin-left:4px}
        .dw-ok{color:#3B6D11}
        .dw-fail{color:#A32D2D}
        .dw-pending{color:#854F0B}
        .notif-log{font-size:12px;color:#73726c;background:#f5f4f0;border-radius:6px;padding:8px 10px;font-family:'Courier New',monospace}

        .empty-state{text-align:center;padding:48px 20px;color:#73726c}
        .empty-state h3{font-size:15px;font-weight:500;color:#1a1a18;margin-bottom:6px}
        .empty-state p{font-size:13px}

        @media(max-width:800px){
            .bt-head,.book-main{grid-template-columns:30px 1fr 100px 100px}
            .bt-th:nth-child(5),.bt-th:nth-child(6),
            .book-sent-info,.book-actions:not(:last-child){display:none}
            .stats-row{flex-wrap:wrap}
            .stat{min-width:calc(50% - 5px)}
        }
    </style>
</head>
<body>

<div class="topbar">
    <div class="logo">IMS</div>
    <div class="topbar-nav"><span>Notifications</span></div>
    <div class="topbar-right">
        <span class="topbar-user">{{ Auth::user()->email }}</span>
        <form method="POST" action="{{ route('logout') }}" style="display:inline">
            @csrf
            <button type="submit" class="topbar-btn">Sign out</button>
        </form>
    </div>
</div>

@include('layouts._nav')

{{-- Date bar --}}
@php $parsedDate = \Carbon\Carbon::parse($date); @endphp
<div class="date-bar">
    <a href="{{ route('notifications.index', ['date' => $parsedDate->copy()->subDay()->toDateString()]) }}" class="nav-btn">&#8249;</a>
    <div class="date-label">{{ $parsedDate->format('l, d M Y') }}</div>
    @if($date === today()->toDateString())
        <span class="date-sub">Today</span>
    @endif
    <a href="{{ route('notifications.index', ['date' => $parsedDate->copy()->addDay()->toDateString()]) }}" class="nav-btn">&#8250;</a>
    @if($date !== today()->toDateString())
        <a href="{{ route('notifications.index') }}" class="today-btn">Today</a>
    @endif
    <form method="GET" action="{{ route('notifications.index') }}">
        <input type="date" class="date-input" name="date" value="{{ $date }}" onchange="this.form.submit()">
    </form>
</div>

<div class="page">

    @if(session('success'))
        <div class="flash flash-success">✓ {{ session('success') }}</div>
    @endif
    @if(session('info'))
        <div class="flash flash-info">ℹ {{ session('info') }}</div>
    @endif

    {{-- Stats --}}
    <div class="stats-row">
        <div class="stat">
            <div class="stat-label">Total books</div>
            <div class="stat-value">{{ $books->count() }}</div>
        </div>
        <div class="stat">
            <div class="stat-label">Not notified</div>
            <div class="stat-value" style="color:#854F0B">{{ $pendingCount }}</div>
        </div>
        <div class="stat">
            <div class="stat-label">Notified</div>
            <div class="stat-value" style="color:#3B6D11">{{ $notifiedCount }}</div>
        </div>
        <div class="stat">
            <div class="stat-label">Workers to notify</div>
            <div class="stat-value" style="color:#185FA5">{{ $totalWorkers }}</div>
        </div>
    </div>

    {{-- Bulk bar --}}
    <div class="bulk-bar" id="bulk-bar">
        <span class="bulk-label" id="bulk-label">0 selected</span>
        <button class="bbtn" onclick="clearSel()">Cancel</button>
        <form method="POST" action="{{ route('books.notify-selected') }}" id="bulk-form">
            @csrf
            <input type="hidden" name="date" value="{{ $date }}">
            <div id="bulk-ids"></div>
            <button type="submit" class="bbtn green">Send notifications to selected</button>
        </form>
    </div>

    {{-- Toolbar --}}
    <div class="toolbar">
        <div class="chips">
            <span class="chip active" id="c-all" onclick="chipFilter('all',this)">All</span>
            <span class="chip cn" id="c-pending" onclick="chipFilter('pending',this)">Not notified</span>
            <span class="chip cs" id="c-sent" onclick="chipFilter('sent',this)">Notified</span>
        </div>
        @if($pendingCount > 0)
        <form method="POST" action="{{ route('books.notify-all') }}" style="margin-left:auto">
            @csrf
            <input type="hidden" name="date" value="{{ $date }}">
            <button type="submit" class="send-all-btn">
                Send all ({{ $pendingCount }} pending)
            </button>
        </form>
        @else
        <span style="margin-left:auto;font-size:12px;color:#3B6D11;padding:7px 0">✓ All books notified for this day</span>
        @endif
    </div>

    {{-- Books table --}}
    @if($books->isEmpty())
        <div class="empty-state">
            <h3>No books for {{ $parsedDate->format('d M Y') }}</h3>
            <p>Create a book from the dashboard to schedule workers for this day.</p>
        </div>
    @else
    <div class="books-table">
        <div class="bt-head">
            <div class="bt-th">
                <input type="checkbox" class="cb" id="select-all" onchange="selectAll(this)">
            </div>
            <div class="bt-th">Jobs & containers</div>
            <div class="bt-th">Workers</div>
            <div class="bt-th">Status</div>
            <div class="bt-th">Sent info</div>
            <div class="bt-th">Actions</div>
        </div>

        @foreach($books as $book)
        @php
            $isPending = is_null($book->notified_at);
            $statusClass = $isPending ? 'sp-pending' : 'sp-sent';
            $statusLabel = $isPending ? 'Not notified' : 'Notified';
        @endphp
        <div class="book-row" data-status="{{ $isPending ? 'pending' : 'sent' }}" id="book-row-{{ $book->id }}">
            <div class="book-main" onclick="toggleDetail({{ $book->id }})">
                <div class="book-check" onclick="event.stopPropagation()">
                    <input type="checkbox"
                           class="cb book-cb"
                           data-id="{{ $book->id }}"
                           data-pending="{{ $isPending ? '1' : '0' }}"
                           {{ !$isPending ? 'disabled' : '' }}
                           onchange="updateBulk()"
                           onclick="event.stopPropagation()">
                </div>

                {{-- Jobs --}}
                <div class="book-jobs">
                    @foreach($book->jobs as $job)
                    <div class="job-line">
                        <span class="job-time">{{ \Carbon\Carbon::parse($job->start_time)->format('H:i') }}</span>
                        <span class="job-client">{{ $job->site->client->name }}</span>
                        <span class="job-site">· {{ $job->site->name }}</span>
                        @if($job->containers->count() > 0)
                            <span class="job-containers">{{ $job->containers->count() }} container{{ $job->containers->count() !== 1 ? 's' : '' }}</span>
                        @endif
                    </div>
                    @endforeach
                </div>

                {{-- Workers --}}
                <div class="book-workers">
                    <div class="worker-pills">
                        @foreach($book->workers->take(3) as $worker)
                            <span class="wp">{{ collect(explode(' ',$worker->name))->map(fn($p)=>ucfirst($p))->first() }}</span>
                        @endforeach
                        @if($book->workers->count() > 3)
                            <span class="wp">+{{ $book->workers->count() - 3 }}</span>
                        @endif
                    </div>
                </div>

                {{-- Status --}}
                <div class="book-status">
                    <span class="status-pill {{ $statusClass }}">{{ $statusLabel }}</span>
                </div>

                {{-- Sent info --}}
                <div class="book-sent-info">
                    @if(!$isPending)
                        <strong>{{ $book->notifiedBy?->email ?? '—' }}</strong>
                        {{ $book->notified_at->format('d M · H:i') }}
                    @else
                        <span style="color:#c2c0b6;font-style:italic">Not sent yet</span>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="book-actions" onclick="event.stopPropagation()">
                    @if($isPending)
                        <form method="POST" action="{{ route('books.notify', $book) }}">
                            @csrf
                            <button type="submit" class="act-btn send">Send</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('books.notify', $book) }}">
                            @csrf
                            <button type="submit" class="act-btn resend">Resend</button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Expanded detail --}}
            <div class="book-detail" id="detail-{{ $book->id }}">
                <div class="detail-section">
                    <div class="detail-title">Workers who will receive this notification</div>
                    <div class="detail-workers">
                        @foreach($book->workers as $worker)
                        @php $initials = collect(explode(' ',$worker->name))->map(fn($p)=>strtoupper(substr($p,0,1)))->take(2)->join(''); @endphp
                        <div class="dw">
                            <div class="dw-av">{{ $initials }}</div>
                            <span>{{ $worker->name }}</span>
                            <span class="dw-status {{ $isPending ? 'dw-pending' : 'dw-ok' }}">
                                {{ $isPending ? '· pending' : '· notified' }}
                            </span>
                            @if(!$worker->phone)
                                <span class="dw-status dw-fail">· no phone</span>
                            @endif
                            @if(!$worker->user?->fcm_token)
                                <span class="dw-status" style="color:#73726c">· no push token</span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>

                @if(!$isPending)
                <div class="detail-section">
                    <div class="detail-title">Notification log</div>
                    <div class="notif-log">
                        Sent by {{ $book->notifiedBy?->email ?? 'system' }}
                        at {{ $book->notified_at->format('d M Y H:i:s') }}
                        — {{ $book->workers->count() }} worker(s) targeted
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

</div>

<script>
// Toggle detail expand
function toggleDetail(id) {
    var d = document.getElementById('detail-' + id);
    if (d) d.classList.toggle('open');
}

// Bulk selection
function updateBulk() {
    var checked = document.querySelectorAll('.book-cb:checked');
    var bar = document.getElementById('bulk-bar');
    var label = document.getElementById('bulk-label');
    var idsDiv = document.getElementById('bulk-ids');

    label.textContent = checked.length + ' book' + (checked.length !== 1 ? 's' : '') + ' selected';
    bar.classList.toggle('on', checked.length > 0);

    // Update hidden inputs
    idsDiv.innerHTML = '';
    checked.forEach(function(cb) {
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'book_ids[]';
        input.value = cb.dataset.id;
        idsDiv.appendChild(input);
    });
}

function clearSel() {
    document.querySelectorAll('.book-cb').forEach(function(cb) { cb.checked = false; });
    document.getElementById('bulk-bar').classList.remove('on');
    document.getElementById('bulk-ids').innerHTML = '';
}

function selectAll(masterCb) {
    document.querySelectorAll('.book-cb:not(:disabled)').forEach(function(cb) {
        cb.checked = masterCb.checked;
    });
    updateBulk();
}

// Filter chips
var activeChip = 'all';
function chipFilter(type, el) {
    activeChip = type;
    document.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
    el.classList.add('active');
    document.querySelectorAll('.book-row').forEach(function(row) {
        var status = row.dataset.status;
        var show = type === 'all' || status === type;
        row.style.display = show ? '' : 'none';
    });
}
</script>
</body>
</html>
