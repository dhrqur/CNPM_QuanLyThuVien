@extends('layouts.app')

@section('title', $isEdit ? 'Sửa Nhân viên' : 'Thêm Nhân viên')
@section('page_title', $isEdit ? 'Sửa Nhân viên' : 'Thêm Nhân viên')
@section('page_subtitle', 'Cập nhật thông tin nhân viên')

@section('content')
<div class="content-card p-4">
    <form method="POST" action="{{ $isEdit ? route('nhanvien.update', $staff) : route('nhanvien.store') }}">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Tên nhân viên *</label>
                <input type="text" name="tenNV" value="{{ old('tenNV', $staff->tenNV) }}" class="form-control @error('tenNV') is-invalid @enderror" required>
                @error('tenNV') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">Ngày sinh *</label>
                <input type="date" name="ngaySinh" value="{{ old('ngaySinh', optional($staff->ngaySinh)->format('Y-m-d')) }}" class="form-control @error('ngaySinh') is-invalid @enderror" required>
                @error('ngaySinh') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">Giới tính *</label>
                <select name="gioiTinh" class="form-select @error('gioiTinh') is-invalid @enderror" required>
                    <option value="">-- Chọn --</option>
                    <option value="Nam" @selected(old('gioiTinh', $staff->gioiTinh) === 'Nam')>Nam</option>
                    <option value="Nữ" @selected(old('gioiTinh', $staff->gioiTinh) === 'Nữ')>Nữ</option>
                </select>
                @error('gioiTinh') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Địa chỉ *</label>
                <input type="text" name="diaChi" value="{{ old('diaChi', $staff->diaChi) }}" class="form-control @error('diaChi') is-invalid @enderror" required>
                @error('diaChi') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">Số điện thoại *</label>
                <input type="text" name="soDT" value="{{ old('soDT', $staff->soDT) }}" class="form-control @error('soDT') is-invalid @enderror" required>
                @error('soDT') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">Vai trò *</label>
                <select name="vaitro" class="form-select @error('vaitro') is-invalid @enderror" required>
                    <option value="">-- Chọn vai trò --</option>
                    <option value="Quản lý thư viện" @selected(old('vaitro', $staff->vaitro) === 'Quản lý thư viện')>Quản lý thư viện</option>
                    <option value="Thủ thư" @selected(old('vaitro', $staff->vaitro) === 'Thủ thư')>Thủ thư</option>
                </select>
                @error('vaitro') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Email *</label>
                <input type="email" name="email" value="{{ old('email', $staff->email) }}" class="form-control @error('email') is-invalid @enderror" required>
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Tên đăng nhập *</label>
                <input type="text" name="tenDangNhap" value="{{ old('tenDangNhap', $staff->tenDangNhap) }}" class="form-control @error('tenDangNhap') is-invalid @enderror" required>
                @error('tenDangNhap') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Mật khẩu {{ $isEdit ? '(để trống nếu không đổi)' : '*' }}</label>
                <input type="password" name="matKhau" class="form-control @error('matKhau') is-invalid @enderror" {{ $isEdit ? '' : 'required' }}>
                @error('matKhau') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-orange">{{ $isEdit ? 'Lưu thay đổi' : 'Thêm mới' }}</button>
            <a href="{{ route('nhanvien.index') }}" class="btn btn-outline-secondary">Quay lại</a>
        </div>
    </form>
</div>
@endsection
