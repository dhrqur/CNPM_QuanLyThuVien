<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'theloai';

    protected $primaryKey = 'maTL';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'maTL',
        'tenTL',
    ];

    public function books()
    {
        return $this->hasMany(Book::class, 'maTL', 'maTL');
    }
}
