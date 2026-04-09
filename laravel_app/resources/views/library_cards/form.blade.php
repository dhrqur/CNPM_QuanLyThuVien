@extends('layouts.app')

@section('title', $isEdit ? 'Sửa Thẻ thư viện' : 'Thêm Thẻ thư viện')
@section('page_title', $isEdit ? 'Sửa Thẻ thư viện' : 'Thêm Thẻ thư viện')
@section('page_subtitle', 'Cập nhật thông tin thẻ thư viện')

@section('content')
<div class="content-card p-4">
    <form method="POST" action="{{ $isEdit ? route('thethuvien.update', $libraryCard) : route('thethuvien.store') }}">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Độc giả *</label>
                <select name="maDG" class="form-select @error('maDG') is-invalid @enderror" required>
                    <option value="">-- Chọn độc giả --</option>
                    @foreach ($readers as $reader)
                        <option value="{{ $reader->maDG }}" @selected(old('maDG', $libraryCard->maDG) === $reader->maDG)>
                            {{ $reader->maDG }} - {{ $reader->tenDG }}
                        </option>
                    @endforeach
                </select>
                @error('maDG') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label">Ngày cấp *</label>
                <input type="date" name="ngayCap" value="{{ old('ngayCap', optional($libraryCard->ngayCap)->format('Y-m-d')) }}" class="form-control @error('ngayCap') is-invalid @enderror" required>
                @error('ngayCap') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label">Ngày hết hạn *</label>
                <input type="date" name="ngayHetHan" value="{{ old('ngayHetHan', optional($libraryCard->ngayHetHan)->format('Y-m-d')) }}" class="form-control @error('ngayHetHan') is-invalid @enderror" required>
                @error('ngayHetHan') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-orange">{{ $isEdit ? 'Lưu thay đổi' : 'Thêm mới' }}</button>
            <a href="{{ route('thethuvien.index') }}" class="btn btn-outline-secondary">Quay lại</a>
        </div>
    </form>
</div>
@endsection
