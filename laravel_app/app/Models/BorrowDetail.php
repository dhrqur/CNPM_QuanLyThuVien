<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BorrowDetail extends Model
{
    use HasFactory;

    protected $table = 'chitietmuontra';

    protected $primaryKey = null;

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'maMT',
        'maSach',
        'soLuong',
        'ghiChu',
    ];

    public function borrow()
    {
        return $this->belongsTo(Borrow::class, 'maMT', 'maMT');
    }

    public function book()
    {
        return $this->belongsTo(Book::class, 'maSach', 'maSach');
    }
}
