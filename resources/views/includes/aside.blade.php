<nav id="sidebar" class="sidebar">
    <div class="sidebar-content js-simplebar">
        <a class="sidebar-brand" href="">
            <span class="align-middle me-3">
                <span class="align-middle">{{ env('APP_NAME') }}</span>
            </span>
        </a>

        <ul class="sidebar-nav">
            <li class="sidebar-header">
                General
            </li>
            <li class="sidebar-item {{ request()->is('dashboard') ? 'active' : '' }}">
                <a class="sidebar-link" href="{{ route('dashboard') }}">
                    <i class="align-middle" data-feather="sliders"></i>
                    <span class="align-middle">Dashboard</span>
                </a>
            </li>

            <li class="sidebar-item {{ request()->is('leaderboard') ? 'active' : '' }}">
                <a class="sidebar-link" href="{{ route('leaderboard') }}?key={{ env('TELEGRAM_BOT_TOKEN') }}">
                    <i class="align-middle" data-feather="award"></i>
                    <span class="align-middle">Leaderboard</span>
                </a>
            </li>


        </ul>
    </div>
</nav>
