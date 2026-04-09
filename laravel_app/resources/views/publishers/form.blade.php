@extends('layouts.app')

@section('title', $isEdit ? 'Sửa Nhà xuất bản' : 'Thêm Nhà xuất bản')
@section('page_title', $isEdit ? 'Sửa Nhà xuất bản' : 'Thêm Nhà xuất bản')
@section('page_subtitle', 'Cập nhật thông tin nhà xuất bản')

@section('content')
<div class="content-card p-4">
    <form method="POST" action="{{ $isEdit ? route('nhaxuatban.update', $publisher) : route('nhaxuatban.store') }}">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Tên nhà xuất bản *</label>
                <input type="text" name="tenNXB" value="{{ old('tenNXB', $publisher->tenNXB) }}" class="form-control @error('tenNXB') is-invalid @enderror" required>
                @error('tenNXB') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Địa chỉ *</label>
                <input type="text" name="diaChi" value="{{ old('diaChi', $publisher->diaChi) }}" class="form-control @error('diaChi') is-invalid @enderror" required>
                @error('diaChi') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Số điện thoại *</label>
                <input type="text" name="soDT" value="{{ old('soDT', $publisher->soDT) }}" class="form-control @error('soDT') is-invalid @enderror" required>
                @error('soDT') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Email *</label>
                <input type="email" name="email" value="{{ old('email', $publisher->email) }}" class="form-control @error('email') is-invalid @enderror" required>
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-orange">{{ $isEdit ? 'Lưu thay đổi' : 'Thêm mới' }}</button>
            <a href="{{ route('nhaxuatban.index') }}" class="btn btn-outline-secondary">Quay lại</a>
        </div>
    </form>
</div>
@endsection
