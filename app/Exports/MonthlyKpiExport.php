<?php

namespace App\Exports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

// ✅ Pastikan nama class ini sesuai dengan nama file
class MonthlyKpiExport implements FromArray, WithHeadings, ShouldAutoSize, WithTitle, WithStyles, WithEvents, WithCustomStartCell
{
    protected $employee;
    protected $points;
    protected $months;
    protected $scores;
    protected $totals;

    public function __construct(Employee $employee, array $pivotedData)
    {
        $this->employee = $employee;
        $this->points = $pivotedData['points'];
        $this->months = $pivotedData['months'];
        $this->scores = $pivotedData['scores'];
        $this->totals = $pivotedData['totals'];
    }

    public function startCell(): string
    {
        return 'A8';
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                $event->sheet->setCellValue('A1', 'REKAP KPI KARYAWAN');
                $event->sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $event->sheet->mergeCells('A1:D1');
                $event->sheet->setCellValue('A3', 'Nama');
                $event->sheet->setCellValue('B3', ': ' . $this->employee->nama);
                $event->sheet->setCellValue('A4', 'ID Karyawan');
                $event->sheet->setCellValue('B4', ': ' . $this->employee->id_karyawan);
                $event->sheet->setCellValue('A5', 'Jabatan');
                $event->sheet->setCellValue('B5', ': ' . ($this->employee->position->nama_jabatan ?? 'N/A'));
                $event->sheet->setCellValue('A6', 'Divisi');
                $event->sheet->setCellValue('B6', ': ' . ($this->employee->division->nama_divisi ?? 'N/A'));
                $event->sheet->getStyle('A3:A6')->getFont()->setBold(true);
            },
        ];
    }

    public function title(): string
    {
        return 'Rekap KPI - ' . $this->employee->name;
    }

    public function headings(): array
    {
        return array_merge(['Aspek KPI', 'Bobot %'], $this->months);
    }

    public function array(): array
    {
        $exportData = [];
        foreach ($this->points as $pointName => $weight) {
            $row = [];
            $row[] = $pointName;
            $row[] = $weight;
            foreach ($this->months as $month) {
                $score = $this->scores[$pointName][$month] ?? 0;
                $row[] = $score;
            }
            $exportData[] = $row;
        }
        $exportData[] = [];
        $footerRow = ['SCORE AKHIR', ''];
        foreach ($this->months as $month) {
            $footerRow[] = round($this->totals[$month] ?? 0, 2);
        }
        $exportData[] = $footerRow;
        return $exportData;
    }

    public function styles(Worksheet $sheet)
    {
        $headerRow = 8;
        $footerRowNumber = $headerRow + count($this->points) + 2;
        return [
            $headerRow => ['font' => ['bold' => true]],
            $footerRowNumber => ['font' => ['bold' => true]],
        ];
    }
}