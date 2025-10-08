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
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\Log;

class MonthlyKpiExport implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithStyles, WithEvents
{
    protected $employee;
    protected $points;
    protected $months;
    protected $scores;
    protected $totals;
    protected $year;
    protected $sortedMonths;
    protected $groupedData;

    public function __construct(Employee $employee, array $pivotedData, $year)
    {
        $this->employee = $employee;
        $this->points = $pivotedData['points'] ?? [];
        $this->months = $pivotedData['months'] ?? [];
        $this->scores = $pivotedData['scores'] ?? [];
        $this->totals = $pivotedData['totals'] ?? [];
        $this->sortedMonths = $pivotedData['sorted_months'] ?? [];
        $this->groupedData = $pivotedData['grouped_data'] ?? [];
        $this->year = $year;

        Log::info("MonthlyKpiExport WITH SUB ASPEK loaded", [
            'sub_aspek_count' => count($this->points),
            'months_count' => count($this->months)
        ]);
    }

    public function headings(): array
    {
        // HEADING BARU: Aspek Utama, Sub Aspek, Bobot %, kemudian bulan-bulan
        $headings = ['Aspek Utama', 'Sub Aspek', 'Bobot %'];
        
        foreach ($this->sortedMonths as $monthKey) {
            $headings[] = $this->months[$monthKey] ?? $monthKey;
        }
        
        return $headings;
    }

    public function title(): string
    {
        return 'Rekap KPI Detail ' . $this->year;
    }

    public function array(): array
    {
        Log::info("Generating array data with sub-aspek");
        
        $exportData = [];

        // Data untuk setiap kelompok aspek utama
        foreach ($this->groupedData as $aspekUtama => $subAspekList) {
            
            // Data untuk setiap sub aspek dalam group ini
            foreach ($subAspekList as $fullName) {
                if (isset($this->points[$fullName])) {
                    $subAspekData = $this->points[$fullName];
                    $row = [];
                    $row[] = $aspekUtama; // Aspek Utama
                    $row[] = $subAspekData['sub_aspek_name']; // Sub Aspek
                    $row[] = $subAspekData['bobot']; // Bobot %

                    // Nilai untuk setiap bulan
                    foreach ($this->sortedMonths as $monthKey) {
                        $score = $this->scores[$fullName][$monthKey] ?? 0;
                        $row[] = round($score, 2);
                    }

                    $exportData[] = $row;
                }
            }
            
            // Tambahkan baris kosong antar group (biar rapi)
            $exportData[] = [];
        }

        // Baris kosong sebagai pemisah sebelum total
        $exportData[] = [];

        // Baris TOTAL
        $totalRow = ['TOTAL', '', ''];
        foreach ($this->sortedMonths as $monthKey) {
            $total = $this->totals[$monthKey] ?? 0;
            $totalRow[] = round($total, 2);
        }
        $exportData[] = $totalRow;

        Log::info("Export data with sub-aspek generated", [
            'total_rows' => count($exportData)
        ]);

        return $exportData;
    }

    public function styles(Worksheet $sheet)
    {
        $totalRows = count($this->points) + count($this->groupedData) + 4; // +4 untuk header dan total
        
        return [
            // Style untuk header tabel (row 7)
            7 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E6E6FA']
                ]
            ],
            
            // Style untuk row terakhir (total)
            $totalRows => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F0F8FF']
                ]
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $this->addCustomHeader($event);
                $this->applyBorders($event);
                $this->applyNumberFormatting($event);
                $this->applyGroupStyling($event);
            },
        ];
    }

    private function addCustomHeader(AfterSheet $event)
    {
        $sheet = $event->sheet->getDelegate();

        // Informasi karyawan
        $divisionName = 'N/A';
        $positionName = 'N/A';
        
        if ($this->employee->roles->isNotEmpty()) {
            $firstRole = $this->employee->roles->first();
            $positionName = $firstRole->nama_jabatan ?? 'N/A';
            $divisionName = $firstRole->division->nama_divisi ?? 'N/A';
        }

        // Insert rows untuk header
        $sheet->insertNewRowBefore(1, 6);

        // Header utama - DENGAN SUB ASPEK
        $sheet->setCellValue('A1', 'REKAPITULASI DETAIL KPI KARYAWAN - DENGAN SUB ASPEK');
        $lastColumn = $this->getColumnName(count($this->sortedMonths) + 3); // +3 karena sekarang ada 3 kolom awal
        $sheet->mergeCells('A1:' . $lastColumn . '1');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);

        // Informasi karyawan
        $sheet->setCellValue('A3', 'NAMA');
        $sheet->setCellValue('B3', ': ' . $this->employee->nama);
        $sheet->setCellValue('D3', 'DIVISI');
        $sheet->setCellValue('E3', ': ' . $divisionName);

        $sheet->setCellValue('A4', 'ID KARYAWAN');
        $sheet->setCellValue('B4', ': ' . $this->employee->id_karyawan);
        $sheet->setCellValue('D4', 'JABATAN');
        $sheet->setCellValue('E4', ': ' . $positionName);

        $sheet->setCellValue('A5', 'TAHUN');
        $sheet->setCellValue('B5', ': ' . $this->year);

        // Style untuk informasi
        $sheet->getStyle('A3:A5')->getFont()->setBold(true);
        $sheet->getStyle('D3:D4')->getFont()->setBold(true);

        // Auto-size columns
        foreach (range('A', $lastColumn) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    private function applyBorders(AfterSheet $event)
    {
        $sheet = $event->sheet->getDelegate();
        $lastColumn = $this->getColumnName(count($this->sortedMonths) + 3); // +3 untuk kolom baru
        $lastRow = count($this->points) + count($this->groupedData) + 8;

        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];

        $sheet->getStyle('A7:' . $lastColumn . $lastRow)->applyFromArray($styleArray);
    }

    private function applyNumberFormatting(AfterSheet $event)
    {
        $sheet = $event->sheet->getDelegate();
        $lastColumn = $this->getColumnName(count($this->sortedMonths) + 3);
        $lastRow = count($this->points) + count($this->groupedData) + 8;

        // Format angka untuk kolom bobot (kolom C)
        $sheet->getStyle('C8:C' . $lastRow)
              ->getNumberFormat()
              ->setFormatCode('#,##0.00" %"');

        // Format angka untuk kolom nilai (kolom D sampai terakhir)
        $scoreColumns = range('D', $lastColumn);
        
        foreach ($scoreColumns as $column) {
            $sheet->getStyle($column . '8:' . $column . $lastRow)
                  ->getNumberFormat()
                  ->setFormatCode('#,##0.00');
        }
    }

    private function applyGroupStyling(AfterSheet $event)
    {
        $sheet = $event->sheet->getDelegate();
        
        // Beri background berbeda untuk setiap group aspek utama (biar kelihatan groupingnya)
        $row = 8;
        $currentGroup = '';
        
        foreach ($this->groupedData as $aspekUtama => $subAspekList) {
            $groupStartRow = $row;
            
            foreach ($subAspekList as $fullName) {
                if ($currentGroup !== $aspekUtama) {
                    $currentGroup = $aspekUtama;
                }
                $row++;
            }
            
            // Beri background abu-abu muda untuk group
            $groupRange = 'A' . $groupStartRow . ':' . $this->getColumnName(count($this->sortedMonths) + 3) . ($row - 1);
            $sheet->getStyle($groupRange)->getFill()
                  ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                  ->setStartColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFEFEFEF'));
            
            $row++; // untuk baris kosong
        }
    }

    private function getColumnName($index)
    {
        $columns = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
        
        if ($index < 26) {
            return $columns[$index];
        } else {
            $firstIndex = floor($index / 26) - 1;
            $secondIndex = $index % 26;
            return $columns[$firstIndex] . $columns[$secondIndex];
        }
    }
}