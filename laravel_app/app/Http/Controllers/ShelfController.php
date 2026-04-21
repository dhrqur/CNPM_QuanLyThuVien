<?php

namespace App\Http\Controllers;

use App\Models\Shelf;
use App\Services\IdGeneratorService;
use Illuminate\Http\Request;

class ShelfController extends Controller
{
    public function __construct(IdGeneratorService $idGenerator)
    {
        $this->idGenerator = $idGenerator;
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $shelves = Shelf::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where('maKS', 'like', "%{$search}%")
                    ->orWhere('tenKS', 'like', "%{$search}%");
            })
            ->orderBy('maKS')
            ->paginate(5)
            ->withQueryString();

        return view('shelves.index', compact('shelves', 'search'));
    }

    public function create()
    {
        return view('shelves.form', [
            'shelf' => new Shelf(),
            'isEdit' => false,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tenKS' => ['required', 'string', 'max:50'],
        ], [
            'tenKS.required' => 'Tên kệ sách không được để trống.',
        ]);

        $data['maKS'] = $this->idGenerator->make('kesach', 'maKS', 'KS');
        Shelf::create($data);

        return redirect()->route('kesach.index')->with('success', 'Thêm kệ sách thành công.');
    }

    public function edit(Shelf $shelf)
    {
        return view('shelves.form', [
            'shelf' => $shelf,
            'isEdit' => true,
        ]);
    }

    public function show(Shelf $shelf)
    {
        $shelf->loadCount('books');

        return view('shelves.show', compact('shelf'));
    }

    public function update(Request $request, Shelf $shelf)
    {
        $data = $request->validate([
            'tenKS' => ['required', 'string', 'max:50'],
        ]);

        $shelf->update($data);

        return redirect()->route('kesach.index')->with('success', 'Cập nhật kệ sách thành công.');
    }

    public function destroy(Shelf $shelf)
    {
        if ($shelf->books()->exists()) {
            return back()->with('danger', 'Không thể xóa kệ sách đang có sách liên kết.');
        }

        $shelf->delete();

        return redirect()->route('kesach.index')->with('success', 'Xóa kệ sách thành công.');
    }
}
