@extends('layouts.app')

@section('title', 'Quản lý Thẻ thư viện')
@section('page_title', 'Quản lý Thẻ thư viện')
@section('page_subtitle', 'Theo dõi trạng thái thẻ độc giả')

@section('content')
<div class="content-toolbar">
    <div class="page-header mb-0">
        <div>
            <h2 class="page-title">Thẻ thư viện</h2>
            <p class="page-desc">Quản lý ngày cấp, ngày hết hạn và trạng thái thẻ.</p>
        </div>

        <a href="{{ route('thethuvien.create') }}" class="btn btn-orange"><i class="bi bi-plus-circle"></i> Thêm thẻ</a>
    </div>

    <form method="GET" class="d-flex gap-2 mt-3 search-box">
        <input type="text" name="search" class="form-control" placeholder="Tìm mã thẻ, mã độc giả hoặc tên độc giả..." value="{{ $search }}">
        <button type="submit" class="btn btn-outline-orange">Tìm</button>
    </form>
</div>

<div class="table-wrap">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
            <tr>
                <th>Mã thẻ</th>
                <th>Mã độc giả</th>
                <th>Tên độc giả</th>
                <th>Ngày cấp</th>
                <th>Ngày hết hạn</th>
                <th>Trạng thái</th>
                <th width="170">Thao tác</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($cards as $card)
                <tr>
                    <td>{{ $card->maTTV }}</td>
                    <td>{{ $card->maDG }}</td>
                    <td>{{ optional($card->reader)->tenDG }}</td>
                    <td>{{ optional($card->ngayCap)->format('d/m/Y') }}</td>
                    <td>{{ optional($card->ngayHetHan)->format('d/m/Y') }}</td>
                    <td>
                        @if ($card->trangThai === 'Còn hạn')
                            <span class="badge-soft-success">{{ $card->trangThai }}</span>
                        @else
                            <span class="badge-soft-danger">{{ $card->trangThai }}</span>
                        @endif
                    </td>
                    <td class="action-btns">
                        <a href="{{ route('thethuvien.edit', $card) }}" class="btn btn-warning btn-sm">Sửa</a>
                        <form method="POST" action="{{ route('thethuvien.destroy', $card) }}" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Xác nhận xóa thẻ này?')">Xóa</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center py-4">Không có dữ liệu thẻ thư viện.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $cards->links('pagination::bootstrap-4') }}</div>
@endsection
