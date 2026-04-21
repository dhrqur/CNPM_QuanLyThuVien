@extends('layouts.app')

@section('title', 'Chi Tiết Nhân viên')
@section('page_title', 'Chi Tiết Nhân viên')
@section('page_subtitle', 'Thông tin đầy đủ của nhân viên')

@section('content')
    <div class="content-card p-4">
        <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
            <div>
                <h5 class="mb-1">{{ $staff->tenNV }}</h5>
                <div class="text-muted">Mã nhân viên: {{ $staff->maNV }}</div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('nhanvien.edit', $staff) }}" class="btn btn-warning">Sửa</a>
                <a href="{{ route('nhanvien.index') }}" class="btn btn-outline-secondary">Quay lại</a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <tbody>
                    <tr>
                        <th width="220">Mã nhân viên</th>
                        <td>{{ $staff->maNV }}</td>
                    </tr>
                    <tr>
                        <th>Tên nhân viên</th>
                        <td>{{ $staff->tenNV }}</td>
                    </tr>
                    <tr>
                        <th>Ngày sinh</th>
                        <td>{{ optional($staff->ngaySinh)->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <th>Giới tính</th>
                        <td>{{ $staff->gioiTinh }}</td>
                    </tr>
                    <tr>
                        <th>Địa chỉ</th>
                        <td>{{ $staff->diaChi }}</td>
                    </tr>
                    <tr>
                        <th>Số điện thoại</th>
                        <td>{{ $staff->soDT }}</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>{{ $staff->email }}</td>
                    </tr>
                    <tr>
                        <th>Vai trò</th>
                        <td>{{ $staff->vaitro }}</td>
                    </tr>
                    <tr>
                        <th>Tên đăng nhập</th>
                        <td>{{ $staff->tenDangNhap }}</td>
                    </tr>
                    <tr>
                        <th>Mật khẩu</th>
                        <td>{{ $staff->matKhau }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection