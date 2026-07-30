<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'store_id',
        'channel',
        'customer_id',
        'staff_id',
        'promotion_id',
        'subtotal',
        'discount',
        'total',
        'payment_method',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function scopeOffline($query)
    {
        return $query->where('channel', 'offline');
    }

    public function scopeOnline($query)
    {
        return $query->where('channel', 'online');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    
    public static function generateInvoiceNumber(int $storeId): string
    {
        $date = now()->format('Ymd');
        $countToday = self::where('store_id', $storeId)
            ->whereDate('created_at', now()->toDateString())
            ->count() + 1;

        return sprintf('INV-%s-%s-%04d', $storeId, $date, $countToday);
    }
}
