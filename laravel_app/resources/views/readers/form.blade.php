@extends('layouts.app')

@section('title', $isEdit ? 'Sửa Độc giả' : 'Thêm Độc giả')
@section('page_title', $isEdit ? 'Sửa Độc giả' : 'Thêm Độc giả')
@section('page_subtitle', 'Cập nhật thông tin độc giả')

@section('content')
<div class="content-card p-4">
    <form method="POST" action="{{ $isEdit ? route('docgia.update', $reader) : route('docgia.store') }}">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Tên độc giả *</label>
                <input type="text" name="tenDG" value="{{ old('tenDG', $reader->tenDG) }}" class="form-control @error('tenDG') is-invalid @enderror" required>
                @error('tenDG') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">Ngày sinh *</label>
                <input type="date" name="ngaySinh" value="{{ old('ngaySinh', optional($reader->ngaySinh)->format('Y-m-d')) }}" class="form-control @error('ngaySinh') is-invalid @enderror" required>
                @error('ngaySinh') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">Giới tính *</label>
                <select name="gioiTinh" class="form-select @error('gioiTinh') is-invalid @enderror" required>
                    <option value="">-- Chọn --</option>
                    <option value="Nam" @selected(old('gioiTinh', $reader->gioiTinh) === 'Nam')>Nam</option>
                    <option value="Nữ" @selected(old('gioiTinh', $reader->gioiTinh) === 'Nữ')>Nữ</option>
                </select>
                @error('gioiTinh') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Địa chỉ *</label>
                <input type="text" name="diaChi" value="{{ old('diaChi', $reader->diaChi) }}" class="form-control @error('diaChi') is-invalid @enderror" required>
                @error('diaChi') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">Số điện thoại *</label>
                <input type="text" name="soDT" value="{{ old('soDT', $reader->soDT) }}" class="form-control @error('soDT') is-invalid @enderror" required>
                @error('soDT') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">Email *</label>
                <input type="email" name="email" value="{{ old('email', $reader->email) }}" class="form-control @error('email') is-invalid @enderror" required>
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-orange">{{ $isEdit ? 'Lưu thay đổi' : 'Thêm mới' }}</button>
            <a href="{{ route('docgia.index') }}" class="btn btn-outline-secondary">Quay lại</a>
        </div>
    </form>
</div>
@endsection
