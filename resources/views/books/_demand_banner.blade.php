{{-- Shown at top of book create when coming from Day Planning --}}
@if(isset($demand) && $demand)
<div style="background:#E6F1FB;border:0.5px solid #B5D4F4;border-radius:12px;padding:14px 16px;margin-bottom:14px;display:flex;align-items:flex-start;gap:12px">
    <div style="flex:1">
        <div style="font-size:12px;font-weight:500;color:#185FA5;margin-bottom:6px;display:flex;align-items:center;gap:8px">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M7 1L1 4v3c0 3.3 2.5 6.4 6 7 3.5-.6 6-3.7 6-7V4L7 1z" stroke="#185FA5" stroke-width="1.2" stroke-linejoin="round"/></svg>
            Allocating crew for demand from Day Planning
        </div>
        <div style="font-size:14px;font-weight:500;color:#1a1a18;margin-bottom:4px">
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
    <a href="{{ route('planning.index', ['date' => $demand->date->toDateString()]) }}"
       style="font-size:11px;color:#185FA5;text-decoration:none;white-space:nowrap;padding:4px 10px;border:0.5px solid #85B7EB;border-radius:6px">
        ← Back to planning
    </a>
</div>
@endif
