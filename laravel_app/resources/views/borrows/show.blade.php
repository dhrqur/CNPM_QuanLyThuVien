@extends('layouts.app')

@section('title', 'Chi Tiết Phiếu mượn')
@section('page_title', 'Chi Tiết Phiếu mượn')
@section('page_subtitle', 'Thông tin đầy đủ của phiếu mượn trả')

@section('content')
<div class="content-card p-4">
    <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
        <div>
            <h5 class="mb-1">{{ optional($borrow->reader)->tenDG ?: 'Phiếu mượn' }}</h5>
            <div class="text-muted">Mã phiếu: {{ $borrow->maMT }}</div>
        </div>
        <div class="d-flex flex-wrap gap-2 justify-content-end">
            @if ($borrow->trangThai !== 'Đã trả')
                <form method="POST" action="{{ route('muontra.return', $borrow) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success">Trả sách</button>
                </form>
            @endif

            <a href="{{ route('muontra.index') }}" class="btn btn-outline-secondary">Quay lại</a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
            <tbody>
                <tr>
                    <th width="220">Mã phiếu mượn</th>
                    <td>{{ $borrow->maMT }}</td>
                </tr>
                <tr>
                    <th>Mã độc giả</th>
                    <td>{{ $borrow->maDG }}</td>
                </tr>
                <tr>
                    <th>Tên độc giả</th>
                    <td>{{ optional($borrow->reader)->tenDG ?: 'Không có dữ liệu.' }}</td>
                </tr>
                <tr>
                    <th>Mã nhân viên</th>
                    <td>{{ $borrow->maNV }}</td>
                </tr>
                <tr>
                    <th>Tên nhân viên</th>
                    <td>{{ optional($borrow->staff)->tenNV ?: 'Không có dữ liệu.' }}</td>
                </tr>
                <tr>
                    <th>Ngày mượn</th>
                    <td>{{ optional($borrow->ngayMuon)->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <th>Hạn trả</th>
                    <td>{{ optional($borrow->hanTra)->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <th>Ngày trả</th>
                    <td>{{ optional($borrow->ngayTra)->format('d/m/Y') ?: 'Chưa trả.' }}</td>
                </tr>
                <tr>
                    <th>Trạng thái</th>
                    <td>
                        @if ($borrow->trangThai === 'Đã trả')
                            <span class="badge-soft-success">{{ $borrow->trangThai }}</span>
                        @elseif ($borrow->trangThai === 'Quá hạn')
                            <span class="badge-soft-danger">{{ $borrow->trangThai }}</span>
                        @else
                            <span class="badge-soft-warning">{{ $borrow->trangThai }}</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Sách mượn</th>
                    <td>
                        <ul class="mb-0 ps-3">
                            @forelse ($borrow->details as $detail)
                                <li>{{ optional($detail->book)->tenSach ?: 'Sách không còn tồn tại' }} ({{ $detail->soLuong }})</li>
                            @empty
                                <li>Không có dữ liệu sách mượn.</li>
                            @endforelse
                        </ul>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
