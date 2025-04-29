<nav class="admin-navbar">
    <div class="navbar-left">
        <button id="sidebar-toggle" class="sidebar-toggle">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div class="company-logo">
            <img src="{{ asset('storage/logo/'.$company->logo) }}" alt="{{ $company->name }}">
            <span class="company-name">{{ strtoupper($company->name) }}</span>
        </div>
    </div>

    <div class="navbar-right">
        <div class="navbar-search">
            <div class="search-input-wrapper">
                <i class="fa-solid fa-search search-icon"></i>
                <input type="text" placeholder="Search..." class="search-input">
            </div>
        </div>

        <div class="navbar-actions">
            <div class="navbar-item">
                <button class="notification-btn">
                    <i class="fa-regular fa-bell"></i>
                    <span class="notification-badge">3</span>
                </button>
            </div>

            <div class="navbar-item dropdown">
                <button class="dropdown-toggle">
                    <div class="user-avatar">
                        <img src="{{ asset('images/avatar.png') }}" alt="User Avatar">
                    </div>
                    <span class="user-name">{{ auth()->user()->name }}</span>
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div class="dropdown-menu">
                    <a href="#" class="dropdown-item">
                        <i class="fa-regular fa-user"></i>
                        <span>Profile</span>
                    </a>
                    <a href="#" class="dropdown-item">
                        <i class="fa-solid fa-gear"></i>
                        <span>Settings</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                    <a href="{{ route('logout') }}" class="dropdown-item"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</nav>
