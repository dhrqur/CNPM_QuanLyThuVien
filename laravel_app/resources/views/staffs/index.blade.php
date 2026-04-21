@extends('layouts.app')

@section('title', 'Quản lý Nhân viên')
@section('page_title', 'Quản lý Nhân viên')
@section('page_subtitle', 'Chỉ quản lý thư viện mới có quyền truy cập')

@section('content')
<div class="content-toolbar">
    <div class="page-header mb-0">
        <div>
            <h2 class="page-title">Nhân viên</h2>
            <p class="page-desc">Quản lý tài khoản và phân quyền nhân viên thư viện.</p>
        </div>

        <a href="{{ route('nhanvien.create') }}" class="btn btn-orange"><i class="bi bi-plus-circle"></i> Thêm nhân viên</a>
    </div>

    <form method="GET" class="d-flex gap-2 mt-3 search-box">
        <input type="text" name="search" class="form-control" placeholder="Tìm mã, tên hoặc tên đăng nhập..." value="{{ $search }}">
        <button type="submit" class="btn btn-outline-orange">Tìm</button>
    </form>
</div>

<div class="table-wrap">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
            <tr>
                <th>Mã NV</th>
                <th>Tên nhân viên</th>
                <th>Vai trò</th>
                <th>SĐT</th>
                <th>Email</th>
                <th>Tên đăng nhập</th>
                <th width="240">Thao tác</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($staffs as $staff)
                <tr>
                    <td>{{ $staff->maNV }}</td>
                    <td>{{ $staff->tenNV }}</td>
                    <td>{{ $staff->vaitro }}</td>
                    <td>{{ $staff->soDT }}</td>
                    <td>{{ $staff->email }}</td>
                    <td>{{ $staff->tenDangNhap }}</td>
                    <td class="action-btns">
                        <a href="{{ route('nhanvien.show', $staff) }}" class="btn btn-info btn-sm">Xem chi tiết</a>
                        <a href="{{ route('nhanvien.edit', $staff) }}" class="btn btn-warning btn-sm">Sửa</a>
                        <form method="POST" action="{{ route('nhanvien.destroy', $staff) }}" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Xác nhận xóa nhân viên này?')">Xóa</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center py-4">Không có dữ liệu nhân viên.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $staffs->links('pagination::bootstrap-4') }}</div>
@endsection
