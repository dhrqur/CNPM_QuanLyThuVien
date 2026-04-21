<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LibraryCard extends Model
{
    use HasFactory;

    protected $table = 'thethuvien';

    protected $primaryKey = 'maTTV';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'maTTV',
        'maDG',
        'ngayCap',
        'ngayHetHan',
        'trangThai',
    ];

    protected $casts = [
        'ngayCap' => 'date',
        'ngayHetHan' => 'date',
    ];

    public function reader()
    {
        return $this->belongsTo(Reader::class, 'maDG', 'maDG');
    }
}
