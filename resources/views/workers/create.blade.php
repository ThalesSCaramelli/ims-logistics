<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMS — New Worker</title>
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
        .page{padding:20px;max-width:720px;margin:0 auto}
        .card{background:#fff;border:0.5px solid rgba(0,0,0,0.1);border-radius:12px;padding:20px;margin-bottom:14px}
        .card-title{font-size:14px;font-weight:500;margin-bottom:16px;padding-bottom:10px;border-bottom:0.5px solid rgba(0,0,0,0.08)}
        .grid-2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
        .grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px}
        .field{display:flex;flex-direction:column;gap:4px}
        .field label{font-size:11px;color:#73726c}
        .field input,.field select{font-size:13px;padding:7px 10px;border:0.5px solid #c2c0b6;border-radius:8px;background:#fff;color:#1a1a18;font-family:inherit;width:100%}
        .field input:focus,.field select:focus{outline:none;border-color:#185FA5}
        .section-label{font-size:11px;font-weight:500;color:#73726c;text-transform:uppercase;letter-spacing:0.6px;margin-bottom:10px;margin-top:16px;padding-top:16px;border-top:0.5px solid rgba(0,0,0,0.08)}
        .toggle-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px}
        .toggle-row label{font-size:13px}
        .toggle-row small{font-size:11px;color:#73726c;display:block}
        .toggle{width:36px;height:20px;border-radius:10px;position:relative;cursor:pointer;transition:background .2s;border:none;padding:0;flex-shrink:0}
        .toggle.off{background:#c2c0b6}.toggle.on{background:#185FA5}
        .toggle::after{content:'';position:absolute;top:2px;left:2px;width:16px;height:16px;background:#fff;border-radius:50%;transition:transform .2s}
        .toggle.on::after{transform:translateX(16px)}
        .footer{display:flex;gap:8px;justify-content:flex-end;margin-top:4px}
        .btn{font-size:13px;padding:8px 16px;border-radius:8px;cursor:pointer;font-family:inherit;border:0.5px solid #c2c0b6;background:#fff;color:#1a1a18;text-decoration:none;display:inline-block}
        .btn:hover{background:#f5f4f0}
        .btn-primary{background:#185FA5;color:#fff;border-color:#185FA5}
        .btn-primary:hover{background:#0C447C}
        .hint{font-size:11px;color:#73726c;margin-top:3px}
        .errors{background:#FCEBEB;border:0.5px solid #F09595;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:13px;color:#A32D2D}
    </style>
</head>
<body>
<div class="topbar">
    <div class="logo">IMS</div>
    <div class="topbar-nav">
        <a href="{{ route('workers.index') }}">Workers</a>
        <span>/</span><span>New worker</span>
    </div>
    <div class="topbar-right">
        <span class="topbar-user">{{ Auth::user()->email }}</span>
        <form method="POST" action="{{ route('logout') }}" style="display:inline">
            @csrf<button type="submit" class="topbar-btn">Sign out</button>
        </form>
    </div>
</div>

<div class="page">
    @if($errors->any())
        <div class="errors">
            @foreach($errors->all() as $e) <div>· {{ $e }}</div> @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('workers.store') }}">
    @csrf

    {{-- Personal --}}
    <div class="card">
        <div class="card-title">Personal details</div>
        <div class="grid-2" style="margin-bottom:12px">
            <div class="field"><label>Full name *</label><input type="text" name="name" value="{{ old('name') }}" required autofocus></div>
            <div class="field"><label>Phone *</label><input type="text" name="phone" value="{{ old('phone') }}" required placeholder="+61 4xx xxx xxx"></div>
            <div class="field"><label>Email</label><input type="email" name="email" value="{{ old('email') }}" placeholder="worker@email.com"></div>
            <div class="field"><label>ABN</label><input type="text" name="abn" value="{{ old('abn') }}" placeholder="xx xxx xxx xxx"></div>
            <div class="field"><label>Min. weekly pay (AUD)</label><input type="number" name="min_weekly" value="{{ old('min_weekly') }}" min="0" step="0.01" placeholder="Optional"></div>
            <div class="field"><label>Status</label>
                <select name="status">
                    <option value="active">Active</option>
                    <option value="suspended">Suspended</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
        <div class="field">
            <label>Australian citizen / permanent resident *</label>
            <select name="is_australian" onchange="toggleVisa(this)">
                <option value="1">Yes — Australian citizen or PR</option>
                <option value="0" {{ old('is_australian')==='0'?'selected':'' }}>No — requires visa</option>
            </select>
        </div>
    </div>

    {{-- Licences --}}
    <div class="card">
        <div class="card-title">Licences</div>
        <div class="toggle-row">
            <div><label>Holds a valid forklift licence (LF)</label><small>Worker will be tagged as Driver in books</small></div>
            <button type="button" class="toggle off" id="lf-toggle"
                onclick="this.classList.toggle('on');this.classList.toggle('off');document.getElementById('has_forklift').value=this.classList.contains('on')?'1':'0'"></button>
        </div>
        <input type="hidden" name="has_forklift" id="has_forklift" value="{{ old('has_forklift','0') }}">
        <div class="grid-3">
            <div class="field"><label>Licence number</label><input type="text" name="forklift_licence_number" value="{{ old('forklift_licence_number') }}"></div>
            <div class="field"><label>Expiry date</label><input type="date" name="forklift_expiry" value="{{ old('forklift_expiry') }}"></div>
            <div class="field"><label>Issuing state</label>
                <select name="forklift_state">
                    @foreach(['VIC','NSW','QLD','WA','SA','TAS','ACT','NT'] as $s)
                        <option>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Visa --}}
    <div class="card" id="visa-section" style="{{ old('is_australian','1')==='0'?'':'display:none' }}">
        <div class="card-title">Visa & work rights</div>
        <div class="grid-2">
            <div class="field"><label>Visa class *</label>
                <select name="visa_class">
                    @foreach(['500 — Student','482 — TSS Sponsored','485 — Graduate','417 — Working Holiday','Other'] as $vc)
                        <option>{{ $vc }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field"><label>Expiry date *</label><input type="date" name="visa_valid_until" value="{{ old('visa_valid_until') }}"></div>
            <div class="field"><label>Fortnightly hours limit</label><input type="number" name="visa_fortnightly_hours_limit" value="{{ old('visa_fortnightly_hours_limit') }}" placeholder="e.g. 48"></div>
            <div class="field"><label>Work permitted</label>
                <select name="visa_work_permitted">
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
            </div>
        </div>
    </div>

    <div class="footer">
        <a href="{{ route('workers.index') }}" class="btn">Cancel</a>
        <button type="submit" class="btn btn-primary">Create worker</button>
    </div>

    </form>
</div>
<script>
function toggleVisa(sel) {
    document.getElementById('visa-section').style.display = sel.value==='0' ? 'block' : 'none';
}
</script>
</body>
</html>
