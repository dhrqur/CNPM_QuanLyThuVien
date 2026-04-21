<?php

namespace App\Http\Controllers\Api;

use App\Models\Book;
use App\Models\Reader;
use App\Services\StatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardApiController extends BaseApiController
{
    public function __construct(StatusService $statusService)
    {
        $this->statusService = $statusService;
    }

    public function index(): JsonResponse
    {
        $this->statusService->syncAllCardStatuses();
        $this->statusService->syncAllBorrowStatuses();

        $stats = [
            'totalBooks' => Book::count(),
            'borrowingBooks' => DB::table('chitietmuontra as ct')
                ->join('muontra as mt', 'mt.maMT', '=', 'ct.maMT')
                ->where('mt.trangThai', 'Đang mượn')
                ->sum('ct.soLuong'),
            'totalReaders' => Reader::count(),
            'totalCopies' => Book::sum('soLuong'),
        ];

        $topBorrowers = DB::table('muontra as mt')
            ->join('docgia as dg', 'dg.maDG', '=', 'mt.maDG')
            ->leftJoin('chitietmuontra as ct', 'ct.maMT', '=', 'mt.maMT')
            ->select('dg.maDG', 'dg.tenDG', DB::raw('COALESCE(SUM(ct.soLuong),0) AS tongLuot'))
            ->groupBy('dg.maDG', 'dg.tenDG')
            ->orderByDesc('tongLuot')
            ->limit(5)
            ->get();

        $topBooks = DB::table('sach as s')
            ->leftJoin('chitietmuontra as ct', 'ct.maSach', '=', 's.maSach')
            ->select('s.maSach', 's.tenSach', DB::raw('COALESCE(SUM(ct.soLuong),0) AS tongMuon'))
            ->groupBy('s.maSach', 's.tenSach')
            ->orderByDesc('tongMuon')
            ->limit(5)
            ->get();

        $overdues = DB::table('muontra as mt')
            ->join('docgia as dg', 'dg.maDG', '=', 'mt.maDG')
            ->select('mt.maMT', 'dg.maDG', 'dg.tenDG', 'mt.ngayMuon', 'mt.hanTra', 'mt.trangThai')
            ->whereNull('mt.ngayTra')
            ->where('mt.hanTra', '<', now()->toDateString())
            ->orderBy('mt.hanTra')
            ->get();

        return $this->success([
            'stats' => $stats,
            'topBorrowers' => $topBorrowers,
            'topBooks' => $topBooks,
            'overdues' => $overdues,
        ]);
    }
}
