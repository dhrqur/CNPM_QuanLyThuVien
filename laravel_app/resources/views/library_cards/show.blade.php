@extends('layouts.app')

@section('title', 'Chi Tiết Thẻ thư viện')
@section('page_title', 'Chi Tiết Thẻ thư viện')
@section('page_subtitle', 'Thông tin đầy đủ của thẻ thư viện')

@section('content')
<div class="content-card p-4">
    <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
        <div>
            <h5 class="mb-1">{{ optional($libraryCard->reader)->tenDG ?: 'Thẻ thư viện' }}</h5>
            <div class="text-muted">Mã thẻ: {{ $libraryCard->maTTV }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('thethuvien.edit', $libraryCard) }}" class="btn btn-warning">Sửa</a>
            <a href="{{ route('thethuvien.index') }}" class="btn btn-outline-secondary">Quay lại</a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
            <tbody>
                <tr>
                    <th width="220">Mã thẻ thư viện</th>
                    <td>{{ $libraryCard->maTTV }}</td>
                </tr>
                <tr>
                    <th>Mã độc giả</th>
                    <td>{{ $libraryCard->maDG }}</td>
                </tr>
                <tr>
                    <th>Tên độc giả</th>
                    <td>{{ optional($libraryCard->reader)->tenDG ?: 'Không có dữ liệu.' }}</td>
                </tr>
                <tr>
                    <th>Ngày cấp</th>
                    <td>{{ optional($libraryCard->ngayCap)->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <th>Ngày hết hạn</th>
                    <td>{{ optional($libraryCard->ngayHetHan)->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <th>Trạng thái</th>
                    <td>
                        @if ($libraryCard->trangThai === 'Còn hạn')
                            <span class="badge-soft-success">{{ $libraryCard->trangThai }}</span>
                        @else
                            <span class="badge-soft-danger">{{ $libraryCard->trangThai }}</span>
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
