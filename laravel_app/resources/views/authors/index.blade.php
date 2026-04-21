@extends('layouts.app')

@section('title', 'Quản lý Tác giả')
@section('page_title', 'Quản lý Tác giả')
@section('page_subtitle', 'Danh mục tác giả trong thư viện')

@section('content')
<div class="content-toolbar">
    <div class="page-header mb-0">
        <div>
            <h2 class="page-title">Tác giả</h2>
            <p class="page-desc">Tìm kiếm, thêm, sửa và xóa thông tin tác giả.</p>
        </div>

        <a href="{{ route('tacgia.create') }}" class="btn btn-orange">
            <i class="bi bi-plus-circle"></i> Thêm tác giả
        </a>
    </div>

    <form method="GET" class="d-flex gap-2 mt-3 search-box">
        <input type="text" name="search" class="form-control" placeholder="Tìm mã, tên, quốc tịch..." value="{{ $search }}">
        <button type="submit" class="btn btn-outline-orange">Tìm</button>
    </form>
</div>

<div class="table-wrap">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
            <tr>
                <th>Mã TG</th>
                <th>Tên tác giả</th>
                <th>Ngày sinh</th>
                <th>Giới tính</th>
                <th>Quốc tịch</th>
                <th>Mô tả</th>
                <th width="170">Thao tác</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($authors as $author)
                <tr>
                    <td>{{ $author->maTG }}</td>
                    <td>{{ $author->tenTG }}</td>
                    <td>{{ optional($author->namSinh)->format('d/m/Y') }}</td>
                    <td>{{ $author->gioiTinh }}</td>
                    <td>{{ $author->quocTich }}</td>
                    <td>{{ $author->moTa }}</td>
                    <td class="action-btns">
                        <a href="{{ route('tacgia.edit', $author) }}" class="btn btn-warning btn-sm">Sửa</a>
                        <form method="POST" action="{{ route('tacgia.destroy', $author) }}" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Xác nhận xóa tác giả này?')">Xóa</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-4">Không có dữ liệu tác giả.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    {{ $authors->links('pagination::bootstrap-4') }}
</div>
@endsection
