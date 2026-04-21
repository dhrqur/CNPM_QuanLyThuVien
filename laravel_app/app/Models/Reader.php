<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reader extends Model
{
    use HasFactory;

    protected $table = 'docgia';

    protected $primaryKey = 'maDG';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'maDG',
        'tenDG',
        'ngaySinh',
        'gioiTinh',
        'diaChi',
        'soDT',
        'email',
    ];

    protected $casts = [
        'ngaySinh' => 'date',
    ];

    public function card()
    {
        return $this->hasOne(LibraryCard::class, 'maDG', 'maDG');
    }

    public function borrows()
    {
        return $this->hasMany(Borrow::class, 'maDG', 'maDG');
    }

    public function hasActiveBorrow(): bool
    {
        return $this->borrows()
            ->whereIn('trangThai', ['Đang mượn', 'Quá hạn'])
            ->exists();
    }
}
