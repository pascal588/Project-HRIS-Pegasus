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
use Illuminate\Support\Facades\Log;

class MonthlyKpiExport implements FromArray, WithHeadings, ShouldAutoSize, WithTitle, WithStyles, WithEvents, WithCustomStartCell
{
    protected $employee;
    protected $points;
    protected $months;
    protected $scores;
    protected $totals;
    protected $year;

    public function __construct(Employee $employee, array $pivotedData, $year)
    {
        \Log::info("=== CONSTRUCTOR MONTHLY KPI EXPORT ===");
        \Log::info("Employee: " . $employee->nama);
        \Log::info("Year: " . $year);
        \Log::info("Pivoted Data Structure:", array_keys($pivotedData));

        $this->employee = $employee;
        $this->points = $pivotedData['points'] ?? [];
        $this->months = $pivotedData['months'] ?? [];
        $this->scores = $pivotedData['scores'] ?? [];
        $this->totals = $pivotedData['totals'] ?? [];
        $this->year = $year;

        \Log::info("Data yang diterima:", [
            'points_count' => count($this->points),
            'months_count' => count($this->months),
            'scores_count' => count($this->scores),
            'totals_count' => count($this->totals),
            'points_sample' => array_slice($this->points, 0, 3),
            'months_sample' => $this->months,
            'totals_sample' => $this->totals,
        ]);
    }

    public function startCell(): string
    {
        return 'A8';
    }

    public function registerEvents(): array
{
    return [
        BeforeSheet::class => function (BeforeSheet $event) {
            \Log::info("=== BEFORE SHEET EVENT ===");

            $event->sheet->setCellValue('A1', 'REKAPITULASI KPI KARYAWAN');
            $event->sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
            $event->sheet->mergeCells('A1:F1');
            $event->sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

            // INFORMASI KARYAWAN - DENGAN CARA YANG BENAR
            $divisionName = 'N/A';
            $positionName = 'N/A';
            
            // AMBIL DATA DARI RELASI ROLES
            if ($this->employee->roles->isNotEmpty()) {
                $firstRole = $this->employee->roles->first();
                $positionName = $firstRole->nama_jabatan ?? 'N/A';
                $divisionName = $firstRole->division->nama_divisi ?? 'N/A';
                
                \Log::info("Data Role & Division:", [
                    'jabatan' => $positionName,
                    'divisi' => $divisionName
                ]);
            }

            $event->sheet->setCellValue('A3', 'NAMA');
            $event->sheet->setCellValue('B3', ': ' . $this->employee->nama);

            $event->sheet->setCellValue('A4', 'ID KARYAWAN');
            $event->sheet->setCellValue('B4', ': ' . $this->employee->id_karyawan);

            $event->sheet->setCellValue('C3', 'DIVISI');
            $event->sheet->setCellValue('D3', ': ' . $divisionName);

            $event->sheet->setCellValue('C4', 'JABATAN');
            $event->sheet->setCellValue('D4', ': ' . $positionName);

            $event->sheet->setCellValue('E3', 'TAHUN');
            $event->sheet->setCellValue('F3', ': ' . $this->year);

            // Style untuk header informasi
            $event->sheet->getStyle('A3:A4')->getFont()->setBold(true);
            $event->sheet->getStyle('C3:C4')->getFont()->setBold(true);
            $event->sheet->getStyle('E3')->getFont()->setBold(true);
        },
    ];
}

    public function title(): string
    {
        return 'Rekap KPI ' . $this->year;
    }

    public function headings(): array
    {
        \Log::info("=== HEADINGS ===");
        \Log::info("Months data:", $this->months);

        if (empty($this->months)) {
            \Log::warning("HEADINGS: Months kosong, return default");
            return ['Aspek KPI', 'Bobot %', 'Tidak Ada Data'];
        }

        $headings = array_merge(['Aspek KPI', 'Bobot %'], $this->months);
        \Log::info("Final headings:", $headings);

        return $headings;
    }

    public function array(): array
    {
        \Log::info("=== ARRAY METHOD ===");
        \Log::info("Points count: " . count($this->points));
        \Log::info("Months count: " . count($this->months));

        $exportData = [];

        // Cek jika data kosong
        if (empty($this->points) || empty($this->months)) {
            \Log::warning("DATA KOSONG - Points atau Months kosong");
            return [
                ['⚠️ DEBUG: DATA KOSONG'],
                ['Points: ' . count($this->points)],
                ['Months: ' . count($this->months)],
                ['Scores: ' . count($this->scores)],
                ['Totals: ' . count($this->totals)],
                [''],
                ['Silakan cek log Laravel untuk detail'],
            ];
        }

        \Log::info("Memproses data export...");
        \Log::info("Points sample:", array_slice($this->points, 0, 3));

        // Data untuk setiap sub-aspek
        foreach ($this->points as $pointName => $weight) {
            $row = [];
            $row[] = $pointName; // Nama sub-aspek
            $row[] = $weight;    // Bobot

            // Nilai untuk setiap bulan
            foreach ($this->months as $month) {
                $score = $this->scores[$pointName][$month] ?? 0;
                $row[] = round($score, 2);
            }

            $exportData[] = $row;
        }

        \Log::info("Jumlah rows data: " . count($exportData));

        // Baris kosong sebagai pemisah
        $exportData[] = [];

        // Baris TOTAL
        $totalRow = ['TOTAL SCORE', ''];
        foreach ($this->months as $month) {
            $total = $this->totals[$month] ?? 0;
            $totalRow[] = round($total, 2);
        }
        $exportData[] = $totalRow;

        \Log::info("Final export data count: " . count($exportData));
        \Log::info("Sample export data:", array_slice($exportData, 0, 2));

        return $exportData;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 16]],
            8 => ['font' => ['bold' => true]],
        ];
    }
}
