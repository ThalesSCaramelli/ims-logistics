@extends('layouts.app')
@section('title', 'Job History')
@section('breadcrumb')
    <span>Job History</span>
@endsection

@section('content')
<div class="page">
    <div class="page-header">
        <div>
            <div class="page-title">Job History</div>
            <div class="page-sub">All jobs across all books</div>
        </div>
        <div style="display:flex;gap:8px">
            <form method="GET" action="{{ route('jobs.history') }}" style="display:flex;gap:8px;align-items:center">
                <input type="date" name="from" value="{{ $from ?? '' }}"
                    style="font-size:12px;padding:6px 10px;border:0.5px solid #c2c0b6;border-radius:8px;font-family:inherit">
                <span style="font-size:12px;color:#73726c">to</span>
                <input type="date" name="to" value="{{ $to ?? '' }}"
                    style="font-size:12px;padding:6px 10px;border:0.5px solid #c2c0b6;border-radius:8px;font-family:inherit">
                <button type="submit" class="btn btn-sm">Filter</button>
                @if(request('from') || request('to'))
                    <a href="{{ route('jobs.history') }}" class="btn btn-sm">Clear</a>
                @endif
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card" style="padding:0;overflow:hidden">
        <div style="display:grid;grid-template-columns:90px 1fr 140px 100px 110px 90px;padding:8px 16px;background:#f5f4f0;border-bottom:0.5px solid rgba(0,0,0,0.08)">
            <span style="font-size:10px;font-weight:500;color:#73726c;text-transform:uppercase;letter-spacing:0.4px">Date</span>
            <span style="font-size:10px;font-weight:500;color:#73726c;text-transform:uppercase;letter-spacing:0.4px">Job</span>
            <span style="font-size:10px;font-weight:500;color:#73726c;text-transform:uppercase;letter-spacing:0.4px">Team Leader</span>
            <span style="font-size:10px;font-weight:500;color:#73726c;text-transform:uppercase;letter-spacing:0.4px">Containers</span>
            <span style="font-size:10px;font-weight:500;color:#73726c;text-transform:uppercase;letter-spacing:0.4px">Worksheet</span>
            <span style="font-size:10px;font-weight:500;color:#73726c;text-transform:uppercase;letter-spacing:0.4px">Status</span>
        </div>

        @forelse($jobs as $job)
        @php
            $ws = $job->worksheet;
            $wsStatus = $ws?->sync_status?->value ?? $ws?->sync_status ?? null;
            $tl = $job->book->workers->find($job->team_leader_id);
            $ini = $tl ? collect(explode(' ',$tl->name))->map(fn($p)=>strtoupper(substr($p,0,1)))->take(2)->join('') : '?';
        @endphp
        <a href="{{ route('jobs.show', $job) }}"
            style="display:grid;grid-template-columns:90px 1fr 140px 100px 110px 90px;padding:11px 16px;border-bottom:0.5px solid rgba(0,0,0,0.06);text-decoration:none;color:inherit;align-items:center;transition:background .1s"
            onmouseover="this.style.background='#f9f8f6'" onmouseout="this.style.background=''">
            <div style="font-size:12px;color:#73726c">{{ $job->date->format('d M Y') }}</div>
            <div>
                <div style="font-size:13px;font-weight:500">{{ $job->site->client->name }}</div>
                <div style="font-size:11px;color:#73726c">{{ $job->site->name }} · {{ \Carbon\Carbon::parse($job->start_time)->format('H:i') }}</div>
            </div>
            <div style="display:flex;align-items:center;gap:6px">
                @if($tl)
                <div style="width:24px;height:24px;border-radius:50%;background:#E6F1FB;color:#185FA5;display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:500;flex-shrink:0">{{ $ini }}</div>
                <span style="font-size:12px">{{ $tl->name }}</span>
                @else
                <span style="font-size:12px;color:#73726c">—</span>
                @endif
            </div>
            <div style="font-size:12px">{{ $job->containers->count() }} container{{ $job->containers->count() !== 1 ? 's' : '' }}</div>
            <div>
                @if($wsStatus)
                    <span class="pill pill-{{ $wsStatus }}">{{ ucfirst($wsStatus) }}</span>
                @else
                    <span style="font-size:11px;color:#73726c">No worksheet</span>
                @endif
            </div>
            <div>
                @php $statusVal = $job->status instanceof \BackedEnum ? $job->status->value : (string)$job->status->value; @endphp
                <span class="pill pill-{{ $job->status }}">{{ ucfirst(str_replace('_',' ',$statusVal)) }}</span>
            </div>
        </a>
        @empty
        <div style="padding:40px;text-align:center;color:#73726c;font-size:13px">No jobs found for this period.</div>
        @endforelse
    </div>

    <div style="margin-top:12px">
        {{ $jobs->links() }}
    </div>
</div>
@endsection
