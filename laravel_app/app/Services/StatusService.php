<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Borrow;
use Illuminate\Support\Facades\DB;

class StatusService
{
    public function syncBookStatusById(string $maSach): void
    {
        $book = Book::find($maSach);

        if (!$book) {
            return;
        }

        $book->trangThai = $book->soLuong > 0 ? 'Còn' : 'Hết';
        $book->save();
    }

    public function syncAllCardStatuses(): void
    {
        DB::table('thethuvien')->update([
            'trangThai' => DB::raw("CASE WHEN ngayHetHan >= CURDATE() THEN 'Còn hạn' ELSE 'Hết hạn' END"),
        ]);
    }

    public function syncAllBorrowStatuses(): void
    {
        DB::table('muontra')->update([
            'trangThai' => DB::raw("CASE WHEN ngayTra IS NOT NULL THEN 'Đã trả' WHEN ngayTra IS NULL AND hanTra < CURDATE() THEN 'Quá hạn' ELSE 'Đang mượn' END"),
        ]);
    }

    public function syncBorrowStatus(Borrow $borrow): void
    {
        $newStatus = 'Đang mượn';

        if ($borrow->ngayTra !== null) {
            $newStatus = 'Đã trả';
        } elseif ($borrow->hanTra !== null && $borrow->hanTra->toDateString() < now()->toDateString()) {
            $newStatus = 'Quá hạn';
        }

        if ($borrow->trangThai !== $newStatus) {
            $borrow->trangThai = $newStatus;
            $borrow->save();
        }
    }
}
