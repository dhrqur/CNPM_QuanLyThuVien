<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (Book $book) {
            // Tự động đồng bộ trạng thái theo số lượng khi tạo mới hoặc khi số lượng thay đổi.
            if (! $book->exists || $book->isDirty('soLuong')) {
                $book->trangThai = ((int) $book->soLuong > 0) ? 'Còn' : 'Hết';
            }
        });
    }

    protected $table = 'sach';

    protected $primaryKey = 'maSach';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'maSach',
        'maNXB',
        'maTL',
        'maNN',
        'maTG',
        'maKS',
        'tenSach',
        'namXB',
        'soLuong',
        'moTa',
        'trangThai',
    ];

    public function author()
    {
        return $this->belongsTo(Author::class, 'maTG', 'maTG');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'maTL', 'maTL');
    }

    public function publisher()
    {
        return $this->belongsTo(Publisher::class, 'maNXB', 'maNXB');
    }

    public function language()
    {
        return $this->belongsTo(Language::class, 'maNN', 'maNN');
    }

    public function shelf()
    {
        return $this->belongsTo(Shelf::class, 'maKS', 'maKS');
    }

    public function borrowDetails()
    {
        return $this->hasMany(BorrowDetail::class, 'maSach', 'maSach');
    }

    public function scopeApplyFilters($query, array $filters)
    {
        $keyword = trim((string) ($filters['search'] ?? ''));
        $maSach = trim((string) ($filters['maSach'] ?? ''));
        $tenSach = trim((string) ($filters['tenSach'] ?? ''));
        $tacGia = trim((string) ($filters['tacGia'] ?? ''));
        $trangThai = trim((string) ($filters['trangThai'] ?? ''));

        return $query
            ->when($keyword !== '', function ($builder) use ($keyword) {
                $builder->where(function ($searchQuery) use ($keyword) {
                    $searchQuery->where('maSach', 'like', "%{$keyword}%")
                        ->orWhere('tenSach', 'like', "%{$keyword}%")
                        ->orWhere('trangThai', 'like', "%{$keyword}%")
                        ->orWhereHas('author', function ($authorQuery) use ($keyword) {
                            $authorQuery->where('tenTG', 'like', "%{$keyword}%");
                        });
                });
            })
            ->when($maSach !== '', function ($builder) use ($maSach) {
                $builder->where('maSach', 'like', "%{$maSach}%");
            })
            ->when($tenSach !== '', function ($builder) use ($tenSach) {
                $builder->where('tenSach', 'like', "%{$tenSach}%");
            })
            ->when($tacGia !== '', function ($builder) use ($tacGia) {
                $builder->whereHas('author', function ($authorQuery) use ($tacGia) {
                    $authorQuery->where('tenTG', 'like', "%{$tacGia}%");
                });
            })
            ->when(in_array($trangThai, ['Còn', 'Hết'], true), function ($builder) use ($trangThai) {
                $builder->where('trangThai', $trangThai);
            });
    }
}
