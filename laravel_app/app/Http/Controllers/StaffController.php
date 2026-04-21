<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\IdGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    public function __construct(IdGeneratorService $idGenerator)
    {
        $this->idGenerator = $idGenerator;
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $staffs = User::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where('maNV', 'like', "%{$search}%")
                    ->orWhere('tenNV', 'like', "%{$search}%")
                    ->orWhere('tenDangNhap', 'like', "%{$search}%");
            })
            ->orderBy('maNV')
            ->paginate(5)
            ->withQueryString();

        return view('staffs.index', compact('staffs', 'search'));
    }

    public function create()
    {
        return view('staffs.form', [
            'staff' => new User(),
            'isEdit' => false,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['maNV'] = $this->idGenerator->make('nhanvien', 'maNV', 'NV');
        $data['matKhau'] = Hash::make($data['matKhau']);

        User::create($data);

        return redirect()->route('nhanvien.index')->with('success', 'Thêm nhân viên thành công.');
    }

    public function edit(User $staff)
    {
        return view('staffs.form', [
            'staff' => $staff,
            'isEdit' => true,
        ]);
    }

    public function show(User $staff)
    {
        $staff->loadCount('borrows');

        return view('staffs.show', compact('staff'));
    }

    public function update(Request $request, User $staff)
    {
        $data = $this->validateData($request, $staff);

        if (!empty($data['matKhau'])) {
            $data['matKhau'] = Hash::make($data['matKhau']);
        } else {
            unset($data['matKhau']);
        }

        $staff->update($data);

        return redirect()->route('nhanvien.index')->with('success', 'Cập nhật nhân viên thành công.');
    }

    public function destroy(User $staff)
    {
        if ($staff->borrows()->exists()) {
            return back()->with('danger', 'Không thể xóa nhân viên đã có phiếu mượn trả.');
        }

        if (Auth::id() === $staff->maNV) {
            return back()->with('danger', 'Không thể xóa tài khoản đang đăng nhập.');
        }

        $staff->delete();

        return redirect()->route('nhanvien.index')->with('success', 'Xóa nhân viên thành công.');
    }

    private function validateData(Request $request, ?User $staff = null): array
    {
        $isEdit = $staff !== null;
        $id = optional($staff)->maNV;

        return $request->validate([
            'tenNV' => ['required', 'string', 'max:100'],
            'ngaySinh' => ['required', 'date'],
            'diaChi' => ['required', 'string', 'max:255'],
            'gioiTinh' => ['required', 'in:Nam,Nữ'],
            'soDT' => [
                'required',
                'regex:/^\d{10,12}$/',
                Rule::unique('nhanvien', 'soDT')->ignore($id, 'maNV'),
            ],
            'email' => [
                'required',
                'email',
                'max:100',
                Rule::unique('nhanvien', 'email')->ignore($id, 'maNV'),
            ],
            'vaitro' => ['required', 'in:Quản lý thư viện,Thủ thư'],
            'tenDangNhap' => [
                'required',
                'string',
                'max:50',
                Rule::unique('nhanvien', 'tenDangNhap')->ignore($id, 'maNV'),
            ],
            'matKhau' => [$isEdit ? 'nullable' : 'required', 'string', 'min:6'],
        ], [
            'tenNV.required' => 'Tên nhân viên không được để trống.',
            'ngaySinh.required' => 'Ngày sinh không được để trống.',
            'diaChi.required' => 'Địa chỉ không được để trống.',
            'gioiTinh.required' => 'Giới tính không được để trống.',
            'soDT.required' => 'Số điện thoại không được để trống.',
            'soDT.regex' => 'Số điện thoại phải từ 10 đến 12 chữ số.',
            'email.required' => 'Email không được để trống.',
            'email.email' => 'Email không đúng định dạng.',
            'vaitro.required' => 'Vai trò không được để trống.',
            'tenDangNhap.required' => 'Tên đăng nhập không được để trống.',
            'matKhau.required' => 'Mật khẩu không được để trống.',
            'matKhau.min' => 'Mật khẩu tối thiểu 6 ký tự.',
        ]);
    }
}
