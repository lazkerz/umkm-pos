<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockDistribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'stock_item_id',
        'quantity',
        'distributed_by',
        'distribution_date',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'distribution_date' => 'date',
        ];
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function stockItem()
    {
        return $this->belongsTo(StockItem::class);
    }

    public function distributor()
    {
        return $this->belongsTo(User::class, 'distributed_by');
    }
}
