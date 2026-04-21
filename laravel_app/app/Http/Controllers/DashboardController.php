<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Reader;
use App\Services\StatusService;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct(StatusService $statusService)
    {
        $this->statusService = $statusService;
    }

    public function index()
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
            ->select('dg.tenDG', DB::raw('COALESCE(SUM(ct.soLuong),0) AS tongLuot'))
            ->groupBy('dg.maDG', 'dg.tenDG')
            ->orderByDesc('tongLuot')
            ->limit(5)
            ->get();

        $topBooks = DB::table('sach as s')
            ->leftJoin('chitietmuontra as ct', 'ct.maSach', '=', 's.maSach')
            ->select('s.tenSach', DB::raw('COALESCE(SUM(ct.soLuong),0) AS tongMuon'))
            ->groupBy('s.maSach', 's.tenSach')
            ->orderByDesc('tongMuon')
            ->limit(5)
            ->get();

        $overdues = DB::table('muontra as mt')
            ->join('docgia as dg', 'dg.maDG', '=', 'mt.maDG')
            ->select('mt.maMT', 'dg.tenDG', 'mt.hanTra', 'mt.trangThai')
            ->whereNull('mt.ngayTra')
            ->where('mt.hanTra', '<', now()->toDateString())
            ->orderBy('mt.hanTra')
            ->get();

        return view('dashboard.index', [
            'stats' => $stats,
            'topBorrowers' => $topBorrowers,
            'topBooks' => $topBooks,
            'overdues' => $overdues,
        ]);
    }
}
