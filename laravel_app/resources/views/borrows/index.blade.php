@extends('layouts.app')

@section('title', 'Quản lý Mượn trả')
@section('page_title', 'Quản lý Mượn trả')
@section('page_subtitle', 'Theo dõi phiếu mượn, gia hạn hoặc sửa trực tiếp từ danh sách')

@section('content')
<div class="content-toolbar">
    <div class="page-header mb-0">
        <div>
            <h2 class="page-title">Mượn trả</h2>
            <p class="page-desc">Tạo phiếu mượn, gia hạn, chỉnh sửa và theo dõi lịch sử mượn trả.</p>
        </div>

        <a href="{{ route('muontra.create') }}" class="btn btn-orange"><i class="bi bi-plus-circle"></i> Tạo phiếu mượn</a>
    </div>

    <form method="GET" class="d-flex gap-2 mt-3 search-box">
        <input type="text" name="search" class="form-control" placeholder="Tìm mã phiếu hoặc tên độc giả..." value="{{ $search }}">
        <button type="submit" class="btn btn-outline-orange">Tìm</button>
    </form>
</div>

<div class="table-wrap">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
            <tr>
                <th>Mã phiếu</th>
                <th>Độc giả</th>
                <th>Nhân viên</th>
                <th>Ngày mượn</th>
                <th>Hạn trả</th>
                <th>Ngày trả</th>
                <th>Trạng thái</th>
                <th>Sách mượn</th>
                <th width="330">Thao tác</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($borrows as $borrow)
                <tr>
                    <td>{{ $borrow->maMT }}</td>
                    <td>{{ optional($borrow->reader)->tenDG }}</td>
                    <td>{{ optional($borrow->staff)->tenNV }}</td>
                    <td>{{ optional($borrow->ngayMuon)->format('d/m/Y') }}</td>
                    <td>{{ optional($borrow->hanTra)->format('d/m/Y') }}</td>
                    <td>{{ optional($borrow->ngayTra)->format('d/m/Y') }}</td>
                    <td>
                        @if ($borrow->trangThai === 'Đã trả')
                            <span class="badge-soft-success">{{ $borrow->trangThai }}</span>
                        @elseif ($borrow->trangThai === 'Quá hạn')
                            <span class="badge-soft-danger">{{ $borrow->trangThai }}</span>
                        @else
                            <span class="badge-soft-warning">{{ $borrow->trangThai }}</span>
                        @endif
                    </td>
                    <td>
                        <ul class="mb-0 ps-3">
                            @foreach ($borrow->details as $detail)
                                <li>{{ optional($detail->book)->tenSach }} ({{ $detail->soLuong }})</li>
                            @endforeach
                        </ul>
                    </td>
                    <td class="action-btns">
                        <a href="{{ route('muontra.show', $borrow) }}" class="btn btn-info btn-sm">Xem chi tiết</a>
                        @if ($borrow->trangThai !== 'Đã trả')
                            <form method="POST" action="{{ route('muontra.extend', $borrow) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm">Gia hạn</button>
                            </form>
                        @endif
                        <a href="{{ route('muontra.edit', $borrow) }}" class="btn btn-warning btn-sm">Sửa</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center py-4">Không có dữ liệu mượn trả.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $borrows->links('pagination::bootstrap-4') }}</div>
@endsection
