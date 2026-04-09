<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    use HasFactory;

    protected $table = 'tacgia';

    protected $primaryKey = 'maTG';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'maTG',
        'tenTG',
        'namSinh',
        'gioiTinh',
        'quocTich',
        'moTa',
    ];

    public function books()
    {
        return $this->hasMany(Book::class, 'maTG', 'maTG');
    }
}
