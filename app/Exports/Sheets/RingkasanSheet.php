<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class RingkasanSheet implements FromArray, WithHeadings, WithTitle, ShouldAutoSize
{
    public function __construct(
        private string $storeName,
        private string $periodLabel,
        private float $totalRevenue,
        private float $totalExpense,
        private float $totalProfit,
    ) {}

    public function array(): array
    {
        return [
            ['Toko', $this->storeName],
            ['Periode', $this->periodLabel],
            [''],
            ['Total Pendapatan', $this->totalRevenue],
            ['Total Pengeluaran', $this->totalExpense],
            ['Laba / Rugi', $this->totalProfit],
        ];
    }

    public function headings(): array
    {
        return ['Keterangan', 'Nilai'];
    }

    public function title(): string
    {
        return 'Ringkasan';
    }
}
