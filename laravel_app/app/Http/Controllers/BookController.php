<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Language;
use App\Models\Publisher;
use App\Models\Shelf;
use App\Services\IdGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class BookController extends Controller
{
    public function __construct(IdGeneratorService $idGenerator)
    {
        $this->idGenerator = $idGenerator;
    }

    public function index(Request $request)
    {
        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'maSach' => trim((string) $request->query('maSach', '')),
            'tenSach' => trim((string) $request->query('tenSach', '')),
            'tacGia' => trim((string) $request->query('tacGia', '')),
            'trangThai' => trim((string) $request->query('trangThai', '')),
        ];

        $hasFilter = collect($filters)->contains(function ($value) {
            return $value !== '';
        });

        $validator = Validator::make($filters, [
            'search' => ['nullable', 'string', 'max:200'],
            'maSach' => ['nullable', 'string', 'max:20'],
            'tenSach' => ['nullable', 'string', 'max:200'],
            'tacGia' => ['nullable', 'string', 'max:100'],
            'trangThai' => ['nullable', Rule::in(['Còn', 'Hết'])],
        ], [
            'search.max' => 'Dữ liệu tìm kiếm không hợp lệ.',
            'maSach.max' => 'Mã sách tìm kiếm không hợp lệ.',
            'tenSach.max' => 'Tên sách tìm kiếm không hợp lệ.',
            'tacGia.max' => 'Tên tác giả tìm kiếm không hợp lệ.',
            'trangThai.in' => 'Trạng thái tìm kiếm không hợp lệ.',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('sach.index')
                ->withInput()
                ->withErrors($validator)
                ->with('danger', 'Dữ liệu tìm kiếm không hợp lệ.');
        }

        try {
            $books = Book::query()
                ->with(['author', 'publisher', 'category', 'language', 'shelf'])
                ->applyFilters($filters)
                ->orderBy('maSach')
                ->paginate(5)
                ->withQueryString();
        } catch (Throwable $exception) {
            $message = $hasFilter
                ? 'Tìm kiếm thất bại, vui lòng thử lại.'
                : 'Không thể tải danh sách sách.';

            return redirect()
                ->route('sach.index')
                ->withInput()
                ->with('danger', $message);
        }

        return view('books.index', compact('books', 'filters', 'hasFilter'));
    }

    public function create()
    {
        return view('books.form', [
            'book' => new Book(),
            'authors' => Author::orderBy('tenTG')->get(),
            'publishers' => Publisher::orderBy('tenNXB')->get(),
            'categories' => Category::orderBy('tenTL')->get(),
            'languages' => Language::orderBy('tenNN')->get(),
            'shelves' => Shelf::orderBy('tenKS')->get(),
            'isEdit' => false,
        ]);
    }

    public function store(Request $request)
    {
        try {
            $data = $this->validateData($request, null, true);
        } catch (ValidationException $exception) {
            $cancelMessage = $exception->errors()['cancel'][0] ?? null;

            if ($cancelMessage) {
                return redirect()->route('sach.index')->with('danger', $cancelMessage);
            }

            throw $exception;
        }

        $data['maSach'] = $this->idGenerator->make('sach', 'maSach', 'S');

        Book::create($data);

        return redirect()->route('sach.index')->with('success', 'Thêm sách thành công.');
    }

    public function edit(Book $book)
    {
        return view('books.form', [
            'book' => $book,
            'authors' => Author::orderBy('tenTG')->get(),
            'publishers' => Publisher::orderBy('tenNXB')->get(),
            'categories' => Category::orderBy('tenTL')->get(),
            'languages' => Language::orderBy('tenNN')->get(),
            'shelves' => Shelf::orderBy('tenKS')->get(),
            'isEdit' => true,
        ]);
    }

    public function show(Book $book)
    {
        try {
            $book->load(['author', 'publisher', 'category', 'language', 'shelf']);
        } catch (Throwable $exception) {
            $message = str_contains(strtolower($exception->getMessage()), 'connection')
                ? 'Không thể kết nối hệ thống.'
                : 'Không thể tải thông tin chi tiết sách.';

            return redirect()->route('sach.index')->with('danger', $message);
        }

        return view('books.show', compact('book'));
    }

    public function update(Request $request, Book $book)
    {
        $data = $this->validateData($request, $book);

        try {
            $book->update($data);
        } catch (Throwable $exception) {
            return back()
                ->withInput()
                ->with('danger', 'Cập nhật thất bại, vui lòng thử lại.');
        }

        return redirect()->route('sach.index')->with('success', 'Cập nhật sách thành công.');
    }

    public function destroy(Book $book)
    {
        if ($book->borrowDetails()->exists()) {
            return back()->with('danger', 'Không thể xóa sách do đang được sử dụng.');
        }

        try {
            $book->delete();
        } catch (Throwable $exception) {
            return back()->with('danger', 'Xóa thất bại, vui lòng thử lại.');
        }

        return redirect()->route('sach.index')->with('success', 'Xóa sách thành công.');
    }

    private function validateData(Request $request, ?Book $book = null, bool $trackCreateAttempts = false): array
    {
        $id = optional($book)->maSach;

        $rules = [
            'tenSach' => [
                'required',
                'string',
                'max:200',
                Rule::unique('sach', 'tenSach')->ignore($id, 'maSach'),
            ],
            'maTG' => ['required', Rule::exists('tacgia', 'maTG')],
            'maNXB' => ['required', Rule::exists('nhaxuatban', 'maNXB')],
            'maTL' => ['required', Rule::exists('theloai', 'maTL')],
            'maNN' => ['required', Rule::exists('ngonngu', 'maNN')],
            'maKS' => ['required', Rule::exists('kesach', 'maKS')],
            'namXB' => ['required', 'integer', 'lte:'.date('Y')],
            'soLuong' => ['required', 'integer', 'min:1'],
            'moTa' => ['nullable', 'string', 'max:255'],
        ];

        $messages = [
            'tenSach.required' => 'Tên sách không được để trống.',
            'maTG.required' => 'Vui lòng chọn tác giả.',
            'maTG.exists' => 'Tác giả không tồn tại trong hệ thống.',
            'maNXB.required' => 'Vui lòng chọn nhà xuất bản.',
            'maNXB.exists' => 'Nhà xuất bản không tồn tại trong hệ thống.',
            'maTL.required' => 'Vui lòng chọn thể loại.',
            'maTL.exists' => 'Thể loại không tồn tại trong hệ thống.',
            'maNN.required' => 'Vui lòng chọn ngôn ngữ.',
            'maNN.exists' => 'Ngôn ngữ không tồn tại trong hệ thống.',
            'maKS.required' => 'Vui lòng chọn kệ sách.',
            'maKS.exists' => 'Vị trí kệ sách không tồn tại trong hệ thống.',
            'namXB.required' => 'Năm xuất bản không được để trống.',
            'namXB.integer' => 'Năm xuất bản không hợp lệ.',
            'namXB.lte' => 'Năm xuất bản không hợp lệ.',
            'soLuong.required' => 'Số lượng không được để trống.',
            'soLuong.integer' => 'Số lượng phải là số nguyên dương.',
            'soLuong.min' => 'Số lượng phải là số nguyên dương.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            if ($trackCreateAttempts) {
                $attemptKey = 'book_store_invalid_attempts';
                $attempts = (int) $request->session()->get($attemptKey, 0) + 1;

                if ($attempts >= 3) {
                    $request->session()->forget($attemptKey);
                    throw ValidationException::withMessages([
                        'cancel' => ['Bạn đã nhập sai 3 lần. Thao tác đã bị hủy.'],
                    ]);
                }

                $request->session()->put($attemptKey, $attempts);
            }

            throw new ValidationException($validator);
        }

        if ($trackCreateAttempts) {
            $request->session()->forget('book_store_invalid_attempts');
        }

        return $validator->validated();
    }
}
