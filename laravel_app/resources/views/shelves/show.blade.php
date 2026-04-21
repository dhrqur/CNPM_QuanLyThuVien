@extends('layouts.app')

@section('title', 'Chi Tiết Kệ sách')
@section('page_title', 'Chi Tiết Kệ sách')
@section('page_subtitle', 'Thông tin đầy đủ của kệ sách')

@section('content')
<div class="content-card p-4">
    <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
        <div>
            <h5 class="mb-1">{{ $shelf->tenKS }}</h5>
            <div class="text-muted">Mã kệ sách: {{ $shelf->maKS }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('kesach.edit', $shelf) }}" class="btn btn-warning">Sửa</a>
            <a href="{{ route('kesach.index') }}" class="btn btn-outline-secondary">Quay lại</a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
            <tbody>
                <tr>
                    <th width="220">Mã kệ sách</th>
                    <td>{{ $shelf->maKS }}</td>
                </tr>
                <tr>
                    <th>Tên kệ sách</th>
                    <td>{{ $shelf->tenKS }}</td>
                </tr>
                <tr>
                    <th>Số sách trên kệ</th>
                    <td>{{ $shelf->books_count }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
