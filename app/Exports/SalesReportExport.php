<?php

namespace App\Exports;

use App\Models\Store;
use App\Models\Transaction;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SalesReportExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize
{
    public function __construct(private Store $store, private $start, private $end) {}

    public function collection(): Collection
    {
        return Transaction::where('store_id', $this->store->id)
            ->completed()
            ->with(['customer', 'items.product'])
            ->whereBetween('created_at', [$this->start, $this->end])
            ->latest()
            ->get();
    }

    public function headings(): array
    {
        return ['Invoice', 'Tanggal', 'Channel', 'Customer', 'Item', 'Subtotal', 'Diskon', 'Total', 'Metode Bayar'];
    }

    public function map($transaction): array
    {
        $itemsSummary = $transaction->items->map(fn ($i) => "{$i->product->name} x{$i->quantity}")->implode(', ');

        return [
            $transaction->invoice_number,
            $transaction->created_at->format('d-m-Y H:i'),
            ucfirst($transaction->channel),
            $transaction->customer->name ?? 'Walk-in',
            $itemsSummary,
            $transaction->subtotal,
            $transaction->discount,
            $transaction->total,
            strtoupper($transaction->payment_method ?? '-'),
        ];
    }

    public function title(): string
    {
        return 'Report Penjualan';
    }
}
