<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Borrow;
use App\Models\Reader;
use App\Services\BorrowService;
use App\Services\StatusService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BorrowController extends Controller
{
    public function __construct(
        private readonly BorrowService $borrowService,
        private readonly StatusService $statusService,
    ) {
    }

    public function index(Request $request)
    {
        $this->statusService->syncAllBorrowStatuses();

        $search = trim((string) $request->query('search', ''));

        $borrows = Borrow::query()
            ->with(['reader', 'staff', 'details.book'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where('maMT', 'like', "%{$search}%")
                    ->orWhereHas('reader', function ($subQuery) use ($search) {
                        $subQuery->where('tenDG', 'like', "%{$search}%");
                    });
            })
            ->orderByDesc('maMT')
            ->paginate(5)
            ->withQueryString();

        return view('borrows.index', compact('borrows', 'search'));
    }

    public function create()
    {
        return view('borrows.form', [
            'readers' => Reader::orderBy('tenDG')->get(),
            'books' => Book::where('soLuong', '>', 0)->orderBy('tenSach')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'maDG' => ['required', Rule::exists('docgia', 'maDG')],
            'ngayMuon' => ['required', 'date'],
            'hanTra' => ['required', 'date', 'after_or_equal:ngayMuon'],
            'books' => ['required', 'array', 'min:1'],
            'books.*.maSach' => ['required', Rule::exists('sach', 'maSach')],
            'books.*.soLuong' => ['required', 'integer', 'min:1'],
        ], [
            'maDG.required' => 'Vui lòng chọn độc giả.',
            'ngayMuon.required' => 'Ngày mượn không được để trống.',
            'hanTra.required' => 'Hạn trả không được để trống.',
            'hanTra.after_or_equal' => 'Hạn trả phải lớn hơn hoặc bằng ngày mượn.',
            'books.required' => 'Vui lòng chọn ít nhất một sách.',
        ]);

        $this->borrowService->createBorrow($validated, $request->user());

        return redirect()->route('muontra.index')->with('success', 'Tạo phiếu mượn thành công.');
    }

    public function returnBook(Borrow $borrow)
    {
        $this->borrowService->returnBorrow($borrow);

        return back()->with('success', 'Trả sách thành công.');
    }

    public function extend(Borrow $borrow)
    {
        $this->borrowService->extendBorrow($borrow);

        return back()->with('success', 'Gia hạn phiếu mượn thêm 14 ngày.');
    }

    public function destroy(Borrow $borrow)
    {
        $this->borrowService->deleteBorrow($borrow);

        return redirect()->route('muontra.index')->with('success', 'Xóa phiếu mượn thành công.');
    }
}
