<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Borrow;
use App\Models\BorrowDetail;
use App\Models\Reader;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BorrowService
{
    public function __construct(IdGeneratorService $idGenerator)
    {
        $this->idGenerator = $idGenerator;
    }

    public function createBorrow(array $validated, User $staff): Borrow
    {
        $this->validateBeforeCreate($validated);

        return DB::transaction(function () use ($validated, $staff) {
            $borrow = Borrow::create([
                'maMT' => $this->idGenerator->make('muontra', 'maMT', 'MT'),
                'maDG' => $validated['maDG'],
                'maNV' => $staff->maNV,
                'ngayMuon' => $validated['ngayMuon'],
                'hanTra' => $validated['hanTra'],
                'ngayTra' => null,
                'trangThai' => 'Đang mượn',
            ]);

            $groupedBooks = $this->groupBooks($validated['books']);

            foreach ($groupedBooks as $maSach => $qty) {
                BorrowDetail::create([
                    'maMT' => $borrow->maMT,
                    'maSach' => $maSach,
                    'soLuong' => $qty,
                    'ghiChu' => '',
                ]);

                $book = Book::findOrFail($maSach);
                $book->soLuong -= $qty;
                $book->save();
            }

            return $borrow;
        });
    }

    public function returnBorrow(Borrow $borrow): void
    {
        if ($borrow->isReturned()) {
            return;
        }

        DB::transaction(function () use ($borrow) {
            $borrow->loadMissing('details');

            foreach ($borrow->details as $detail) {
                $book = Book::find($detail->maSach);
                if (!$book) {
                    continue;
                }

                $book->soLuong += (int) $detail->soLuong;
                $book->save();
            }

            $borrow->ngayTra = Carbon::today()->toDateString();
            $borrow->trangThai = 'Đã trả';
            $borrow->save();
        });
    }

    public function extendBorrow(Borrow $borrow): void
    {
        if ($borrow->isReturned()) {
            throw ValidationException::withMessages([
                'borrow' => 'Phiếu mượn đã trả nên không thể gia hạn.',
            ]);
        }

        $borrow->hanTra = Carbon::parse($borrow->hanTra)->addDays(14)->toDateString();
        $borrow->trangThai = 'Đang mượn';
        $borrow->save();
    }

    public function deleteBorrow(Borrow $borrow): void
    {
        DB::transaction(function () use ($borrow) {
            if (!$borrow->isReturned()) {
                $this->returnBorrow($borrow);
                $borrow->refresh();
            }

            BorrowDetail::where('maMT', $borrow->maMT)->delete();
            $borrow->delete();
        });
    }

    private function validateBeforeCreate(array $validated): void
    {
        $ngayMuon = Carbon::parse($validated['ngayMuon']);
        $hanTra = Carbon::parse($validated['hanTra']);

        if ($hanTra->lt($ngayMuon)) {
            throw ValidationException::withMessages([
                'hanTra' => 'Hạn trả phải lớn hơn hoặc bằng ngày mượn.',
            ]);
        }

        $reader = Reader::with('card')->find($validated['maDG']);

        if (!$reader) {
            throw ValidationException::withMessages([
                'maDG' => 'Độc giả không tồn tại.',
            ]);
        }

        if (!$reader->card || Carbon::parse($reader->card->ngayHetHan)->lt(Carbon::today())) {
            throw ValidationException::withMessages([
                'maDG' => 'Độc giả chưa có thẻ thư viện còn hạn.',
            ]);
        }

        if ($reader->hasActiveBorrow()) {
            throw ValidationException::withMessages([
                'maDG' => 'Độc giả đang có phiếu mượn chưa trả.',
            ]);
        }

        $groupedBooks = $this->groupBooks($validated['books']);

        foreach ($groupedBooks as $maSach => $qty) {
            $book = Book::find($maSach);

            if (!$book) {
                throw ValidationException::withMessages([
                    'books' => 'Sách không tồn tại: '.$maSach,
                ]);
            }

            if ((int) $book->soLuong < $qty) {
                throw ValidationException::withMessages([
                    'books' => 'Số lượng sách mượn vượt quá tồn kho cho mã '.$maSach,
                ]);
            }
        }
    }

    private function groupBooks(array $books): array
    {
        $counter = [];

        foreach ($books as $row) {
            if (empty($row['maSach'])) {
                continue;
            }

            $qty = isset($row['soLuong']) ? (int) $row['soLuong'] : 0;
            if ($qty <= 0) {
                continue;
            }

            $counter[$row['maSach']] = ($counter[$row['maSach']] ?? 0) + $qty;
        }

        if ($counter === []) {
            throw ValidationException::withMessages([
                'books' => 'Vui lòng chọn ít nhất một sách với số lượng hợp lệ.',
            ]);
        }

        return $counter;
    }
}
