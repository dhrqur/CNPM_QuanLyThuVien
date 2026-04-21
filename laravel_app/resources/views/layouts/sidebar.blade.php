@php
    $user = auth()->user();
@endphp

<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-title">Hệ thống</div>
        <div class="brand-sub">Quản lý thư viện</div>
    </div>

    <nav class="sidebar-nav">
        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="bi bi-grid"></i> Thống kê và Báo cáo</a>
        <a href="{{ route('sach.index') }}" class="nav-item {{ request()->routeIs('sach.*') ? 'active' : '' }}"><i class="bi bi-book"></i> Quản lý Sách</a>
        <a href="{{ route('docgia.index') }}" class="nav-item {{ request()->routeIs('docgia.*') ? 'active' : '' }}"><i class="bi bi-people"></i> Quản lý Độc giả</a>
        <a href="{{ route('muontra.index') }}" class="nav-item {{ request()->routeIs('muontra.*') ? 'active' : '' }}"><i class="bi bi-arrow-left-right"></i> Quản lý Mượn trả</a>
        <a href="{{ route('tacgia.index') }}" class="nav-item {{ request()->routeIs('tacgia.*') ? 'active' : '' }}"><i class="bi bi-pen"></i> Quản lý Tác giả</a>
        <a href="{{ route('theloai.index') }}" class="nav-item {{ request()->routeIs('theloai.*') ? 'active' : '' }}"><i class="bi bi-tags"></i> Quản lý Thể loại</a>
        <a href="{{ route('nhaxuatban.index') }}" class="nav-item {{ request()->routeIs('nhaxuatban.*') ? 'active' : '' }}"><i class="bi bi-building"></i> Quản lý Nhà xuất bản</a>
        <a href="{{ route('ngonngu.index') }}" class="nav-item {{ request()->routeIs('ngonngu.*') ? 'active' : '' }}"><i class="bi bi-translate"></i> Quản lý Ngôn ngữ</a>
        <a href="{{ route('kesach.index') }}" class="nav-item {{ request()->routeIs('kesach.*') ? 'active' : '' }}"><i class="bi bi-bookshelf"></i> Quản lý Kệ sách</a>
        <a href="{{ route('thethuvien.index') }}" class="nav-item {{ request()->routeIs('thethuvien.*') ? 'active' : '' }}"><i class="bi bi-person-vcard"></i> Quản lý Thẻ thư viện</a>

        @if ($user && $user->isManager())
            <a href="{{ route('nhanvien.index') }}" class="nav-item {{ request()->routeIs('nhanvien.*') ? 'active' : '' }}"><i class="bi bi-person-badge"></i> Quản lý Nhân viên</a>
        @endif
    </nav>

    <div class="sidebar-user">
        <div>Đăng nhập bởi</div>
        <strong>{{ optional($user)->tenNV }}</strong>
    </div>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="logout-btn border-0"><i class="bi bi-box-arrow-right"></i> Đăng xuất</button>
    </form>
</aside>
