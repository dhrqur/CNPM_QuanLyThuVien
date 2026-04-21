<?php

namespace App\Http\Controllers\Api;

use App\Models\Language;
use App\Services\IdGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LanguageApiController extends BaseApiController
{
    public function __construct(IdGeneratorService $idGenerator)
    {
        $this->idGenerator = $idGenerator;
    }

    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));

        $languages = Language::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where('maNN', 'like', "%{$search}%")
                    ->orWhere('tenNN', 'like', "%{$search}%");
            })
            ->orderBy('maNN')
            ->paginate((int) $request->query('per_page', 10));

        return $this->paginated($languages);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tenNN' => ['required', 'string', 'max:50'],
        ]);

        $data['maNN'] = $this->idGenerator->make('ngonngu', 'maNN', 'NN');
        $language = Language::create($data);

        return $this->success($language, 'Thêm ngôn ngữ thành công.', 201);
    }

    public function show(Language $language): JsonResponse
    {
        return $this->success($language);
    }

    public function update(Request $request, Language $language): JsonResponse
    {
        $data = $request->validate([
            'tenNN' => ['required', 'string', 'max:50'],
        ]);

        $language->update($data);

        return $this->success($language->fresh(), 'Cập nhật ngôn ngữ thành công.');
    }

    public function destroy(Language $language): JsonResponse
    {
        if ($language->books()->exists()) {
            return $this->error('Không thể xóa ngôn ngữ đang có sách liên kết.', 409);
        }

        $language->delete();

        return $this->success(null, 'Xóa ngôn ngữ thành công.');
    }
}
