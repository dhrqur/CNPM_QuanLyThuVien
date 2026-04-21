@extends('layouts.app')

@section('title', 'Quản lý Kệ sách')
@section('page_title', 'Quản lý Kệ sách')
@section('page_subtitle', 'Danh mục vị trí kệ sách')

@section('content')
<div class="content-toolbar">
    <div class="page-header mb-0">
        <div>
            <h2 class="page-title">Kệ sách</h2>
            <p class="page-desc">Quản lý vị trí lưu trữ sách.</p>
        </div>

        <a href="{{ route('kesach.create') }}" class="btn btn-orange"><i class="bi bi-plus-circle"></i> Thêm kệ sách</a>
    </div>

    <form method="GET" class="d-flex gap-2 mt-3 search-box">
        <input type="text" name="search" class="form-control" placeholder="Tìm mã hoặc tên kệ..." value="{{ $search }}">
        <button type="submit" class="btn btn-outline-orange">Tìm</button>
    </form>
</div>

<div class="table-wrap">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
            <tr>
                <th>Mã KS</th>
                <th>Tên kệ sách</th>
                <th width="170">Thao tác</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($shelves as $shelf)
                <tr>
                    <td>{{ $shelf->maKS }}</td>
                    <td>{{ $shelf->tenKS }}</td>
                    <td class="action-btns">
                        <a href="{{ route('kesach.edit', $shelf) }}" class="btn btn-warning btn-sm">Sửa</a>
                        <form method="POST" action="{{ route('kesach.destroy', $shelf) }}" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Xác nhận xóa kệ sách này?')">Xóa</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center py-4">Không có dữ liệu kệ sách.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $shelves->links('pagination::bootstrap-4') }}</div>
@endsection
