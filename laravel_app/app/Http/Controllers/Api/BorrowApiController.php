<?php

namespace App\Http\Controllers\Api;

use App\Models\Borrow;
use App\Services\BorrowService;
use App\Services\StatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BorrowApiController extends BaseApiController
{
    public function __construct(
        BorrowService $borrowService,
        StatusService $statusService
    )
    {
        $this->borrowService = $borrowService;
        $this->statusService = $statusService;
    }

    public function index(Request $request): JsonResponse
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
            ->paginate((int) $request->query('per_page', 10));

        return $this->paginated($borrows);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'maDG' => ['required', Rule::exists('docgia', 'maDG')],
            'ngayMuon' => ['required', 'date'],
            'hanTra' => ['required', 'date', 'after_or_equal:ngayMuon'],
            'books' => ['required', 'array', 'min:1'],
            'books.*.maSach' => ['required', Rule::exists('sach', 'maSach')],
            'books.*.soLuong' => ['required', 'integer', 'min:1'],
        ]);

        $borrow = $this->borrowService->createBorrow($validated, $request->user());

        return $this->success(
            $borrow->load(['reader', 'staff', 'details.book']),
            'Tạo phiếu mượn thành công.',
            201
        );
    }

    public function show(Borrow $borrow): JsonResponse
    {
        return $this->success($borrow->load(['reader', 'staff', 'details.book']));
    }

    public function returnBook(Borrow $borrow): JsonResponse
    {
        $this->borrowService->returnBorrow($borrow);

        return $this->success($borrow->fresh()->load(['reader', 'staff', 'details.book']), 'Trả sách thành công.');
    }

    public function extend(Borrow $borrow): JsonResponse
    {
        $this->borrowService->extendBorrow($borrow);

        return $this->success($borrow->fresh()->load(['reader', 'staff', 'details.book']), 'Gia hạn phiếu mượn thêm 14 ngày.');
    }

    public function destroy(Borrow $borrow): JsonResponse
    {
        $this->borrowService->deleteBorrow($borrow);

        return $this->success(null, 'Xóa phiếu mượn thành công.');
    }
}
