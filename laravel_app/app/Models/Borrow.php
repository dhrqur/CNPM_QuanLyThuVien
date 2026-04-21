<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Borrow extends Model
{
    use HasFactory;

    protected $table = 'muontra';

    protected $primaryKey = 'maMT';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'maMT',
        'maDG',
        'maNV',
        'ngayMuon',
        'hanTra',
        'ngayTra',
        'trangThai',
    ];

    protected $casts = [
        'ngayMuon' => 'date',
        'hanTra' => 'date',
        'ngayTra' => 'date',
    ];

    public function reader()
    {
        return $this->belongsTo(Reader::class, 'maDG', 'maDG');
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'maNV', 'maNV');
    }

    public function details()
    {
        return $this->hasMany(BorrowDetail::class, 'maMT', 'maMT');
    }

    public function isReturned(): bool
    {
        return $this->trangThai === 'Đã trả';
    }
}
