<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'category',
        'amount',
        'description',
        'expense_date',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'stock_item_id',
        'restock_quantity',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'expense_date' => 'date',
            'approved_at' => 'datetime',
            'restock_quantity' => 'decimal:2',
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

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    // Nambah stok + catat StockMovement 'in' kalau expense ini dikaitkan ke StockItem.
    // Dipanggil pas expense jadi 'approved' - owner langsung saat create (auto-approved),
    // staff pas owner approve. Aman dipanggil berkali-kali kalau restock_quantity kosong.
    public function applyRestock(int $actingUserId): void
    {
        if (! $this->stock_item_id || ! $this->restock_quantity) {
            return;
        }

        DB::transaction(function () use ($actingUserId) {
            StockItem::where('id', $this->stock_item_id)
                ->where('store_id', $this->store_id)
                ->increment('quantity', $this->restock_quantity);

            StockMovement::create([
                'store_id' => $this->store_id,
                'stock_item_id' => $this->stock_item_id,
                'type' => 'in',
                'quantity' => $this->restock_quantity,
                'note' => "Restock dari pengeluaran: {$this->category}",
                'created_by' => $actingUserId,
            ]);
        });
    }
}
