@extends('layouts.app')

@section('title', $isEdit ? 'Sửa Kệ sách' : 'Thêm Kệ sách')
@section('page_title', $isEdit ? 'Sửa Kệ sách' : 'Thêm Kệ sách')
@section('page_subtitle', 'Cập nhật thông tin kệ sách')

@section('content')
<div class="content-card p-4">
    <form method="POST" action="{{ $isEdit ? route('kesach.update', $shelf) : route('kesach.store') }}">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="mb-3">
            <label class="form-label">Tên kệ sách *</label>
            <input type="text" name="tenKS" value="{{ old('tenKS', $shelf->tenKS) }}" class="form-control @error('tenKS') is-invalid @enderror" required>
            @error('tenKS') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-orange">{{ $isEdit ? 'Lưu thay đổi' : 'Thêm mới' }}</button>
            <a href="{{ route('kesach.index') }}" class="btn btn-outline-secondary">Quay lại</a>
        </div>
    </form>
</div>
@endsection
