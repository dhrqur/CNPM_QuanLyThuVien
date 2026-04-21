@extends('layouts.app')

@section('title', 'Quản lý Sách')
@section('page_title', 'Quản lý Sách')
@section('page_subtitle', 'Danh mục sách trong thư viện')

@section('content')
<div class="content-toolbar">
    <div class="page-header mb-0">
        <div>
            <h2 class="page-title">Sách</h2>
            <p class="page-desc">Quản lý thông tin, số lượng và trạng thái sách.</p>
        </div>

        <a href="{{ route('sach.create') }}" class="btn btn-orange"><i class="bi bi-plus-circle"></i> Thêm sách</a>
    </div>

    <form method="GET" class="mt-3">
        <div class="row g-2 search-box search-box-wide">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Từ khóa chung" value="{{ $filters['search'] ?? '' }}">
            </div>
            <div class="col-md-2">
                <input type="text" name="maSach" class="form-control" placeholder="Mã sách" value="{{ $filters['maSach'] ?? '' }}">
            </div>
            <div class="col-md-3">
                <input type="text" name="tenSach" class="form-control" placeholder="Tên sách" value="{{ $filters['tenSach'] ?? '' }}">
            </div>
            <div class="col-md-2">
                <input type="text" name="tacGia" class="form-control" placeholder="Tác giả" value="{{ $filters['tacGia'] ?? '' }}">
            </div>
            <div class="col-md-2">
                <select name="trangThai" class="form-select">
                    <option value="">-- Trạng thái --</option>
                    <option value="Còn" @selected(($filters['trangThai'] ?? '') === 'Còn')>Còn</option>
                    <option value="Hết" @selected(($filters['trangThai'] ?? '') === 'Hết')>Hết</option>
                </select>
            </div>
        </div>
        <div class="mt-2 d-flex gap-2">
            <button type="submit" class="btn btn-outline-orange">Tìm kiếm</button>
            <a href="{{ route('sach.index') }}" class="btn btn-outline-secondary">Xóa lọc</a>
        </div>
    </form>

    @if ($errors->any())
        <div class="alert alert-danger mt-3 mb-0">{{ $errors->first() }}</div>
    @endif
</div>

<div class="table-wrap">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
            <tr>
                <th>Mã sách</th>
                <th>Tên sách</th>
                <th>Tác giả</th>
                <th>Thể loại</th>
                <th>NXB</th>
                <th>Ngôn ngữ</th>
                <th>Kệ sách</th>
                <th>Năm XB</th>
                <th>Số lượng</th>
                <th>Trạng thái</th>
                <th width="240">Thao tác</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($books as $book)
                <tr>
                    <td>{{ $book->maSach }}</td>
                    <td>{{ $book->tenSach }}</td>
                    <td>{{ optional($book->author)->tenTG }}</td>
                    <td>{{ optional($book->category)->tenTL }}</td>
                    <td>{{ optional($book->publisher)->tenNXB }}</td>
                    <td>{{ optional($book->language)->tenNN }}</td>
                    <td>{{ optional($book->shelf)->tenKS }}</td>
                    <td>{{ $book->namXB }}</td>
                    <td>{{ $book->soLuong }}</td>
                    <td>
                        @if ($book->trangThai === 'Còn')
                            <span class="badge-soft-success">{{ $book->trangThai }}</span>
                        @else
                            <span class="badge-soft-danger">{{ $book->trangThai }}</span>
                        @endif
                    </td>
                    <td class="action-btns">
                        <a href="{{ route('sach.show', $book) }}" class="btn btn-info btn-sm">Xem chi tiết</a>
                        <a href="{{ route('sach.edit', $book) }}" class="btn btn-warning btn-sm">Sửa</a>
                        <form method="POST" action="{{ route('sach.destroy', $book) }}" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Xác nhận xóa sách này?')">Xóa</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center py-4">
                        {{ $hasFilter ? 'Không tìm thấy sách phù hợp.' : 'Không có sách trong thư viện.' }}
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $books->links('pagination::bootstrap-4') }}</div>
@endsection
