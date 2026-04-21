@extends('layouts.app')

@section('title', 'Chi Tiết Sách')
@section('page_title', 'Chi Tiết Sách')
@section('page_subtitle', 'Thông tin đầy đủ của sách')

@section('content')
<div class="content-card p-4">
    <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
        <div>
            <h5 class="mb-1">{{ $book->tenSach }}</h5>
            <div class="text-muted">Mã sách: {{ $book->maSach }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('sach.edit', $book) }}" class="btn btn-warning">Sửa</a>
            <a href="{{ route('sach.index') }}" class="btn btn-outline-secondary">Quay lại</a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
            <tbody>
                <tr>
                    <th width="220">Mã sách</th>
                    <td>{{ $book->maSach }}</td>
                </tr>
                <tr>
                    <th>Tên sách</th>
                    <td>{{ $book->tenSach }}</td>
                </tr>
                <tr>
                    <th>Tác giả</th>
                    <td>{{ optional($book->author)->tenTG }}</td>
                </tr>
                <tr>
                    <th>Nhà xuất bản</th>
                    <td>{{ optional($book->publisher)->tenNXB }}</td>
                </tr>
                <tr>
                    <th>Thể loại</th>
                    <td>{{ optional($book->category)->tenTL }}</td>
                </tr>
                <tr>
                    <th>Năm xuất bản</th>
                    <td>{{ $book->namXB }}</td>
                </tr>
                <tr>
                    <th>Ngôn ngữ</th>
                    <td>{{ optional($book->language)->tenNN }}</td>
                </tr>
                <tr>
                    <th>Vị trí / Kệ sách</th>
                    <td>{{ optional($book->shelf)->tenKS }}</td>
                </tr>
                <tr>
                    <th>Số lượng</th>
                    <td>{{ $book->soLuong }}</td>
                </tr>
                <tr>
                    <th>Trạng thái sách</th>
                    <td>
                        @if ($book->trangThai === 'Còn')
                            <span class="badge-soft-success">{{ $book->trangThai }}</span>
                        @else
                            <span class="badge-soft-danger">{{ $book->trangThai }}</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Mô tả sách</th>
                    <td>{{ $book->moTa ?: 'Không có mô tả.' }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
