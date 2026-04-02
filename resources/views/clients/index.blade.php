@extends('layouts.app')
@section('title', 'Clients')
@section('breadcrumb')<span>Clients</span>@endsection

@push('styles')
<style>
.client-row{display:grid;grid-template-columns:1fr 170px 120px 90px 90px;padding:12px 16px;border-bottom:0.5px solid rgba(0,0,0,0.07);align-items:center;text-decoration:none;color:inherit;transition:background .1s}
.client-row:last-child{border-bottom:none}
.client-row:hover{background:#f9f8f6}
.cav{width:32px;height:32px;border-radius:8px;background:#E6F1FB;color:#185FA5;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;flex-shrink:0}
</style>
@endpush

@section('content')
<div class="page">
    <div class="page-header">
        <div>
            <div class="page-title">Clients</div>
            <div class="page-sub">{{ $clients->count() }} clients registered</div>
        </div>
        <button type="button" class="btn btn-primary" onclick="document.getElementById('modal-add').style.display='flex'">+ Add client</button>
    </div>

    <div class="card" style="padding:12px 16px;margin-bottom:14px">
        <div class="search-wrap">
            <svg class="search-icon" width="13" height="13" viewBox="0 0 14 14" fill="none"><circle cx="6" cy="6" r="4.5" stroke="#1a1a18" stroke-width="1.2"/><path d="M9.5 9.5L12.5 12.5" stroke="#1a1a18" stroke-width="1.2" stroke-linecap="round"/></svg>
            <input type="text" placeholder="Search clients..." oninput="filterClients(this.value)">
        </div>
    </div>

    <div class="card" style="padding:0;overflow:hidden">
        <div style="display:grid;grid-template-columns:1fr 170px 120px 90px 90px;padding:9px 16px;background:#f5f4f0;border-bottom:0.5px solid rgba(0,0,0,0.1)">
            @foreach(['Client','Contact','ABN','Status',''] as $h)
            <span style="font-size:11px;font-weight:500;color:#73726c;text-transform:uppercase;letter-spacing:0.4px">{{ $h }}</span>
            @endforeach
        </div>

        @forelse($clients as $client)
        @php $initials = collect(explode(' ',$client->name))->map(fn($p)=>strtoupper(substr($p,0,1)))->take(2)->join(''); @endphp
        <a href="{{ route('clients.show', $client) }}" class="client-row" data-name="{{ strtolower($client->name) }}">
            <div style="display:flex;align-items:center;gap:10px">
                <div class="cav">{{ $initials }}</div>
                <div>
                    <div style="font-size:13px;font-weight:500">{{ $client->name }}</div>
                    <div style="font-size:11px;color:#73726c;margin-top:1px">
                        {{ $client->sites->count() }} site(s)
                        @if($client->requires_induction ?? false)· <span style="color:#854F0B">⚠ Induction req.</span>@endif
                    </div>
                </div>
            </div>
            <div style="font-size:12px;color:#73726c">
                @if($client->contact_name)<div>{{ $client->contact_name }}</div>@endif
                @if($client->contact_phone)<div>{{ $client->contact_phone }}</div>@endif
            </div>
            <div style="font-size:12px;color:#73726c">{{ $client->abn ?? '—' }}</div>
            <div><span class="pill {{ $client->is_active?'pill-active':'pill-inactive' }}">{{ $client->is_active?'Active':'Inactive' }}</span></div>
            <div style="display:flex;justify-content:flex-end"><span class="btn btn-sm">View →</span></div>
        </a>
        @empty
        <div class="empty-state"><h3>No clients yet</h3><p>Add your first client to get started.</p></div>
        @endforelse
    </div>
</div>

{{-- Add client modal --}}
<div id="modal-add" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:200;align-items:center;justify-content:center;padding:20px">
    <div style="background:#fff;border-radius:16px;padding:24px;width:100%;max-width:500px;max-height:90vh;overflow-y:auto">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px">
            <div style="font-size:16px;font-weight:500">Add client</div>
            <button type="button" onclick="document.getElementById('modal-add').style.display='none'" style="background:none;border:none;font-size:22px;cursor:pointer;color:#73726c;line-height:1">×</button>
        </div>
        <form method="POST" action="{{ route('clients.store') }}">
            @csrf
            <div style="display:flex;flex-direction:column;gap:12px">
                <div class="field"><label>Client name *</label><input type="text" name="name" required placeholder="e.g. Frutex Australia" autofocus></div>
                <div class="field"><label>ABN</label><input type="text" name="abn" placeholder="12 345 678 901"></div>
                <div class="grid-2">
                    <div class="field"><label>Contact name</label><input type="text" name="contact_name"></div>
                    <div class="field"><label>Contact phone</label><input type="tel" name="contact_phone"></div>
                </div>
                <div class="field"><label>Contact email</label><input type="email" name="contact_email"></div>
                <div style="display:flex;align-items:center;gap:10px;padding:10px;background:#FAEEDA;border-radius:8px">
                    <input type="checkbox" name="requires_induction" value="1" id="chk-ind" style="width:16px;height:16px;flex-shrink:0">
                    <label for="chk-ind" style="font-size:13px;cursor:pointer;color:#854F0B">Requires worker induction before first job</label>
                </div>
                <div class="field"><label>Notes</label><textarea name="notes" rows="2" style="font-size:13px;padding:7px 10px;border:0.5px solid #c2c0b6;border-radius:8px;font-family:inherit;width:100%;resize:none" placeholder="Internal notes..."></textarea></div>
            </div>
            <div style="display:flex;gap:8px;margin-top:18px;justify-content:flex-end">
                <button type="button" onclick="document.getElementById('modal-add').style.display='none'" class="btn">Cancel</button>
                <button type="submit" class="btn btn-primary" style="width:auto">Add client</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function filterClients(q) {
    q = q.toLowerCase();
    document.querySelectorAll('.client-row').forEach(function(r) {
        r.style.display = !q || r.dataset.name.includes(q) ? '' : 'none';
    });
}
</script>
@endpush
