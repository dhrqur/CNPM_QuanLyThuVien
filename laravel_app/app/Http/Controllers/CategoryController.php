<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\IdGeneratorService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(IdGeneratorService $idGenerator)
    {
        $this->idGenerator = $idGenerator;
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $categories = Category::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where('maTL', 'like', "%{$search}%")
                    ->orWhere('tenTL', 'like', "%{$search}%");
            })
            ->orderBy('maTL')
            ->paginate(5)
            ->withQueryString();

        return view('categories.index', compact('categories', 'search'));
    }

    public function create()
    {
        return view('categories.form', [
            'category' => new Category(),
            'isEdit' => false,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tenTL' => ['required', 'string', 'max:100'],
        ], [
            'tenTL.required' => 'Tên thể loại không được để trống.',
        ]);

        $data['maTL'] = $this->idGenerator->make('theloai', 'maTL', 'TL');
        Category::create($data);

        return redirect()->route('theloai.index')->with('success', 'Thêm thể loại thành công.');
    }

    public function edit(Category $category)
    {
        return view('categories.form', [
            'category' => $category,
            'isEdit' => true,
        ]);
    }

    public function show(Category $category)
    {
        $category->loadCount('books');

        return view('categories.show', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'tenTL' => ['required', 'string', 'max:100'],
        ]);

        $category->update($data);

        return redirect()->route('theloai.index')->with('success', 'Cập nhật thể loại thành công.');
    }

    public function destroy(Category $category)
    {
        if ($category->books()->exists()) {
            return back()->with('danger', 'Không thể xóa thể loại đang có sách liên kết.');
        }

        $category->delete();

        return redirect()->route('theloai.index')->with('success', 'Xóa thể loại thành công.');
    }
}
