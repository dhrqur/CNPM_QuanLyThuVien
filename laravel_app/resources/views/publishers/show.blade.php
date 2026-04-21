@extends('layouts.app')

@section('title', 'Chi Tiết Nhà xuất bản')
@section('page_title', 'Chi Tiết Nhà xuất bản')
@section('page_subtitle', 'Thông tin đầy đủ của nhà xuất bản')

@section('content')
<div class="content-card p-4">
    <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
        <div>
            <h5 class="mb-1">{{ $publisher->tenNXB }}</h5>
            <div class="text-muted">Mã nhà xuất bản: {{ $publisher->maNXB }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('nhaxuatban.edit', $publisher) }}" class="btn btn-warning">Sửa</a>
            <a href="{{ route('nhaxuatban.index') }}" class="btn btn-outline-secondary">Quay lại</a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
            <tbody>
                <tr>
                    <th width="220">Mã nhà xuất bản</th>
                    <td>{{ $publisher->maNXB }}</td>
                </tr>
                <tr>
                    <th>Tên nhà xuất bản</th>
                    <td>{{ $publisher->tenNXB }}</td>
                </tr>
                <tr>
                    <th>Địa chỉ</th>
                    <td>{{ $publisher->diaChi }}</td>
                </tr>
                <tr>
                    <th>Số điện thoại</th>
                    <td>{{ $publisher->soDT }}</td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td>{{ $publisher->email }}</td>
                </tr>
                <tr>
                    <th>Số sách liên kết</th>
                    <td>{{ $publisher->books_count }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
