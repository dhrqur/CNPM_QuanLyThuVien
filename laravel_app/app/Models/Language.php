<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasFactory;

    protected $table = 'ngonngu';

    protected $primaryKey = 'maNN';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'maNN',
        'tenNN',
    ];

    public function books()
    {
        return $this->hasMany(Book::class, 'maNN', 'maNN');
    }
}
