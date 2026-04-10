@extends('layouts.app')
@section('title', $worksheet->job->site->client->name . ' — Worksheet')
@section('breadcrumb')
    <a href="{{ route('worksheets.index') }}">Worksheets</a>
    <span>/</span>
    <span>{{ $worksheet->job->site->client->name }} · {{ $worksheet->job->date->format('d M Y') }}</span>
@endsection

@push('styles')
<style>
.ctable{width:100%;border-collapse:collapse;font-size:12px;table-layout:fixed}
.ctable th{text-align:left;font-size:10px;font-weight:500;color:#73726c;text-transform:uppercase;letter-spacing:0.4px;padding:6px 8px;background:#f5f4f0}
.ctable td{padding:6px 8px;border-bottom:0.5px solid rgba(0,0,0,0.05);vertical-align:middle}
.ctable input,.ctable select{font-size:12px;padding:3px 6px;border:0.5px solid #c2c0b6;border-radius:5px;background:#fff;color:#1a1a18;width:100%}
.ocr-badge{font-size:9px;padding:1px 5px;border-radius:4px;background:#FAEEDA;color:#854F0B;margin-left:3px}
.exp-body{padding:10px 12px 12px 28px;background:#f9f8f6}
.exp-label{font-size:10px;font-weight:500;color:#73726c;text-transform:uppercase;letter-spacing:0.4px;margin-bottom:6px}
.wtag{display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:6px;font-size:11px;border:0.5px solid #c2c0b6;background:#fff;cursor:pointer;user-select:none;margin:2px}
.wtag.on{background:#E6F1FB;border-color:#85B7EB;color:#0C447C}
.wtag.off{color:#73726c}
.exp-btn{background:none;border:none;cursor:pointer;color:#73726c;font-size:11px;display:flex;align-items:center;gap:3px;padding:0;white-space:nowrap}
.rm{background:none;border:none;cursor:pointer;color:#73726c;font-size:14px;width:22px;height:22px;display:flex;align-items:center;justify-content:center;border-radius:4px}
.rm:hover{color:#A32D2D;background:#FCEBEB}
.add-btn{font-size:11px;color:#185FA5;background:none;border:0.5px dashed #B5D4F4;border-radius:6px;padding:5px 12px;cursor:pointer;margin-top:8px;width:100%}
.split-part{border:0.5px solid rgba(0,0,0,0.1);border-radius:8px;padding:10px 12px;margin-bottom:8px;background:#fff}
.split-part-hdr{display:flex;align-items:center;gap:8px;margin-bottom:8px}
.split-qty{font-size:12px;padding:3px 7px;border:0.5px solid #c2c0b6;border-radius:5px;width:70px;font-family:inherit;background:#fff;color:#1a1a18}
.split-total{font-size:10px;padding:4px 8px;border-radius:6px;display:inline-block;margin-top:6px}
.total-ok{background:#EAF3DE;color:#27500A}
.total-err{background:#FCEBEB;color:#A32D2D}
.split-toggle{font-size:10px;color:#185FA5;background:none;border:none;cursor:pointer;padding:0;display:flex;align-items:center;gap:3px;margin-top:6px}
.stable{width:100%;border-collapse:collapse;font-size:12px}
.stable th{text-align:left;font-size:10px;font-weight:500;color:#73726c;text-transform:uppercase;letter-spacing:0.4px;padding:6px 8px;background:#f5f4f0}
.stable td{padding:6px 8px;border-bottom:0.5px solid rgba(0,0,0,0.05);vertical-align:middle}
.stable tr:last-child td{border-bottom:none}
.stable input,.stable select{font-size:12px;padding:3px 6px;border:0.5px solid #c2c0b6;border-radius:5px;background:#fff;color:#1a1a18;width:100%}
.sum-row{display:flex;justify-content:space-between;font-size:12px;padding:4px 0;border-bottom:0.5px solid rgba(0,0,0,0.06)}
.sum-row:last-child{border-bottom:none}
.crew-chip{font-size:11px;padding:2px 8px;border-radius:10px;background:#E6F1FB;color:#0C447C;display:inline-block;margin:2px}
.desc-input{width:100%;font-size:11px;padding:5px 8px;border:0.5px solid #c2c0b6;border-radius:6px;font-family:inherit;background:#fff;color:#1a1a18;margin-top:8px}
.warn{background:#FAEEDA;border:0.5px solid #FAC775;border-radius:8px;padding:8px 12px;font-size:11px;color:#854F0B;margin-bottom:10px;display:flex;align-items:center;gap:8px}
</style>
@endpush

@section('content')
@if($isApproved)
<div style="background:#EAF3DE;border:0.5px solid #C0DD97;border-radius:8px;padding:10px 16px;margin:12px 20px 0;font-size:13px;color:#27500A;display:flex;align-items:center;gap:8px">
    <span>✓</span>
    <span>This worksheet was approved on <strong>{{ $worksheet->approved_at->format('d M Y \a\t H:i') }}</strong> and is now locked for editing.</span>
</div>
@endif
@php
    $statusVal = $worksheet->sync_status?->value ?? $worksheet->sync_status ?? 'draft';
    $isApproved = in_array($statusVal, ['pending','approved','paid']);
@endphp

<div class="page">
    <div class="page-header">
        <div>
            <div class="page-title">{{ $worksheet->job->site->client->name }}</div>
            <div style="display:flex;gap:8px;align-items:center;margin-top:4px">
                <span class="pill pill-{{ $statusVal }}">{{ ucfirst($statusVal) }}</span>
                <span style="font-size:12px;color:#73726c">{{ $worksheet->job->site->name }} · {{ $worksheet->job->date->format('d M Y') }}</span>
            </div>
        </div>
        <a href="{{ route('worksheets.index') }}" class="btn">← Back</a>
    </div>

    <div class="page-2col">
    <div>
        @if(session('success'))
            <div style="background:#EAF3DE;border:0.5px solid #C0DD97;border-radius:8px;padding:10px 14px;font-size:12px;color:#27500A;margin-bottom:10px">✓ {{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div style="background:#FCEBEB;border:0.5px solid #F09595;border-radius:8px;padding:10px 14px;font-size:12px;color:#A32D2D;margin-bottom:10px">
                @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
            </div>
        @endif

        @if(!$isApproved)
        <div class="warn">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" style="flex-shrink:0"><path d="M8 2L14.5 13H1.5L8 2Z" stroke="#854F0B" stroke-width="1.3"/><path d="M8 6v3M8 11v.5" stroke="#854F0B" stroke-width="1.3" stroke-linecap="round"/></svg>
            Review all fields before approving. OCR data may need correction.
        </div>
        @endif

        <form method="POST" action="{{ route('worksheets.save', $worksheet) }}" id="ws-form">
        @csrf

        {{-- Containers --}}
        <div class="card" style="padding:0;overflow:hidden;margin-bottom:12px">
            <div style="padding:14px 16px;border-bottom:0.5px solid rgba(0,0,0,0.07);display:flex;align-items:center;justify-content:space-between">
                <span style="font-size:12px;font-weight:500">Containers</span>
                <button type="button" class="btn btn-sm" onclick="addContainer()">+ Add container</button>
            </div>
            <div style="overflow-x:auto">
            <table class="ctable">
                <thead><tr>
                    <th style="width:125px">Container no.</th>
                    <th style="width:58px">Size</th>
                    <th style="width:100px">Product</th>
                    <th style="width:64px">Boxes</th>
                    <th style="width:52px">Skills</th>
                    <th style="width:100px">Team</th>
                    <th style="width:22px"></th>
                </tr></thead>
                <tbody id="containers">
                @foreach($worksheet->job->containers as $container)
                @php
                    $ci = $loop->index;
                    $cid = $container->id;
                    $containerWorkerIds = $container->workers->pluck('worker_id')->unique();
                    $hasSplit = $container->workers->pluck('part')->unique()->count() > 1;
                    $simpleWorkers = $container->workers->where('part',1)->pluck('worker_id');
                    $previewText = $allWorkers->whereIn('id', $containerWorkerIds)->map(fn($w) => collect(explode(' ',$w->name))->map(fn($p)=>strtoupper(substr($p,0,1)))->take(2)->join(''))->join(' ');
                @endphp
                <tr id="cr-{{ $ci }}">
                    <td>
                        <input type="text" name="containers[{{ $cid }}][container_number]" value="{{ $container->container_number }}" style="font-family:monospace;font-size:11px">
                        @if($container->container_number)<span class="ocr-badge">OCR</span>@endif
                    </td>
                    <td><select name="containers[{{ $cid }}][feet]" onchange="updateProductOptions(this)">
                        <option value="20" {{ $container->feet==='20'?'selected':'' }}>20ft</option>
                        <option value="40" {{ $container->feet==='40'?'selected':'' }}>40ft</option>
                    </select></td>
                    <td><select name="containers[{{ $cid }}][product_id]">
                        @foreach($productsByFeet[$container->feet] ?? [] as $p)
                        <option value="{{ $p['id'] ?? '' }}" {{ ($container->product_id===$p['id'])||($p['id']===null&&$container->product_id===null)?'selected':'' }}>{{ $p['name'] }}</option>
                        @endforeach
                    </select></td>
                    <td><input type="number" name="containers[{{ $cid }}][boxes_count]" value="{{ $container->boxes_count }}" min="0" style="border-color:#B5D4F4"></td>
                    <td><input type="number" name="containers[{{ $cid }}][skills_count]" value="{{ $container->skills_count }}" min="0" style="border-color:#C0DD97"></td>
                    <td><button type="button" class="exp-btn" onclick="toggleExpand({{ $ci }})">
                        <span style="color:#0C447C;font-size:11px">{{ $previewText ?: 'Set team' }}</span>
                        <svg width="10" height="10" viewBox="0 0 10 10" fill="none" id="chev-{{ $ci }}"><path d="M2 3.5l3 3 3-3" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>
                    </button></td>
                    <td><button type="button" class="rm" onclick="removeC({{ $ci }})">×</button></td>
                </tr>
                <tr id="exp-{{ $ci }}" style="display:none">
                    <td colspan="7" style="padding:0">
                        <div class="exp-body">
                            <div id="simple-{{ $ci }}" style="{{ $hasSplit?'display:none':'' }}">
                                <div class="exp-label">Workers on this container</div>
                                <div>
                                @foreach($allWorkers as $w)
                                @php $on = $simpleWorkers->contains($w->id); @endphp
                                <span class="wtag {{ $on?'on':'off' }}" onclick="toggleWorker(this)">
                                    {{ collect(explode(' ',$w->name))->map(fn($p)=>strtoupper(substr($p,0,1)))->take(2)->join('') }}
                                    <span style="font-size:10px">{{ explode(' ',$w->name)[0] }}</span>
                                    <input type="checkbox" name="containers[{{ $cid }}][workers][]" value="{{ $w->id }}" {{ $on?'checked':'' }} style="display:none">
                                </span>
                                @endforeach
                                </div>
                                <div style="font-size:10px;color:#73726c;margin-top:5px">Worker rate split equally among selected.</div>
                                <button type="button" class="split-toggle" onclick="enableSplit({{ $ci }},{{ $cid }})">
                                    <svg width="11" height="11" viewBox="0 0 12 12" fill="none"><path d="M6 2v8M2 6h8" stroke="#185FA5" stroke-width="1.3" stroke-linecap="round"/></svg>
                                    Split between different teams
                                </button>
                            </div>
                            <div id="split-{{ $ci }}" style="{{ $hasSplit?'':'display:none' }}">
                                <input type="hidden" name="containers[{{ $cid }}][split]" value="{{ $hasSplit?'1':'' }}" id="sf-{{ $ci }}">
                                <div class="exp-label">Split between teams</div>
                                <div id="parts-{{ $ci }}-{{ $cid }}">
                                @if($hasSplit)
                                    @foreach($container->workers->groupBy('part') as $partNum => $partWorkers)
                                    <div class="split-part">
                                        <div class="split-part-hdr">
                                            <span style="font-size:11px;font-weight:500">Part {{ $partNum }}</span>
                                            <input type="number" class="split-qty" name="containers[{{ $cid }}][parts][{{ $partNum-1 }}][qty]" value="{{ $partWorkers->first()->qty }}" step="0.1" min="0.1" max="1" oninput="updateTotal({{ $ci }},{{ $cid }})">
                                            <span style="font-size:11px;color:#73726c">containers</span>
                                            <button type="button" class="rm" style="margin-left:auto" onclick="removePart(this,{{ $ci }},{{ $cid }})">×</button>
                                        </div>
                                        <div>
                                        @foreach($allWorkers as $w)
                                        @php $on = $partWorkers->pluck('worker_id')->contains($w->id); @endphp
                                        <span class="wtag {{ $on?'on':'off' }}" onclick="toggleWorker(this)">
                                            {{ collect(explode(' ',$w->name))->map(fn($p)=>strtoupper(substr($p,0,1)))->take(2)->join('') }}
                                            <span style="font-size:10px">{{ explode(' ',$w->name)[0] }}</span>
                                            <input type="checkbox" name="containers[{{ $cid }}][parts][{{ $partNum-1 }}][workers][]" value="{{ $w->id }}" {{ $on?'checked':'' }} style="display:none">
                                        </span>
                                        @endforeach
                                        </div>
                                    </div>
                                    @endforeach
                                @endif
                                </div>
                                <div id="total-{{ $ci }}-{{ $cid }}" class="split-total {{ $hasSplit?'total-ok':'' }}">
                                    @if($hasSplit)@php $tq = $container->workers->groupBy('part')->sum(fn($p)=>$p->first()->qty); @endphp
                                    Total: {{ number_format($tq,1) }} / 1.0 {{ abs($tq-1.0)<0.01?'✓':'— must equal 1.0' }}@endif
                                </div>
                                <button type="button" class="add-btn" style="margin-top:6px" onclick="addPart({{ $ci }},{{ $cid }})">+ Add part</button>
                                <button type="button" class="split-toggle" style="margin-top:6px" onclick="disableSplit({{ $ci }},{{ $cid }})">Remove split — use simple mode</button>
                            </div>
                            {{-- Container additionals --}}
                            @php
                                $availAddl = $clientAdditionals->filter(fn($a) => $a->feet === 'both' || $a->feet === $container->feet);
                                $markedIds = $container->additionals->pluck('additional_id')->toArray();
                            @endphp
                            @if($availAddl->isNotEmpty())
                            <div style="margin-top:10px">
                                <div class="exp-label">Additionals</div>
                                <div style="display:flex;flex-wrap:wrap;gap:6px">
                                @foreach($availAddl as $addl)
                                @php $checked = in_array($addl->id, $markedIds); @endphp
                                <label style="display:flex;align-items:center;gap:5px;font-size:11px;padding:4px 10px;border:0.5px solid {{ $checked ? '#85B7EB' : '#c2c0b6' }};border-radius:7px;background:{{ $checked ? '#E6F1FB' : '#fff' }};cursor:pointer">
                                    <input type="checkbox"
                                        name="containers[{{ $cid }}][additionals][]"
                                        value="{{ $addl->id }}"
                                        {{ $checked ? 'checked' : '' }}
                                        onchange="this.closest('label').style.background=this.checked?'#E6F1FB':'#fff';this.closest('label').style.borderColor=this.checked?'#85B7EB':'#c2c0b6'">
                                    {{ $addl->name }}
                                    <span style="color:#73726c">(+${{ number_format($addl->client_rate,0) }})</span>
                                </label>
                                @endforeach
                                </div>
                            </div>
                            @endif
                            <input class="desc-input" name="containers[{{ $cid }}][description]" placeholder="Description (optional)..." value="{{ $container->description_extra }}">
                        </div>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
            </div>
            <div style="padding:10px 16px"><button type="button" class="add-btn" onclick="addContainer()">+ Add container</button></div>
        </div>

        {{-- Services --}}
        <div class="card" style="padding:0;overflow:hidden;margin-bottom:12px">
            <div style="padding:14px 16px;border-bottom:0.5px solid rgba(0,0,0,0.07);display:flex;align-items:center;justify-content:space-between">
                <span style="font-size:12px;font-weight:500">Extra services <span style="font-size:10px;font-weight:400;color:#73726c">optional</span></span>
                <button type="button" class="btn btn-sm" onclick="addService()">+ Add line</button>
            </div>
            <div style="padding:10px 16px">
                <table class="stable">
                    <thead><tr><th style="width:120px">Type</th><th style="width:68px">Hours</th><th>Description</th><th style="width:22px"></th></tr></thead>
                    <tbody id="services">
                    @foreach($worksheet->services as $svc)
                    <tr>
                        <td><select name="services[{{ $loop->index }}][service_type]">
                            <option value="extra_work" {{ $svc->service_type==='extra_work'?'selected':'' }}>Extra Work</option>
                            <option value="waiting_time" {{ $svc->service_type==='waiting_time'?'selected':'' }}>Waiting Time</option>
                            <option value="labour_hire" {{ $svc->service_type==='labour_hire'?'selected':'' }}>Labour Hire</option>
                        </select></td>
                        <td><input type="number" name="services[{{ $loop->index }}][hours]" value="{{ $svc->hours }}" step="0.5" min="0"></td>
                        <td><input type="text" name="services[{{ $loop->index }}][description]" value="{{ $svc->description }}"></td>
                        <td><button type="button" class="rm" onclick="this.closest('tr').remove()">×</button></td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
                <button type="button" class="add-btn" onclick="addService()">+ Add line</button>
            </div>
            <div style="padding:14px 16px;border-top:0.5px solid rgba(0,0,0,0.07)">
                <div style="font-size:12px;font-weight:500;margin-bottom:8px">TL observations</div>
                <textarea name="observations" style="width:100%;font-size:12px;padding:8px 10px;border:0.5px solid #c2c0b6;border-radius:8px;font-family:inherit;resize:none;min-height:56px">{{ $worksheet->observations }}</textarea>
            </div>
        </div>

        <div style="display:flex;gap:8px;justify-content:flex-end;margin-bottom:20px">
            <button type="submit" class="btn">Save draft</button>
            @if(!$isApproved)
            <button type="submit" formaction="{{ route('worksheets.approve', $worksheet) }}" class="btn btn-primary"
                onclick="return confirm('Approve and generate payment?')">
                Approve &amp; generate payment
            </button>
            @endif
        </div>
        </form>
    </div>

    <div class="page-sidebar">
        <div class="card">
            <div class="card-title">Job info</div>
            <div style="display:flex;flex-direction:column;gap:5px;font-size:12px">
                <div style="display:flex;justify-content:space-between"><span style="color:#73726c">Client</span><span style="font-weight:500">{{ $worksheet->job->site->client->name }}</span></div>
                <div style="display:flex;justify-content:space-between"><span style="color:#73726c">Site</span><span>{{ $worksheet->job->site->name }}</span></div>
                <div style="display:flex;justify-content:space-between"><span style="color:#73726c">Date</span><span>{{ $worksheet->job->date->format('d M Y') }}</span></div>
            </div>
            <div style="margin-top:10px;padding-top:10px;border-top:0.5px solid rgba(0,0,0,0.07)">
                <div style="font-size:10px;color:#73726c;margin-bottom:5px">Book crew</div>
                <div>@foreach($bookWorkers as $w)<span class="crew-chip">{{ collect(explode(' ',$w->name))->map(fn($p)=>strtoupper(substr($p,0,1)))->take(2)->join('') }}</span>@endforeach</div>
                <form method="POST" action="{{ route('worksheets.addWorker', $worksheet) }}" style="margin-top:8px;display:flex;gap:6px">
                    @csrf
                    <select name="worker_id" style="font-size:11px;padding:3px 6px;border:0.5px solid #c2c0b6;border-radius:6px;flex:1">
                        <option value="">Add worker...</option>
                        @foreach(\App\Models\Worker::where('status','active')->orderBy('name')->get() as $w)
                            @if(!$bookWorkers->contains('id',$w->id))<option value="{{ $w->id }}">{{ $w->name }}</option>@endif
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-sm">Add</button>
                </form>
            </div>
        </div>

        {{-- Attachments --}}
        <div class="card" id="attachments-card">
            <div class="card-title">
                Attachments
                <label style="font-size:11px;color:#185FA5;cursor:pointer;background:none;border:none;padding:0">
                    + Upload
                    <input type="file" id="file-upload" multiple accept=".jpg,.jpeg,.png,.webp,.pdf" style="display:none" onchange="uploadFiles(this)">
                </label>
            </div>
            <div id="attachments-list">
            @forelse($worksheet->attachments ?? [] as $att)
            <div style="display:flex;align-items:center;gap:8px;padding:6px 0;border-bottom:0.5px solid rgba(0,0,0,0.06)" id="att-{{ $loop->index }}">
                @if($att['mime'] === 'application/pdf')
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" style="flex-shrink:0"><rect x="2" y="1" width="12" height="14" rx="2" stroke="#A32D2D" stroke-width="1.2"/><path d="M5 5h6M5 8h6M5 11h4" stroke="#A32D2D" stroke-width="1" stroke-linecap="round"/></svg>
                @else
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" style="flex-shrink:0"><rect x="1" y="1" width="14" height="14" rx="2" stroke="#185FA5" stroke-width="1.2"/><path d="M1 11l4-4 3 3 2-2 5 4" stroke="#185FA5" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"/></svg>
                @endif
                <div style="flex:1;min-width:0">
                    <a href="{{ $att['url'] }}" target="_blank" style="font-size:11px;color:#185FA5;text-decoration:none;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $att['filename'] }}</a>
                    @if($att['is_primary'] ?? false)<span style="font-size:9px;background:#E6F1FB;color:#185FA5;padding:1px 5px;border-radius:3px">OCR</span>@endif
                </div>
                <div style="display:flex;gap:4px;flex-shrink:0">
                    @if($att['mime'] === 'application/pdf' && !($att['is_primary'] ?? false))
                    <button type="button" onclick="setPrimary('{{ $att['path'] }}')" style="font-size:9px;color:#73726c;background:none;border:0.5px solid #c2c0b6;border-radius:4px;padding:1px 5px;cursor:pointer">Set OCR</button>
                    @endif
                    <button type="button" onclick="deleteAttachment('{{ $att['path'] }}', {{ $loop->index }})" class="rm">×</button>
                </div>
            </div>
            @empty
            <div id="att-empty" style="font-size:11px;color:#73726c;font-style:italic">No attachments yet.</div>
            @endforelse
            </div>
            @if(collect($worksheet->attachments ?? [])->where('mime','application/pdf')->isNotEmpty())
            <button type="button" onclick="runOCR()" id="ocr-btn"
                style="margin-top:8px;width:100%;font-size:11px;padding:6px;border-radius:7px;background:#185FA5;color:#fff;border:none;cursor:pointer">
                Run OCR on PDF
            </button>
            <div id="ocr-status" style="font-size:10px;color:#73726c;margin-top:4px;display:none"></div>
            @endif
        </div>

        <div class="card">
            <div class="card-title">
                {{ $isApproved ? 'Approved amounts' : 'Calculation preview' }}
                @if($isApproved)
                    <span style="font-size:10px;background:#EAF3DE;color:#3B6D11;padding:2px 8px;border-radius:10px">Locked</span>
                @endif
            </div>
            @if(isset($preview['error']))
                <div style="font-size:11px;color:#73726c;font-style:italic">No pricing configured yet.</div>
            @else
                <div style="font-size:10px;color:#73726c;text-transform:uppercase;letter-spacing:0.4px;margin-bottom:6px">Client total</div>
                @foreach($preview['client_lines'] ?? [] as $line)
                    <div class="sum-row"><span style="color:#73726c">{{ $line['description'] }}</span><span>${{ number_format($line['client'],2) }}</span></div>
                @endforeach
                <div style="display:flex;justify-content:space-between;font-size:12px;font-weight:500;padding-top:6px;margin-bottom:12px">
                    <span>Total</span><span>${{ number_format($preview['client_total']??0,2) }}</span>
                </div>
                @if(count($preview['worker_totals']??[]))
                <div style="font-size:10px;color:#73726c;text-transform:uppercase;letter-spacing:0.4px;margin-bottom:6px">Per worker</div>
                @foreach($preview['worker_totals'] as $wt)
                    <div class="sum-row"><span style="color:#73726c">{{ $wt['name'] }}</span><span style="color:#3B6D11">${{ number_format($wt['amount'],2) }}</span></div>
                @endforeach
                @endif
                @if($preview['is_holiday']??false)
                    <div style="font-size:10px;color:#854F0B;background:#FAEEDA;padding:4px 8px;border-radius:5px;margin-top:8px">Holiday multiplier applied</div>
                @endif
            @endif
        </div>

        <div class="card">
            @if(!$isApproved)
                <button type="submit" form="ws-form" class="btn btn-sm" style="width:100%;margin-bottom:7px">Save draft</button>
                <button type="submit" form="ws-form" formaction="{{ route('worksheets.approve', $worksheet) }}"
                    class="btn btn-primary btn-sm" style="width:100%"
                    onclick="return confirm('Approve and generate payment?')">
                    Approve &amp; generate payment
                </button>
            @else
                <div style="font-size:12px;color:#3B6D11">✓ Approved {{ $worksheet->approved_at?->format('d M Y H:i') }}</div>
            @endif
        </div>
    </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var WORKERS = {!! json_encode($workersJson) !!};
var PRODUCTS_BY_FEET = {!! json_encode($productsByFeet) !!};
var cIdx = {{ $worksheet->job->containers->count() }};
var svcIdx = {{ $worksheet->services->count() }};
var partCtrs = {};

function toggleExpand(i){var r=document.getElementById('exp-'+i);var c=document.getElementById('chev-'+i);if(!r)return;var open=r.style.display!=='none';r.style.display=open?'none':'';if(c)c.style.transform=open?'':'rotate(180deg)';}
function toggleWorker(el){el.classList.toggle('on');el.classList.toggle('off');var chk=el.querySelector('input[type=checkbox]');if(chk)chk.checked=el.classList.contains('on');}

function enableSplit(ci,cid){
    document.getElementById('simple-'+ci).style.display='none';
    var sd=document.getElementById('split-'+ci);sd.style.display='block';
    var sf=document.getElementById('sf-'+ci);if(sf)sf.value='1';
    var parts=document.getElementById('parts-'+ci+'-'+cid);
    if(parts&&parts.children.length===0){addPart(ci,cid);addPart(ci,cid);}
    updateTotal(ci,cid);
}
function disableSplit(ci,cid){
    document.getElementById('split-'+ci).style.display='none';
    document.getElementById('simple-'+ci).style.display='block';
    var sf=document.getElementById('sf-'+ci);if(sf)sf.value='';
}

function workerTagsHTML(cid,pIdx,onIds){
    return WORKERS.map(function(w){
        var on=onIds.indexOf(w.id)>=0;
        return '<span class="wtag '+(on?'on':'off')+'" onclick="toggleWorker(this)">'+w.initials+' <span style="font-size:10px">'+w.first+'</span><input type="checkbox" name="containers['+cid+'][parts]['+pIdx+'][workers][]" value="'+w.id+'" '+(on?'checked':'')+' style="display:none"></span>';
    }).join('');
}

function addPart(ci,cid){
    var key=ci+'-'+cid;if(!partCtrs[key])partCtrs[key]=0;var pIdx=partCtrs[key]++;
    var parts=document.getElementById('parts-'+ci+'-'+cid);
    var div=document.createElement('div');div.className='split-part';
    var defOn=pIdx===0?WORKERS.slice(0,2).map(function(w){return w.id;}):WORKERS.slice(2).map(function(w){return w.id;});
    div.innerHTML='<div class="split-part-hdr"><span style="font-size:11px;font-weight:500">Part '+(pIdx+1)+'</span><input type="number" class="split-qty" name="containers['+cid+'][parts]['+pIdx+'][qty]" value="0.5" step="0.1" min="0.1" max="1" oninput="updateTotal('+ci+','+cid+')"><span style="font-size:11px;color:#73726c">containers</span><button type="button" class="rm" style="margin-left:auto" onclick="removePart(this,'+ci+','+cid+')">×</button></div><div>'+workerTagsHTML(cid,pIdx,defOn)+'</div>';
    parts.appendChild(div);updateTotal(ci,cid);
}
function removePart(btn,ci,cid){btn.closest('.split-part').remove();updateTotal(ci,cid);}
function updateTotal(ci,cid){
    var parts=document.getElementById('parts-'+ci+'-'+cid);if(!parts)return;
    var total=0;parts.querySelectorAll('.split-qty').forEach(function(inp){total+=parseFloat(inp.value||0);});
    total=Math.round(total*100)/100;
    var el=document.getElementById('total-'+ci+'-'+cid);if(!el)return;
    var ok=Math.abs(total-1.0)<0.001;
    el.textContent='Total: '+total.toFixed(1)+' / 1.0 '+(ok?'✓':'— must equal 1.0');
    el.className='split-total '+(ok?'total-ok':'total-err');
}

function removeC(i){['cr-','exp-'].forEach(function(p){var el=document.getElementById(p+i);if(el)el.remove();});}

function getProductOptions(feet) {
    var list = PRODUCTS_BY_FEET[feet] || PRODUCTS_BY_FEET['40'] || [];
    return list.map(function(p){
        return '<option value="'+(p.id===null?'':p.id)+'">'+p.name+'</option>';
    }).join('');
}

function updateProductOptions(sizeSelect) {
    var row = sizeSelect.closest('tr');
    var feet = sizeSelect.value.replace('ft','');
    var productSelect = row.querySelector('select[name*="product_id"]');
    if (!productSelect) return;
    var current = productSelect.value;
    productSelect.innerHTML = getProductOptions(feet);
    // Try to restore previous selection
    var opt = productSelect.querySelector('option[value="'+current+'"]');
    if (opt) opt.selected = true;
}

function addContainer(){
    var tbody=document.getElementById('containers');var i=cIdx++;var tmpId='new-'+i;
    var mr=document.createElement('tr');mr.id='cr-'+i;
    var pOpts=getProductOptions('40');
    mr.innerHTML='<td><input type="text" name="new_containers['+i+'][container_number]" placeholder="XXXX 0000000" style="font-family:monospace;font-size:11px"></td>'+
        '<td><select name="new_containers['+i+'][feet]" onchange="updateProductOptions(this)"><option value="40">40ft</option><option value="20">20ft</option></select></td>'+
        '<td><select name="new_containers['+i+'][product_id]">'+pOpts+'</select></td>'+
        '<td><input type="number" name="new_containers['+i+'][boxes_count]" style="border-color:#B5D4F4" min="0"></td>'+
        '<td><input type="number" name="new_containers['+i+'][skills_count]" style="border-color:#C0DD97" min="0"></td>'+
        '<td><button type="button" class="exp-btn" onclick="toggleExpand('+i+')"><span style="color:#0C447C;font-size:11px">Set team</span><svg width="10" height="10" viewBox="0 0 10 10" fill="none" id="chev-'+i+'"><path d="M2 3.5l3 3 3-3" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg></button></td>'+
        '<td><button type="button" class="rm" onclick="removeC('+i+')">×</button></td>';
    tbody.appendChild(mr);
    var er=document.createElement('tr');er.id='exp-'+i;er.style.display='none';
    var workerTags=WORKERS.map(function(w){return '<span class="wtag on" onclick="toggleWorker(this)">'+w.initials+' <span style="font-size:10px">'+w.first+'</span><input type="checkbox" name="new_containers['+i+'][workers][]" value="'+w.id+'" checked style="display:none"></span>';}).join('');
    er.innerHTML='<td colspan="7" style="padding:0"><div class="exp-body"><div id="simple-'+i+'"><div class="exp-label">Workers on this container</div><div>'+workerTags+'</div><div style="font-size:10px;color:#73726c;margin-top:5px">Worker rate split equally.</div><button type="button" class="split-toggle" onclick="enableSplit('+i+',\''+tmpId+'\')"><svg width="11" height="11" viewBox="0 0 12 12" fill="none"><path d="M6 2v8M2 6h8" stroke="#185FA5" stroke-width="1.3" stroke-linecap="round"/></svg> Split between teams</button></div><div id="split-'+i+'" style="display:none"><input type="hidden" name="new_containers['+i+'][split]" value="" id="sf-'+i+'"><div class="exp-label">Split between teams</div><div id="parts-'+i+'-'+tmpId+'"></div><div id="total-'+i+'-'+tmpId+'" class="split-total"></div><button type="button" class="add-btn" style="margin-top:6px" onclick="addPart('+i+',\''+tmpId+'\')">+ Add part</button><button type="button" class="split-toggle" style="margin-top:6px" onclick="disableSplit('+i+',\''+tmpId+'\')">Remove split</button></div><input class="desc-input" name="new_containers['+i+'][description]" placeholder="Description (optional)..."></div></td>';
    tbody.appendChild(er);
}

function addService(){
    var t=document.getElementById('services');var tr=document.createElement('tr');var i=svcIdx++;
    tr.innerHTML='<td><select name="services['+i+'][service_type]"><option value="extra_work">Extra Work</option><option value="waiting_time">Waiting Time</option><option value="labour_hire">Labour Hire</option></select></td><td><input type="number" name="services['+i+'][hours]" step="0.5" min="0" placeholder="0.0"></td><td><input type="text" name="services['+i+'][description]" placeholder="Description..."></td><td><button type="button" class="rm" onclick="this.closest(\'tr\').remove()">×</button></td>';
    t.appendChild(tr);
}

// ── Attachments ────────────────────────────────────────────────────
function uploadFiles(input) {
    var files = input.files;
    if (!files.length) return;
    var fd = new FormData();
    for (var i = 0; i < files.length; i++) fd.append('files[]', files[i]);
    fd.append('_token', '{{ csrf_token() }}');
    fetch('{{ route("worksheets.attachments.upload", $worksheet) }}', {method:'POST', body:fd})
        .then(function(r){return r.json();})
        .then(function(data){
            if (data.success) { location.reload(); }
            else { alert('Upload failed: ' + (data.error || 'Unknown error')); }
        })
        .catch(function(e){ alert('Upload error: ' + e); });
}

function deleteAttachment(path, idx) {
    if (!confirm('Remove this attachment?')) return;
    fetch('{{ route("worksheets.attachments.delete", $worksheet) }}', {
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
        body: JSON.stringify({path: path})
    })
    .then(function(r){return r.json();})
    .then(function(data){ if (data.success) location.reload(); });
}

function setPrimary(path) {
    fetch('{{ route("worksheets.attachments.primary", $worksheet) }}', {
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
        body: JSON.stringify({path: path})
    })
    .then(function(r){return r.json();})
    .then(function(data){ if (data.success) location.reload(); });
}

function runOCR() {
    var btn = document.getElementById('ocr-btn');
    var status = document.getElementById('ocr-status');
    if (btn) btn.disabled = true;
    if (status) { status.style.display='block'; status.textContent='Running OCR...'; }
    fetch('{{ route("worksheets.attachments.ocr", $worksheet) }}', {
        headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}
    })
    .then(function(r){return r.json();})
    .then(function(data){
        if (btn) btn.disabled = false;
        if (!data.success) {
            if (status) status.textContent = 'OCR failed: ' + (data.error || 'Unknown error');
            return;
        }
        var ext = data.extracted;
        if (status) status.textContent = 'OCR complete — ' + ext.container_count + ' container(s) found.';
        applyOcrData(ext);
    })
    .catch(function(e){
        if (btn) btn.disabled = false;
        if (status) status.textContent = 'OCR error: ' + e;
    });
}

function applyOcrData(ext) {
    // Pre-fill existing empty containers with OCR data
    var containers = ext.containers || [];
    var rows = document.querySelectorAll('#containers tr[id^="cr-"]');

    containers.forEach(function(c, i) {
        var row = rows[i];
        if (!row) return;
        var numInput = row.querySelector('input[name*="container_number"]');
        if (numInput && !numInput.value) numInput.value = c.container_number || '';
        var boxInput = row.querySelector('input[name*="boxes_count"]');
        if (boxInput && !boxInput.value && c.boxes_count) boxInput.value = c.boxes_count;
        var skillInput = row.querySelector('input[name*="skills_count"]');
        if (skillInput && !skillInput.value && c.skills_count) skillInput.value = c.skills_count;
    });

    // If OCR found more containers than existing rows, add them
    for (var i = rows.length; i < containers.length; i++) {
        addContainer();
        var newRows = document.querySelectorAll('#containers tr[id^="cr-"]');
        var newRow = newRows[newRows.length - 1];
        if (newRow) {
            var numInput = newRow.querySelector('input[name*="container_number"]');
            if (numInput) numInput.value = containers[i].container_number || '';
        }
    }

    if (ext.container_count > 0) {
        alert('OCR applied: ' + ext.container_count + ' container(s) pre-filled. Please review and correct any errors.');
    }
}

@if($isApproved)
document.querySelectorAll('#ws-form input, #ws-form select, #ws-form textarea, #ws-form button:not(.btn-primary)').forEach(function(el) {
    el.disabled = true;
});
@endif

</script>
@endpush
