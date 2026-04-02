<div class="grid-3">
    <div class="field">
        <label>Client / Site *</label>
        <select name="jobs[{{ $index }}][site_id]" required onchange="refreshAlerts()">
            <option value="">Select site...</option>
            @foreach($clients as $client)
                @foreach($client->sites as $site)
                    <option value="{{ $site->id }}" data-client-id="{{ $client->id }}">
                        {{ $client->name }} — {{ $site->name }}
                    </option>
                @endforeach
            @endforeach
        </select>
    </div>
    <div class="field">
        <label>Start time *</label>
        <input type="time" name="jobs[{{ $index }}][start_time]" required>
    </div>
    <div class="field">
        <label>Team Leader *</label>
        <select name="jobs[{{ $index }}][team_leader_id]" id="tl-select-{{ $index }}" required>
            <option value="">— Select workers first —</option>
        </select>
    </div>
</div>
<div class="field" style="margin-top:8px">
    <label>Job notes</label>
    <input type="text" name="jobs[{{ $index }}][notes]" placeholder="Optional notes for this job...">
</div>
