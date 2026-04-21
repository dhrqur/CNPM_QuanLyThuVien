@extends('layouts.app')

@section('title', 'Quản lý Nhà xuất bản')
@section('page_title', 'Quản lý Nhà xuất bản')
@section('page_subtitle', 'Danh mục nhà xuất bản')

@section('content')
<div class="content-toolbar">
    <div class="page-header mb-0">
        <div>
            <h2 class="page-title">Nhà xuất bản</h2>
            <p class="page-desc">Quản lý thông tin nhà xuất bản của sách.</p>
        </div>

        <a href="{{ route('nhaxuatban.create') }}" class="btn btn-orange"><i class="bi bi-plus-circle"></i> Thêm nhà xuất bản</a>
    </div>

    <form method="GET" class="d-flex gap-2 mt-3 search-box">
        <input type="text" name="search" class="form-control" placeholder="Tìm mã, tên, SĐT hoặc email..." value="{{ $search }}">
        <button type="submit" class="btn btn-outline-orange">Tìm</button>
    </form>
</div>

<div class="table-wrap">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
            <tr>
                <th>Mã NXB</th>
                <th>Tên NXB</th>
                <th>Địa chỉ</th>
                <th>Số điện thoại</th>
                <th>Email</th>
                <th width="170">Thao tác</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($publishers as $publisher)
                <tr>
                    <td>{{ $publisher->maNXB }}</td>
                    <td>{{ $publisher->tenNXB }}</td>
                    <td>{{ $publisher->diaChi }}</td>
                    <td>{{ $publisher->soDT }}</td>
                    <td>{{ $publisher->email }}</td>
                    <td class="action-btns">
                        <a href="{{ route('nhaxuatban.edit', $publisher) }}" class="btn btn-warning btn-sm">Sửa</a>
                        <form method="POST" action="{{ route('nhaxuatban.destroy', $publisher) }}" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Xác nhận xóa nhà xuất bản này?')">Xóa</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center py-4">Không có dữ liệu nhà xuất bản.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $publishers->links('pagination::bootstrap-4') }}</div>
@endsection
