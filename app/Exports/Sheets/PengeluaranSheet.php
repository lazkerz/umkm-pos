<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PengeluaranSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize
{
    public function __construct(private Collection $expenses) {}

    public function collection(): Collection
    {
        return $this->expenses;
    }

    public function headings(): array
    {
        return ['Tanggal', 'Kategori', 'Deskripsi', 'Jumlah', 'Diinput oleh', 'Disetujui oleh'];
    }

    public function map($expense): array
    {
        return [
            $expense->expense_date->format('d-m-Y'),
            $expense->category,
            $expense->description ?? '-',
            $expense->amount,
            $expense->creator->name ?? '-',
            $expense->approver->name ?? '-',
        ];
    }

    public function title(): string
    {
        return 'Pengeluaran';
    }
}
