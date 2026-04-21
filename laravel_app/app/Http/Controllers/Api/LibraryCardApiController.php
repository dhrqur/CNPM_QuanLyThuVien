<?php

namespace App\Http\Controllers\Api;

use App\Models\LibraryCard;
use App\Services\IdGeneratorService;
use App\Services\StatusService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LibraryCardApiController extends BaseApiController
{
    public function __construct(
        IdGeneratorService $idGenerator,
        StatusService $statusService
    )
    {
        $this->idGenerator = $idGenerator;
        $this->statusService = $statusService;
    }

    public function index(Request $request): JsonResponse
    {
        $this->statusService->syncAllCardStatuses();

        $search = trim((string) $request->query('search', ''));

        $cards = LibraryCard::query()
            ->with('reader')
            ->when($search !== '', function ($query) use ($search) {
                $query->where('maTTV', 'like', "%{$search}%")
                    ->orWhere('maDG', 'like', "%{$search}%")
                    ->orWhereHas('reader', function ($subQuery) use ($search) {
                        $subQuery->where('tenDG', 'like', "%{$search}%");
                    });
            })
            ->orderBy('maTTV')
            ->paginate((int) $request->query('per_page', 10));

        return $this->paginated($cards);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validateData($request);
        $data['maTTV'] = $this->idGenerator->make('thethuvien', 'maTTV', 'TTV');
        $data['trangThai'] = Carbon::parse($data['ngayHetHan'])->gte(Carbon::today()) ? 'Còn hạn' : 'Hết hạn';

        $card = LibraryCard::create($data);

        return $this->success($card->load('reader'), 'Thêm thẻ thư viện thành công.', 201);
    }

    public function show(LibraryCard $libraryCard): JsonResponse
    {
        return $this->success($libraryCard->load('reader'));
    }

    public function update(Request $request, LibraryCard $libraryCard): JsonResponse
    {
        $data = $this->validateData($request, $libraryCard);
        $data['trangThai'] = Carbon::parse($data['ngayHetHan'])->gte(Carbon::today()) ? 'Còn hạn' : 'Hết hạn';

        $libraryCard->update($data);

        return $this->success($libraryCard->fresh()->load('reader'), 'Cập nhật thẻ thư viện thành công.');
    }

    public function destroy(LibraryCard $libraryCard): JsonResponse
    {
        $isActive = Carbon::parse($libraryCard->ngayHetHan)->gte(Carbon::today());
        if ($libraryCard->trangThai === 'Còn hạn' || $isActive) {
            return $this->error('Không thể xóa thẻ thư viện còn hạn.', 409);
        }

        $libraryCard->delete();

        return $this->success(null, 'Xóa thẻ thư viện thành công.');
    }

    private function validateData(Request $request, ?LibraryCard $libraryCard = null): array
    {
        return $request->validate([
            'maDG' => [
                'required',
                Rule::exists('docgia', 'maDG'),
                Rule::unique('thethuvien', 'maDG')->ignore(optional($libraryCard)->maTTV, 'maTTV'),
            ],
            'ngayCap' => ['required', 'date'],
            'ngayHetHan' => ['required', 'date', 'after_or_equal:ngayCap'],
        ]);
    }
}
