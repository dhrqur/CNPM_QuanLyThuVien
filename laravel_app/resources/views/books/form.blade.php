@extends('layouts.app')

@section('title', $isEdit ? 'Sửa Sách' : 'Thêm Sách')
@section('page_title', $isEdit ? 'Sửa Sách' : 'Thêm Sách')
@section('page_subtitle', 'Cập nhật thông tin sách')

@section('content')
<div class="content-card p-4">
    <form method="POST" action="{{ $isEdit ? route('sach.update', $book) : route('sach.store') }}">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Tên sách *</label>
                <input type="text" name="tenSach" value="{{ old('tenSach', $book->tenSach) }}" class="form-control @error('tenSach') is-invalid @enderror" required>
                @error('tenSach') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">Năm xuất bản *</label>
                <input type="number" name="namXB" value="{{ old('namXB', $book->namXB) }}" class="form-control @error('namXB') is-invalid @enderror" required>
                @error('namXB') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">Số lượng *</label>
                <input type="number" name="soLuong" min="1" value="{{ old('soLuong', $book->soLuong ?? 1) }}" class="form-control @error('soLuong') is-invalid @enderror" required>
                @error('soLuong') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label">Tác giả *</label>
                <select name="maTG" class="form-select @error('maTG') is-invalid @enderror" required>
                    <option value="">-- Chọn tác giả --</option>
                    @foreach ($authors as $author)
                        <option value="{{ $author->maTG }}" @selected(old('maTG', $book->maTG) === $author->maTG)>{{ $author->tenTG }}</option>
                    @endforeach
                </select>
                @error('maTG') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label">Nhà xuất bản *</label>
                <select name="maNXB" class="form-select @error('maNXB') is-invalid @enderror" required>
                    <option value="">-- Chọn NXB --</option>
                    @foreach ($publishers as $publisher)
                        <option value="{{ $publisher->maNXB }}" @selected(old('maNXB', $book->maNXB) === $publisher->maNXB)>{{ $publisher->tenNXB }}</option>
                    @endforeach
                </select>
                @error('maNXB') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label">Thể loại *</label>
                <select name="maTL" class="form-select @error('maTL') is-invalid @enderror" required>
                    <option value="">-- Chọn thể loại --</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->maTL }}" @selected(old('maTL', $book->maTL) === $category->maTL)>{{ $category->tenTL }}</option>
                    @endforeach
                </select>
                @error('maTL') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Ngôn ngữ *</label>
                <select name="maNN" class="form-select @error('maNN') is-invalid @enderror" required>
                    <option value="">-- Chọn ngôn ngữ --</option>
                    @foreach ($languages as $language)
                        <option value="{{ $language->maNN }}" @selected(old('maNN', $book->maNN) === $language->maNN)>{{ $language->tenNN }}</option>
                    @endforeach
                </select>
                @error('maNN') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Kệ sách *</label>
                <select name="maKS" class="form-select @error('maKS') is-invalid @enderror" required>
                    <option value="">-- Chọn kệ sách --</option>
                    @foreach ($shelves as $shelf)
                        <option value="{{ $shelf->maKS }}" @selected(old('maKS', $book->maKS) === $shelf->maKS)>{{ $shelf->tenKS }}</option>
                    @endforeach
                </select>
                @error('maKS') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-12">
                <label class="form-label">Mô tả</label>
                <textarea name="moTa" rows="3" class="form-control @error('moTa') is-invalid @enderror">{{ old('moTa', $book->moTa) }}</textarea>
                @error('moTa') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-orange">{{ $isEdit ? 'Lưu thay đổi' : 'Thêm mới' }}</button>
            <a href="{{ route('sach.index') }}" class="btn btn-outline-secondary">Quay lại</a>
        </div>
    </form>
</div>
@endsection
