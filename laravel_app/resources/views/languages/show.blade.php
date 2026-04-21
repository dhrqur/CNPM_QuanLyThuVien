@extends('layouts.app')

@section('title', 'Chi Tiết Ngôn ngữ')
@section('page_title', 'Chi Tiết Ngôn ngữ')
@section('page_subtitle', 'Thông tin đầy đủ của ngôn ngữ')

@section('content')
<div class="content-card p-4">
    <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
        <div>
            <h5 class="mb-1">{{ $language->tenNN }}</h5>
            <div class="text-muted">Mã ngôn ngữ: {{ $language->maNN }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('ngonngu.edit', $language) }}" class="btn btn-warning">Sửa</a>
            <a href="{{ route('ngonngu.index') }}" class="btn btn-outline-secondary">Quay lại</a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
            <tbody>
                <tr>
                    <th width="220">Mã ngôn ngữ</th>
                    <td>{{ $language->maNN }}</td>
                </tr>
                <tr>
                    <th>Tên ngôn ngữ</th>
                    <td>{{ $language->tenNN }}</td>
                </tr>
                <tr>
                    <th>Số sách liên kết</th>
                    <td>{{ $language->books_count }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
