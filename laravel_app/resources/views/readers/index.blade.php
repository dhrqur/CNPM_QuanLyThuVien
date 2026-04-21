@extends('layouts.app')

@section('title', 'Quản lý Độc giả')
@section('page_title', 'Quản lý Độc giả')
@section('page_subtitle', 'Danh sách độc giả thư viện')

@section('content')
<div class="content-toolbar">
    <div class="page-header mb-0">
        <div>
            <h2 class="page-title">Độc giả</h2>
            <p class="page-desc">Quản lý thông tin độc giả và liên hệ.</p>
        </div>

        <a href="{{ route('docgia.create') }}" class="btn btn-orange"><i class="bi bi-plus-circle"></i> Thêm độc giả</a>
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
                <th>Mã DG</th>
                <th>Tên độc giả</th>
                <th>Ngày sinh</th>
                <th>Giới tính</th>
                <th>Địa chỉ</th>
                <th>SĐT</th>
                <th>Email</th>
                <th width="240">Thao tác</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($readers as $reader)
                <tr>
                    <td>{{ $reader->maDG }}</td>
                    <td>{{ $reader->tenDG }}</td>
                    <td>{{ optional($reader->ngaySinh)->format('d/m/Y') }}</td>
                    <td>{{ $reader->gioiTinh }}</td>
                    <td>{{ $reader->diaChi }}</td>
                    <td>{{ $reader->soDT }}</td>
                    <td>{{ $reader->email }}</td>
                    <td class="action-btns">
                        <a href="{{ route('docgia.show', $reader) }}" class="btn btn-info btn-sm">Xem chi tiết</a>
                        <a href="{{ route('docgia.edit', $reader) }}" class="btn btn-warning btn-sm">Sửa</a>
                        <form method="POST" action="{{ route('docgia.destroy', $reader) }}" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Xác nhận xóa độc giả này?')">Xóa</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center py-4">Không có dữ liệu độc giả.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $readers->links('pagination::bootstrap-4') }}</div>
@endsection
