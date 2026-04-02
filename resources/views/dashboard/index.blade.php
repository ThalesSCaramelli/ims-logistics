@extends('layouts.app')
@section('title','Dashboard')
@section('breadcrumb')<span>Dashboard</span>@endsection

@push('styles')
<style>
.toggle-group{display:flex;border:0.5px solid rgba(0,0,0,0.15);border-radius:8px;overflow:hidden}
.toggle-btn{padding:5px 14px;font-size:12px;cursor:pointer;background:#fff;border:none;color:#73726c;font-family:inherit;white-space:nowrap}
.toggle-btn.active{background:#185FA5;color:#fff}
.view-toggle{display:flex;border:0.5px solid rgba(0,0,0,0.15);border-radius:8px;overflow:hidden;margin-left:8px}
.view-toggle button{padding:5px 14px;font-size:12px;cursor:pointer;background:#fff;border:none;color:#73726c;font-family:inherit;border-left:0.5px solid rgba(0,0,0,0.1)}
.view-toggle button:first-child{border-left:none}
.view-toggle button.active{background:#1a1a18;color:#fff}
.board-wrap{padding:0 20px 20px;overflow-x:auto}
.board{display:flex;gap:12px;min-height:400px;align-items:flex-start}
.col{min-width:220px;flex:1}
.col-hdr{display:flex;align-items:center;justify-content:space-between;padding:7px 10px;border-radius:8px;margin-bottom:8px}
.col-title{font-size:12px;font-weight:500}
.col-count{font-size:11px;padding:2px 7px;border-radius:10px;font-weight:500}
.s-hdr{background:#E6F1FB}.s-hdr .col-title{color:#185FA5}.s-hdr .col-count{background:#B5D4F4;color:#0C447C}
.i-hdr{background:#FAEEDA}.i-hdr .col-title{color:#854F0B}.i-hdr .col-count{background:#FAC775;color:#633806}
.c-hdr{background:#EAF3DE}.c-hdr .col-title{color:#3B6D11}.c-hdr .col-count{background:#C0DD97;color:#27500A}
.x-hdr{background:#F1EFE8}.x-hdr .col-title{color:#5F5E5A}.x-hdr .col-count{background:#D3D1C7;color:#444441}
.job-card{background:#fff;border:0.5px solid rgba(0,0,0,0.1);border-radius:12px;padding:11px 13px;margin-bottom:8px;transition:border-color .15s;text-decoration:none;display:block;color:inherit}
.job-card:hover{border-color:#185FA5}
.job-card.in-progress{border-left:2px solid #EF9F27;border-radius:0 12px 12px 0}
.job-card.faded{opacity:0.55}
.card-top{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:8px;gap:6px}
.card-client{font-size:13px;font-weight:500;flex:1}
.card-site{font-size:11px;color:#73726c;margin-top:1px}
.card-date{font-size:10px;color:#73726c;margin-top:2px;font-weight:400}
.ws-pill{font-size:10px;padding:2px 7px;border-radius:10px;white-space:nowrap;flex-shrink:0}
.ws-draft{background:#F1EFE8;color:#5F5E5A}
.ws-pending{background:#FAEEDA;color:#854F0B}
.ws-approved{background:#E6F1FB;color:#185FA5}
.ws-paid{background:#EAF3DE;color:#3B6D11}
.card-divider{border:none;border-top:0.5px solid rgba(0,0,0,0.08);margin:8px 0}
.card-row{display:flex;align-items:center;gap:6px;font-size:11px;color:#73726c;margin-bottom:4px}
.card-row:last-child{margin-bottom:0}
.card-row strong{color:#1a1a18;font-weight:500}
.tl-tag{font-size:10px;color:#185FA5;font-weight:500}
.now-tag{color:#EF9F27;font-weight:500}
.empty-col{text-align:center;padding:20px 8px;color:#73726c;font-size:12px;border:0.5px dashed rgba(0,0,0,0.1);border-radius:8px}
.add-job-btn{display:flex;align-items:center;justify-content:center;gap:6px;font-size:12px;color:#185FA5;background:none;border:0.5px dashed #85B7EB;border-radius:12px;padding:8px;width:100%;font-family:inherit;text-decoration:none}
.add-job-btn:hover{background:#E6F1FB}
.worker-col-hdr{display:flex;align-items:center;gap:8px;padding:6px 0 10px}
.worker-col-name{font-size:13px;font-weight:500}
.worker-col-sub{font-size:11px;color:#73726c}
.empty-day{text-align:center;padding:60px 20px;color:#73726c}
.empty-day h3{font-size:16px;font-weight:500;margin-bottom:8px;color:#1a1a18}
.empty-day p{font-size:13px;margin-bottom:20px}
.empty-day a{font-size:13px;padding:8px 20px;background:#185FA5;color:#fff;border-radius:8px;text-decoration:none;display:inline-block}
.empty-day a:hover{background:#0C447C}
.week-label{font-size:10px;font-weight:500;color:#73726c;text-transform:uppercase;letter-spacing:0.5px;padding:4px 8px;background:#f5f4f0;border-radius:5px;margin-bottom:6px;display:inline-block}
</style>
@endpush

@section('content')
@php
    $isWeekly   = $viewMode === 'week';
    $parsedDate = \Carbon\Carbon::parse($date);
    $weekStart  = $parsedDate->copy()->startOfWeek();
    $weekEnd    = $parsedDate->copy()->endOfWeek();
@endphp

{{-- Date / Week navigation bar --}}
<div class="date-bar">
    @if($isWeekly)
        <a href="{{ route('dashboard',['date'=>$weekStart->copy()->subWeek()->toDateString(),'view'=>'week']) }}" class="nav-btn">&#8249;</a>
        <div class="date-label">{{ $weekStart->format('d M') }} — {{ $weekEnd->format('d M Y') }}</div>
        @if($weekStart->lte(today()) && $weekEnd->gte(today()))
            <span class="date-sub">This week</span>
        @endif
        <a href="{{ route('dashboard',['date'=>$weekStart->copy()->addWeek()->toDateString(),'view'=>'week']) }}" class="nav-btn">&#8250;</a>
        @unless($weekStart->lte(today()) && $weekEnd->gte(today()))
            <a href="{{ route('dashboard',['view'=>'week']) }}" class="today-btn">This week</a>
        @endunless
    @else
        <a href="{{ route('dashboard',['date'=>$parsedDate->copy()->subDay()->toDateString()]) }}" class="nav-btn">&#8249;</a>
        <div class="date-label">{{ $parsedDate->format('l, d M Y') }}</div>
        @if($date===today()->toDateString())<span class="date-sub">Today</span>@endif
        <a href="{{ route('dashboard',['date'=>$parsedDate->copy()->addDay()->toDateString()]) }}" class="nav-btn">&#8250;</a>
        @if($date!==today()->toDateString())
            <a href="{{ route('dashboard') }}" class="today-btn">Today</a>
        @endif
        <form method="GET" action="{{ route('dashboard') }}">
            <input type="hidden" name="view" value="day">
            <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()"
                style="font-size:13px;padding:5px 9px;border:0.5px solid #c2c0b6;border-radius:8px;font-family:inherit;color:#1a1a18">
        </form>
    @endif

    {{-- Day / Week toggle --}}
    <div class="view-toggle">
        <button onclick="window.location='{{ route('dashboard',['date'=>$date,'view'=>'day']) }}'"
            class="{{ !$isWeekly ? 'active' : '' }}">Day</button>
        <button onclick="window.location='{{ route('dashboard',['date'=>$date,'view'=>'week']) }}'"
            class="{{ $isWeekly ? 'active' : '' }}">Week</button>
    </div>

    {{-- By status / By worker (daily only) --}}
    @if(!$isWeekly)
    <div class="toggle-group">
        <button class="toggle-btn active" id="btn-status" onclick="switchView('status')">By status</button>
        <button class="toggle-btn" id="btn-worker" onclick="switchView('worker')">By worker</button>
    </div>
    @endif

    <a href="{{ route('books.create') }}" class="btn btn-primary" style="font-size:12px;padding:6px 14px;margin-left:auto">+ New book</a>

    @if(!$isWeekly)
    @php $pendingDay = \App\Models\Book::where('date',$date)->whereNull('notified_at')->where('status','!=','cancelled')->count(); @endphp
    @if($pendingDay > 0)
        <form method="POST" action="{{ route('books.notify-all') }}">
            @csrf<input type="hidden" name="date" value="{{ $date }}">
            <button type="submit" class="btn btn-success" style="font-size:12px;padding:6px 14px">Notify ({{ $pendingDay }})</button>
        </form>
    @else
        <span style="font-size:12px;color:#3B6D11;padding:6px 10px">✓ All notified</span>
    @endif
    @endif
</div>

{{-- KPIs --}}
<div style="padding:12px 20px 0">
    <div class="kpi-row">
        @if($isWeekly)
        <div class="kpi"><div class="kpi-label">Jobs this week</div><div class="kpi-value" style="color:#185FA5">{{ $kpis['jobs_today'] }}</div><div class="kpi-sub">{{ $weekStart->format('d M') }} – {{ $weekEnd->format('d M') }}</div></div>
        <div class="kpi"><div class="kpi-label">Workers allocated</div><div class="kpi-value">{{ $kpis['workers_allocated'] }}</div><div class="kpi-sub">across week</div></div>
        <div class="kpi"><div class="kpi-label">Worksheets pending</div><div class="kpi-value" style="color:#854F0B">{{ $kpis['worksheets_pending'] }}</div><div class="kpi-sub">awaiting review</div></div>
        <div class="kpi"><div class="kpi-label">Containers</div><div class="kpi-value">{{ $kpis['containers_today'] }}</div><div class="kpi-sub">across week</div></div>
        @else
        <div class="kpi"><div class="kpi-label">Jobs today</div><div class="kpi-value" style="color:#185FA5">{{ $kpis['jobs_today'] }}</div><div class="kpi-sub">{{ ($jobs['in_progress']??collect())->count() }} in progress</div></div>
        <div class="kpi"><div class="kpi-label">Workers allocated</div><div class="kpi-value">{{ $kpis['workers_allocated'] }}</div><div class="kpi-sub">for today</div></div>
        <div class="kpi"><div class="kpi-label">Worksheets pending</div><div class="kpi-value" style="color:#854F0B">{{ $kpis['worksheets_pending'] }}</div><div class="kpi-sub">awaiting review</div></div>
        <div class="kpi"><div class="kpi-label">Containers today</div><div class="kpi-value">{{ $kpis['containers_today'] }}</div><div class="kpi-sub">across all jobs</div></div>
        @endif
    </div>
</div>

@php $allJobs = $jobs->flatten(); @endphp

@if($allJobs->isEmpty())
<div style="padding:0 20px">
    <div class="empty-day">
        <h3>No jobs for {{ $isWeekly ? $weekStart->format('d M').' – '.$weekEnd->format('d M Y') : $parsedDate->format('d M Y') }}</h3>
        <p>Create a book to allocate workers and schedule jobs.</p>
        <a href="{{ route('books.create') }}">+ Create book</a>
    </div>
</div>
@else

{{-- ═══ WEEKLY VIEW ═══ --}}
@if($isWeekly)
<div class="board-wrap">
    <div class="board">
        @foreach(['scheduled'=>['s-hdr','Scheduled'],'in_progress'=>['i-hdr','In progress'],'completed'=>['c-hdr','Completed'],'cancelled'=>['x-hdr','Cancelled']] as $status=>[$hdrClass,$label])
        @php $statusJobs = $jobs[$status] ?? collect(); @endphp
        <div class="col">
            <div class="col-hdr {{ $hdrClass }}">
                <span class="col-title">{{ $label }}</span>
                <span class="col-count">{{ $statusJobs->count() }}</span>
            </div>
            @forelse($statusJobs as $job)
            @php
                $wsStatus    = $job->worksheet?->sync_status?->value ?? 'draft';
                $initials    = collect(explode(' ',$job->teamLeader?->name??'?'))->map(fn($p)=>strtoupper(substr($p,0,1)))->take(2)->join('');
                $workerCount = $job->book->workers->count();
                $jobDate     = \Carbon\Carbon::parse($job->date);
            @endphp
            <a href="{{ route('jobs.show',$job) }}" class="job-card {{ $status==='in_progress'?'in-progress':'' }} {{ $status==='cancelled'?'faded':'' }}">
                <div class="card-top">
                    <div>
                        <div class="card-client">{{ $job->site->client->name }}</div>
                        <div class="card-site">{{ $job->site->name }}</div>
                        <div class="card-date">{{ $jobDate->format('D d M') }}</div>
                    </div>
                    <span class="ws-pill ws-{{ str_replace('_','-',$wsStatus) }}">{{ $wsStatus }}</span>
                </div>
                <hr class="card-divider">
                <div class="card-row">
                    <strong>{{ \Carbon\Carbon::parse($job->start_time)->format('H:i') }}</strong>
                </div>
                @if($job->teamLeader)
                <div class="card-row">
                    <div class="av av-sm">{{ $initials }}</div>
                    <span class="tl-tag">TL</span>
                    <span>{{ $job->teamLeader->name }}@if($workerCount>1) +{{ $workerCount-1 }}@endif</span>
                </div>
                @endif
            </a>
            @empty
                <div class="empty-col">No {{ strtolower($label) }} jobs</div>
            @endforelse
        </div>
        @endforeach
    </div>
</div>

{{-- ═══ DAILY VIEW ═══ --}}
@else

<div id="view-status" class="board-wrap">
    <div class="board">
        @foreach(['scheduled'=>['s-hdr','Scheduled'],'in_progress'=>['i-hdr','In progress'],'completed'=>['c-hdr','Completed'],'cancelled'=>['x-hdr','Cancelled']] as $status=>[$hdrClass,$label])
        <div class="col">
            <div class="col-hdr {{ $hdrClass }}">
                <span class="col-title">{{ $label }}</span>
                <span class="col-count">{{ ($jobs[$status]??collect())->count() }}</span>
            </div>
            @forelse($jobs[$status]??[] as $job)
            @php
                $wsStatus    = $job->worksheet?->sync_status?->value ?? 'draft';
                $initials    = collect(explode(' ',$job->teamLeader?->name??'?'))->map(fn($p)=>strtoupper(substr($p,0,1)))->take(2)->join('');
                $workerCount = $job->book->workers->count();
            @endphp
            <a href="{{ route('jobs.show',$job) }}" class="job-card {{ $status==='in_progress'?'in-progress':'' }} {{ $status==='cancelled'?'faded':'' }}">
                <div class="card-top">
                    <div><div class="card-client">{{ $job->site->client->name }}</div><div class="card-site">{{ $job->site->name }}</div></div>
                    <span class="ws-pill ws-{{ str_replace('_','-',$wsStatus) }}">{{ $wsStatus }}</span>
                </div>
                <hr class="card-divider">
                <div class="card-row">
                    <strong>{{ \Carbon\Carbon::parse($job->start_time)->format('H:i') }}</strong>
                    @if($status==='in_progress')<span class="now-tag">· now</span>@endif
                </div>
                @if($job->teamLeader)
                <div class="card-row">
                    <div class="av av-sm">{{ $initials }}</div>
                    <span class="tl-tag">TL</span>
                    <span>{{ $job->teamLeader->name }}@if($workerCount>1) +{{ $workerCount-1 }}@endif</span>
                </div>
                @endif
            </a>
            @empty
                <div class="empty-col">No {{ strtolower($label) }} jobs</div>
            @endforelse
            @if($status==='scheduled')
                <a href="{{ route('books.create') }}" class="add-job-btn">+ New job</a>
            @endif
        </div>
        @endforeach
    </div>
</div>

<div id="view-worker" class="board-wrap" style="display:none">
    <div class="board">
        @php
            $workerMap=[];$avatarColors=[['#E6F1FB','#185FA5'],['#B5D4F4','#0C447C'],['#85B7EB','#042C53'],['#E1F5EE','#0F6E56'],['#F1EFE8','#5F5E5A'],['#EEEDFE','#3C3489']];$ci=0;
            foreach($allJobs as $job){foreach($job->book->workers as $worker){if(!isset($workerMap[$worker->id])){$workerMap[$worker->id]=['worker'=>$worker,'jobs'=>[],'color'=>$avatarColors[$ci%count($avatarColors)]];$ci++;}$workerMap[$worker->id]['jobs'][]=$job;}}
        @endphp
        @foreach($workerMap as $wid=>$wd)
        @php $wI=collect(explode(' ',$wd['worker']->name))->map(fn($p)=>strtoupper(substr($p,0,1)))->take(2)->join(''); @endphp
        <div class="col">
            <div class="worker-col-hdr">
                <div class="av av-md" style="background:{{ $wd['color'][0] }};color:{{ $wd['color'][1] }}">{{ $wI }}</div>
                <div><div class="worker-col-name">{{ $wd['worker']->name }}</div><div class="worker-col-sub">{{ count($wd['jobs']) }} job{{ count($wd['jobs'])!==1?'s':'' }} today</div></div>
            </div>
            @foreach($wd['jobs'] as $job)
            @php
                $wsStatus = $job->worksheet?->sync_status?->value ?? 'draft';
                $isTL     = $job->team_leader_id === $wid;
                $jobStatus = $job->status instanceof \BackedEnum ? $job->status->value : (string)$job->status;
            @endphp
            <a href="{{ route('jobs.show',$job) }}" class="job-card {{ $jobStatus==='in_progress'?'in-progress':'' }}">
                <div class="card-top">
                    <div><div class="card-client">{{ $job->site->client->name }}</div><div class="card-site">{{ $job->site->name }}</div></div>
                    <span class="ws-pill ws-{{ str_replace('_','-',$wsStatus) }}">{{ $wsStatus }}</span>
                </div>
                <hr class="card-divider">
                <div class="card-row"><strong>{{ \Carbon\Carbon::parse($job->start_time)->format('H:i') }}</strong></div>
                <div class="card-row">@if($isTL)<span class="tl-tag">Team Leader</span>@else<span>Worker</span>@endif</div>
            </a>
            @endforeach
        </div>
        @endforeach
    </div>
</div>
@endif {{-- end daily/weekly --}}
@endif {{-- end empty check --}}
@endsection

@push('scripts')
<script>
function switchView(v){
    document.getElementById('view-status').style.display=v==='status'?'block':'none';
    document.getElementById('view-worker').style.display=v==='worker'?'block':'none';
    document.getElementById('btn-status').classList.toggle('active',v==='status');
    document.getElementById('btn-worker').classList.toggle('active',v==='worker');
}
</script>
@endpush
