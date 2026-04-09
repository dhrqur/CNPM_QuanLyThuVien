@extends('layouts.app')

@section('title', $isEdit ? 'Sửa Ngôn ngữ' : 'Thêm Ngôn ngữ')
@section('page_title', $isEdit ? 'Sửa Ngôn ngữ' : 'Thêm Ngôn ngữ')
@section('page_subtitle', 'Cập nhật thông tin ngôn ngữ')

@section('content')
<div class="content-card p-4">
    <form method="POST" action="{{ $isEdit ? route('ngonngu.update', $language) : route('ngonngu.store') }}">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="mb-3">
            <label class="form-label">Tên ngôn ngữ *</label>
            <input type="text" name="tenNN" value="{{ old('tenNN', $language->tenNN) }}" class="form-control @error('tenNN') is-invalid @enderror" required>
            @error('tenNN') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-orange">{{ $isEdit ? 'Lưu thay đổi' : 'Thêm mới' }}</button>
            <a href="{{ route('ngonngu.index') }}" class="btn btn-outline-secondary">Quay lại</a>
        </div>
    </form>
</div>
@endsection
