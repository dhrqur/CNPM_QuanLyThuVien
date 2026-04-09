@extends('layouts.app')

@section('title', $isEdit ? 'Sửa Thể loại' : 'Thêm Thể loại')
@section('page_title', $isEdit ? 'Sửa Thể loại' : 'Thêm Thể loại')
@section('page_subtitle', 'Cập nhật thông tin thể loại')

@section('content')
<div class="content-card p-4">
    <form method="POST" action="{{ $isEdit ? route('theloai.update', $category) : route('theloai.store') }}">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="mb-3">
            <label class="form-label">Tên thể loại *</label>
            <input type="text" name="tenTL" value="{{ old('tenTL', $category->tenTL) }}" class="form-control @error('tenTL') is-invalid @enderror" required>
            @error('tenTL') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-orange">{{ $isEdit ? 'Lưu thay đổi' : 'Thêm mới' }}</button>
            <a href="{{ route('theloai.index') }}" class="btn btn-outline-secondary">Quay lại</a>
        </div>
    </form>
</div>
@endsection
