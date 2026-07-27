<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'category_id',
        'name',
        'description',
        'price',
        'image',
        'is_available',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_available' => 'boolean',
        ];
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }

    // Resep/BOM - bahan baku apa aja & berapa banyak yang dipakai untuk 1 unit menu ini
    public function recipes()
    {
        return $this->hasMany(ProductRecipe::class);
    }

    public function hasRecipe(): bool
    {
        return $this->recipes()->exists();
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }
}
