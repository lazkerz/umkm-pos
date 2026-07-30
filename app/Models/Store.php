<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'name',
        'address',
        'phone',
        'logo',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function staff()
    {
        return $this->hasMany(User::class, 'store_id');
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function stockItems()
    {
        return $this->hasMany(StockItem::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function stockDistributions()
    {
        return $this->hasMany(StockDistribution::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function promotions()
    {
        return $this->hasMany(Promotion::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
