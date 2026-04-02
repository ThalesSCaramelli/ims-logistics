@extends('layouts.app')
@section('title', $client->name)
@section('breadcrumb')
    <a href="{{ route('clients.index') }}">Clients</a>
    <span>/</span>
    <span>{{ $client->name }}</span>
@endsection

@push('styles')
<style>
.ptable{width:100%;border-collapse:collapse;font-size:12px}
.ptable th{text-align:left;font-size:10px;font-weight:500;color:var(--color-text-secondary,#73726c);text-transform:uppercase;letter-spacing:0.4px;padding:7px 8px;white-space:nowrap;background:var(--color-background-secondary,#f5f4f0)}
.th-c{background:#EBF4FF !important;color:#185FA5 !important}
.th-w{background:#EAF3DE !important;color:#3B6D11 !important}
.th-m{background:#F1EFE8 !important;color:#5F5E5A !important}
.ptable td{padding:7px 8px;border-bottom:0.5px solid rgba(0,0,0,0.06);vertical-align:middle}
.ptable tr:last-child td{border-bottom:none}
.ptable input,.ptable select{font-size:12px;padding:4px 7px;border:0.5px solid #c2c0b6;border-radius:6px;font-family:inherit;width:100%;background:#fff;color:#1a1a18}
.ptable input:focus,.ptable select:focus{outline:none;border-color:#185FA5}
.in-c{border-color:#B5D4F4 !important}
.in-w{border-color:#C0DD97 !important}
.mc{font-size:11px;font-weight:500;white-space:nowrap}
.mc.pos{color:#3B6D11}.mc.neg{color:#A32D2D}
.rm{background:none;border:none;cursor:pointer;color:#73726c;font-size:16px;width:24px;height:24px;display:flex;align-items:center;justify-content:center;border-radius:4px;flex-shrink:0}
.rm:hover{color:#A32D2D;background:#FCEBEB}

/* Expand sections */
.expand-sec{display:none;background:#f9f8f6;border-top:0.5px solid rgba(0,0,0,0.07)}
.expand-inner{padding:10px 12px 10px 28px}
.expand-title{font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:0.4px;margin-bottom:8px}
.expand-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:8px}
.ef{display:flex;flex-direction:column;gap:3px}
.ef label{font-size:10px;color:#73726c}
.ef input{font-size:12px;padding:5px 8px;border:0.5px solid #c2c0b6;border-radius:7px;font-family:inherit;width:100%;background:#fff}
.ef input:focus{outline:none;border-color:#185FA5}
.expand-ex{font-size:10px;color:#73726c;background:rgba(0,0,0,0.04);border-radius:5px;padding:5px 8px;margin-top:6px;line-height:1.5}

/* Checkbox style */
.chk-cell{text-align:center}
.chk-wrap{display:inline-flex;align-items:center;gap:4px;cursor:pointer}
.chk-wrap input[type=checkbox]{width:14px;height:14px;accent-color:#185FA5;cursor:pointer;flex-shrink:0}
.chk-lbl{font-size:10px;color:#73726c}

.csec{padding:14px 16px;border-bottom:0.5px solid rgba(0,0,0,0.07)}
.csec:last-child{border-bottom:none}
.csec-title{font-size:13px;font-weight:500;margin-bottom:10px;display:flex;align-items:center;justify-content:space-between}
.site-row{display:flex;align-items:center;gap:8px;padding:7px 0;border-bottom:0.5px solid rgba(0,0,0,0.06)}
.site-row:last-child{border-bottom:none}
.hist-row{padding:7px 0;border-bottom:0.5px solid rgba(0,0,0,0.06);font-size:12px}
.hist-row:last-child{border-bottom:none}
</style>
@endpush

@section('content')
<div class="page">

    {{-- Header --}}
    <div class="page-header">
        <div style="display:flex;align-items:center;gap:14px">
            <div style="width:44px;height:44px;border-radius:10px;background:#E6F1FB;color:#185FA5;display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:700;flex-shrink:0">
                {{ collect(explode(' ',$client->name))->map(fn($p)=>strtoupper(substr($p,0,1)))->take(2)->join('') }}
            </div>
            <div>
                <div class="page-title">{{ $client->name }}</div>
                <div style="display:flex;gap:8px;align-items:center;margin-top:4px">
                    <span class="pill {{ $client->is_active?'pill-active':'pill-inactive' }}">{{ $client->is_active?'Active':'Inactive' }}</span>
                    @if($client->requires_induction??false)<span class="pill pill-pending">⚠ Induction required</span>@endif
                    @if($client->abn)<span style="font-size:12px;color:#73726c">ABN: {{ $client->abn }}</span>@endif
                </div>
            </div>
        </div>
        <div style="display:flex;gap:8px">
            <a href="{{ route('clients.index') }}" class="btn">← Back</a>
            <button type="button" class="btn btn-primary" onclick="document.getElementById('modal-edit').style.display='flex'">Edit client</button>
        </div>
    </div>

    <div class="page-2col">
        <div>

            {{-- Legend --}}
            <div style="display:flex;gap:14px;margin-bottom:14px;font-size:11px;color:#73726c">
                <span style="display:flex;align-items:center;gap:4px"><span style="width:10px;height:10px;border-radius:2px;background:#B5D4F4;display:inline-block"></span>Client rate</span>
                <span style="display:flex;align-items:center;gap:4px"><span style="width:10px;height:10px;border-radius:2px;background:#C0DD97;display:inline-block"></span>Worker rate</span>
                <span style="display:flex;align-items:center;gap:4px"><span style="width:10px;height:10px;border-radius:2px;background:#D3D1C7;display:inline-block"></span>Margin</span>
            </div>

            <div class="card" style="padding:0;overflow:hidden;margin-bottom:14px">

                {{-- Container prices --}}
                <div class="csec">
                    <div class="csec-title">
                        Container prices
                        <button type="button" class="btn btn-sm" onclick="addRow()">+ Add row</button>
                    </div>
                    <p style="font-size:11px;color:#73726c;margin-bottom:10px">Configure rates per size + product. Enable box/skill additionals per row if applicable.</p>

                    <form method="POST" action="{{ route('clients.container-prices.save', $client) }}" id="cp-form">
                        @csrf

                        {{-- Table header --}}
                        <table class="ptable" id="cp-table">
                            <thead>
                                <tr>
                                    <th style="width:70px">Size</th>
                                    <th>Product</th>
                                    <th class="th-c" style="width:100px">Client rate</th>
                                    <th class="th-w" style="width:100px">Worker rate</th>
                                    <th class="th-m" style="width:70px">Margin</th>
                                    <th style="width:55px;text-align:center">Boxes?</th>
                                    <th style="width:55px;text-align:center">Skills?</th>
                                    <th style="width:24px"></th>
                                </tr>
                            </thead>
                            <tbody id="cp-body">

                            @foreach($client->containerPrices as $price)
                            @php
                                $idx = $loop->index;
                                $m   = ($price->client_rate??0) - ($price->worker_rate??0);
                                $rid = 'row-'.$idx;
                            @endphp
                            <tr id="{{ $rid }}">
                                <td>
                                    <select name="prices[{{ $idx }}][feet]">
                                        <option value="20" {{ $price->feet==='20'?'selected':'' }}>20ft</option>
                                        <option value="40" {{ $price->feet==='40'?'selected':'' }}>40ft</option>
                                    </select>
                                </td>
                                <td>
                                    <select name="prices[{{ $idx }}][product_id]">
                                        <option value="">Standard</option>
                                        @foreach($products as $p)
                                            <option value="{{ $p->id }}" {{ $price->product_id===$p->id?'selected':'' }}>{{ $p->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="number" name="prices[{{ $idx }}][client_rate]" value="{{ $price->client_rate }}" step="0.01" min="0" class="in-c" oninput="calcM(this)"></td>
                                <td><input type="number" name="prices[{{ $idx }}][worker_rate]" value="{{ $price->worker_rate }}" step="0.01" min="0" class="in-w" oninput="calcM(this)"></td>
                                <td class="mc {{ $m>=0?'pos':'neg' }}">${{ number_format(abs($m),2) }}</td>
                                <td class="chk-cell">
                                    <label class="chk-wrap">
                                        <input type="checkbox" name="prices[{{ $idx }}][has_box_additional]" value="1"
                                            {{ $price->has_box_additional?'checked':'' }}
                                            onchange="toggleExpand('box-{{ $idx }}',this)">
                                        <span class="chk-lbl">{{ $price->has_box_additional?'Yes':'No' }}</span>
                                    </label>
                                </td>
                                <td class="chk-cell">
                                    <label class="chk-wrap">
                                        <input type="checkbox" name="prices[{{ $idx }}][has_skill_additional]" value="1"
                                            {{ $price->has_skill_additional?'checked':'' }}
                                            onchange="toggleExpand('skill-{{ $idx }}',this)">
                                        <span class="chk-lbl">{{ $price->has_skill_additional?'Yes':'No' }}</span>
                                    </label>
                                </td>
                                <td><button type="button" class="rm" onclick="removeRow('{{ $rid }}')">×</button></td>
                            </tr>

                            {{-- Box expand --}}
                            <tr id="box-{{ $idx }}-row" style="{{ $price->has_box_additional?'':'display:none' }}">
                                <td colspan="8" style="padding:0">
                                    <div class="expand-sec" id="box-{{ $idx }}" style="{{ $price->has_box_additional?'display:block':'' }}">
                                        <div class="expand-inner">
                                            <div class="expand-title" style="color:#185FA5">Box / carton additionals</div>
                                            <div class="expand-grid">
                                                <div class="ef"><label>Threshold (boxes)</label><input type="number" name="prices[{{ $idx }}][box_threshold]" value="{{ $price->box_threshold }}" min="0" placeholder="e.g. 1500" style="border-color:#B5D4F4"></div>
                                                <div class="ef"><label>Block size</label><input type="number" name="prices[{{ $idx }}][box_block_size]" value="{{ $price->box_block_size }}" min="1" placeholder="e.g. 500" style="border-color:#B5D4F4"></div>
                                                <div class="ef"><label>Client rate / block</label><input type="number" name="prices[{{ $idx }}][box_client_rate_per_block]" value="{{ $price->box_client_rate_per_block }}" step="0.01" min="0" class="in-c"></div>
                                                <div class="ef"><label>Worker rate / block</label><input type="number" name="prices[{{ $idx }}][box_worker_rate_per_block]" value="{{ $price->box_worker_rate_per_block }}" step="0.01" min="0" class="in-w"></div>
                                            </div>
                                            @if($price->box_threshold && $price->box_block_size)
                                            <div class="expand-ex">
                                                Example: 2100 boxes → {{ 2100 - $price->box_threshold }} above threshold → {{ ceil((2100 - $price->box_threshold) / $price->box_block_size) }} block(s) →
                                                client +${{ number_format(ceil((2100 - $price->box_threshold) / $price->box_block_size) * ($price->box_client_rate_per_block??0), 2) }},
                                                workers +${{ number_format(ceil((2100 - $price->box_threshold) / $price->box_block_size) * ($price->box_worker_rate_per_block??0), 2) }}
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            {{-- Skill expand --}}
                            <tr id="skill-{{ $idx }}-row" style="{{ $price->has_skill_additional?'':'display:none' }}">
                                <td colspan="8" style="padding:0">
                                    <div class="expand-sec" id="skill-{{ $idx }}" style="{{ $price->has_skill_additional?'display:block':'' }}">
                                        <div class="expand-inner">
                                            <div class="expand-title" style="color:#3B6D11">Skill additionals</div>
                                            <div class="expand-grid">
                                                <div class="ef"><label>Threshold (skills)</label><input type="number" name="prices[{{ $idx }}][skill_threshold]" value="{{ $price->skill_threshold }}" min="0" placeholder="e.g. 20" style="border-color:#C0DD97"></div>
                                                <div class="ef"><label>Block size</label><input type="number" name="prices[{{ $idx }}][skill_block_size]" value="{{ $price->skill_block_size }}" min="1" placeholder="e.g. 5" style="border-color:#C0DD97"></div>
                                                <div class="ef"><label>Client rate / block</label><input type="number" name="prices[{{ $idx }}][skill_client_rate_per_block]" value="{{ $price->skill_client_rate_per_block }}" step="0.01" min="0" class="in-c"></div>
                                                <div class="ef"><label>Worker rate / block</label><input type="number" name="prices[{{ $idx }}][skill_worker_rate_per_block]" value="{{ $price->skill_worker_rate_per_block }}" step="0.01" min="0" class="in-w"></div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach

                            </tbody>
                        </table>

                        <div style="padding:10px 16px">
                            <button type="button" class="add-btn" onclick="addRow()" style="margin-bottom:8px">+ Add row</button>
                            <div style="display:flex;justify-content:flex-end">
                                <button type="submit" class="btn btn-primary btn-sm">Save container prices</button>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Hourly services --}}
                <div class="csec">
                    <div class="csec-title">Hourly services</div>
                    <form method="POST" action="{{ route('clients.hourly-rates.save', $client) }}">
                        @csrf
                        <table class="ptable">
                            <thead><tr>
                                <th>Service</th>
                                <th class="th-c" style="width:100px">Client / hour</th>
                                <th class="th-w" style="width:100px">Worker / hour</th>
                                <th class="th-m" style="width:70px">Margin</th>
                                <th style="width:90px">Holiday ×</th>
                            </tr></thead>
                            <tbody>
                            @foreach(['labour_hire'=>'Labour Hire','extra_work'=>'Extra Work','waiting_time'=>'Waiting Time'] as $type => $label)
                            @php $hr = $hourlyRates->get($type); $m = $hr ? ($hr->client_rate_per_hour - $hr->worker_rate_per_hour) : null; @endphp
                            <tr>
                                <td style="font-size:12px;font-weight:500;white-space:nowrap">
                                    {{ $label }}
                                    <input type="hidden" name="hourly[{{ $type }}][service_type]" value="{{ $type }}">
                                </td>
                                <td><input type="number" name="hourly[{{ $type }}][client_rate_per_hour]" value="{{ $hr?->client_rate_per_hour }}" step="0.01" min="0" placeholder="0.00" class="in-c" oninput="calcHM(this)"></td>
                                <td><input type="number" name="hourly[{{ $type }}][worker_rate_per_hour]" value="{{ $hr?->worker_rate_per_hour }}" step="0.01" min="0" placeholder="0.00" class="in-w" oninput="calcHM(this)"></td>
                                <td class="mc {{ $m!==null?($m>=0?'pos':'neg'):'' }}">{{ $m!==null?'$'.number_format(abs($m),2):'—' }}</td>
                                <td>
                                    <div style="display:flex;align-items:center;gap:4px">
                                        <input type="number" name="hourly[{{ $type }}][holiday_multiplier]" value="{{ $hr?->holiday_multiplier??'1.00' }}" step="0.05" min="1" max="5" style="width:60px">
                                        <span style="font-size:10px;color:#73726c">×</span>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                        <div style="font-size:10px;color:#73726c;margin-top:8px;padding:0 0 4px">Holiday multiplier applied on registered special days.</div>
                        <div style="display:flex;justify-content:flex-end;margin-top:8px">
                            <button type="submit" class="btn btn-primary btn-sm">Save hourly rates</button>
                        </div>
                    </form>
                </div>

            </div>

            {{-- Price history --}}
            @if($priceHistory->count())
            <div class="card">
                <div class="card-title">
                    Price history
                    <span style="font-size:12px;color:#73726c;font-weight:400">Last {{ $priceHistory->count() }} changes</span>
                </div>
                @foreach($priceHistory as $entry)
                <div class="hist-row">
                    <div style="display:flex;align-items:center;gap:6px">
                        <span style="font-size:10px;padding:1px 7px;border-radius:4px;font-weight:500;background:#E6F1FB;color:#185FA5">
                            {{ str_replace('_',' ',ucfirst($entry->section)) }}
                        </span>
                        <span style="font-size:11px;color:#73726c">{{ $entry->changed_at->format('d M Y H:i') }} · {{ $entry->changedBy?->name ?? 'Unknown' }}</span>
                        <button type="button" onclick="toggleHist({{ $entry->id }})" style="background:none;border:none;color:#185FA5;font-size:11px;cursor:pointer;padding:0">diff</button>
                    </div>
                    <div id="hist-{{ $entry->id }}" style="display:none;margin-top:6px;padding:8px;background:#f5f4f0;border-radius:6px;font-size:10px;font-family:monospace;white-space:pre-wrap;overflow-x:auto">{{ json_encode(['before'=>$entry->previous_data,'after'=>$entry->new_data],JSON_PRETTY_PRINT) }}</div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="page-sidebar">
            <div class="card">
                <div class="card-title">Contact info</div>
                <div style="display:flex;flex-direction:column;gap:7px;font-size:12px">
                    @if($client->contact_name)<div><span style="color:#73726c;min-width:50px;display:inline-block">Name</span>{{ $client->contact_name }}</div>@endif
                    @if($client->contact_email)<div><span style="color:#73726c;min-width:50px;display:inline-block">Email</span><a href="mailto:{{ $client->contact_email }}" style="color:#185FA5">{{ $client->contact_email }}</a></div>@endif
                    @if($client->contact_phone)<div><span style="color:#73726c;min-width:50px;display:inline-block">Phone</span><a href="tel:{{ $client->contact_phone }}" style="color:#185FA5">{{ $client->contact_phone }}</a></div>@endif
                    @if($client->abn)<div><span style="color:#73726c;min-width:50px;display:inline-block">ABN</span>{{ $client->abn }}</div>@endif
                </div>
                @if($client->notes)<div style="margin-top:10px;padding-top:10px;border-top:0.5px solid rgba(0,0,0,0.08);font-size:12px;color:#73726c;line-height:1.5">{{ $client->notes }}</div>@endif
            </div>

            <div class="card">
                <div class="card-title">
                    Sites
                    <button type="button" class="btn btn-sm" onclick="document.getElementById('modal-site').style.display='flex'">+ Add</button>
                </div>
                @forelse($client->sites as $site)
                <div class="site-row">
                    <div style="flex:1">
                        <div style="font-size:13px;font-weight:500">{{ $site->name }}</div>
                        @if($site->address)<div style="font-size:11px;color:#73726c">{{ $site->address }}</div>@endif
                    </div>
                    <span class="pill {{ $site->is_active?'pill-active':'pill-inactive' }}">{{ $site->is_active?'Active':'Inactive' }}</span>
                    <form method="POST" action="{{ route('sites.toggleActive', $site) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-sm">{{ $site->is_active?'Deactivate':'Activate' }}</button>
                    </form>
                </div>
                @empty
                <p style="font-size:12px;color:#73726c;font-style:italic">No sites yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Edit client modal --}}
<div id="modal-edit" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:200;align-items:center;justify-content:center;padding:20px">
    <div style="background:#fff;border-radius:16px;padding:24px;width:100%;max-width:500px;max-height:90vh;overflow-y:auto">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px">
            <div style="font-size:16px;font-weight:500">Edit client</div>
            <button type="button" onclick="document.getElementById('modal-edit').style.display='none'" style="background:none;border:none;font-size:22px;cursor:pointer;color:#73726c">×</button>
        </div>
        <form method="POST" action="{{ route('clients.update', $client) }}">
            @csrf @method('PUT')
            <div style="display:flex;flex-direction:column;gap:12px">
                <div class="field"><label>Client name *</label><input type="text" name="name" value="{{ $client->name }}" required></div>
                <div class="field"><label>ABN</label><input type="text" name="abn" value="{{ $client->abn }}"></div>
                <div class="grid-2">
                    <div class="field"><label>Contact name</label><input type="text" name="contact_name" value="{{ $client->contact_name }}"></div>
                    <div class="field"><label>Contact phone</label><input type="tel" name="contact_phone" value="{{ $client->contact_phone }}"></div>
                </div>
                <div class="field"><label>Contact email</label><input type="email" name="contact_email" value="{{ $client->contact_email }}"></div>
                <div style="display:flex;align-items:center;gap:10px;padding:10px;background:#FAEEDA;border-radius:8px">
                    <input type="checkbox" name="requires_induction" value="1" id="edit-ind" {{ ($client->requires_induction??false)?'checked':'' }} style="width:16px;height:16px;flex-shrink:0">
                    <label for="edit-ind" style="font-size:13px;cursor:pointer;color:#854F0B">Requires worker induction</label>
                </div>
                <div style="display:flex;align-items:center;gap:10px">
                    <input type="checkbox" name="is_active" value="1" id="edit-active" {{ $client->is_active?'checked':'' }} style="width:16px;height:16px;flex-shrink:0">
                    <label for="edit-active" style="font-size:13px;cursor:pointer">Active</label>
                </div>
                <div class="field"><label>Notes</label><textarea name="notes" rows="2" style="font-size:13px;padding:7px 10px;border:0.5px solid #c2c0b6;border-radius:8px;font-family:inherit;width:100%;resize:none">{{ $client->notes }}</textarea></div>
            </div>
            <div style="display:flex;gap:8px;margin-top:18px;justify-content:flex-end">
                <button type="button" onclick="document.getElementById('modal-edit').style.display='none'" class="btn">Cancel</button>
                <button type="submit" class="btn btn-primary" style="width:auto">Save changes</button>
            </div>
        </form>
    </div>
</div>

{{-- Add site modal --}}
<div id="modal-site" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:200;align-items:center;justify-content:center;padding:20px">
    <div style="background:#fff;border-radius:16px;padding:24px;width:100%;max-width:420px">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px">
            <div style="font-size:16px;font-weight:500">Add site</div>
            <button type="button" onclick="document.getElementById('modal-site').style.display='none'" style="background:none;border:none;font-size:22px;cursor:pointer;color:#73726c">×</button>
        </div>
        <form method="POST" action="{{ route('sites.store') }}">
            @csrf
            <input type="hidden" name="client_id" value="{{ $client->id }}">
            <div style="display:flex;flex-direction:column;gap:12px">
                <div class="field"><label>Site name *</label><input type="text" name="name" required placeholder="e.g. Botany Warehouse" autofocus></div>
                <div class="field"><label>Address</label><input type="text" name="address" placeholder="123 Main St, Sydney NSW"></div>
            </div>
            <div style="display:flex;gap:8px;margin-top:18px;justify-content:flex-end">
                <button type="button" onclick="document.getElementById('modal-site').style.display='none'" class="btn">Cancel</button>
                <button type="submit" class="btn btn-primary" style="width:auto">Add site</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
var productOptions = '<option value="">Standard</option>';
@foreach($products as $p)
productOptions += '<option value="{{ $p->id }}">{{ addslashes($p->name) }}</option>';
@endforeach

var rowIdx = {{ $client->containerPrices->count() }};

function addRow() {
    var i = rowIdx++;
    var tbody = document.getElementById('cp-body');

    var mainRow = document.createElement('tr');
    mainRow.id = 'row-' + i;
    mainRow.innerHTML =
        '<td><select name="prices['+i+'][feet]"><option value="20">20ft</option><option value="40">40ft</option></select></td>'+
        '<td><select name="prices['+i+'][product_id]">'+productOptions+'</select></td>'+
        '<td><input type="number" name="prices['+i+'][client_rate]" step="0.01" min="0" placeholder="0.00" class="in-c" oninput="calcM(this)"></td>'+
        '<td><input type="number" name="prices['+i+'][worker_rate]" step="0.01" min="0" placeholder="0.00" class="in-w" oninput="calcM(this)"></td>'+
        '<td class="mc">—</td>'+
        '<td class="chk-cell"><label class="chk-wrap"><input type="checkbox" name="prices['+i+'][has_box_additional]" value="1" onchange="toggleExpand(\'box-'+i+'\',this)"><span class="chk-lbl">No</span></label></td>'+
        '<td class="chk-cell"><label class="chk-wrap"><input type="checkbox" name="prices['+i+'][has_skill_additional]" value="1" onchange="toggleExpand(\'skill-'+i+'\',this)"><span class="chk-lbl">No</span></label></td>'+
        '<td><button type="button" class="rm" onclick="removeRow(\'row-'+i+'\')">×</button></td>';
    tbody.appendChild(mainRow);

    // Box expand row
    var boxRow = document.createElement('tr');
    boxRow.id = 'box-'+i+'-row';
    boxRow.style.display = 'none';
    boxRow.innerHTML = '<td colspan="8" style="padding:0"><div class="expand-sec" id="box-'+i+'" style="display:none"><div class="expand-inner">'+
        '<div class="expand-title" style="color:#185FA5">Box / carton additionals</div>'+
        '<div class="expand-grid">'+
        '<div class="ef"><label>Threshold (boxes)</label><input type="number" name="prices['+i+'][box_threshold]" min="0" placeholder="e.g. 1500" style="border-color:#B5D4F4"></div>'+
        '<div class="ef"><label>Block size</label><input type="number" name="prices['+i+'][box_block_size]" min="1" placeholder="e.g. 500" style="border-color:#B5D4F4"></div>'+
        '<div class="ef"><label>Client rate / block</label><input type="number" name="prices['+i+'][box_client_rate_per_block]" step="0.01" min="0" class="in-c"></div>'+
        '<div class="ef"><label>Worker rate / block</label><input type="number" name="prices['+i+'][box_worker_rate_per_block]" step="0.01" min="0" class="in-w"></div>'+
        '</div></div></div></td>';
    tbody.appendChild(boxRow);

    // Skill expand row
    var skillRow = document.createElement('tr');
    skillRow.id = 'skill-'+i+'-row';
    skillRow.style.display = 'none';
    skillRow.innerHTML = '<td colspan="8" style="padding:0"><div class="expand-sec" id="skill-'+i+'" style="display:none"><div class="expand-inner">'+
        '<div class="expand-title" style="color:#3B6D11">Skill additionals</div>'+
        '<div class="expand-grid">'+
        '<div class="ef"><label>Threshold (skills)</label><input type="number" name="prices['+i+'][skill_threshold]" min="0" placeholder="e.g. 20" style="border-color:#C0DD97"></div>'+
        '<div class="ef"><label>Block size</label><input type="number" name="prices['+i+'][skill_block_size]" min="1" placeholder="e.g. 5" style="border-color:#C0DD97"></div>'+
        '<div class="ef"><label>Client rate / block</label><input type="number" name="prices['+i+'][skill_client_rate_per_block]" step="0.01" min="0" class="in-c"></div>'+
        '<div class="ef"><label>Worker rate / block</label><input type="number" name="prices['+i+'][skill_worker_rate_per_block]" step="0.01" min="0" class="in-w"></div>'+
        '</div></div></div></td>';
    tbody.appendChild(skillRow);
}

function removeRow(id) {
    // Remove main row + associated expand rows
    var nums = id.replace('row-','');
    ['row-'+nums, 'box-'+nums+'-row', 'skill-'+nums+'-row'].forEach(function(rid) {
        var el = document.getElementById(rid);
        if (el) el.remove();
    });
}

function toggleExpand(id, checkbox) {
    var el  = document.getElementById(id);
    var row = document.getElementById(id + '-row');
    var lbl = checkbox.nextElementSibling;
    if (checkbox.checked) {
        if (el) el.style.display = 'block';
        if (row) row.style.display = '';
        if (lbl) lbl.textContent = 'Yes';
    } else {
        if (el) el.style.display = 'none';
        if (row) row.style.display = 'none';
        if (lbl) lbl.textContent = 'No';
    }
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

function toggleHist(id) {
    var el = document.getElementById('hist-'+id);
    if (el) el.style.display = el.style.display==='none'?'block':'none';
}
</script>
@endpush
