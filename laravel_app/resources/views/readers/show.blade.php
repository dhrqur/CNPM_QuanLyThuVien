@extends('layouts.app')

@section('title', 'Chi Tiết Độc giả')
@section('page_title', 'Chi Tiết Độc giả')
@section('page_subtitle', 'Thông tin đầy đủ của độc giả')

@section('content')
<div class="content-card p-4">
    <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
        <div>
            <h5 class="mb-1">{{ $reader->tenDG }}</h5>
            <div class="text-muted">Mã độc giả: {{ $reader->maDG }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('docgia.edit', $reader) }}" class="btn btn-warning">Sửa</a>
            <a href="{{ route('docgia.index') }}" class="btn btn-outline-secondary">Quay lại</a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
            <tbody>
                <tr>
                    <th width="220">Mã độc giả</th>
                    <td>{{ $reader->maDG }}</td>
                </tr>
                <tr>
                    <th>Tên độc giả</th>
                    <td>{{ $reader->tenDG }}</td>
                </tr>
                <tr>
                    <th>Ngày sinh</th>
                    <td>{{ optional($reader->ngaySinh)->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <th>Giới tính</th>
                    <td>{{ $reader->gioiTinh }}</td>
                </tr>
                <tr>
                    <th>Địa chỉ</th>
                    <td>{{ $reader->diaChi }}</td>
                </tr>
                <tr>
                    <th>Số điện thoại</th>
                    <td>{{ $reader->soDT }}</td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td>{{ $reader->email }}</td>
                </tr>
                <tr>
                    <th>Mã thẻ thư viện</th>
                    <td>{{ optional($reader->card)->maTTV ?: 'Chưa có thẻ thư viện.' }}</td>
                </tr>
                <tr>
                    <th>Trạng thái thẻ</th>
                    <td>
                        @if (optional($reader->card)->trangThai === 'Còn hạn')
                            <span class="badge-soft-success">{{ $reader->card->trangThai }}</span>
                        @elseif (optional($reader->card)->trangThai === 'Hết hạn')
                            <span class="badge-soft-danger">{{ $reader->card->trangThai }}</span>
                        @else
                            <span class="text-muted">Chưa có thẻ thư viện.</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Tổng phiếu mượn</th>
                    <td>{{ $reader->borrows_count }}</td>
                </tr>
                <tr>
                    <th>Phiếu chưa trả</th>
                    <td>{{ $reader->active_borrows_count }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
