<div class="topbar">
    <div class="logo">IMS</div>
    <div class="topbar-nav">
        @foreach($breadcrumbs ?? [] as $label => $url)
            @if($url)
                <a href="{{ $url }}">{{ $label }}</a>
                <span>/</span>
            @else
                <span>{{ $label }}</span>
            @endif
        @endforeach
    </div>
    <div class="topbar-right">
        <span class="topbar-user">{{ Auth::user()->email }}</span>
        <form method="POST" action="{{ route('logout') }}" style="display:inline">
            @csrf
            <button type="submit" class="topbar-btn">Sign out</button>
        </form>
    </div>
</div>