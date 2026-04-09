@extends('layouts.app')

@section('title', $isEdit ? 'Sửa Tác giả' : 'Thêm Tác giả')
@section('page_title', $isEdit ? 'Sửa Tác giả' : 'Thêm Tác giả')
@section('page_subtitle', 'Cập nhật thông tin tác giả')

@section('content')
<div class="content-card p-4">
    <form method="POST" action="{{ $isEdit ? route('tacgia.update', $author) : route('tacgia.store') }}">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Tên tác giả *</label>
                <input type="text" name="tenTG" value="{{ old('tenTG', $author->tenTG) }}" class="form-control @error('tenTG') is-invalid @enderror" required>
                @error('tenTG') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">Ngày sinh</label>
                <input type="date" name="namSinh" value="{{ old('namSinh', optional($author->namSinh)->format('Y-m-d')) }}" class="form-control @error('namSinh') is-invalid @enderror">
                @error('namSinh') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">Giới tính</label>
                <select name="gioiTinh" class="form-select @error('gioiTinh') is-invalid @enderror">
                    <option value="">-- Chọn --</option>
                    <option value="Nam" @selected(old('gioiTinh', $author->gioiTinh) === 'Nam')>Nam</option>
                    <option value="Nữ" @selected(old('gioiTinh', $author->gioiTinh) === 'Nữ')>Nữ</option>
                </select>
                @error('gioiTinh') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Quốc tịch *</label>
                <input type="text" name="quocTich" value="{{ old('quocTich', $author->quocTich) }}" class="form-control @error('quocTich') is-invalid @enderror" required>
                @error('quocTich') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Mô tả</label>
                <input type="text" name="moTa" value="{{ old('moTa', $author->moTa) }}" class="form-control @error('moTa') is-invalid @enderror">
                @error('moTa') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-orange">{{ $isEdit ? 'Lưu thay đổi' : 'Thêm mới' }}</button>
            <a href="{{ route('tacgia.index') }}" class="btn btn-outline-secondary">Quay lại</a>
        </div>
    </form>
</div>
@endsection
