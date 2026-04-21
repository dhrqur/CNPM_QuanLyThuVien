<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'nhanvien';

    protected $primaryKey = 'maNV';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'maNV',
        'tenNV',
        'ngaySinh',
        'diaChi',
        'gioiTinh',
        'soDT',
        'email',
        'vaitro',
        'trangThaiTK',
        'tenDangNhap',
        'matKhau',
        'remember_token',
    ];

    protected $hidden = [
        'matKhau',
        'remember_token',
    ];

    protected $casts = [
        'ngaySinh' => 'date',
    ];

    public function getAuthPassword(): string
    {
        return $this->matKhau;
    }

    public function getAccountStatus(): string
    {
        return $this->trangThaiTK ?: 'Đang hoạt động';
    }

    public function isManager(): bool
    {
        return $this->vaitro === 'Quản lý thư viện';
    }

    public function isLibrarian(): bool
    {
        return $this->vaitro === 'Thủ thư';
    }

    public function borrows()
    {
        return $this->hasMany(Borrow::class, 'maNV', 'maNV');
    }
}
