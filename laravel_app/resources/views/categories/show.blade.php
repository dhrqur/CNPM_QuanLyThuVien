@extends('layouts.app')

@section('title', 'Chi Tiết Thể loại')
@section('page_title', 'Chi Tiết Thể loại')
@section('page_subtitle', 'Thông tin đầy đủ của thể loại sách')

@section('content')
<div class="content-card p-4">
    <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
        <div>
            <h5 class="mb-1">{{ $category->tenTL }}</h5>
            <div class="text-muted">Mã thể loại: {{ $category->maTL }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('theloai.edit', $category) }}" class="btn btn-warning">Sửa</a>
            <a href="{{ route('theloai.index') }}" class="btn btn-outline-secondary">Quay lại</a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
            <tbody>
                <tr>
                    <th width="220">Mã thể loại</th>
                    <td>{{ $category->maTL }}</td>
                </tr>
                <tr>
                    <th>Tên thể loại</th>
                    <td>{{ $category->tenTL }}</td>
                </tr>
                <tr>
                    <th>Số sách thuộc thể loại</th>
                    <td>{{ $category->books_count }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
