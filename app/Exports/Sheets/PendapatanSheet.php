<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PendapatanSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize
{
    public function __construct(private Collection $transactions) {}

    public function collection(): Collection
    {
        return $this->transactions;
    }

    public function headings(): array
    {
        return ['Invoice', 'Tanggal', 'Channel', 'Customer', 'Subtotal', 'Diskon', 'Total', 'Metode Bayar'];
    }

    public function map($transaction): array
    {
        return [
            $transaction->invoice_number,
            $transaction->created_at->format('d-m-Y H:i'),
            ucfirst($transaction->channel),
            $transaction->customer->name ?? 'Walk-in',
            $transaction->subtotal,
            $transaction->discount,
            $transaction->total,
            strtoupper($transaction->payment_method ?? '-'),
        ];
    }

    public function title(): string
    {
        return 'Pendapatan';
    }
}
