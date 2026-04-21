@extends('layouts.app')

@section('title', 'Quản lý Ngôn ngữ')
@section('page_title', 'Quản lý Ngôn ngữ')
@section('page_subtitle', 'Danh mục ngôn ngữ sách')

@section('content')
<div class="content-toolbar">
    <div class="page-header mb-0">
        <div>
            <h2 class="page-title">Ngôn ngữ</h2>
            <p class="page-desc">Quản lý ngôn ngữ của sách trong thư viện.</p>
        </div>

        <a href="{{ route('ngonngu.create') }}" class="btn btn-orange"><i class="bi bi-plus-circle"></i> Thêm ngôn ngữ</a>
    </div>

    <form method="GET" class="d-flex gap-2 mt-3 search-box">
        <input type="text" name="search" class="form-control" placeholder="Tìm mã hoặc tên ngôn ngữ..." value="{{ $search }}">
        <button type="submit" class="btn btn-outline-orange">Tìm</button>
    </form>
</div>

<div class="table-wrap">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
            <tr>
                <th>Mã NN</th>
                <th>Tên ngôn ngữ</th>
                <th width="170">Thao tác</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($languages as $language)
                <tr>
                    <td>{{ $language->maNN }}</td>
                    <td>{{ $language->tenNN }}</td>
                    <td class="action-btns">
                        <a href="{{ route('ngonngu.edit', $language) }}" class="btn btn-warning btn-sm">Sửa</a>
                        <form method="POST" action="{{ route('ngonngu.destroy', $language) }}" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Xác nhận xóa ngôn ngữ này?')">Xóa</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center py-4">Không có dữ liệu ngôn ngữ.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $languages->links('pagination::bootstrap-4') }}</div>
@endsection
