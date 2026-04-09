<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Publisher extends Model
{
    use HasFactory;

    protected $table = 'nhaxuatban';

    protected $primaryKey = 'maNXB';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'maNXB',
        'tenNXB',
        'diaChi',
        'soDT',
        'email',
    ];

    public function books()
    {
        return $this->hasMany(Book::class, 'maNXB', 'maNXB');
    }
}
