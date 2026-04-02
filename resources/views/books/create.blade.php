<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMS — Create Book</title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0;font-family:system-ui,sans-serif}
        body{background:#f5f4f0;color:#1a1a18}
        .topbar{background:#185FA5;padding:10px 20px;display:flex;align-items:center;gap:12px;position:sticky;top:0;z-index:100}
        .logo{background:#fff;color:#185FA5;font-size:11px;font-weight:700;padding:3px 10px;border-radius:6px}
        .topbar-nav{font-size:13px;color:rgba(255,255,255,0.7);margin-left:8px;display:flex;align-items:center;gap:6px}
        .topbar-nav a{color:rgba(255,255,255,0.65);text-decoration:none}.topbar-nav a:hover{color:#fff}
        .topbar-nav span{color:#fff}
        .topbar-right{margin-left:auto;display:flex;align-items:center;gap:12px}
        .topbar-user{font-size:12px;color:rgba(255,255,255,0.75)}
        .topbar-btn{font-size:12px;color:rgba(255,255,255,0.8);background:rgba(255,255,255,0.15);border:none;border-radius:6px;padding:5px 12px;cursor:pointer;font-family:inherit;text-decoration:none;display:inline-block}
        .topbar-btn:hover{background:rgba(255,255,255,0.25);color:#fff}
        .page{padding:20px;display:grid;grid-template-columns:1fr 290px;gap:16px;align-items:start;max-width:1200px;margin:0 auto}
        .card{background:#fff;border:0.5px solid rgba(0,0,0,0.1);border-radius:12px;padding:18px;margin-bottom:14px}
        .card-title{font-size:14px;font-weight:500;margin-bottom:14px;padding-bottom:10px;border-bottom:0.5px solid rgba(0,0,0,0.08);display:flex;align-items:center;justify-content:space-between}
        .grid-2{display:grid;grid-template-columns:1fr 1fr;gap:10px}
        .grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px}
        .field{display:flex;flex-direction:column;gap:4px}
        .field label{font-size:11px;color:#73726c}
        .field input,.field select{font-size:13px;padding:7px 10px;border:0.5px solid #c2c0b6;border-radius:8px;background:#fff;color:#1a1a18;font-family:inherit;width:100%}
        .field input:focus,.field select:focus{outline:none;border-color:#185FA5}
        .filter-chips{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:10px}
        .fchip{font-size:12px;padding:4px 12px;border:0.5px solid #c2c0b6;border-radius:20px;cursor:pointer;background:#fff;color:#73726c;white-space:nowrap}
        .fchip:hover{border-color:#185FA5;color:#185FA5}
        .fchip.active{background:#E6F1FB;color:#185FA5;border-color:#85B7EB}
        .workers-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:8px}
        .worker-chip{border:0.5px solid rgba(0,0,0,0.1);border-radius:10px;padding:10px 12px;cursor:pointer;transition:all .15s;background:#fff}
        .worker-chip:hover{border-color:#185FA5}
        .worker-chip.selected{border-color:#185FA5;background:#E6F1FB}
        .worker-chip.has-warn{border-color:#FAC775}
        .worker-chip.has-danger{border-color:#F09595}
        .wc-top{display:flex;align-items:center;gap:8px;margin-bottom:5px}
        .wc-av{width:26px;height:26px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:600;flex-shrink:0;background:#E6F1FB;color:#185FA5}
        .wc-name{font-size:13px;font-weight:500;flex:1}
        .wc-check{width:15px;height:15px;border:0.5px solid #c2c0b6;border-radius:4px;flex-shrink:0;transition:all .15s;display:flex;align-items:center;justify-content:center}
        .worker-chip.selected .wc-check{background:#185FA5;border-color:#185FA5}
        .wc-tags{display:flex;gap:4px;flex-wrap:wrap;margin-bottom:4px}
        .tag{font-size:10px;padding:2px 7px;border-radius:10px}
        .tag-lf{background:#EEEDFE;color:#3C3489}
        .tag-min{background:#EEEDFE;color:#3C3489}
        .wc-alerts{display:flex;flex-direction:column;gap:3px;margin-top:3px}
        .alert-item{font-size:11px;padding:3px 7px;border-radius:5px;line-height:1.4}
        .alert-warn{background:#FAEEDA;color:#854F0B}
        .alert-danger{background:#FCEBEB;color:#A32D2D}
        .job-block{border:0.5px solid rgba(0,0,0,0.1);border-radius:12px;margin-bottom:10px;overflow:hidden}
        .job-block-header{display:flex;align-items:center;justify-content:space-between;padding:12px 14px;background:#f5f4f0;border-bottom:0.5px solid rgba(0,0,0,0.08)}
        .job-block-title{font-size:13px;font-weight:500}
        .job-block-body{padding:14px}
        .remove-job-btn{font-size:11px;color:#A32D2D;background:none;border:none;cursor:pointer;padding:3px 8px;border-radius:6px;font-family:inherit}
        .remove-job-btn:hover{background:#FCEBEB}
        .containers-section{margin-top:14px}
        .containers-list{display:flex;flex-direction:column;gap:6px;margin-bottom:6px}
        .container-row{display:grid;grid-template-columns:1fr 100px 28px;gap:8px;align-items:center}
        .container-row input,.container-row select{font-size:13px;padding:6px 9px;border:0.5px solid #c2c0b6;border-radius:8px;font-family:inherit;width:100%;background:#fff;color:#1a1a18}
        .container-row input:focus,.container-row select:focus{outline:none;border-color:#185FA5}
        .remove-container-btn{background:none;border:none;cursor:pointer;color:#73726c;font-size:18px;line-height:1;padding:0;width:28px;height:28px;display:flex;align-items:center;justify-content:center;border-radius:6px}
        .remove-container-btn:hover{background:#FCEBEB;color:#A32D2D}
        .add-container-btn{display:flex;align-items:center;gap:5px;font-size:12px;color:#185FA5;background:none;border:0.5px dashed #85B7EB;border-radius:8px;padding:6px 12px;cursor:pointer;font-family:inherit;width:100%;justify-content:center}
        .add-container-btn:hover{background:#E6F1FB}
        .containers-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:6px}
        .containers-label{font-size:11px;font-weight:500;color:#73726c;text-transform:uppercase;letter-spacing:0.6px}
        .containers-count{font-size:11px;color:#73726c}
        .container-col-labels{display:grid;grid-template-columns:1fr 100px 28px;gap:8px;margin-bottom:4px}
        .container-col-label{font-size:10px;color:#73726c}
        .add-job-btn{display:flex;align-items:center;justify-content:center;gap:6px;font-size:13px;color:#185FA5;background:none;border:0.5px dashed #85B7EB;border-radius:10px;padding:10px 14px;width:100%;font-family:inherit;cursor:pointer}
        .add-job-btn:hover{background:#E6F1FB}
        .summary-card{background:#fff;border:0.5px solid rgba(0,0,0,0.1);border-radius:12px;padding:16px;margin-bottom:12px}
        .summary-title{font-size:11px;font-weight:500;color:#73726c;text-transform:uppercase;letter-spacing:0.6px;margin-bottom:10px}
        .summary-worker{display:flex;align-items:center;gap:8px;padding:5px 0;border-bottom:0.5px solid rgba(0,0,0,0.06)}
        .summary-worker:last-child{border-bottom:none}
        .sw-av{width:22px;height:22px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:600;background:#E6F1FB;color:#185FA5;flex-shrink:0}
        .sw-name{font-size:12px;flex:1}
        .sw-remove{background:none;border:none;cursor:pointer;color:#73726c;font-size:16px;padding:0 2px;line-height:1}
        .sw-remove:hover{color:#A32D2D}
        .empty-workers{font-size:12px;color:#73726c;text-align:center;padding:12px 0;font-style:italic}
        .notify-box{background:#EAF3DE;border:0.5px solid #C0DD97;border-radius:8px;padding:10px 12px;font-size:12px;color:#27500A;line-height:1.5}
        .notify-title{font-weight:500;margin-bottom:3px}
        .btn-primary{font-size:13px;padding:10px 18px;border-radius:8px;cursor:pointer;font-family:inherit;background:#185FA5;color:#fff;border:none;width:100%;margin-bottom:8px}
        .btn-primary:hover{background:#0C447C}
        .btn-ghost{font-size:13px;padding:9px 18px;border-radius:8px;cursor:pointer;font-family:inherit;background:#fff;color:#73726c;border:0.5px solid #c2c0b6;width:100%;text-align:center;text-decoration:none;display:block}
        .btn-ghost:hover{background:#f5f4f0}
        .summary-stat{display:flex;justify-content:space-between;font-size:12px;padding:5px 0;border-bottom:0.5px solid rgba(0,0,0,0.06)}
        .summary-stat:last-child{border-bottom:none}
        .summary-stat-label{color:#73726c}
        .summary-stat-val{font-weight:500}
        @media(max-width:800px){.page{grid-template-columns:1fr}.grid-3{grid-template-columns:1fr 1fr}}
    </style>
</head>
<body>

<div class="topbar">
    <div class="logo">IMS</div>
    <div class="topbar-nav">
        @if($demand)
            <a href="{{ route('planning.index', ['date' => $demand->date->toDateString()]) }}">Day Planning</a>
        @else
            <a href="{{ route('dashboard') }}">Dashboard</a>
        @endif
        <span>/</span>
        <span>New book</span>
    </div>
    <div class="topbar-right">
        <span class="topbar-user">{{ Auth::user()->email }}</span>
        <form method="POST" action="{{ route('logout') }}" style="display:inline">
            @csrf
            <button type="submit" class="topbar-btn">Sign out</button>
        </form>
    </div>
</div>

<form method="POST" action="{{ route('books.store') }}" id="book-form">
@csrf
<input type="hidden" name="demand_id" value="{{ $demand?->id }}">

<div class="page">
    <div>

        {{-- Demand banner --}}
        @if($demand)
        <div style="background:#E6F1FB;border:0.5px solid #B5D4F4;border-radius:12px;padding:14px 16px;margin-bottom:14px">
            <div style="font-size:12px;font-weight:500;color:#185FA5;margin-bottom:6px">
                Allocating crew for demand from Day Planning
            </div>
            <div style="font-size:14px;font-weight:500;color:#1a1a18;margin-bottom:6px">
                {{ $demand->client->name }} — {{ $demand->site->name }}
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
                @if($demand->qty_40ft > 0)
                    <span style="font-size:12px;background:#EAF3DE;color:#3B6D11;padding:2px 9px;border-radius:10px;font-weight:500">{{ $demand->qty_40ft }}× 40ft</span>
                @endif
                @if($demand->qty_20ft > 0)
                    <span style="font-size:12px;background:#E6F1FB;color:#185FA5;padding:2px 9px;border-radius:10px;font-weight:500">{{ $demand->qty_20ft }}× 20ft</span>
                @endif
                @if($demand->qty_workers > 0)
                    <span style="font-size:12px;background:#EEEDFE;color:#3C3489;padding:2px 9px;border-radius:10px;font-weight:500">{{ $demand->qty_workers }} worker{{ $demand->qty_workers > 1 ? 's' : '' }}</span>
                @endif
                @if($demand->product)
                    <span style="font-size:12px;color:#73726c">· {{ $demand->product->name }}</span>
                @endif
                @if($demand->crews_allocated > 0)
                    <span style="font-size:12px;color:#73726c">· {{ $demand->crews_allocated }} crew{{ $demand->crews_allocated > 1 ? 's' : '' }} already allocated</span>
                @endif
            </div>
            @if($demand->notes)
                <div style="font-size:12px;color:#73726c;margin-top:6px;font-style:italic">Note: {{ $demand->notes }}</div>
            @endif
        </div>
        @endif

        {{-- Book details --}}
        <div class="card">
            <div class="card-title">Book details</div>
            <div class="grid-2">
                <div class="field">
                    <label>Date *</label>
                    <input type="date" name="date" id="book-date"
                           value="{{ old('date', $demand?->date->toDateString() ?? today()->toDateString()) }}"
                           required onchange="refreshAlerts()">
                </div>
                <div class="field">
                    <label>Notes</label>
                    <input type="text" name="notes" placeholder="Optional notes...">
                </div>
            </div>
        </div>

        {{-- Workers --}}
        <div class="card">
            <div class="card-title">
                <span>Workers</span>
                <span id="worker-count-label" style="font-size:12px;color:#73726c;font-weight:400">0 selected</span>
            </div>
            <div class="filter-chips">
                <span class="fchip active" onclick="filterWorkers('all',this)">All</span>
                <span class="fchip" onclick="filterWorkers('forklift',this)">Forklift LF</span>
                <span class="fchip" onclick="filterWorkers('min',this)">Below minimum</span>
            </div>
            <div class="workers-grid" id="workers-grid">
                @foreach($workers as $worker)
                @php $initials = collect(explode(' ',$worker->name))->map(fn($p)=>strtoupper(substr($p,0,1)))->take(2)->join(''); @endphp
                <div class="worker-chip"
                     id="wchip-{{ $worker->id }}"
                     data-id="{{ $worker->id }}"
                     data-name="{{ $worker->name }}"
                     data-initials="{{ $initials }}"
                     data-forklift="{{ $worker->has_forklift ? '1' : '0' }}"
                     data-min="{{ $worker->min_weekly ? '1' : '0' }}"
                     onclick="toggleWorker({{ $worker->id }})">
                    <div class="wc-top">
                        <div class="wc-av">{{ $initials }}</div>
                        <div class="wc-name">{{ $worker->name }}</div>
                        <div class="wc-check" id="wcheck-{{ $worker->id }}">
                            <svg width="10" height="10" viewBox="0 0 10 10" fill="none" style="display:none" id="wcheck-icon-{{ $worker->id }}"><path d="M2 5l2.5 2.5 3.5-4" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </div>
                    </div>
                    <div class="wc-tags">
                        @if($worker->has_forklift)<span class="tag tag-lf">Driver LF</span>@endif
                        @if($worker->min_weekly)<span class="tag tag-min" id="min-tag-{{ $worker->id }}" style="display:none">Min pending</span>@endif
                    </div>
                    <div class="wc-alerts" id="walerts-{{ $worker->id }}"></div>
                    <input type="checkbox" name="worker_ids[]" value="{{ $worker->id }}" id="wcb-{{ $worker->id }}" style="display:none">
                </div>
                @endforeach
            </div>
        </div>

        {{-- Jobs --}}
        <div class="card">
            <div class="card-title">Jobs & containers</div>
            <div id="jobs-container"></div>
            <button type="button" class="add-job-btn" onclick="addJob()">
                <svg width="13" height="13" viewBox="0 0 12 12" fill="none"><path d="M6 2v8M2 6h8" stroke="#185FA5" stroke-width="1.5" stroke-linecap="round"/></svg>
                Add another job
            </button>
        </div>

    </div>

    {{-- Sidebar --}}
    <div>
        <div class="summary-card">
            <div class="summary-title">Summary</div>
            <div class="summary-stat"><span class="summary-stat-label">Workers selected</span><span class="summary-stat-val" id="sum-workers">0</span></div>
            <div class="summary-stat"><span class="summary-stat-label">Jobs</span><span class="summary-stat-val" id="sum-jobs">0</span></div>
            <div class="summary-stat"><span class="summary-stat-label">Containers</span><span class="summary-stat-val" id="sum-containers">0</span></div>
        </div>
        <div class="summary-card">
            <div class="summary-title">Workers</div>
            <div id="selected-workers-list"><div class="empty-workers">No workers selected</div></div>
        </div>
        <div class="summary-card">
            <div class="summary-title">Notifications</div>
            <div class="notify-box">
                <div class="notify-title">On save, workers will receive:</div>
                Push notification + SMS with book date and job details
            </div>
        </div>
        <button type="submit" class="btn-primary">Save book</button>
        @if($demand)
            <a href="{{ route('planning.index', ['date' => $demand->date->toDateString()]) }}" class="btn-ghost">← Back to planning</a>
        @else
            <a href="{{ route('dashboard') }}" class="btn-ghost">Cancel</a>
        @endif
    </div>
</div>
</form>

<script>
var selectedWorkers = {};
var jobCount = 0;
var alertsCache = {};

// Pre-fill data from demand (PHP → JS)
@php
    $demandJson = $demand ? json_encode([
        'site_id'  => $demand->site_id,
        'qty_40ft' => $demand->qty_40ft,
        'qty_20ft' => $demand->qty_20ft,
    ]) : 'null';
@endphp
var demandData = {!! $demandJson !!};

function toggleWorker(id) {
    var chip = document.getElementById('wchip-' + id);
    var cb   = document.getElementById('wcb-' + id);
    var icon = document.getElementById('wcheck-icon-' + id);
    if (selectedWorkers[id]) {
        delete selectedWorkers[id]; cb.checked = false;
        chip.classList.remove('selected');
        if (icon) icon.style.display = 'none';
    } else {
        selectedWorkers[id] = { id: id, name: chip.dataset.name, initials: chip.dataset.initials };
        cb.checked = true; chip.classList.add('selected');
        if (icon) icon.style.display = 'block';
        loadWorkerAlerts(id);
    }
    updateSelectedList(); updateAllTLSelects(); updateSummary();
}

function loadWorkerAlerts(workerId) {
    var date = document.getElementById('book-date').value;
    var clientSel = document.querySelector('select[name^="jobs"][name$="[site_id]"]');
    var clientId = clientSel ? (clientSel.options[clientSel.selectedIndex]?.dataset?.clientId || '') : '';
    if (!clientId) return;
    var key = workerId + '-' + clientId + '-' + date;
    if (alertsCache[key]) { renderAlerts(workerId, alertsCache[key]); return; }
    fetch('{{ route("books.worker-alerts") }}?worker_id=' + workerId + '&client_id=' + clientId + '&date=' + date)
        .then(r => r.json()).then(data => { alertsCache[key] = data; renderAlerts(workerId, data); }).catch(() => {});
}

function renderAlerts(workerId, data) {
    var container = document.getElementById('walerts-' + workerId);
    var chip = document.getElementById('wchip-' + workerId);
    if (!container) return;
    container.innerHTML = '';
    (data.alerts || []).forEach(function(a) {
        var div = document.createElement('div');
        div.className = 'alert-item ' + (a.level === 'danger' ? 'alert-danger' : 'alert-warn');
        div.textContent = '⚠ ' + a.message;
        container.appendChild(div);
    });
    if (data.below_min) { var t = document.getElementById('min-tag-' + workerId); if (t) t.style.display = 'inline-block'; }
    var hasDanger = (data.alerts || []).some(a => a.level === 'danger');
    chip.classList.toggle('has-danger', hasDanger);
    chip.classList.toggle('has-warn', (data.alerts||[]).length > 0 && !hasDanger);
}

function refreshAlerts() {
    alertsCache = {};
    Object.keys(selectedWorkers).forEach(loadWorkerAlerts);
}

function updateSelectedList() {
    var list = document.getElementById('selected-workers-list');
    var ids  = Object.keys(selectedWorkers);
    document.getElementById('worker-count-label').textContent = ids.length + ' selected';
    if (ids.length === 0) { list.innerHTML = '<div class="empty-workers">No workers selected</div>'; return; }
    list.innerHTML = ids.map(function(id) {
        var w = selectedWorkers[id];
        return '<div class="summary-worker"><div class="sw-av">' + w.initials + '</div><span class="sw-name">' + w.name + '</span><button type="button" class="sw-remove" onclick="toggleWorker(' + id + ')">×</button></div>';
    }).join('');
}

function addJob(prefilledSiteId) {
    var idx = jobCount++;
    var html = '<div class="job-block" id="job-' + idx + '">' +
        '<div class="job-block-header"><span class="job-block-title">Job ' + (idx+1) + '</span>' +
        '<button type="button" class="remove-job-btn" onclick="removeJob(' + idx + ')">Remove</button></div>' +
        '<div class="job-block-body">' + buildJobFields(idx, prefilledSiteId) + '</div></div>';
    document.getElementById('jobs-container').insertAdjacentHTML('beforeend', html);
    updateAllTLSelects(); updateSummary();
}

function removeJob(idx) {
    var el = document.getElementById('job-' + idx);
    if (el) el.remove();
    renumberJobs(); updateSummary();
}

function renumberJobs() {
    document.querySelectorAll('.job-block').forEach(function(b, i) {
        var t = b.querySelector('.job-block-title');
        if (t) t.textContent = 'Job ' + (i + 1);
    });
}

function buildJobFields(idx, prefilledSiteId) {
    var siteOpts = '<option value="">Select client / site...</option>';
    @foreach($clients as $client)
        @foreach($client->sites as $site)
            siteOpts += '<option value="{{ $site->id }}" data-client-id="{{ $client->id }}"' +
                (prefilledSiteId == {{ $site->id }} ? ' selected' : '') +
                '>{{ $client->name }} — {{ $site->name }}</option>';
        @endforeach
    @endforeach

    var tlOpts = '<option value="">— Select workers first —</option>';
    Object.keys(selectedWorkers).forEach(function(id) {
        tlOpts += '<option value="' + id + '">' + selectedWorkers[id].name + '</option>';
    });

    return '<div class="grid-3" style="margin-bottom:10px">' +
        '<div class="field"><label>Client / Site *</label>' +
            '<select name="jobs[' + idx + '][site_id]" required onchange="refreshAlerts()">' + siteOpts + '</select></div>' +
        '<div class="field"><label>Start time *</label>' +
            '<input type="time" name="jobs[' + idx + '][start_time]" required></div>' +
        '<div class="field"><label>Team Leader *</label>' +
            '<select name="jobs[' + idx + '][team_leader_id]" id="tl-' + idx + '" required>' + tlOpts + '</select></div>' +
        '</div>' +
        '<div class="field" style="margin-bottom:12px"><label>Job notes</label>' +
            '<input type="text" name="jobs[' + idx + '][notes]" placeholder="Optional..."></div>' +
        '<div class="containers-section">' +
            '<div class="containers-header">' +
                '<span class="containers-label">Containers</span>' +
                '<span class="containers-count" id="ccount-' + idx + '">0 containers</span>' +
            '</div>' +
            '<div class="container-col-labels">' +
                '<span class="container-col-label">Container number</span>' +
                '<span class="container-col-label">Size</span>' +
                '<span class="container-col-label"></span>' +
            '</div>' +
            '<div class="containers-list" id="containers-' + idx + '"></div>' +
            '<button type="button" class="add-container-btn" onclick="addContainer(' + idx + ')">' +
                '<svg width="11" height="11" viewBox="0 0 12 12" fill="none"><path d="M6 2v8M2 6h8" stroke="#185FA5" stroke-width="1.5" stroke-linecap="round"/></svg> Add container' +
            '</button></div>';
}

var containerCounters = {};

function addContainer(jobIdx, prefillFeet) {
    if (!containerCounters[jobIdx]) containerCounters[jobIdx] = 0;
    var cIdx = containerCounters[jobIdx]++;
    var id = 'c-' + jobIdx + '-' + cIdx;
    var row = document.createElement('div');
    row.className = 'container-row'; row.id = id;
    row.innerHTML =
        '<input type="text" name="jobs[' + jobIdx + '][containers][' + cIdx + '][container_number]" ' +
            'placeholder="e.g. MSCU 4821903" oninput="updateContainerCount(' + jobIdx + ')" ' +
            'style="text-transform:uppercase" onblur="this.value=this.value.toUpperCase().trim()">' +
        '<select name="jobs[' + jobIdx + '][containers][' + cIdx + '][feet]">' +
            '<option value="40"' + (prefillFeet == 40 ? ' selected' : '') + '>40ft</option>' +
            '<option value="20"' + (prefillFeet == 20 ? ' selected' : '') + '>20ft</option>' +
        '</select>' +
        '<button type="button" class="remove-container-btn" onclick="removeContainer(\'' + id + '\',' + jobIdx + ')">×</button>';
    document.getElementById('containers-' + jobIdx).appendChild(row);
    updateContainerCount(jobIdx); updateSummary();
}

function removeContainer(id, jobIdx) {
    var el = document.getElementById(id);
    if (el) el.remove();
    updateContainerCount(jobIdx); updateSummary();
}

function updateContainerCount(jobIdx) {
    var list = document.getElementById('containers-' + jobIdx);
    var count = list ? list.children.length : 0;
    var label = document.getElementById('ccount-' + jobIdx);
    if (label) label.textContent = count + ' container' + (count !== 1 ? 's' : '');
}

function updateAllTLSelects() {
    document.querySelectorAll('[id^="tl-"]').forEach(function(sel) {
        var current = sel.value;
        var html = '<option value="">— Select team leader —</option>';
        Object.keys(selectedWorkers).forEach(function(id) {
            html += '<option value="' + id + '"' + (current == id ? ' selected' : '') + '>' + selectedWorkers[id].name + '</option>';
        });
        sel.innerHTML = html;
    });
}

function filterWorkers(type, el) {
    document.querySelectorAll('.fchip').forEach(c => c.classList.remove('active'));
    el.classList.add('active');
    document.querySelectorAll('.worker-chip').forEach(function(chip) {
        var show = type === 'all' || (type === 'forklift' && chip.dataset.forklift === '1') || (type === 'min' && chip.dataset.min === '1');
        chip.style.display = show ? '' : 'none';
    });
}

function updateSummary() {
    document.getElementById('sum-workers').textContent = Object.keys(selectedWorkers).length;
    document.getElementById('sum-jobs').textContent = document.querySelectorAll('.job-block').length;
    var totalC = 0;
    document.querySelectorAll('[id^="containers-"]').forEach(function(l) { totalC += l.children.length; });
    document.getElementById('sum-containers').textContent = totalC;
}

// ── Initialise ────────────────────────────────────────────────────────
// First job — pre-fill site if coming from demand
addJob(demandData ? demandData.site_id : null);

// Pre-fill containers from demand quantities
if (demandData) {
    var jobIdx = 0;
    for (var i = 0; i < (demandData.qty_40ft || 0); i++) addContainer(jobIdx, 40);
    for (var j = 0; j < (demandData.qty_20ft || 0); j++) addContainer(jobIdx, 20);
}
</script>
</body>
</html>
