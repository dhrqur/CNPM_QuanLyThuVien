<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Services\IdGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StaffApiController extends BaseApiController
{
    public function __construct(IdGeneratorService $idGenerator)
    {
        $this->idGenerator = $idGenerator;
    }

    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));

        $staffs = User::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where('maNV', 'like', "%{$search}%")
                    ->orWhere('tenNV', 'like', "%{$search}%")
                    ->orWhere('tenDangNhap', 'like', "%{$search}%");
            })
            ->orderBy('maNV')
            ->paginate((int) $request->query('per_page', 10));

        return $this->paginated($staffs);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validateData($request);
        $data['maNV'] = $this->idGenerator->make('nhanvien', 'maNV', 'NV');
        $data['matKhau'] = Hash::make((string) $data['matKhau']);
        $data['trangThaiTK'] = $data['trangThaiTK'] ?? 'Đang hoạt động';

        $staff = User::create($data);

        return $this->success($staff, 'Thêm nhân viên thành công.', 201);
    }

    public function show(User $staff): JsonResponse
    {
        return $this->success($staff);
    }

    public function update(Request $request, User $staff): JsonResponse
    {
        $data = $this->validateData($request, $staff);

        if (!empty($data['matKhau'])) {
            $data['matKhau'] = Hash::make((string) $data['matKhau']);
        } else {
            unset($data['matKhau']);
        }

        $staff->update($data);

        return $this->success($staff->fresh(), 'Cập nhật nhân viên thành công.');
    }

    public function destroy(User $staff, Request $request): JsonResponse
    {
        if ($staff->borrows()->exists()) {
            return $this->error('Không thể xóa nhân viên đã có phiếu mượn trả.', 409);
        }

        if (optional($request->user())->maNV === $staff->maNV) {
            return $this->error('Không thể xóa tài khoản đang đăng nhập.', 409);
        }

        $staff->delete();

        return $this->success(null, 'Xóa nhân viên thành công.');
    }

    private function validateData(Request $request, ?User $staff = null): array
    {
        return $request->validate([
            'tenNV' => ['required', 'string', 'max:100'],
            'ngaySinh' => ['required', 'date'],
            'diaChi' => ['required', 'string', 'max:255'],
            'gioiTinh' => ['required', 'in:Nam,Nữ'],
            'soDT' => ['required', 'regex:/^\d{10,12}$/', Rule::unique('nhanvien', 'soDT')->ignore(optional($staff)->maNV, 'maNV')],
            'email' => ['required', 'email', 'max:100', Rule::unique('nhanvien', 'email')->ignore(optional($staff)->maNV, 'maNV')],
            'vaitro' => ['required', 'in:Quản lý thư viện,Thủ thư'],
            'trangThaiTK' => ['nullable', 'in:Đang hoạt động,Bị khóa,Chờ kích hoạt'],
            'tenDangNhap' => ['required', 'string', 'max:50', Rule::unique('nhanvien', 'tenDangNhap')->ignore(optional($staff)->maNV, 'maNV')],
            'matKhau' => [$staff ? 'nullable' : 'required', 'string', 'min:6'],
        ]);
    }
}
