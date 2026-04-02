<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMS — Day Planning</title>
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

        .page{padding:20px;display:grid;grid-template-columns:1fr 320px;gap:16px;align-items:start;max-width:1200px;margin:0 auto}

        .flash{padding:10px 16px;border-radius:8px;margin-bottom:14px;font-size:13px}
        .flash-success{background:#EAF3DE;border:0.5px solid #C0DD97;color:#27500A}

        /* Date group headers */
        .date-group{margin-bottom:24px}
        .date-group-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;padding-bottom:8px;border-bottom:0.5px solid rgba(0,0,0,0.1)}
        .date-group-label{font-size:13px;font-weight:500;color:#1a1a18;display:flex;align-items:center;gap:8px}
        .date-group-sub{font-size:11px;color:#73726c;font-weight:400}
        .date-group-stats{font-size:12px;color:#73726c;display:flex;gap:12px}
        .date-stat-pending{color:#854F0B;font-weight:500}

        /* Demand cards */
        .demands-list{display:flex;flex-direction:column;gap:8px}
        .demand-card{background:#fff;border:0.5px solid rgba(0,0,0,0.1);border-radius:12px;overflow:hidden;transition:border-color .15s}
        .demand-card.status-pending{border-left:3px solid #FAC775;border-radius:0 12px 12px 0}
        .demand-card.status-partial{border-left:3px solid #85B7EB;border-radius:0 12px 12px 0}
        .demand-card.status-allocated{opacity:0.6}
        .demand-card.status-cancelled{opacity:0.4}
        .demand-main{display:grid;grid-template-columns:1fr 160px 130px 150px;gap:12px;padding:13px 16px;align-items:center}
        .demand-info{}
        .demand-client{font-size:14px;font-weight:500;margin-bottom:3px;display:flex;align-items:center;gap:8px;flex-wrap:wrap}
        .demand-site{font-size:12px;color:#73726c}
        .demand-product{font-size:11px;color:#3C3489;background:#EEEDFE;padding:2px 8px;border-radius:10px;font-weight:500}
        .demand-containers{display:flex;gap:6px;flex-wrap:wrap}
        .container-badge{font-size:12px;font-weight:500;padding:4px 10px;border-radius:8px;white-space:nowrap}
        .cb-40{background:#EAF3DE;color:#3B6D11}
        .cb-20{background:#E6F1FB;color:#185FA5}
        .cb-worker{background:#EEEDFE;color:#3C3489}
        .demand-status{}
        .status-pill{font-size:11px;padding:4px 10px;border-radius:10px;font-weight:500;white-space:nowrap;display:inline-block}
        .sp-pending{background:#FAEEDA;color:#854F0B}
        .sp-partial{background:#E6F1FB;color:#185FA5}
        .sp-allocated{background:#EAF3DE;color:#3B6D11}
        .sp-cancelled{background:#F1EFE8;color:#5F5E5A}
        .crews-label{font-size:11px;color:#73726c;margin-top:4px}
        .demand-actions{display:flex;gap:6px;justify-content:flex-end;flex-wrap:wrap}
        .act-btn{font-size:11px;padding:5px 10px;border-radius:7px;cursor:pointer;font-family:inherit;border:0.5px solid #c2c0b6;background:#fff;color:#1a1a18;white-space:nowrap;text-decoration:none;display:inline-block}
        .act-btn:hover{background:#f5f4f0}
        .act-btn.primary{background:#185FA5;color:#fff;border-color:#185FA5}
        .act-btn.primary:hover{background:#0C447C}
        .act-btn.success{background:#3B6D11;color:#fff;border-color:#3B6D11}
        .act-btn.success:hover{background:#27500A}
        .act-btn.danger{color:#A32D2D;border-color:#F09595}
        .act-btn.danger:hover{background:#FCEBEB}
        .demand-notes{padding:8px 16px 12px;border-top:0.5px solid rgba(0,0,0,0.07);background:#fafaf8;font-size:12px;color:#73726c;font-style:italic}

        /* Linked books */
        .linked-books{padding:10px 16px 12px;border-top:0.5px solid rgba(0,0,0,0.07);background:#fafaf8}
        .lb-title{font-size:11px;font-weight:500;color:#73726c;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px}
        .lb-item{display:flex;align-items:center;gap:8px;padding:6px 10px;background:#fff;border:0.5px solid rgba(0,0,0,0.1);border-radius:8px;margin-bottom:5px;font-size:12px}
        .lb-time{font-weight:500;min-width:40px}
        .lb-workers{display:flex;gap:4px;flex-wrap:wrap;flex:1}
        .lb-worker-pill{font-size:11px;background:#E6F1FB;color:#185FA5;padding:1px 7px;border-radius:10px}
        .lb-status{font-size:10px;padding:2px 8px;border-radius:10px}
        .lb-link{font-size:11px;color:#185FA5;text-decoration:none;white-space:nowrap}

        /* Empty state */
        .empty-state{text-align:center;padding:40px 20px;background:#fff;border:0.5px solid rgba(0,0,0,0.1);border-radius:12px;color:#73726c}
        .empty-state h3{font-size:15px;font-weight:500;color:#1a1a18;margin-bottom:6px}
        .empty-state p{font-size:13px}

        /* Add form sidebar */
        .add-form{background:#fff;border:0.5px solid rgba(0,0,0,0.1);border-radius:12px;padding:18px;margin-bottom:12px;position:sticky;top:20px}
        .form-title{font-size:14px;font-weight:500;margin-bottom:14px;padding-bottom:10px;border-bottom:0.5px solid rgba(0,0,0,0.08)}
        .field{display:flex;flex-direction:column;gap:4px;margin-bottom:10px}
        .field label{font-size:11px;color:#73726c}
        .field input,.field select,.field textarea{font-size:13px;padding:7px 10px;border:0.5px solid #c2c0b6;border-radius:8px;background:#fff;color:#1a1a18;font-family:inherit;width:100%}
        .field input:focus,.field select:focus,.field textarea:focus{outline:none;border-color:#185FA5}
        .field textarea{resize:none;height:52px}
        .qty-row{display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px}
        .section-label{font-size:11px;font-weight:500;color:#73726c;text-transform:uppercase;letter-spacing:0.6px;margin-bottom:6px;margin-top:12px}
        .btn-primary{font-size:13px;padding:9px 14px;border-radius:8px;background:#185FA5;color:#fff;border:none;cursor:pointer;font-family:inherit;width:100%}
        .btn-primary:hover{background:#0C447C}

        /* Summary box */
        .summary-box{background:#fff;border:0.5px solid rgba(0,0,0,0.1);border-radius:12px;padding:16px}
        .summary-box-title{font-size:11px;font-weight:500;color:#73726c;text-transform:uppercase;letter-spacing:0.6px;margin-bottom:10px}
        .sum-row{display:flex;justify-content:space-between;font-size:13px;padding:5px 0;border-bottom:0.5px solid rgba(0,0,0,0.07)}
        .sum-row:last-child{border-bottom:none}
        .sum-label{color:#73726c}

        @media(max-width:800px){
            .page{grid-template-columns:1fr}
            .demand-main{grid-template-columns:1fr 100px}
            .demand-containers,.demand-actions{display:none}
        }
    </style>
</head>
<body>

<div class="topbar">
    <div class="logo">IMS</div>
    <div class="topbar-nav"><span>Day Planning</span></div>
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
    <div>
        @if(session('success'))
            <div class="flash flash-success">✓ {{ session('success') }}</div>
        @endif

        @if($demands->isEmpty())
            <div class="empty-state">
                <h3>No demands registered yet</h3>
                <p>Add the demands you received from clients — containers, service type and the date needed.</p>
            </div>
        @else

        {{-- Group demands by date --}}
        @foreach($demands->groupBy(fn($d) => $d->date->toDateString()) as $groupDate => $groupDemands)
        @php
            $parsedGroupDate = \Carbon\Carbon::parse($groupDate);
            $pendingInGroup = $groupDemands->whereIn('status', ['pending','partial'])->count();
        @endphp
        <div class="date-group">
            <div class="date-group-header">
                <div class="date-group-label">
                    {{ $parsedGroupDate->format('l, d M Y') }}
                    @if($groupDate === today()->toDateString())
                        <span style="font-size:10px;background:#185FA5;color:#fff;padding:2px 8px;border-radius:10px">Today</span>
                    @elseif($groupDate === today()->addDay()->toDateString())
                        <span style="font-size:10px;background:#EAF3DE;color:#3B6D11;padding:2px 8px;border-radius:10px">Tomorrow</span>
                    @endif
                </div>
                <div class="date-group-stats">
                    @if($pendingInGroup > 0)
                        <span class="date-stat-pending">{{ $pendingInGroup }} pending crew</span>
                    @else
                        <span style="color:#3B6D11">✓ All allocated</span>
                    @endif
                    <span>{{ $groupDemands->count() }} demand{{ $groupDemands->count() > 1 ? 's' : '' }}</span>
                </div>
            </div>

            <div class="demands-list">
            @foreach($groupDemands as $demand)
            @php
                $statusLabels  = ['pending'=>'Pending','partial'=>'Partial','allocated'=>'Allocated','cancelled'=>'Cancelled'];
                $statusClasses = ['pending'=>'sp-pending','partial'=>'sp-partial','allocated'=>'sp-allocated','cancelled'=>'sp-cancelled'];
            @endphp
            <div class="demand-card status-{{ $demand->status }}">
                <div class="demand-main">
                    <div class="demand-info">
                        <div class="demand-client">
                            {{ $demand->client->name }}
                            @if($demand->product)
                                <span class="demand-product">{{ $demand->product->name }}</span>
                            @endif
                        </div>
                        <div class="demand-site">{{ $demand->site->name }}</div>
                    </div>
                    <div class="demand-containers">
                        @if($demand->qty_40ft > 0)<span class="container-badge cb-40">{{ $demand->qty_40ft }}× 40ft</span>@endif
                        @if($demand->qty_20ft > 0)<span class="container-badge cb-20">{{ $demand->qty_20ft }}× 20ft</span>@endif
                        @if($demand->qty_workers > 0)<span class="container-badge cb-worker">{{ $demand->qty_workers }} worker{{ $demand->qty_workers > 1 ? 's' : '' }}</span>@endif
                    </div>
                    <div class="demand-status">
                        <span class="status-pill {{ $statusClasses[$demand->status] }}">{{ $statusLabels[$demand->status] }}</span>
                        @if($demand->crews_allocated > 0)
                            <div class="crews-label">{{ $demand->crews_allocated }} crew{{ $demand->crews_allocated > 1 ? 's' : '' }} allocated</div>
                        @endif
                    </div>
                    <div class="demand-actions">
                        @if(!in_array($demand->status, ['cancelled','allocated']))
                            <a href="{{ route('books.create', ['demand_id' => $demand->id, 'date' => $demand->date->toDateString()]) }}"
                               class="act-btn primary">+ Allocate crew</a>
                        @endif
                        @if($demand->status === 'partial')
                            <form method="POST" action="{{ route('planning.mark-allocated', $demand) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="act-btn success">Mark done</button>
                            </form>
                        @endif
                        @if($demand->status !== 'cancelled')
                            <form method="POST" action="{{ route('planning.cancel', $demand) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="act-btn danger"
                                    onclick="return confirm('Cancel this demand?')">Cancel</button>
                            </form>
                        @endif
                    </div>
                </div>

                @if($demand->notes)
                    <div class="demand-notes">Note: {{ $demand->notes }}</div>
                @endif

                {{-- Linked books --}}
                @if($demand->books->count() > 0)
                <div class="linked-books">
                    <div class="lb-title">Crews allocated — {{ $demand->books->count() }} book{{ $demand->books->count() > 1 ? 's' : '' }}</div>
                    @foreach($demand->books as $book)
                    @php $bs = $book->status->value; @endphp
                    <div class="lb-item">
                        <span class="lb-time">{{ $book->jobs->first() ? \Carbon\Carbon::parse($book->jobs->first()->start_time)->format('H:i') : '—' }}</span>
                        <div class="lb-workers">
                            @foreach($book->workers->take(4) as $worker)
                                @php $ini = collect(explode(' ',$worker->name))->map(fn($p)=>strtoupper(substr($p,0,1)))->take(2)->join(''); @endphp
                                <span class="lb-worker-pill">{{ $ini }} — {{ collect(explode(' ',$worker->name))->first() }}</span>
                            @endforeach
                            @if($book->workers->count() > 4)
                                <span style="font-size:11px;color:#73726c">+{{ $book->workers->count() - 4 }}</span>
                            @endif
                        </div>
                        <span class="lb-status" style="background:{{ $bs==='completed'?'#EAF3DE':($bs==='cancelled'?'#F1EFE8':'#E6F1FB') }};color:{{ $bs==='completed'?'#3B6D11':($bs==='cancelled'?'#5F5E5A':'#185FA5') }}">
                            {{ ucfirst(str_replace('_',' ',$bs)) }}
                        </span>
                        <a href="{{ route('dashboard', ['date' => $book->date->toDateString()]) }}" class="lb-link">View →</a>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            @endforeach
            </div>
        </div>
        @endforeach

        @endif
    </div>

    {{-- Add demand form --}}
    <div>
        <div class="add-form">
            <div class="form-title">Add demand</div>
            <form method="POST" action="{{ route('planning.store') }}">
                @csrf

                <div class="field">
                    <label>Date needed *</label>
                    <input type="date" name="date"
                           value="{{ old('date', $defaultDate) }}"
                           min="{{ today()->toDateString() }}"
                           required>
                </div>

                <div class="field">
                    <label>Client *</label>
                    <select name="client_id" id="client-select" required onchange="updateSites(this)">
                        <option value="">Select client...</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}"
                                    data-sites="{{ $client->sites->map(fn($s)=>['id'=>$s->id,'name'=>$s->name])->toJson() }}">
                                {{ $client->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label>Site *</label>
                    <select name="site_id" id="site-select" required>
                        <option value="">Select client first...</option>
                    </select>
                </div>

                <div class="field">
                    <label>Service / Product</label>
                    <select name="product_id">
                        <option value="">Not specified</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="section-label">Containers / Workers</div>
                <div class="qty-row">
                    <div class="field">
                        <label>40ft</label>
                        <input type="number" name="qty_40ft" min="0" value="{{ old('qty_40ft', 0) }}" placeholder="0">
                    </div>
                    <div class="field">
                        <label>20ft</label>
                        <input type="number" name="qty_20ft" min="0" value="{{ old('qty_20ft', 0) }}" placeholder="0">
                    </div>
                    <div class="field">
                        <label>Workers</label>
                        <input type="number" name="qty_workers" min="0" value="{{ old('qty_workers', 0) }}" placeholder="0">
                    </div>
                </div>

                <div class="field">
                    <label>Notes (optional)</label>
                    <textarea name="notes" placeholder="e.g. client confirmed 3×40ft, may change...">{{ old('notes') }}</textarea>
                </div>

                <button type="submit" class="btn-primary">Add demand</button>
            </form>
        </div>

        {{-- Overall summary --}}
        <div class="summary-box">
            <div class="summary-box-title">Overall pending</div>
            @php
                $allPending = $demands->whereIn('status',['pending','partial']);
                $total40    = $allPending->sum('qty_40ft');
                $total20    = $allPending->sum('qty_20ft');
                $totalW     = $allPending->sum('qty_workers');
            @endphp
            <div class="sum-row"><span class="sum-label">40ft containers</span><strong>{{ $total40 }}</strong></div>
            <div class="sum-row"><span class="sum-label">20ft containers</span><strong>{{ $total20 }}</strong></div>
            <div class="sum-row"><span class="sum-label">Workers requested</span><strong>{{ $totalW }}</strong></div>
            <div class="sum-row"><span class="sum-label">Demands needing crew</span>
                <strong style="color:{{ $allPending->count() > 0 ? '#854F0B' : '#3B6D11' }}">{{ $allPending->count() }}</strong>
            </div>
        </div>
    </div>
</div>

<script>
function updateSites(clientSelect) {
    var siteSelect = document.getElementById('site-select');
    var option = clientSelect.options[clientSelect.selectedIndex];
    siteSelect.innerHTML = '<option value="">Select site...</option>';
    if (!option.dataset.sites) return;
    var sites = JSON.parse(option.dataset.sites);
    sites.forEach(function(site) {
        var opt = document.createElement('option');
        opt.value = site.id;
        opt.textContent = site.name;
        siteSelect.appendChild(opt);
    });
    if (sites.length === 1) siteSelect.value = sites[0].id;
}
</script>
</body>
</html>
