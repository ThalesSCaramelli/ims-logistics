<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMS — {{ $worker->name }}</title>
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
        .topbar-btn:hover{background:rgba(255,255,255,0.25)}
        .page{padding:20px;max-width:820px;margin:0 auto}

        .alert-bar{border-radius:10px;padding:10px 14px;margin-bottom:12px;font-size:13px;display:flex;align-items:center;gap:8px;border:0.5px solid}
        .alert-amber{background:#FAEEDA;border-color:#FAC775;color:#854F0B}
        .alert-red{background:#FCEBEB;border-color:#F09595;color:#A32D2D}

        .profile-header{background:#fff;border:0.5px solid rgba(0,0,0,0.1);border-radius:12px;padding:18px;margin-bottom:14px;display:flex;align-items:center;gap:14px;flex-wrap:wrap}
        .av{width:52px;height:52px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:600;flex-shrink:0;background:#E6F1FB;color:#185FA5}
        .ph-info{flex:1;min-width:0}
        .ph-name{font-size:20px;font-weight:500;margin-bottom:4px;display:flex;align-items:center;gap:8px;flex-wrap:wrap}
        .ph-meta{font-size:13px;color:#73726c;display:flex;gap:12px;flex-wrap:wrap}
        .ph-actions{display:flex;gap:8px;flex-wrap:wrap}
        .status-badge{font-size:11px;padding:3px 10px;border-radius:10px;font-weight:500}
        .s-active{background:#EAF3DE;color:#3B6D11}
        .s-suspended{background:#FAEEDA;color:#854F0B}
        .s-inactive{background:#F1EFE8;color:#5F5E5A}
        .tag{font-size:10px;padding:2px 8px;border-radius:10px;font-weight:500}
        .tag-lf{background:#EEEDFE;color:#3C3489}

        .acc{border:0.5px solid rgba(0,0,0,0.1);border-radius:12px;margin-bottom:10px;overflow:hidden}
        .acc-hdr{display:flex;align-items:center;justify-content:space-between;padding:14px 16px;cursor:pointer;background:#fff;transition:background .15s}
        .acc-hdr:hover{background:#f9f8f6}
        .acc-title{font-size:14px;font-weight:500}
        .acc-meta{font-size:12px;color:#73726c;margin-left:6px}
        .chevron{width:16px;height:16px;color:#73726c;transition:transform .2s}
        .chevron.open{transform:rotate(180deg)}
        .acc-body{display:none;padding:16px;border-top:0.5px solid rgba(0,0,0,0.08);background:#fff}
        .acc-body.open{display:block}

        .grid-2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
        .grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px}
        .field{display:flex;flex-direction:column;gap:4px;margin-bottom:0}
        .field label{font-size:11px;color:#73726c}
        .field input,.field select{font-size:13px;padding:7px 10px;border:0.5px solid #c2c0b6;border-radius:8px;background:#fff;color:#1a1a18;font-family:inherit;width:100%}
        .field input:focus,.field select:focus{outline:none;border-color:#185FA5}
        .field input.warn{border-color:#FAC775;background:#FFFBF5}
        .field input[readonly]{background:#f5f4f0;color:#73726c}

        .divider{height:0.5px;background:rgba(0,0,0,0.08);margin:14px 0}
        .section-label{font-size:11px;font-weight:500;color:#73726c;text-transform:uppercase;letter-spacing:0.6px;margin-bottom:8px;margin-top:14px}
        .section-label:first-child{margin-top:0}

        .toggle-row{display:flex;align-items:center;justify-content:space-between;padding:8px 0;margin-bottom:10px}
        .toggle-row label{font-size:13px}
        .toggle-row small{font-size:11px;color:#73726c;display:block;margin-top:1px}
        .toggle{width:36px;height:20px;border-radius:10px;position:relative;cursor:pointer;transition:background .2s;border:none;padding:0;flex-shrink:0}
        .toggle.off{background:#c2c0b6}
        .toggle.on{background:#185FA5}
        .toggle::after{content:'';position:absolute;top:2px;left:2px;width:16px;height:16px;background:#fff;border-radius:50%;transition:transform .2s}
        .toggle.on::after{transform:translateX(16px)}

        .ind-row{display:flex;align-items:center;justify-content:space-between;padding:9px 12px;border:0.5px solid rgba(0,0,0,0.08);border-radius:8px;margin-bottom:6px;background:#f9f8f6}
        .ind-client{font-size:13px;font-weight:500}
        .ind-site{font-size:11px;color:#73726c;margin-top:1px}
        .ind-right{display:flex;align-items:center;gap:10px}
        .ind-status{font-size:11px;padding:2px 8px;border-radius:10px;font-weight:500}
        .ind-done{background:#EAF3DE;color:#3B6D11}
        .ind-pending{background:#FAEEDA;color:#854F0B}

        .rest-row{display:flex;align-items:center;gap:10px;padding:9px 12px;border:0.5px solid rgba(0,0,0,0.08);border-radius:8px;margin-bottom:6px;background:#f9f8f6}
        .rest-type{font-size:10px;padding:2px 7px;border-radius:10px;font-weight:500;white-space:nowrap;flex-shrink:0}
        .rt-date{background:#FAEEDA;color:#854F0B}
        .rt-site{background:#EEEDFE;color:#3C3489}
        .rt-client{background:#FCEBEB;color:#A32D2D}
        .rest-info{flex:1;min-width:0}
        .rest-val{font-size:13px;font-weight:500}
        .rest-reason{font-size:11px;color:#73726c;margin-top:1px}

        .visa-doc{background:#f5f4f0;border:0.5px solid rgba(0,0,0,0.1);border-radius:8px;padding:10px 12px;display:flex;align-items:center;gap:10px;margin-top:6px}
        .visa-doc-name{font-size:13px;flex:1}
        .visa-doc-meta{font-size:11px;color:#73726c}

        .add-btn{display:flex;align-items:center;gap:6px;font-size:12px;color:#185FA5;background:none;border:0.5px dashed #85B7EB;border-radius:8px;padding:7px 14px;width:100%;justify-content:center;cursor:pointer;font-family:inherit;margin-top:4px}
        .add-btn:hover{background:#E6F1FB}

        .btn{font-size:12px;padding:7px 14px;border-radius:8px;cursor:pointer;font-family:inherit;border:0.5px solid #c2c0b6;background:#fff;color:#1a1a18;white-space:nowrap;text-decoration:none;display:inline-block}
        .btn:hover{background:#f5f4f0}
        .btn-primary{background:#185FA5;color:#fff;border-color:#185FA5}
        .btn-primary:hover{background:#0C447C}
        .btn-danger{color:#A32D2D;border-color:#F09595}
        .btn-danger:hover{background:#FCEBEB}

        .footer{display:flex;justify-content:flex-end;gap:8px;margin-top:16px}

        /* Job history */
        .job-row{display:flex;align-items:center;gap:12px;padding:10px 14px;border:0.5px solid rgba(0,0,0,0.08);border-radius:8px;margin-bottom:6px;background:#fff;font-size:13px}
        .job-date{font-weight:500;min-width:80px;color:#73726c}
        .job-client{flex:1;font-weight:500}
        .job-site{font-size:11px;color:#73726c}
        .job-pill{font-size:10px;padding:2px 8px;border-radius:10px;font-weight:500;white-space:nowrap}
        .jp-completed{background:#EAF3DE;color:#3B6D11}
        .jp-scheduled{background:#E6F1FB;color:#185FA5}
        .jp-cancelled{background:#F1EFE8;color:#5F5E5A}

        @media(max-width:600px){.grid-3{grid-template-columns:1fr 1fr}.grid-2{grid-template-columns:1fr}.profile-header{flex-direction:column;align-items:flex-start}}
    </style>
</head>
<body>
<div class="topbar">
    <div class="logo">IMS</div>
    <div class="topbar-nav">
        <a href="{{ route('workers.index') }}">Workers</a>
        <span>/</span>
        <span>{{ $worker->name }}</span>
    </div>
    <div class="topbar-right">
        <span class="topbar-user">{{ Auth::user()->email }}</span>
        <form method="POST" action="{{ route('logout') }}" style="display:inline">
            @csrf<button type="submit" class="topbar-btn">Sign out</button>
        </form>
    </div>
</div>

<div class="page">

    {{-- Alerts --}}
    @if(!$worker->is_australian && $worker->visa?->isExpiredOrExpiringSoon())
        @if($worker->visa->isExpired())
            <div class="alert-bar alert-red">⚠ Visa expired on {{ $worker->visa->valid_until->format('d M Y') }} — review immediately.</div>
        @else
            <div class="alert-bar alert-amber">⚠ Visa expires on {{ $worker->visa->valid_until->format('d M Y') }} — {{ $worker->visa->valid_until->diffInDays(today()) }} days remaining.</div>
        @endif
    @endif

    @if(session('success'))
        <div style="background:#EAF3DE;border:0.5px solid #C0DD97;border-radius:8px;padding:10px 14px;font-size:13px;color:#27500A;margin-bottom:12px">✓ {{ session('success') }}</div>
    @endif

    {{-- Profile header --}}
    @php $initials = collect(explode(' ',$worker->name))->map(fn($p)=>strtoupper(substr($p,0,1)))->take(2)->join(''); @endphp
    <div class="profile-header">
        <div class="av">{{ $initials }}</div>
        <div class="ph-info">
            <div class="ph-name">
                {{ $worker->name }}
                <span class="status-badge s-{{ $worker->status->value }}">{{ ucfirst($worker->status->value) }}</span>
                @if($worker->has_forklift) <span class="tag tag-lf">Forklift LF</span> @endif
            </div>
            <div class="ph-meta">
                @if($worker->abn) <span>ABN {{ $worker->abn }}</span> @endif
                <span>{{ $worker->phone }}</span>
                @if($worker->email) <span>{{ $worker->email }}</span> @endif
            </div>
        </div>
        <div class="ph-actions">
            <a href="{{ route('jobs.history') }}?worker={{ $worker->id }}" class="btn">View job history</a>
        </div>
    </div>

    <form method="POST" action="{{ route('workers.update', $worker) }}">
        @csrf @method('PUT')

    {{-- 1. Personal details --}}
    <div class="acc">
        <div class="acc-hdr" onclick="toggleAcc('personal')">
            <div style="display:flex;align-items:center;gap:8px">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="6" r="3" stroke="#73726c" stroke-width="1.2"/><path d="M2 14c0-3.3 2.7-5 6-5s6 1.7 6 5" stroke="#73726c" stroke-width="1.2" stroke-linecap="round"/></svg>
                <span class="acc-title">Personal details</span>
                <span class="acc-meta">· {{ $worker->name }}</span>
            </div>
            <svg class="chevron open" id="chev-personal" viewBox="0 0 16 16" fill="none"><path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <div class="acc-body open" id="personal">
            <div class="grid-3" style="gap:12px;margin-bottom:12px">
                <div class="field"><label>Full name *</label><input type="text" name="name" value="{{ $worker->name }}" required></div>
                <div class="field"><label>Phone *</label><input type="text" name="phone" value="{{ $worker->phone }}" required></div>
                <div class="field"><label>Email</label><input type="email" name="email" value="{{ $worker->email }}"></div>
                <div class="field"><label>ABN</label><input type="text" name="abn" value="{{ $worker->abn }}"></div>
                <div class="field"><label>Status *</label>
                    <select name="status" onchange="toggleSuspension(this)">
                        <option value="active" {{ $worker->status->value==='active'?'selected':'' }}>Active</option>
                        <option value="suspended" {{ $worker->status->value==='suspended'?'selected':'' }}>Suspended (temporary)</option>
                        <option value="inactive" {{ $worker->status->value==='inactive'?'selected':'' }}>Inactive (permanent)</option>
                    </select>
                </div>
                <div class="field"><label>Min. weekly pay (AUD)</label><input type="number" name="min_weekly" value="{{ $worker->min_weekly }}" min="0" step="0.01" placeholder="No minimum"></div>
            </div>
            <div class="grid-2" id="suspension-fields" style="{{ $worker->status->value!=='suspended'?'display:none':'' }}">
                <div class="field"><label>Return date</label><input type="date" name="return_date" value="{{ $worker->return_date?->toDateString() }}"></div>
                <div class="field"><label>Reason</label><input type="text" name="suspension_reason" value="{{ $worker->suspension_reason }}" placeholder="Reason for suspension..."></div>
            </div>
            <div class="field" style="margin-top:12px"><label>Australian citizen / permanent resident</label>
                <select name="is_australian" onchange="toggleVisa(this)">
                    <option value="1" {{ $worker->is_australian?'selected':'' }}>Yes — Australian citizen or PR</option>
                    <option value="0" {{ !$worker->is_australian?'selected':'' }}>No — requires visa</option>
                </select>
            </div>
        </div>
    </div>

    {{-- 2. Licences --}}
    <div class="acc">
        <div class="acc-hdr" onclick="toggleAcc('licences')">
            <div style="display:flex;align-items:center;gap:8px">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="2" y="4" width="12" height="8" rx="2" stroke="#73726c" stroke-width="1.2"/><path d="M5 8h3M5 10.5h2" stroke="#73726c" stroke-width="1"/><circle cx="11" cy="9" r="1.5" stroke="#73726c" stroke-width="1"/></svg>
                <span class="acc-title">Licences</span>
                <span class="acc-meta">· {{ $worker->has_forklift ? 'Forklift LF active' : 'No licence registered' }}</span>
            </div>
            <svg class="chevron" id="chev-licences" viewBox="0 0 16 16" fill="none"><path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <div class="acc-body" id="licences">
            <div class="toggle-row">
                <div><label>Holds a valid forklift licence (LF)</label><small>Worker will show a "Driver" tag in book creation</small></div>
                <button type="button" class="toggle {{ $worker->has_forklift?'on':'off' }}" id="lf-toggle" onclick="this.classList.toggle('on');this.classList.toggle('off');document.getElementById('has_forklift').value=this.classList.contains('on')?'1':'0'"></button>
            </div>
            <input type="hidden" name="has_forklift" id="has_forklift" value="{{ $worker->has_forklift?'1':'0' }}">
            <div class="grid-3">
                <div class="field"><label>Licence number</label><input type="text" name="forklift_licence_number" value="{{ $worker->forklift_licence_number }}"></div>
                <div class="field"><label>Expiry date</label><input type="date" name="forklift_expiry" value="{{ $worker->forklift_expiry?->toDateString() }}" class="{{ $worker->forklift_expiry?->isPast() ? 'warn' : '' }}"></div>
                <div class="field"><label>Issuing state</label>
                    <select name="forklift_state">
                        @foreach(['VIC','NSW','QLD','WA','SA','TAS','ACT','NT'] as $state)
                            <option {{ $worker->forklift_state===$state?'selected':'' }}>{{ $state }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. Visa --}}
    <div class="acc" id="visa-section" style="{{ $worker->is_australian?'display:none':'' }}">
        <div class="acc-hdr" onclick="toggleAcc('visa')">
            <div style="display:flex;align-items:center;gap:8px">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="2" y="3" width="12" height="10" rx="2" stroke="#73726c" stroke-width="1.2"/><path d="M5 7h6M5 10h4" stroke="#73726c" stroke-width="1" stroke-linecap="round"/></svg>
                <span class="acc-title">Visa & work rights</span>
                @if($worker->visa)
                    <span class="acc-meta {{ $worker->visa->isExpiredOrExpiringSoon() ? 'style=color:#A32D2D' : '' }}">
                        · {{ $worker->visa->visa_class }} — expires {{ $worker->visa->valid_until->format('d M Y') }}
                    </span>
                @endif
            </div>
            <svg class="chevron" id="chev-visa" viewBox="0 0 16 16" fill="none"><path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <div class="acc-body" id="visa">
            <div class="grid-3" style="margin-bottom:12px">
                <div class="field"><label>Visa class *</label>
                    <select name="visa_class">
                        @foreach(['500 — Student','482 — TSS Sponsored','485 — Graduate','417 — Working Holiday','Other'] as $vc)
                            <option {{ $worker->visa?->visa_class===explode(' ',$vc)[0]?'selected':'' }}>{{ $vc }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field"><label>Expiry date *</label>
                    <input type="date" name="visa_valid_until" value="{{ $worker->visa?->valid_until->toDateString() }}" class="{{ $worker->visa?->isExpiredOrExpiringSoon()?'warn':'' }}">
                </div>
                <div class="field"><label>Work permitted</label>
                    <select name="visa_work_permitted">
                        <option value="1" {{ $worker->visa?->work_permitted?'selected':'' }}>Yes</option>
                        <option value="0" {{ !$worker->visa?->work_permitted?'selected':'' }}>No</option>
                    </select>
                </div>
                <div class="field"><label>Fortnightly hours limit</label>
                    <input type="number" name="visa_fortnightly_hours_limit" value="{{ $worker->visa?->fortnightly_hours_limit }}" placeholder="e.g. 48">
                </div>
            </div>
        </div>
    </div>

    {{-- 4. Inductions --}}
    <div class="acc">
        <div class="acc-hdr" onclick="toggleAcc('inductions')">
            <div style="display:flex;align-items:center;gap:8px">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M3 8l3 3 7-7" stroke="#73726c" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span class="acc-title">Client inductions</span>
                <span class="acc-meta">· {{ $worker->inductions->where('completed',true)->count() }} of {{ $clients->count() }} completed</span>
            </div>
            <svg class="chevron" id="chev-inductions" viewBox="0 0 16 16" fill="none"><path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <div class="acc-body" id="inductions">
            <p style="font-size:12px;color:#73726c;margin-bottom:12px">Workers without induction will trigger a warning when booked for that client.</p>
            @foreach($clients as $client)
            @php $completed = $worker->inductions->where('client_id',$client->id)->first()?->completed ?? false; @endphp
            <div class="ind-row">
                <div>
                    <div class="ind-client">{{ $client->name }}</div>
                    <div class="ind-site">{{ $client->sites->pluck('name')->join(' · ') }}</div>
                </div>
                <div class="ind-right">
                    <span class="ind-status {{ $completed?'ind-done':'ind-pending' }}">{{ $completed?'Completed':'Not done' }}</span>
                    <label style="display:flex;align-items:center;gap:5px;font-size:12px;cursor:pointer">
                        <input type="checkbox" name="inductions[{{ $client->id }}]" value="1" {{ $completed?'checked':'' }} style="accent-color:#185FA5">
                        Done
                    </label>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- 5. Restrictions --}}
    <div class="acc">
        <div class="acc-hdr" onclick="toggleAcc('restrictions')">
            <div style="display:flex;align-items:center;gap:8px">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6" stroke="#73726c" stroke-width="1.2"/><path d="M5 8h6" stroke="#73726c" stroke-width="1.3" stroke-linecap="round"/></svg>
                <span class="acc-title">Restrictions</span>
                <span class="acc-meta">· {{ $worker->restrictions->count() }} active</span>
            </div>
            <svg class="chevron" id="chev-restrictions" viewBox="0 0 16 16" fill="none"><path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <div class="acc-body" id="restrictions">
            <p style="font-size:12px;color:#73726c;margin-bottom:12px">Restrictions are advisory only — office will see a warning but can still book the worker.</p>
            @forelse($worker->restrictions as $rest)
            <div class="rest-row">
                <span class="rest-type rt-{{ $rest->type }}">{{ ucfirst($rest->type) }}</span>
                <div class="rest-info">
                    <div class="rest-val">
                        @if($rest->type==='date') {{ \Carbon\Carbon::parse($rest->value['date'] ?? '')->format('d M Y') }}
                        @elseif($rest->type==='site') {{ $sites->find($rest->value['site_id'] ?? 0)?->name ?? '—' }}
                        @else {{ $clients->find($rest->value['client_id'] ?? 0)?->name ?? '—' }}
                        @endif
                    </div>
                    @if($rest->reason)<div class="rest-reason">{{ $rest->reason }}</div>@endif
                </div>
                <form method="POST" action="{{ route('workers.restrictions.destroy', [$worker, $rest]) }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger" style="font-size:11px;padding:3px 8px">Remove</button>
                </form>
            </div>
            @empty
                <p style="font-size:12px;color:#73726c;font-style:italic">No restrictions.</p>
            @endforelse
        </div>
    </div>

    <div class="footer">
        <a href="{{ route('workers.index') }}" class="btn">Cancel</a>
        <button type="submit" class="btn btn-primary">Save changes</button>
    </div>

    </form>

    {{-- Recent jobs --}}
    <div class="acc" style="margin-top:10px">
        <div class="acc-hdr" onclick="toggleAcc('jobs')">
            <div style="display:flex;align-items:center;gap:8px">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="2" y="3" width="12" height="12" rx="2" stroke="#73726c" stroke-width="1.2"/><path d="M5 7h6M5 10h4" stroke="#73726c" stroke-width="1" stroke-linecap="round"/></svg>
                <span class="acc-title">Recent jobs</span>
            </div>
            <svg class="chevron" id="chev-jobs" viewBox="0 0 16 16" fill="none"><path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <div class="acc-body" id="jobs">
            @forelse($recentJobs as $job)
            <div class="job-row">
                <div class="job-date">{{ $job->date->format('d M Y') }}</div>
                <div style="flex:1">
                    <div class="job-client">{{ $job->site->client->name }}</div>
                    <div class="job-site">{{ $job->site->name }} · {{ \Carbon\Carbon::parse($job->start_time)->format('H:i') }}</div>
                </div>
                <span class="job-pill jp-{{ $job->status->value }}">{{ ucfirst(str_replace('_',' ',$job->status->value)) }}</span>
                <a href="{{ route('jobs.show', $job) }}" class="btn" style="font-size:11px;padding:3px 9px">View</a>
            </div>
            @empty
                <p style="font-size:12px;color:#73726c;font-style:italic">No jobs yet.</p>
            @endforelse
        </div>
    </div>
</div>

<script>
function toggleAcc(id) {
    document.getElementById(id).classList.toggle('open');
    document.getElementById('chev-'+id).classList.toggle('open');
}
function toggleSuspension(sel) {
    document.getElementById('suspension-fields').style.display = sel.value==='suspended' ? 'grid' : 'none';
}
function toggleVisa(sel) {
    document.getElementById('visa-section').style.display = sel.value==='0' ? 'block' : 'none';
}
</script>
</body>
</html>
