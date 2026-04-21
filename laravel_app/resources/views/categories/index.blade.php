@extends('layouts.app')

@section('title', 'Quản lý Thể loại')
@section('page_title', 'Quản lý Thể loại')
@section('page_subtitle', 'Danh mục thể loại sách')

@section('content')
<div class="content-toolbar">
    <div class="page-header mb-0">
        <div>
            <h2 class="page-title">Thể loại</h2>
            <p class="page-desc">Quản lý các nhóm thể loại sách.</p>
        </div>

        <a href="{{ route('theloai.create') }}" class="btn btn-orange"><i class="bi bi-plus-circle"></i> Thêm thể loại</a>
    </div>

    <form method="GET" class="d-flex gap-2 mt-3 search-box">
        <input type="text" name="search" class="form-control" placeholder="Tìm mã hoặc tên thể loại..." value="{{ $search }}">
        <button type="submit" class="btn btn-outline-orange">Tìm</button>
    </form>
</div>

<div class="table-wrap">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
            <tr>
                <th>Mã TL</th>
                <th>Tên thể loại</th>
                <th width="170">Thao tác</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($categories as $category)
                <tr>
                    <td>{{ $category->maTL }}</td>
                    <td>{{ $category->tenTL }}</td>
                    <td class="action-btns">
                        <a href="{{ route('theloai.edit', $category) }}" class="btn btn-warning btn-sm">Sửa</a>
                        <form method="POST" action="{{ route('theloai.destroy', $category) }}" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Xác nhận xóa thể loại này?')">Xóa</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center py-4">Không có dữ liệu thể loại.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $categories->links('pagination::bootstrap-4') }}</div>
@endsection
