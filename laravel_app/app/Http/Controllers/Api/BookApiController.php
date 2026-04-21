<?php

namespace App\Http\Controllers\Api;

use App\Models\Book;
use App\Services\IdGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class BookApiController extends BaseApiController
{
    public function __construct(IdGeneratorService $idGenerator)
    {
        $this->idGenerator = $idGenerator;
    }

    public function index(Request $request): JsonResponse
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
            return $this->error('Dữ liệu tìm kiếm không hợp lệ.', 422, $validator->errors()->toArray());
        }

        try {
            $books = Book::query()
                ->with(['author', 'publisher', 'category', 'language', 'shelf'])
                ->applyFilters($filters)
                ->orderBy('maSach')
                ->paginate((int) $request->query('per_page', 10));
        } catch (Throwable $exception) {
            $message = $hasFilter
                ? 'Tìm kiếm thất bại, vui lòng thử lại.'
                : 'Không thể tải danh sách sách.';

            return $this->error($message, 500);
        }

        return $this->paginated($books);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $data = $this->validateData($request, null, true);
        } catch (ValidationException $exception) {
            $cancelMessage = $exception->errors()['cancel'][0] ?? null;

            if ($cancelMessage) {
                return $this->error($cancelMessage, 429, $exception->errors());
            }

            throw $exception;
        }

        $data['maSach'] = $this->idGenerator->make('sach', 'maSach', 'S');

        $book = Book::create($data);

        return $this->success($book->load(['author', 'publisher', 'category', 'language', 'shelf']), 'Thêm sách thành công.', 201);
    }

    public function show(Book $book): JsonResponse
    {
        try {
            return $this->success($book->load(['author', 'publisher', 'category', 'language', 'shelf']));
        } catch (Throwable $exception) {
            $message = str_contains(strtolower($exception->getMessage()), 'connection')
                ? 'Không thể kết nối hệ thống.'
                : 'Không thể tải thông tin chi tiết sách.';

            return $this->error($message, 500);
        }
    }

    public function update(Request $request, Book $book): JsonResponse
    {
        $data = $this->validateData($request, $book);

        try {
            $book->update($data);
        } catch (Throwable $exception) {
            return $this->error('Cập nhật thất bại, vui lòng thử lại.', 500);
        }

        return $this->success($book->fresh()->load(['author', 'publisher', 'category', 'language', 'shelf']), 'Cập nhật sách thành công.');
    }

    public function destroy(Book $book): JsonResponse
    {
        if ($book->borrowDetails()->exists()) {
            return $this->error('Không thể xóa sách do đang được sử dụng.', 409);
        }

        try {
            $book->delete();
        } catch (Throwable $exception) {
            return $this->error('Xóa thất bại, vui lòng thử lại.', 500);
        }

        return $this->success(null, 'Xóa sách thành công.');
    }

    private function validateData(Request $request, ?Book $book = null, bool $trackCreateAttempts = false): array
    {
        $rules = [
            'tenSach' => [
                'required',
                'string',
                'max:200',
                Rule::unique('sach', 'tenSach')->ignore(optional($book)->maSach, 'maSach'),
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
            'maKS.required' => 'Vui lòng chọn vị trí kệ sách.',
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
                $attemptKey = 'api_book_store_invalid_attempts:'.$request->user()->getAuthIdentifier().':'.$request->ip();
                $attempts = RateLimiter::attempts($attemptKey) + 1;
                RateLimiter::hit($attemptKey, 600);

                if ($attempts >= 3) {
                    RateLimiter::clear($attemptKey);
                    throw ValidationException::withMessages([
                        'cancel' => ['Bạn đã nhập sai 3 lần. Thao tác đã bị hủy.'],
                    ]);
                }
            }

            throw new ValidationException($validator);
        }

        if ($trackCreateAttempts) {
            $attemptKey = 'api_book_store_invalid_attempts:'.$request->user()->getAuthIdentifier().':'.$request->ip();
            RateLimiter::clear($attemptKey);
        }

        return $validator->validated();
    }
}
