<?php

namespace App\Http\Controllers\Api;

use App\Models\Publisher;
use App\Services\IdGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PublisherApiController extends BaseApiController
{
    public function __construct(IdGeneratorService $idGenerator)
    {
        $this->idGenerator = $idGenerator;
    }

    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));

        $publishers = Publisher::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where('maNXB', 'like', "%{$search}%")
                    ->orWhere('tenNXB', 'like', "%{$search}%")
                    ->orWhere('soDT', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->orderBy('maNXB')
            ->paginate((int) $request->query('per_page', 10));

        return $this->paginated($publishers);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tenNXB' => ['required', 'string', 'max:100'],
            'diaChi' => ['required', 'string', 'max:255'],
            'soDT' => ['required', 'regex:/^\d{10,12}$/', Rule::unique('nhaxuatban', 'soDT')],
            'email' => ['required', 'email', 'max:100', Rule::unique('nhaxuatban', 'email')],
        ]);

        $data['maNXB'] = $this->idGenerator->make('nhaxuatban', 'maNXB', 'NXB');
        $publisher = Publisher::create($data);

        return $this->success($publisher, 'Thêm nhà xuất bản thành công.', 201);
    }

    public function show(Publisher $publisher): JsonResponse
    {
        return $this->success($publisher);
    }

    public function update(Request $request, Publisher $publisher): JsonResponse
    {
        $data = $request->validate([
            'tenNXB' => ['required', 'string', 'max:100'],
            'diaChi' => ['required', 'string', 'max:255'],
            'soDT' => ['required', 'regex:/^\d{10,12}$/', Rule::unique('nhaxuatban', 'soDT')->ignore($publisher->maNXB, 'maNXB')],
            'email' => ['required', 'email', 'max:100', Rule::unique('nhaxuatban', 'email')->ignore($publisher->maNXB, 'maNXB')],
        ]);

        $publisher->update($data);

        return $this->success($publisher->fresh(), 'Cập nhật nhà xuất bản thành công.');
    }

    public function destroy(Publisher $publisher): JsonResponse
    {
        if ($publisher->books()->exists()) {
            return $this->error('Không thể xóa nhà xuất bản đang có sách liên kết.', 409);
        }

        $publisher->delete();

        return $this->success(null, 'Xóa nhà xuất bản thành công.');
    }
}
