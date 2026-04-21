@extends('layouts.app')

@section('title', 'Chi Tiết Tác giả')
@section('page_title', 'Chi Tiết Tác giả')
@section('page_subtitle', 'Thông tin đầy đủ của tác giả')

@section('content')
<div class="content-card p-4">
    <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
        <div>
            <h5 class="mb-1">{{ $author->tenTG }}</h5>
            <div class="text-muted">Mã tác giả: {{ $author->maTG }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('tacgia.edit', $author) }}" class="btn btn-warning">Sửa</a>
            <a href="{{ route('tacgia.index') }}" class="btn btn-outline-secondary">Quay lại</a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
            <tbody>
                <tr>
                    <th width="220">Mã tác giả</th>
                    <td>{{ $author->maTG }}</td>
                </tr>
                <tr>
                    <th>Tên tác giả</th>
                    <td>{{ $author->tenTG }}</td>
                </tr>
                <tr>
                    <th>Ngày sinh</th>
                    <td>{{ optional($author->namSinh)->format('d/m/Y') ?: 'Không có dữ liệu.' }}</td>
                </tr>
                <tr>
                    <th>Giới tính</th>
                    <td>{{ $author->gioiTinh ?: 'Không có dữ liệu.' }}</td>
                </tr>
                <tr>
                    <th>Quốc tịch</th>
                    <td>{{ $author->quocTich }}</td>
                </tr>
                <tr>
                    <th>Mô tả</th>
                    <td>{{ $author->moTa ?: 'Không có mô tả.' }}</td>
                </tr>
                <tr>
                    <th>Số sách liên kết</th>
                    <td>{{ $author->books_count }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
