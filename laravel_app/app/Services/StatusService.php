<?php

namespace App\Services;

use App\Models\Book;
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
}
