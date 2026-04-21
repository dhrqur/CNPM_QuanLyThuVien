<?php

namespace App\Http\Controllers\Api;

use App\Models\Reader;
use App\Services\IdGeneratorService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReaderApiController extends BaseApiController
{
    public function __construct(IdGeneratorService $idGenerator)
    {
        $this->idGenerator = $idGenerator;
    }

    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));

        $readers = Reader::query()
            ->with('card')
            ->when($search !== '', function ($query) use ($search) {
                $query->where('maDG', 'like', "%{$search}%")
                    ->orWhere('tenDG', 'like', "%{$search}%")
                    ->orWhere('soDT', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->orderBy('maDG')
            ->paginate((int) $request->query('per_page', 10));

        return $this->paginated($readers);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validateData($request);
        $data['maDG'] = $this->idGenerator->make('docgia', 'maDG', 'DG');

        $reader = Reader::create($data);

        return $this->success($reader, 'Thêm độc giả thành công.', 201);
    }

    public function show(Reader $reader): JsonResponse
    {
        return $this->success($reader->load('card'));
    }

    public function update(Request $request, Reader $reader): JsonResponse
    {
        $reader->update($this->validateData($request, $reader));

        return $this->success($reader->fresh()->load('card'), 'Cập nhật độc giả thành công.');
    }

    public function destroy(Reader $reader): JsonResponse
    {
        if ($reader->hasActiveBorrow()) {
            return $this->error('Không thể xóa độc giả đang có phiếu mượn chưa trả.', 409);
        }

        if ($reader->card && Carbon::parse($reader->card->ngayHetHan)->gte(Carbon::today())) {
            return $this->error('Không thể xóa độc giả đang có thẻ thư viện còn hạn.', 409);
        }

        $reader->delete();

        return $this->success(null, 'Xóa độc giả thành công.');
    }

    private function validateData(Request $request, ?Reader $reader = null): array
    {
        return $request->validate([
            'tenDG' => ['required', 'string', 'max:100'],
            'ngaySinh' => ['required', 'date'],
            'gioiTinh' => ['required', 'in:Nam,Nữ'],
            'diaChi' => ['required', 'string', 'max:255'],
            'soDT' => [
                'required',
                'regex:/^\d{10,12}$/',
                Rule::unique('docgia', 'soDT')->ignore(optional($reader)->maDG, 'maDG'),
            ],
            'email' => [
                'required',
                'email',
                'max:100',
                Rule::unique('docgia', 'email')->ignore(optional($reader)->maDG, 'maDG'),
            ],
        ]);
    }
}
