@extends('layouts.app')

@section('title', 'Sửa Phiếu mượn')
@section('page_title', 'Sửa Phiếu mượn')
@section('page_subtitle', 'Cập nhật thông tin ngày mượn và hạn trả')

@section('content')
<div class="content-card p-4">
    <form method="POST" action="{{ route('muontra.update', $borrow) }}">
        @csrf
        @method('PUT')

        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Mã phiếu</label>
                <input type="text" class="form-control" value="{{ $borrow->maMT }}" readonly>
            </div>

            <div class="col-md-8">
                <label class="form-label">Độc giả</label>
                <input type="text" class="form-control" value="{{ optional($borrow->reader)->tenDG }} ({{ $borrow->maDG }})" readonly>
            </div>

            <div class="col-md-6">
                <label class="form-label">Ngày mượn *</label>
                <input type="date" name="ngayMuon" value="{{ old('ngayMuon', optional($borrow->ngayMuon)->format('Y-m-d')) }}" class="form-control @error('ngayMuon') is-invalid @enderror" required>
                @error('ngayMuon') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Hạn trả *</label>
                <input type="date" name="hanTra" value="{{ old('hanTra', optional($borrow->hanTra)->format('Y-m-d')) }}" class="form-control @error('hanTra') is-invalid @enderror" required>
                @error('hanTra') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-orange">Lưu thay đổi</button>
            <a href="{{ route('muontra.index') }}" class="btn btn-outline-secondary">Quay lại</a>
        </div>
    </form>
</div>
@endsection
