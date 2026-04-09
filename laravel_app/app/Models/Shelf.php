<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shelf extends Model
{
    use HasFactory;

    protected $table = 'kesach';

    protected $primaryKey = 'maKS';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'maKS',
        'tenKS',
    ];

    public function books()
    {
        return $this->hasMany(Book::class, 'maKS', 'maKS');
    }
}
