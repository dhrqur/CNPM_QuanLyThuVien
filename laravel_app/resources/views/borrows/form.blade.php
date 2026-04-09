@extends('layouts.app')

@section('title', 'Tạo Phiếu mượn')
@section('page_title', 'Tạo Phiếu mượn')
@section('page_subtitle', 'Lập phiếu mượn sách cho độc giả')

@section('content')
<div class="content-card p-4">
    <form method="POST" action="{{ route('muontra.store') }}">
        @csrf

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label">Độc giả *</label>
                <select name="maDG" class="form-select @error('maDG') is-invalid @enderror" required>
                    <option value="">-- Chọn độc giả --</option>
                    @foreach ($readers as $reader)
                        <option value="{{ $reader->maDG }}" @selected(old('maDG') === $reader->maDG)>
                            {{ $reader->maDG }} - {{ $reader->tenDG }}
                        </option>
                    @endforeach
                </select>
                @error('maDG') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label">Ngày mượn *</label>
                <input type="date" name="ngayMuon" value="{{ old('ngayMuon', date('Y-m-d')) }}" class="form-control @error('ngayMuon') is-invalid @enderror" required>
                @error('ngayMuon') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label">Hạn trả *</label>
                <input type="date" name="hanTra" value="{{ old('hanTra', date('Y-m-d', strtotime('+14 days'))) }}" class="form-control @error('hanTra') is-invalid @enderror" required>
                @error('hanTra') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="mb-0">Danh sách sách mượn</h5>
            <button type="button" class="btn btn-outline-orange" id="add-book-row">
                <i class="bi bi-plus-lg"></i> Thêm dòng sách
            </button>
        </div>

        <div id="borrow-book-list">
            <div class="borrow-book-row row g-2 align-items-end" data-index="0">
                <div class="col-md-7">
                    <label class="form-label">Sách *</label>
                    <select name="books[0][maSach]" class="form-select" required>
                        <option value="">-- Chọn sách --</option>
                        @foreach ($books as $book)
                            <option value="{{ $book->maSach }}">{{ $book->maSach }} - {{ $book->tenSach }} (Tồn: {{ $book->soLuong }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Số lượng *</label>
                    <input type="number" name="books[0][soLuong]" min="1" value="1" class="form-control" required>
                </div>

                <div class="col-md-2">
                    <button type="button" class="btn btn-danger w-100 remove-book-row">Xóa</button>
                </div>
            </div>
        </div>

        @error('books') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-orange">Tạo phiếu</button>
            <a href="{{ route('muontra.index') }}" class="btn btn-outline-secondary">Quay lại</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    (() => {
        const addBtn = document.getElementById('add-book-row');
        const list = document.getElementById('borrow-book-list');

        const options = `@foreach ($books as $book)<option value="{{ $book->maSach }}">{{ $book->maSach }} - {{ $book->tenSach }} (Tồn: {{ $book->soLuong }})</option>@endforeach`;

        addBtn.addEventListener('click', () => {
            const nextIndex = list.querySelectorAll('.borrow-book-row').length;

            const row = document.createElement('div');
            row.className = 'borrow-book-row row g-2 align-items-end';
            row.innerHTML = `
                <div class="col-md-7">
                    <label class="form-label">Sách *</label>
                    <select name="books[${nextIndex}][maSach]" class="form-select" required>
                        <option value="">-- Chọn sách --</option>
                        ${options}
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Số lượng *</label>
                    <input type="number" name="books[${nextIndex}][soLuong]" min="1" value="1" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger w-100 remove-book-row">Xóa</button>
                </div>
            `;

            list.appendChild(row);
        });

        list.addEventListener('click', (event) => {
            if (!event.target.classList.contains('remove-book-row')) {
                return;
            }

            const rows = list.querySelectorAll('.borrow-book-row');
            if (rows.length <= 1) {
                return;
            }

            event.target.closest('.borrow-book-row').remove();
        });
    })();
</script>
@endpush
