<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use App\Services\IdGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryApiController extends BaseApiController
{
    public function __construct(IdGeneratorService $idGenerator)
    {
        $this->idGenerator = $idGenerator;
    }

    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));

        $categories = Category::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where('maTL', 'like', "%{$search}%")
                    ->orWhere('tenTL', 'like', "%{$search}%");
            })
            ->orderBy('maTL')
            ->paginate((int) $request->query('per_page', 10));

        return $this->paginated($categories);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tenTL' => ['required', 'string', 'max:100'],
        ]);

        $data['maTL'] = $this->idGenerator->make('theloai', 'maTL', 'TL');
        $category = Category::create($data);

        return $this->success($category, 'Thêm thể loại thành công.', 201);
    }

    public function show(Category $category): JsonResponse
    {
        return $this->success($category);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $data = $request->validate([
            'tenTL' => ['required', 'string', 'max:100'],
        ]);

        $category->update($data);

        return $this->success($category->fresh(), 'Cập nhật thể loại thành công.');
    }

    public function destroy(Category $category): JsonResponse
    {
        if ($category->books()->exists()) {
            return $this->error('Không thể xóa thể loại đang có sách liên kết.', 409);
        }

        $category->delete();

        return $this->success(null, 'Xóa thể loại thành công.');
    }
}
