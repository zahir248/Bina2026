<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReportExport implements FromArray, ShouldAutoSize, WithEvents
{
    public function __construct(
        private int $totalRevenueCents,
        private int $revenueExcludingFeeCents,
        private int $totalProcessingFeeCents,
        private int $orderCount,
        private int $totalParticipants,
        private int|string|null $totalStock,
        private int $refundedCount,
        private int $refundedAmountCents,
        private Collection $ticketStats,
        private bool $includeEventColumn = false
    ) {}

    public function array(): array
    {
        $stockDisplay = $this->totalStock !== null && $this->totalStock !== '' ? (string) $this->totalStock : '—';

        $rows = [
            ['Summary Statistics'],
            ['Total revenue (paid orders)', 'RM ' . number_format($this->totalRevenueCents / 100, 2)],
            ['Revenue excl. processing fee', 'RM ' . number_format($this->revenueExcludingFeeCents / 100, 2)],
            ['Total payment processing fees', 'RM ' . number_format($this->totalProcessingFeeCents / 100, 2)],
            ['Paid orders', $this->orderCount],
            ['Total participants', $this->totalParticipants],
            ['Total stock', $stockDisplay],
            ['Refunded orders', $this->refundedCount],
            ['Refunded amount', 'RM ' . number_format($this->refundedAmountCents / 100, 2)],
            [],
            ['Ticket Type Statistics'],
        ];

        $tableHeaders = ['Ticket Type', 'Stock', 'Total sold', 'Total sales (excl. fee)', 'Total sales (with fee)'];
        if ($this->includeEventColumn) {
            array_splice($tableHeaders, 1, 0, ['Event']);
        }
        $rows[] = $tableHeaders;

        $totalExclFeeCents = 0;
        $totalWithFeeCents = 0;
        foreach ($this->ticketStats as $stat) {
            $totalExclFeeCents += $stat->total_sales_cents ?? 0;
            $totalWithFeeCents += $stat->total_sales_with_fee_cents ?? 0;
            $row = [$stat->ticket_name];
            if ($this->includeEventColumn) {
                $row[] = $stat->event_display ?? $stat->event_name ?? '—';
            }
            $row[] = ''; // Stock per ticket type - not in DB
            $row[] = $stat->total_sold;
            $row[] = 'RM ' . number_format(($stat->total_sales_cents ?? 0) / 100, 2);
            $row[] = 'RM ' . number_format(($stat->total_sales_with_fee_cents ?? 0) / 100, 2);
            $rows[] = $row;
        }

        $totalRow = ['Total'];
        if ($this->includeEventColumn) {
            $totalRow[] = '';
        }
        $totalRow[] = '';
        $totalRow[] = '';
        $totalRow[] = 'RM ' . number_format($totalExclFeeCents / 100, 2);
        $totalRow[] = 'RM ' . number_format($totalWithFeeCents / 100, 2);
        $rows[] = $totalRow;

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $greyFill = [
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFD3D3D3'],
                    ],
                ];
                $bold = ['font' => ['bold' => true]];
                $border = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ];
                $alignRight = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]];

                $highestRow = $sheet->getHighestRow();
                $highestCol = $sheet->getHighestColumn();
                $range = 'A1:' . $highestCol . $highestRow;
                $sheet->getStyle($range)->applyFromArray($border);

                $sheet->getStyle('A1')->applyFromArray(array_merge($bold, $greyFill));
                $sheet->getStyle('B2:B9')->applyFromArray($alignRight);
                $sheet->getStyle('A11')->applyFromArray(array_merge($bold, $greyFill));

                $stockCol = $this->includeEventColumn ? 'C' : 'B';
                $soldCol = $this->includeEventColumn ? 'D' : 'C';
                $exclFeeCol = $this->includeEventColumn ? 'E' : 'D';
                $withFeeCol = $this->includeEventColumn ? 'F' : 'E';
                $tableHeaderRow = 12;
                $sheet->getStyle('A' . $tableHeaderRow . ':' . $highestCol . $tableHeaderRow)->applyFromArray(array_merge($bold, $greyFill));
                $sheet->getStyle('A' . $tableHeaderRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                if ($this->includeEventColumn) {
                    $sheet->getStyle('B' . $tableHeaderRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }
                $sheet->getStyle($stockCol . $tableHeaderRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle($soldCol . $tableHeaderRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle($exclFeeCol . $tableHeaderRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle($withFeeCol . $tableHeaderRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                for ($r = $tableHeaderRow + 1; $r < $highestRow; $r++) {
                    $sheet->getStyle('A' . $r)->applyFromArray($bold);
                }

                $lastRow = $highestRow;
                $sheet->getStyle('A' . $lastRow)->applyFromArray($bold);
                $sheet->getStyle($exclFeeCol . $lastRow)->applyFromArray($bold);
                $sheet->getStyle($withFeeCol . $lastRow)->applyFromArray($bold);
                for ($r = $tableHeaderRow + 1; $r <= $highestRow; $r++) {
                    $sheet->getStyle($stockCol . $r)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle($soldCol . $r)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle($exclFeeCol . $r)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle($withFeeCol . $r)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }
            },
        ];
    }
}
