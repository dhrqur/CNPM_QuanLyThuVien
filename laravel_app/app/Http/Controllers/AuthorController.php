<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Services\IdGeneratorService;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    public function __construct(IdGeneratorService $idGenerator)
    {
        $this->idGenerator = $idGenerator;
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $authors = Author::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where('maTG', 'like', "%{$search}%")
                    ->orWhere('tenTG', 'like', "%{$search}%")
                    ->orWhere('quocTich', 'like', "%{$search}%");
            })
            ->orderBy('maTG')
            ->paginate(5)
            ->withQueryString();

        return view('authors.index', compact('authors', 'search'));
    }

    public function create()
    {
        return view('authors.form', [
            'author' => new Author(),
            'isEdit' => false,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['maTG'] = $this->idGenerator->make('tacgia', 'maTG', 'TG');

        Author::create($data);

        return redirect()->route('tacgia.index')->with('success', 'Thêm tác giả thành công.');
    }

    public function edit(Author $author)
    {
        return view('authors.form', [
            'author' => $author,
            'isEdit' => true,
        ]);
    }

    public function show(Author $author)
    {
        $author->loadCount('books');

        return view('authors.show', compact('author'));
    }

    public function update(Request $request, Author $author)
    {
        $author->update($this->validateData($request));

        return redirect()->route('tacgia.index')->with('success', 'Cập nhật tác giả thành công.');
    }

    public function destroy(Author $author)
    {
        if ($author->books()->exists()) {
            return back()->with('danger', 'Không thể xóa tác giả đang có sách liên kết.');
        }

        $author->delete();

        return redirect()->route('tacgia.index')->with('success', 'Xóa tác giả thành công.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'tenTG' => ['required', 'string', 'max:100'],
            'namSinh' => ['nullable', 'date'],
            'gioiTinh' => ['nullable', 'in:Nam,Nữ'],
            'quocTich' => ['required', 'string', 'max:50'],
            'moTa' => ['nullable', 'string', 'max:255'],
        ], [
            'tenTG.required' => 'Tên tác giả không được để trống.',
            'quocTich.required' => 'Quốc tịch không được để trống.',
        ]);
    }
}
