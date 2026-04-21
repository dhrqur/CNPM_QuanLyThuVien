<?php

namespace App\Http\Controllers\Api;

use App\Models\Shelf;
use App\Services\IdGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShelfApiController extends BaseApiController
{
    public function __construct(IdGeneratorService $idGenerator)
    {
        $this->idGenerator = $idGenerator;
    }

    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));

        $shelves = Shelf::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where('maKS', 'like', "%{$search}%")
                    ->orWhere('tenKS', 'like', "%{$search}%");
            })
            ->orderBy('maKS')
            ->paginate((int) $request->query('per_page', 10));

        return $this->paginated($shelves);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tenKS' => ['required', 'string', 'max:50'],
        ]);

        $data['maKS'] = $this->idGenerator->make('kesach', 'maKS', 'KS');
        $shelf = Shelf::create($data);

        return $this->success($shelf, 'Thêm kệ sách thành công.', 201);
    }

    public function show(Shelf $shelf): JsonResponse
    {
        return $this->success($shelf);
    }

    public function update(Request $request, Shelf $shelf): JsonResponse
    {
        $data = $request->validate([
            'tenKS' => ['required', 'string', 'max:50'],
        ]);

        $shelf->update($data);

        return $this->success($shelf->fresh(), 'Cập nhật kệ sách thành công.');
    }

    public function destroy(Shelf $shelf): JsonResponse
    {
        if ($shelf->books()->exists()) {
            return $this->error('Không thể xóa kệ sách đang có sách liên kết.', 409);
        }

        $shelf->delete();

        return $this->success(null, 'Xóa kệ sách thành công.');
    }
}
