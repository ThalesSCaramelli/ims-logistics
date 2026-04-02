@extends('layouts.app')
@section('title', 'Products & Prices')
@section('breadcrumb')<span>Products & Prices</span>@endsection

@push('styles')
<style>
/* ── Pricing tables ── */
.ptable{width:100%;border-collapse:collapse;font-size:13px}
.ptable th{text-align:left;font-size:10px;font-weight:500;color:#73726c;text-transform:uppercase;letter-spacing:0.4px;padding:7px 10px;white-space:nowrap}
.ptable td{padding:8px 10px;border-bottom:0.5px solid rgba(0,0,0,0.06);vertical-align:middle}
.ptable tr:last-child td{border-bottom:none}
.ptable input,.ptable select{font-size:13px;padding:5px 8px;border:0.5px solid #c2c0b6;border-radius:7px;font-family:inherit;width:100%;background:#fff;color:#1a1a18}
.ptable input:focus,.ptable select:focus{outline:none;border-color:#185FA5}
.th-c{background:#EBF4FF;color:#185FA5 !important}
.th-w{background:#EAF3DE;color:#3B6D11 !important}
.th-m{background:#F1EFE8;color:#5F5E5A !important}
.in-c{border-color:#B5D4F4 !important}
.in-w{border-color:#C0DD97 !important}
.mc{font-size:12px;font-weight:500;white-space:nowrap}
.mc.pos{color:#3B6D11}
.mc.neg{color:#A32D2D}

/* ── Client accordion ── */
.cacc{background:#fff;border:0.5px solid rgba(0,0,0,0.1);border-radius:12px;margin-bottom:10px;overflow:hidden}
.cacc-hdr{display:flex;align-items:center;gap:10px;padding:13px 16px;cursor:pointer;transition:background .15s}
.cacc-hdr:hover{background:#f9f8f6}
.cav{width:32px;height:32px;border-radius:8px;background:#E6F1FB;color:#185FA5;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;flex-shrink:0}
.cacc-body{display:none;border-top:0.5px solid rgba(0,0,0,0.08)}
.cacc-body.open{display:block}
.csec{padding:16px;border-bottom:0.5px solid rgba(0,0,0,0.07)}
.csec:last-child{border-bottom:none}
.csec-title{font-size:13px;font-weight:500;margin-bottom:12px;display:flex;align-items:center;justify-content:space-between;color:#1a1a18}

/* ── Additional config ── */
.addl-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:10px}
.addl-field{display:flex;flex-direction:column;gap:4px}
.addl-field label{font-size:11px;color:#73726c}
.addl-field input{font-size:13px;padding:6px 9px;border:0.5px solid #c2c0b6;border-radius:8px;font-family:inherit;width:100%;background:#fff}
.addl-field input.in-c{border-color:#B5D4F4}
.addl-field input.in-w{border-color:#C0DD97}
.addl-field input:focus{outline:none;border-color:#185FA5}
.addl-example{font-size:11px;color:#73726c;background:#f5f4f0;border-radius:6px;padding:6px 10px;margin-top:6px;line-height:1.5}

/* ── Remove btn ── */
.rm{background:none;border:none;cursor:pointer;color:#73726c;font-size:16px;width:26px;height:26px;display:flex;align-items:center;justify-content:center;border-radius:4px;flex-shrink:0}
.rm:hover{color:#A32D2D;background:#FCEBEB}
</style>
@endpush

@section('content')
<div class="page">
    <div class="page-header">
        <div>
            <div class="page-title">Products & Prices</div>
            <div class="page-sub">Configure prices per client — containers, additionals and hourly services</div>
        </div>
    </div>

    {{-- Legend --}}
    <div style="display:flex;gap:14px;margin-bottom:16px;font-size:12px;color:#73726c;flex-wrap:wrap">
        <div style="display:flex;align-items:center;gap:5px"><div style="width:12px;height:12px;border-radius:3px;background:#B5D4F4"></div>Client rate (charged to client)</div>
        <div style="display:flex;align-items:center;gap:5px"><div style="width:12px;height:12px;border-radius:3px;background:#C0DD97"></div>Worker rate (paid to crew)</div>
        <div style="display:flex;align-items:center;gap:5px"><div style="width:12px;height:12px;border-radius:3px;background:#D3D1C7"></div>Margin (auto)</div>
    </div>

    {{-- Product catalogue --}}
    <div class="card" style="margin-bottom:20px">
        <div class="card-title">
            <span>Services / Products</span>
            <span style="font-size:12px;color:#73726c;font-weight:400">{{ $products->count() }} registered</span>
        </div>
        <p style="font-size:12px;color:#73726c;margin-bottom:12px">
            Products are used to differentiate container types — e.g. Standard, FAK, FCL, Solar Panel.
            Standard containers have no special product assigned.
        </p>
        <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:14px">
            @foreach($products as $product)
            <div style="display:flex;align-items:center;gap:8px;padding:6px 12px;background:#f5f4f0;border:0.5px solid rgba(0,0,0,0.1);border-radius:8px">
                @if($product->code)<span style="font-size:11px;font-weight:600;background:#EEEDFE;color:#3C3489;padding:1px 7px;border-radius:6px">{{ $product->code }}</span>@endif
                <span style="font-size:13px">{{ $product->name }}</span>
                <span style="font-size:10px;padding:2px 7px;border-radius:10px;font-weight:500;{{ $product->is_active?'background:#EAF3DE;color:#3B6D11':'background:#F1EFE8;color:#5F5E5A' }}">
                    {{ $product->is_active?'Active':'Inactive' }}
                </span>
                <form method="POST" action="{{ route('products.toggleActive', $product) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn btn-sm">{{ $product->is_active?'Deactivate':'Activate' }}</button>
                </form>
            </div>
            @endforeach
        </div>
        <div style="border-top:0.5px solid rgba(0,0,0,0.08);padding-top:12px">
            <form method="POST" action="{{ route('products.store') }}" style="display:flex;gap:8px;align-items:flex-end;flex-wrap:wrap">
                @csrf
                <div class="field" style="flex:0 0 80px"><label>Code</label><input type="text" name="code" placeholder="FAK" style="text-transform:uppercase"></div>
                <div class="field" style="flex:0 0 130px">
                    <label>Type *</label>
                    <select name="type" required>
                        <option value="container">Container</option>
                        <option value="hour">Hourly</option>
                        <option value="mixed">Mixed</option>
                    </select>
                </div>
                <div class="field" style="flex:1;min-width:160px"><label>Name *</label><input type="text" name="name" required placeholder="e.g. FAK — General Cargo"></div>
                <button type="submit" class="btn btn-primary">Add product</button>
            </form>
        </div>
    </div>

    {{-- Special days --}}
    <div class="card" style="margin-bottom:20px">
        <div class="card-title">
            <span>Public holidays & special days</span>
            <span style="font-size:12px;color:#73726c;font-weight:400">Holiday multiplier is set per service in each client's hourly rates</span>
        </div>
        <form method="POST" action="{{ route('special-days.store') }}" style="display:flex;gap:8px;align-items:flex-end;flex-wrap:wrap;margin-bottom:12px">
            @csrf
            <div class="field"><label>Date *</label><input type="date" name="date" required></div>
            <div class="field" style="flex:1;min-width:140px"><label>Description *</label><input type="text" name="description" required placeholder="e.g. Christmas Day"></div>
            <button type="submit" class="btn btn-primary">Add day</button>
        </form>
        @if($specialDays->count())
        <div style="display:flex;flex-wrap:wrap;gap:6px">
            @foreach($specialDays as $day)
            <div style="display:flex;align-items:center;gap:8px;padding:5px 12px;background:#FAEEDA;border:0.5px solid #FAC775;border-radius:8px;font-size:12px">
                <span style="font-weight:500">{{ \Carbon\Carbon::parse($day->date)->format('d M Y') }}</span>
                <span style="color:#854F0B">{{ $day->description }}</span>
                <form method="POST" action="{{ route('special-days.destroy', $day) }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="rm" style="font-size:13px">×</button>
                </form>
            </div>
            @endforeach
        </div>
        @else
            <p style="font-size:12px;color:#73726c;font-style:italic">No special days registered.</p>
        @endif
    </div>

    {{-- Per-client pricing --}}
    <div style="font-size:15px;font-weight:500;margin-bottom:12px">Pricing by client</div>

    @foreach($clients as $client)
    @php
        $initials = collect(explode(' ',$client->name))->map(fn($p)=>strtoupper(substr($p,0,1)))->take(2)->join('');
        $containerPrices = $client->containerPrices ?? collect();
        $boxAdditional   = $client->boxAdditional;
        $skillAdditional = $client->skillAdditional;
        $hourlyRates     = ($client->hourlyRates ?? collect())->keyBy('service_type');
    @endphp
    <div class="cacc">
        <div class="cacc-hdr" onclick="toggleAcc('{{ $client->id }}')">
            <div class="cav">{{ $initials }}</div>
            <div style="flex:1">
                <div style="font-size:14px;font-weight:500">{{ $client->name }}</div>
                <div style="font-size:12px;color:#73726c">
                    {{ $containerPrices->count() }} container prices
                    · boxes {{ $boxAdditional ? 'configured' : 'not set' }}
                    · skills {{ $skillAdditional ? 'configured' : 'not set' }}
                </div>
            </div>
            <svg class="chevron" id="chev-{{ $client->id }}" viewBox="0 0 16 16" fill="none"><path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>

        <div class="cacc-body" id="acc-{{ $client->id }}">

            {{-- 1. Container prices --}}
            <div class="csec">
                <div class="csec-title">
                    Container prices
                    <button type="button" class="btn btn-sm" onclick="addCPRow({{ $client->id }})">+ Add row</button>
                </div>
                <p style="font-size:12px;color:#73726c;margin-bottom:10px">Leave product blank for Standard containers.</p>
                <form method="POST" action="{{ route('clients.container-prices.save', $client) }}">
                    @csrf
                    <table class="ptable">
                        <thead><tr>
                            <th>Size</th>
                            <th>Product (blank = Standard)</th>
                            <th class="th-c">Client rate / container</th>
                            <th class="th-w">Worker rate / container</th>
                            <th class="th-m">Margin</th>
                            <th></th>
                        </tr></thead>
                        <tbody id="cp-{{ $client->id }}">
                        @foreach($containerPrices as $price)
                        @php $m = ($price->client_rate??0) - ($price->worker_rate??0); @endphp
                        <tr>
                            <td style="width:80px">
                                <select name="prices[{{ $loop->index }}][feet]">
                                    <option value="20" {{ $price->feet==='20'?'selected':'' }}>20ft</option>
                                    <option value="40" {{ $price->feet==='40'?'selected':'' }}>40ft</option>
                                </select>
                            </td>
                            <td>
                                <select name="prices[{{ $loop->index }}][product_id]">
                                    <option value="">Standard</option>
                                    @foreach($products->where('type','container') as $p)
                                        <option value="{{ $p->id }}" {{ $price->product_id===$p->id?'selected':'' }}>{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td style="width:140px"><input type="number" name="prices[{{ $loop->index }}][client_rate]" value="{{ $price->client_rate }}" step="0.01" min="0" placeholder="0.00" class="in-c" oninput="calcM(this)"></td>
                            <td style="width:140px"><input type="number" name="prices[{{ $loop->index }}][worker_rate]" value="{{ $price->worker_rate }}" step="0.01" min="0" placeholder="0.00" class="in-w" oninput="calcM(this)"></td>
                            <td style="width:80px" class="mc {{ $m>=0?'pos':'neg' }}">${{ number_format(abs($m),2) }}</td>
                            <td style="width:32px"><button type="button" class="rm" onclick="this.closest('tr').remove()">×</button></td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <div style="display:flex;justify-content:flex-end;margin-top:10px">
                        <button type="submit" class="btn btn-primary btn-sm">Save container prices</button>
                    </div>
                </form>
            </div>

            {{-- 2. Box additionals --}}
            <div class="csec">
                <div class="csec-title">Box / Carton additionals</div>
                <form method="POST" action="{{ route('clients.box-additional.save', $client) }}">
                    @csrf
                    <div class="addl-grid">
                        <div class="addl-field">
                            <label>Threshold (boxes)</label>
                            <input type="number" name="threshold" value="{{ $boxAdditional?->threshold }}" min="0" placeholder="e.g. 1500">
                        </div>
                        <div class="addl-field">
                            <label>Block size (boxes)</label>
                            <input type="number" name="block_size" value="{{ $boxAdditional?->block_size }}" min="1" placeholder="e.g. 500">
                        </div>
                        <div class="addl-field">
                            <label>Client rate / block</label>
                            <input type="number" name="client_rate_per_block" value="{{ $boxAdditional?->client_rate_per_block }}" step="0.01" min="0" placeholder="0.00" class="in-c">
                        </div>
                        <div class="addl-field">
                            <label>Worker rate / block</label>
                            <input type="number" name="worker_rate_per_block" value="{{ $boxAdditional?->worker_rate_per_block }}" step="0.01" min="0" placeholder="0.00" class="in-w">
                        </div>
                    </div>
                    @if($boxAdditional)
                    <div class="addl-example">
                        Example: container with 2100 boxes →
                        above {{ $boxAdditional->threshold }} = {{ 2100 - $boxAdditional->threshold }} extra →
                        {{ ceil((2100 - $boxAdditional->threshold) / $boxAdditional->block_size) }} block(s) →
                        client +${{ number_format(ceil((2100 - $boxAdditional->threshold) / $boxAdditional->block_size) * $boxAdditional->client_rate_per_block, 2) }},
                        worker +${{ number_format(ceil((2100 - $boxAdditional->threshold) / $boxAdditional->block_size) * $boxAdditional->worker_rate_per_block, 2) }}
                    </div>
                    @endif
                    <div style="display:flex;justify-content:flex-end;margin-top:10px">
                        <button type="submit" class="btn btn-primary btn-sm">Save box additionals</button>
                    </div>
                </form>
            </div>

            {{-- 3. Skill additionals --}}
            <div class="csec">
                <div class="csec-title">Skill additionals</div>
                <form method="POST" action="{{ route('clients.skill-additional.save', $client) }}">
                    @csrf
                    <div class="addl-grid">
                        <div class="addl-field">
                            <label>Threshold (skills)</label>
                            <input type="number" name="threshold" value="{{ $skillAdditional?->threshold }}" min="0" placeholder="e.g. 20">
                        </div>
                        <div class="addl-field">
                            <label>Block size (skills)</label>
                            <input type="number" name="block_size" value="{{ $skillAdditional?->block_size }}" min="1" placeholder="e.g. 5">
                        </div>
                        <div class="addl-field">
                            <label>Client rate / block</label>
                            <input type="number" name="client_rate_per_block" value="{{ $skillAdditional?->client_rate_per_block }}" step="0.01" min="0" placeholder="0.00" class="in-c">
                        </div>
                        <div class="addl-field">
                            <label>Worker rate / block</label>
                            <input type="number" name="worker_rate_per_block" value="{{ $skillAdditional?->worker_rate_per_block }}" step="0.01" min="0" placeholder="0.00" class="in-w">
                        </div>
                    </div>
                    <div style="display:flex;justify-content:flex-end;margin-top:10px">
                        <button type="submit" class="btn btn-primary btn-sm">Save skill additionals</button>
                    </div>
                </form>
            </div>

            {{-- 4. Hourly services --}}
            <div class="csec">
                <div class="csec-title">Hourly services</div>
                <form method="POST" action="{{ route('clients.hourly-rates.save', $client) }}">
                    @csrf
                    <table class="ptable">
                        <thead><tr>
                            <th>Service</th>
                            <th class="th-c">Client rate / hour</th>
                            <th class="th-w">Worker rate / hour</th>
                            <th class="th-m">Margin / hour</th>
                            <th style="white-space:nowrap">Holiday multiplier</th>
                        </tr></thead>
                        <tbody>
                        @foreach(['labour_hire'=>'Labour Hire','extra_work'=>'Extra Work','waiting_time'=>'Waiting Time'] as $type => $label)
                        @php $hr = $hourlyRates->get($type); $m = $hr ? ($hr->client_rate_per_hour - $hr->worker_rate_per_hour) : null; @endphp
                        <tr>
                            <td style="font-size:13px;font-weight:500;white-space:nowrap">
                                {{ $label }}
                                <input type="hidden" name="hourly[{{ $type }}][service_type]" value="{{ $type }}">
                            </td>
                            <td style="width:130px"><input type="number" name="hourly[{{ $type }}][client_rate_per_hour]" value="{{ $hr?->client_rate_per_hour }}" step="0.01" min="0" placeholder="0.00" class="in-c" oninput="calcHM(this)"></td>
                            <td style="width:130px"><input type="number" name="hourly[{{ $type }}][worker_rate_per_hour]" value="{{ $hr?->worker_rate_per_hour }}" step="0.01" min="0" placeholder="0.00" class="in-w" oninput="calcHM(this)"></td>
                            <td style="width:80px" class="mc {{ $m!==null?($m>=0?'pos':'neg'):'' }}">
                                {{ $m!==null ? '$'.number_format(abs($m),2) : '—' }}
                            </td>
                            <td style="width:130px">
                                <div style="display:flex;align-items:center;gap:5px">
                                    <input type="number" name="hourly[{{ $type }}][holiday_multiplier]" value="{{ $hr?->holiday_multiplier ?? 1.00 }}" step="0.05" min="1" max="5" placeholder="1.00" style="width:70px">
                                    <span style="font-size:11px;color:#73726c">× rate</span>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <div style="font-size:11px;color:#73726c;margin-top:8px">
                        Holiday multiplier is applied automatically when the job date is a registered special day.
                        e.g. 1.5 = client pays 1.5× the hourly rate on public holidays.
                    </div>
                    <div style="display:flex;justify-content:flex-end;margin-top:10px">
                        <button type="submit" class="btn btn-primary btn-sm">Save hourly rates</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
    @endforeach
</div>
@endsection

@push('scripts')
<script>
// Container price rows
var productOptions = '<option value="">Standard</option>';
@foreach($products->where('type','container') as $p)
productOptions += '<option value="{{ $p->id }}">{{ addslashes($p->name) }}</option>';
@endforeach

var cpCtrs = {};
@foreach($clients as $c) cpCtrs[{{ $c->id }}] = {{ ($c->containerPrices ?? collect())->count() }}; @endforeach

function addCPRow(cid) {
    if (!cpCtrs[cid]) cpCtrs[cid] = 0;
    var i = cpCtrs[cid]++;
    var tr = document.createElement('tr');
    tr.innerHTML =
        '<td><select name="prices['+i+'][feet]"><option value="20">20ft</option><option value="40">40ft</option></select></td>'+
        '<td><select name="prices['+i+'][product_id]">'+productOptions+'</select></td>'+
        '<td><input type="number" name="prices['+i+'][client_rate]" step="0.01" min="0" placeholder="0.00" class="in-c" oninput="calcM(this)"></td>'+
        '<td><input type="number" name="prices['+i+'][worker_rate]" step="0.01" min="0" placeholder="0.00" class="in-w" oninput="calcM(this)"></td>'+
        '<td class="mc">—</td>'+
        '<td><button type="button" class="rm" onclick="this.closest(\'tr\').remove()">×</button></td>';
    document.getElementById('cp-'+cid).appendChild(tr);
}

function calcM(input) {
    var row = input.closest('tr');
    var ci = row.querySelector('.in-c');
    var wi = row.querySelector('.in-w');
    var mc = row.querySelector('.mc');
    if (!ci||!wi||!mc) return;
    var m = parseFloat(ci.value||0) - parseFloat(wi.value||0);
    mc.textContent = '$'+Math.abs(m).toFixed(2);
    mc.className = 'mc '+(m>=0?'pos':'neg');
}

function calcHM(input) {
    var row = input.closest('tr');
    var ci = row.querySelector('.in-c');
    var wi = row.querySelector('.in-w');
    var mc = row.querySelector('.mc');
    if (!ci||!wi||!mc) return;
    var m = parseFloat(ci.value||0) - parseFloat(wi.value||0);
    mc.textContent = '$'+Math.abs(m).toFixed(2);
    mc.className = 'mc '+(m>=0?'pos':'neg');
}
</script>
@endpush
