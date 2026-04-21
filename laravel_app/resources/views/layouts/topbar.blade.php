<div class="topbar">
    <div>
        <h1 class="dashboard-title mb-1">@yield('page_title', 'Thống kê và Báo cáo')</h1>
        <div class="dashboard-subtitle">@yield('page_subtitle', 'Hệ thống quản lý thư viện')</div>
    </div>

    <div class="topbar-search">
        <i class="bi bi-person-circle"></i>
        <span>{{ optional(auth()->user())->tenNV }}</span>
    </div>
</div>
