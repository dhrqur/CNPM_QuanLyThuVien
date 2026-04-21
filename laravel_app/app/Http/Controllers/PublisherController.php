<?php

namespace App\Http\Controllers;

use App\Models\Publisher;
use App\Services\IdGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PublisherController extends Controller
{
    public function __construct(IdGeneratorService $idGenerator)
    {
        $this->idGenerator = $idGenerator;
    }

    public function index(Request $request)
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
            ->paginate(5)
            ->withQueryString();

        return view('publishers.index', compact('publishers', 'search'));
    }

    public function create()
    {
        return view('publishers.form', [
            'publisher' => new Publisher(),
            'isEdit' => false,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['maNXB'] = $this->idGenerator->make('nhaxuatban', 'maNXB', 'NXB');

        Publisher::create($data);

        return redirect()->route('nhaxuatban.index')->with('success', 'Thêm nhà xuất bản thành công.');
    }

    public function edit(Publisher $publisher)
    {
        return view('publishers.form', [
            'publisher' => $publisher,
            'isEdit' => true,
        ]);
    }

    public function show(Publisher $publisher)
    {
        $publisher->loadCount('books');

        return view('publishers.show', compact('publisher'));
    }

    public function update(Request $request, Publisher $publisher)
    {
        $publisher->update($this->validateData($request, $publisher));

        return redirect()->route('nhaxuatban.index')->with('success', 'Cập nhật nhà xuất bản thành công.');
    }

    public function destroy(Publisher $publisher)
    {
        if ($publisher->books()->exists()) {
            return back()->with('danger', 'Không thể xóa nhà xuất bản đang có sách liên kết.');
        }

        $publisher->delete();

        return redirect()->route('nhaxuatban.index')->with('success', 'Xóa nhà xuất bản thành công.');
    }

    private function validateData(Request $request, ?Publisher $publisher = null): array
    {
        $id = optional($publisher)->maNXB;

        return $request->validate([
            'tenNXB' => ['required', 'string', 'max:100'],
            'diaChi' => ['required', 'string', 'max:255'],
            'soDT' => [
                'required',
                'regex:/^\d{10,12}$/',
                Rule::unique('nhaxuatban', 'soDT')->ignore($id, 'maNXB'),
            ],
            'email' => [
                'required',
                'email',
                'max:100',
                Rule::unique('nhaxuatban', 'email')->ignore($id, 'maNXB'),
            ],
        ], [
            'tenNXB.required' => 'Tên nhà xuất bản không được để trống.',
            'diaChi.required' => 'Địa chỉ không được để trống.',
            'soDT.required' => 'Số điện thoại không được để trống.',
            'soDT.regex' => 'Số điện thoại phải từ 10 đến 12 chữ số.',
            'email.required' => 'Email không được để trống.',
            'email.email' => 'Email không đúng định dạng.',
        ]);
    }
}
