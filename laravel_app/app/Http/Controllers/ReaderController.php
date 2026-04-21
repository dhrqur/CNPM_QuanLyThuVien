<?php

namespace App\Http\Controllers;

use App\Models\Reader;
use App\Services\IdGeneratorService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReaderController extends Controller
{
    public function __construct(IdGeneratorService $idGenerator)
    {
        $this->idGenerator = $idGenerator;
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $readers = Reader::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where('maDG', 'like', "%{$search}%")
                    ->orWhere('tenDG', 'like', "%{$search}%")
                    ->orWhere('soDT', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->orderBy('maDG')
            ->paginate(5)
            ->withQueryString();

        return view('readers.index', compact('readers', 'search'));
    }

    public function create()
    {
        return view('readers.form', [
            'reader' => new Reader(),
            'isEdit' => false,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['maDG'] = $this->idGenerator->make('docgia', 'maDG', 'DG');

        Reader::create($data);

        return redirect()->route('docgia.index')->with('success', 'Thêm độc giả thành công.');
    }

    public function edit(Reader $reader)
    {
        return view('readers.form', [
            'reader' => $reader,
            'isEdit' => true,
        ]);
    }

    public function show(Reader $reader)
    {
        $reader->load('card')->loadCount([
            'borrows',
            'borrows as active_borrows_count' => function ($query) {
                $query->whereIn('trangThai', ['Đang mượn', 'Quá hạn']);
            },
        ]);

        return view('readers.show', compact('reader'));
    }

    public function update(Request $request, Reader $reader)
    {
        $reader->update($this->validateData($request, $reader));

        return redirect()->route('docgia.index')->with('success', 'Cập nhật độc giả thành công.');
    }

    public function destroy(Reader $reader)
    {
        if ($reader->hasActiveBorrow()) {
            return back()->with('danger', 'Không thể xóa độc giả đang có phiếu mượn chưa trả.');
        }

        if ($reader->card && Carbon::parse($reader->card->ngayHetHan)->gte(Carbon::today())) {
            return back()->with('danger', 'Không thể xóa độc giả đang có thẻ thư viện còn hạn.');
        }

        $reader->delete();

        return redirect()->route('docgia.index')->with('success', 'Xóa độc giả thành công.');
    }

    private function validateData(Request $request, ?Reader $reader = null): array
    {
        $id = optional($reader)->maDG;

        return $request->validate([
            'tenDG' => ['required', 'string', 'max:100'],
            'ngaySinh' => ['required', 'date'],
            'gioiTinh' => ['required', 'in:Nam,Nữ'],
            'diaChi' => ['required', 'string', 'max:255'],
            'soDT' => [
                'required',
                'regex:/^\d{10,12}$/',
                Rule::unique('docgia', 'soDT')->ignore($id, 'maDG'),
            ],
            'email' => [
                'required',
                'email',
                'max:100',
                Rule::unique('docgia', 'email')->ignore($id, 'maDG'),
            ],
        ], [
            'tenDG.required' => 'Tên độc giả không được để trống.',
            'ngaySinh.required' => 'Ngày sinh không được để trống.',
            'gioiTinh.required' => 'Giới tính không được để trống.',
            'diaChi.required' => 'Địa chỉ không được để trống.',
            'soDT.required' => 'Số điện thoại không được để trống.',
            'soDT.regex' => 'Số điện thoại phải từ 10 đến 12 chữ số.',
            'email.required' => 'Email không được để trống.',
            'email.email' => 'Email không đúng định dạng.',
        ]);
    }
}
