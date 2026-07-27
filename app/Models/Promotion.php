<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'name',
        'type',
        'value',
        'channel',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function isValidNow(): bool
    {
        $today = now()->toDateString();
        return $this->is_active
            && $this->start_date->toDateString() <= $today
            && $this->end_date->toDateString() >= $today;
    }

    public function calculateDiscount(float $subtotal): float
    {
        if ($this->type === 'percentage') {
            return $subtotal * ($this->value / 100);
        }
        return min($this->value, $subtotal); // fixed discount, ga boleh lebih besar dari subtotal
    }
}
