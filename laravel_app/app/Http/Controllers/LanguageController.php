<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Services\IdGeneratorService;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function __construct(IdGeneratorService $idGenerator)
    {
        $this->idGenerator = $idGenerator;
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $languages = Language::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where('maNN', 'like', "%{$search}%")
                    ->orWhere('tenNN', 'like', "%{$search}%");
            })
            ->orderBy('maNN')
            ->paginate(5)
            ->withQueryString();

        return view('languages.index', compact('languages', 'search'));
    }

    public function create()
    {
        return view('languages.form', [
            'language' => new Language(),
            'isEdit' => false,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tenNN' => ['required', 'string', 'max:50'],
        ], [
            'tenNN.required' => 'Tên ngôn ngữ không được để trống.',
        ]);

        $data['maNN'] = $this->idGenerator->make('ngonngu', 'maNN', 'NN');
        Language::create($data);

        return redirect()->route('ngonngu.index')->with('success', 'Thêm ngôn ngữ thành công.');
    }

    public function edit(Language $language)
    {
        return view('languages.form', [
            'language' => $language,
            'isEdit' => true,
        ]);
    }

    public function show(Language $language)
    {
        $language->loadCount('books');

        return view('languages.show', compact('language'));
    }

    public function update(Request $request, Language $language)
    {
        $data = $request->validate([
            'tenNN' => ['required', 'string', 'max:50'],
        ]);

        $language->update($data);

        return redirect()->route('ngonngu.index')->with('success', 'Cập nhật ngôn ngữ thành công.');
    }

    public function destroy(Language $language)
    {
        if ($language->books()->exists()) {
            return back()->with('danger', 'Không thể xóa ngôn ngữ đang có sách liên kết.');
        }

        $language->delete();

        return redirect()->route('ngonngu.index')->with('success', 'Xóa ngôn ngữ thành công.');
    }
}
