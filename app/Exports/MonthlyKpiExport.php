<?php

namespace App\Exports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
// ✅ DITAMBAHKAN: Import class yang dibutuhkan
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

// ✅ DIMODIFIKASI: Tambahkan WithEvents dan WithCustomStartCell
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
    
    // ✅ DITAMBAHKAN: Method untuk memberitahu tabel agar mulai di sel A8
    public function startCell(): string
    {
        return 'A8';
    }

    // ✅ DITAMBAHKAN: Method untuk menambahkan data sebelum tabel utama ditulis
    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function(BeforeSheet $event) {
                // Judul Utama
                $event->sheet->setCellValue('A1', 'REKAP KPI KARYAWAN');
                $event->sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $event->sheet->mergeCells('A1:D1');

                // Detail Karyawan
                $event->sheet->setCellValue('A3', 'Nama');
                $event->sheet->setCellValue('B3', ': ' . $this->employee->name);
                
                $event->sheet->setCellValue('A4', 'ID Karyawan');
                $event->sheet->setCellValue('B4', ': ' . $this->employee->id_karyawan);
                
                $event->sheet->setCellValue('A5', 'Jabatan');
                // Menggunakan null coalescing operator untuk keamanan jika jabatan tidak ada
                $event->sheet->setCellValue('B5', ': ' . ($this->employee->position->nama_jabatan ?? 'N/A'));

                $event->sheet->setCellValue('A6', 'Divisi');
                $event->sheet->setCellValue('B6', ': ' . ($this->employee->division->nama_divisi ?? 'N/A'));
                
                // Style untuk label
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
        // Posisi baris header sekarang ada di baris 8 (karena startCell A8)
        $headerRow = 8;
        // Hitung posisi baris footer
        $footerRowNumber = $headerRow + count($this->points) + 2;

        return [
            // Style baris header (sekarang di baris 8)
            $headerRow => ['font' => ['bold' => true]],
            // Style baris SCORE AKHIR
            $footerRowNumber => ['font' => ['bold' => true]],
        ];
    }
}