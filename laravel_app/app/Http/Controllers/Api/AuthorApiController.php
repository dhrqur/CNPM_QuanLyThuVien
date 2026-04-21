<?php

namespace App\Http\Controllers\Api;

use App\Models\Author;
use App\Services\IdGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthorApiController extends BaseApiController
{
    public function __construct(IdGeneratorService $idGenerator)
    {
        $this->idGenerator = $idGenerator;
    }

    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));

        $authors = Author::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where('maTG', 'like', "%{$search}%")
                    ->orWhere('tenTG', 'like', "%{$search}%")
                    ->orWhere('quocTich', 'like', "%{$search}%");
            })
            ->orderBy('maTG')
            ->paginate((int) $request->query('per_page', 10));

        return $this->paginated($authors);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tenTG' => ['required', 'string', 'max:100'],
            'namSinh' => ['nullable', 'date'],
            'gioiTinh' => ['nullable', 'in:Nam,Nữ'],
            'quocTich' => ['required', 'string', 'max:50'],
            'moTa' => ['nullable', 'string', 'max:255'],
        ]);

        $data['maTG'] = $this->idGenerator->make('tacgia', 'maTG', 'TG');
        $author = Author::create($data);

        return $this->success($author, 'Thêm tác giả thành công.', 201);
    }

    public function show(Author $author): JsonResponse
    {
        return $this->success($author);
    }

    public function update(Request $request, Author $author): JsonResponse
    {
        $data = $request->validate([
            'tenTG' => ['required', 'string', 'max:100'],
            'namSinh' => ['nullable', 'date'],
            'gioiTinh' => ['nullable', 'in:Nam,Nữ'],
            'quocTich' => ['required', 'string', 'max:50'],
            'moTa' => ['nullable', 'string', 'max:255'],
        ]);

        $author->update($data);

        return $this->success($author->fresh(), 'Cập nhật tác giả thành công.');
    }

    public function destroy(Author $author): JsonResponse
    {
        if ($author->books()->exists()) {
            return $this->error('Không thể xóa tác giả đang có sách liên kết.', 409);
        }

        $author->delete();

        return $this->success(null, 'Xóa tác giả thành công.');
    }
}
