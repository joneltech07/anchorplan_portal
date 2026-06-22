<?php

namespace App\Exports;

use Illuminate\Support\Arr;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class EodReportsExport implements FromArray, WithHeadings, WithCustomStartCell, WithDrawings, WithStyles, WithEvents
{
    /** @var array<int, array<string, mixed>> */
    private array $rows;

    /**
     * @param array<int, array<string, mixed>> $rows
     */
    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    /**
     * Set the start cell for the table (leaves rows 1-7 for header layout)
     */
    public function startCell(): string
    {
        return 'A8';
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    public function array(): array
    {
        return array_map(function ($row) {
            $ministries = $row['ministries'] ?? '';
            if (is_array($ministries)) {
                $ministries = implode(', ', $ministries);
            }

            return [
                Arr::get($row, 'employee_name', ''),
                Arr::get($row, 'department', ''),
                Arr::get($row, 'position', ''),
                Arr::get($row, 'date', ''),
                Arr::get($row, 'accomplishments', ''),
                Arr::get($row, 'tomorrow_plan', ''),
                Arr::get($row, 'blockers', ''),
                $ministries,
                Arr::get($row, 'submitted_at', ''),
            ];
        }, $this->rows);
    }

    public function headings(): array
    {
        return [
            'Employee Name',
            'Department',
            'Position',
            'Report Date',
            'Accomplishments',
            'Tomorrow Plan',
            'Blockers',
            'Ministries',
            'Submitted At',
        ];
    }

    /**
     * Insert the logo drawing at Cell A2
     */
    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('AnchorPlan Logo');
        $drawing->setDescription('AnchorPlan Logo');
        $drawing->setPath(public_path('logo.png'));
        $drawing->setHeight(36); // Set height in pixels
        $drawing->setCoordinates('A2');
        $drawing->setOffsetX(10);
        $drawing->setOffsetY(5);

        return $drawing;
    }

    /**
     * Table styling
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the header row (Row 8)
            8 => [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FFFFFF'],
                    'size' => 10,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => '0F172A'], // Dark slate background
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ]
            ],
        ];
    }

    /**
     * Custom Layout, Borders, Zebra striping, Column Widths, Header & Footer text
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                // 1. Explicit Column Widths
                $widths = [
                    'A' => 20, // Employee Name
                    'B' => 18, // Department
                    'C' => 22, // Position
                    'D' => 14, // Report Date
                    'E' => 45, // Accomplishments
                    'F' => 45, // Tomorrow Plan
                    'G' => 25, // Blockers
                    'H' => 25, // Ministries
                    'I' => 18, // Submitted At
                ];
                foreach ($widths as $col => $w) {
                    $sheet->getColumnDimension($col)->setWidth($w);
                }

                // 2. Set Row Heights
                $sheet->getRowDimension(8)->setRowHeight(30); // Header height
                for ($row = 9; $row <= $highestRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(-1); // Auto row height for text wrapping
                }

                // 3. Header Texts & Branding
                $sheet->setCellValue('B2', 'AnchorPlan');
                $sheet->getStyle('B2')->getFont()->setBold(true)->setSize(16)->setColor(new Color('00A2FF'));
                
                $sheet->setCellValue('B3', 'Committed Plans, Established Results');
                $sheet->getStyle('B3')->getFont()->setItalic(true)->setSize(8.5)->setColor(new Color('64748B'));

                // Document Metadata (right side)
                $sheet->mergeCells('G2:I2');
                $sheet->setCellValue('G2', 'DOCUMENT');
                $sheet->getStyle('G2')->getFont()->setBold(true)->setSize(8.5)->setColor(new Color('8A2BE2'));
                $sheet->getStyle('G2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                $sheet->mergeCells('G3:I3');
                $sheet->setCellValue('G3', 'End of Day Reports');
                $sheet->getStyle('G3')->getFont()->setBold(true)->setSize(14)->setColor(new Color('0F172A'));
                $sheet->getStyle('G3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                $sheet->mergeCells('G4:I4');
                $sheet->setCellValue('G4', 'Date: ' . now()->format('M d, Y') . ' | Ref: AP-EOD');
                $sheet->getStyle('G4')->getFont()->setSize(8.5)->setColor(new Color('64748B'));
                $sheet->getStyle('G4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // 4. Header Top/Bottom Separator Line (Row 6)
                $sheet->mergeCells('A6:I6');
                $sheet->getRowDimension(6)->setRowHeight(3);
                $sheet->getStyle('A6:I6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('00A2FF');

                // 5. Grid Styling & Text Wrapping
                if ($highestRow >= 9) {
                    $dataRange = 'A9:I' . $highestRow;
                    
                    // Borders
                    $sheet->getStyle($dataRange)->getBorders()->getHorizontal()->setBorderStyle(Border::BORDER_THIN);
                    $sheet->getStyle($dataRange)->getBorders()->getHorizontal()->getColor()->setARGB('E2E8F0');
                    
                    // Alignments
                    $sheet->getStyle($dataRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                    $sheet->getStyle('A9:C' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    $sheet->getStyle('D9:D' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('E9:H' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    $sheet->getStyle('I9:I' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    
                    // Zebra striping
                    for ($row = 9; $row <= $highestRow; $row++) {
                        if ($row % 2 === 0) {
                            $sheet->getStyle('A' . $row . ':I' . $row)->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()->setARGB('F8FAFC');
                        }
                    }
                }

                // Wrap text on everything
                $sheet->getStyle('A8:I' . $highestRow)->getAlignment()->setWrapText(true);

                // 6. Draw Footer Layout (2 rows after last data row)
                $footerLineRow = $highestRow + 2;
                $footerTextRow = $highestRow + 3;

                // Thin purple line
                $sheet->mergeCells('A' . $footerLineRow . ':I' . $footerLineRow);
                $sheet->getRowDimension($footerLineRow)->setRowHeight(3);
                $sheet->getStyle('A' . $footerLineRow . ':I' . $footerLineRow)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('8A2BE2');

                // Footer Left
                $sheet->mergeCells('A' . $footerTextRow . ':C' . $footerTextRow);
                $sheet->setCellValue('A' . $footerTextRow, 'Committed Plans, Established Results');
                $sheet->getStyle('A' . $footerTextRow)->getFont()->setItalic(true)->setSize(8.5)->setColor(new Color('64748B'));
                $sheet->getStyle('A' . $footerTextRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // Footer Center
                $sheet->mergeCells('D' . $footerTextRow . ':F' . $footerTextRow);
                $sheet->setCellValue('D' . $footerTextRow, 'AnchorPlan · anchorplan.com · jonel@anchorplan.com');
                $sheet->getStyle('D' . $footerTextRow)->getFont()->setSize(8.5)->setColor(new Color('64748B'));
                $sheet->getStyle('D' . $footerTextRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Footer Right
                $sheet->mergeCells('G' . $footerTextRow . ':I' . $footerTextRow);
                $sheet->setCellValue('G' . $footerTextRow, 'Page 1');
                $sheet->getStyle('G' . $footerTextRow)->getFont()->setBold(true)->setSize(8.5)->setColor(new Color('8A2BE2'));
                $sheet->getStyle('G' . $footerTextRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }
        ];
    }
}


