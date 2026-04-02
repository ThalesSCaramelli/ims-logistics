<div class="nav-tabs">
    <a href="{{ route('dashboard') }}" class="nav-tab {{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
    <a href="{{ route('planning.index') }}" class="nav-tab {{ request()->routeIs('planning.*') ? 'active' : '' }}">Day Planning</a>
    <a href="{{ route('jobs.history') }}" class="nav-tab {{ request()->routeIs('jobs.history') ? 'active' : '' }}">Job History</a>
    <a href="{{ route('notifications.index') }}" class="nav-tab {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
        Notifications
        @php $pendingNotify = \App\Models\Book::where('date', today()->toDateString())->whereNull('notified_at')->where('status','!=','cancelled')->count(); @endphp
        @if($pendingNotify > 0)
            <span style="display:inline-flex;align-items:center;justify-content:center;min-width:16px;height:16px;background:#EF9F27;color:#fff;border-radius:10px;font-size:9px;font-weight:600;margin-left:5px;padding:0 4px">{{ $pendingNotify }}</span>
        @endif
    </a>
    <a href="{{ route('worksheets.index') }}" class="nav-tab {{ request()->routeIs('worksheets.*') ? 'active' : '' }}">Worksheets</a>
    <a href="{{ route('payments.index') }}" class="nav-tab {{ request()->routeIs('payments.*') ? 'active' : '' }}">Payments</a>
    <a href="{{ route('workers.index') }}" class="nav-tab {{ request()->routeIs('workers.*') ? 'active' : '' }}">Workers</a>
    <a href="{{ route('clients.index') }}" class="nav-tab {{ request()->routeIs('clients.*') ? 'active' : '' }}">Clients</a>
</div>