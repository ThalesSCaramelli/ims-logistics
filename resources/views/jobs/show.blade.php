@extends('layouts.app')
@section('title', $job->site->client->name . ' — Job')
@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span>/</span>
    <span>{{ $job->site->client->name }} · {{ $job->date->format('d M Y') }}</span>
@endsection

@section('content')
@php
    $worksheet   = $job->worksheet;
    $wsStatus    = $worksheet?->sync_status?->value ?? $worksheet?->sync_status ?? null;
    $containers  = $job->containers;
    $bookWorkers = $job->book->workers;
    $tl          = $bookWorkers->find($job->team_leader_id);
@endphp

<div class="page">
    <div class="page-header">
        <div>
            <div class="page-title">{{ $job->site->client->name }}</div>
            <div style="display:flex;gap:8px;align-items:center;margin-top:4px">
                <span class="pill pill-{{ $job->status }}">{{ ucfirst(str_replace('_',' ',$job->status->value)) }}</span>
                <span style="font-size:12px;color:#73726c">{{ $job->site->name }} · {{ $job->date->format('d M Y') }} · {{ \Carbon\Carbon::parse($job->start_time)->format('H:i') }}</span>
            </div>
        </div>
        <div style="display:flex;gap:8px">
            <a href="{{ route('dashboard') }}" class="btn">← Back</a>
            @if($worksheet)
                <a href="{{ route('worksheets.show', $worksheet) }}" class="btn btn-primary">
                    {{ $wsStatus === 'pending' ? 'Review worksheet' : 'View worksheet' }}
                </a>
            @else
                <form method="POST" action="{{ route('worksheets.create', $job) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary">Create worksheet</button>
                </form>
            @endif
        </div>
    </div>

    <div class="page-2col">
    <div>

        {{-- Containers --}}
        <div class="card">
            <div class="card-title">Containers <span style="font-size:12px;font-weight:400;color:#73726c">({{ $containers->count() }})</span></div>
            @forelse($containers as $c)
            <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:0.5px solid rgba(0,0,0,0.06)">
                <div style="font-size:12px;font-family:monospace;font-weight:500;min-width:120px">{{ $c->container_number ?: '—' }}</div>
                <span style="font-size:10px;padding:1px 7px;border-radius:6px;background:#E6F1FB;color:#185FA5;font-weight:500">{{ $c->feet }}ft</span>
                @if($c->product)<span style="font-size:11px;color:#73726c">{{ $c->product->name }}</span>@endif
                @if($c->boxes_count)<span style="font-size:11px;color:#73726c">{{ number_format($c->boxes_count) }} boxes</span>@endif
                @if($c->skills_count)<span style="font-size:11px;color:#73726c">{{ $c->skills_count }} skills</span>@endif
            </div>
            @empty
            <p style="font-size:12px;color:#73726c;font-style:italic">No containers yet.</p>
            @endforelse
        </div>

        {{-- Worksheet status --}}
        @if($worksheet)
        <div class="card">
            <div class="card-title">
                Worksheet
                <span class="pill pill-{{ $wsStatus ?? 'draft' }}">{{ ucfirst($wsStatus ?? 'draft') }}</span>
            </div>
            <div style="font-size:12px;color:#73726c;margin-bottom:12px">
                @if($wsStatus === 'pending') Awaiting office review.
                @elseif($wsStatus === 'approved') Approved — payment generated.
                @elseif($wsStatus === 'paid') Paid.
                @else Draft — not yet submitted.
                @endif
            </div>
            <a href="{{ route('worksheets.show', $worksheet) }}" class="btn btn-primary btn-sm" style="width:auto">
                Open worksheet →
            </a>
        </div>
        @endif

    </div>

    <div class="page-sidebar">

        {{-- Job info --}}
        <div class="card">
            <div class="card-title">Job info</div>
            <div style="display:flex;flex-direction:column;gap:6px;font-size:12px">
                <div style="display:flex;justify-content:space-between"><span style="color:#73726c">Client</span><span style="font-weight:500">{{ $job->site->client->name }}</span></div>
                <div style="display:flex;justify-content:space-between"><span style="color:#73726c">Site</span><span>{{ $job->site->name }}</span></div>
                <div style="display:flex;justify-content:space-between"><span style="color:#73726c">Date</span><span>{{ $job->date->format('d M Y') }}</span></div>
                <div style="display:flex;justify-content:space-between"><span style="color:#73726c">Start</span><span>{{ \Carbon\Carbon::parse($job->start_time)->format('H:i') }}</span></div>
                @php $statusVal = $job->status instanceof \BackedEnum ? $job->status->value : (string)$job->status; @endphp
                <div style="display:flex;justify-content:space-between"><span style="color:#73726c">Status</span><span>{{ ucfirst(str_replace('_',' ',$job->status->value)) }}</span></div>
                @if($tl)
                <div style="display:flex;justify-content:space-between"><span style="color:#73726c">Team Leader</span><span style="font-weight:500">{{ $tl->name }}</span></div>
                @endif
            </div>
        </div>

        {{-- Crew --}}
        <div class="card">
            <div class="card-title">Crew ({{ $bookWorkers->count() }})</div>
            @foreach($bookWorkers as $w)
            @php $ini = collect(explode(' ',$w->name))->map(fn($p)=>strtoupper(substr($p,0,1)))->take(2)->join(''); @endphp
            <div style="display:flex;align-items:center;gap:8px;padding:6px 0;border-bottom:0.5px solid rgba(0,0,0,0.06)">
                <div style="width:28px;height:28px;border-radius:50%;background:#E6F1FB;color:#185FA5;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:500;flex-shrink:0">{{ $ini }}</div>
                <div style="flex:1">
                    <div style="font-size:12px;font-weight:500">{{ $w->name }}</div>
                </div>
                @if($job->team_leader_id === $w->id)
                    <span style="font-size:10px;color:#185FA5;font-weight:500">TL</span>
                @endif
            </div>
            @endforeach
        </div>

        @if($job->notes)
        <div class="card">
            <div class="card-title">Notes</div>
            <p style="font-size:12px;color:#73726c;line-height:1.5">{{ $job->notes }}</p>
        </div>
        @endif

    </div>
    </div>
</div>
@endsection
