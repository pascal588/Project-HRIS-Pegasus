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
    protected $rawScores;
    protected $totals;
    protected $year;
    protected $sortedMonths;
    protected $groupedData;
    protected $syncWithBlade;

    public function __construct(Employee $employee, array $pivotedData, $year)
    {
        $this->employee = $employee;
        $this->points = $pivotedData['points'] ?? [];
        $this->months = $pivotedData['months'] ?? [];
        $this->scores = $pivotedData['scores'] ?? [];
        $this->rawScores = $pivotedData['raw_scores'] ?? [];
        $this->totals = $pivotedData['totals'] ?? [];
        $this->sortedMonths = $pivotedData['sorted_months'] ?? [];
        $this->groupedData = $pivotedData['grouped_data'] ?? [];
        $this->syncWithBlade = $pivotedData['sync_with_blade'] ?? false;
        $this->year = $year;

        Log::info("MonthlyKpiExport SYNC WITH BLADE loaded", [
            'sub_aspek_count' => count($this->points),
            'months_count' => count($this->months),
            'sync_with_blade' => $this->syncWithBlade,
            'using_kontribusi' => true
        ]);
    }

public function headings(): array
{
    // HEADING dengan bulan-bulan di header
    $headings = ['Aspek Utama', 'Sub Aspek', 'Bobot %'];
    
    // Tambahkan semua bulan
    foreach ($this->sortedMonths as $monthKey) {
        $headings[] = $this->months[$monthKey] ?? $monthKey;
    }
    
    // Tambahkan kolom TOTAL di akhir
    $headings[] = 'TOTAL';
    
    return $headings;
}

    public function title(): string
    {
        return 'Rekap KPI ' . $this->year;
    }

public function array(): array
{
    Log::info("Generating array data HORIZONTAL BULAN DI HEADER");
    
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

                // Nilai KONTRIBUSI untuk setiap bulan
                $totalSubAspek = 0;
                foreach ($this->sortedMonths as $monthKey) {
                    $kontribusi = $this->scores[$fullName][$monthKey] ?? 0;
                    $row[] = round($kontribusi, 2);
                    $totalSubAspek += $kontribusi;
                }

                // Tambahkan TOTAL untuk sub aspek ini
                $row[] = round($totalSubAspek, 2);

                $exportData[] = $row;
            }
        }
        
        // Tambahkan baris TOTAL ASPEK
        $totalAspekRow = [];
        $totalAspekRow[] = $aspekUtama; // Aspek Utama
        $totalAspekRow[] = 'TOTAL ASPEK'; // Sub Aspek  
        $totalAspekRow[] = ''; // Bobot kosong

        $totalAspek = 0;
        foreach ($this->sortedMonths as $monthKey) {
            $monthTotal = 0;
            foreach ($subAspekList as $fullName) {
                if (isset($this->scores[$fullName][$monthKey])) {
                    $monthTotal += $this->scores[$fullName][$monthKey];
                }
            }
            $totalAspekRow[] = round($monthTotal, 2);
            $totalAspek += $monthTotal;
        }

        // TOTAL untuk aspek ini
        $totalAspekRow[] = round($totalAspek, 2);
        $exportData[] = $totalAspekRow;
        
        // Tambahkan baris kosong antar group
        $exportData[] = [];
    }

    // Baris kosong sebagai pemisah sebelum total
    $exportData[] = [];

    // Baris TOTAL KESELURUHAN - DIKALI 10
    $totalRow = ['TOTAL KESELURUHAN', '', ''];
    
    $totalKeseluruhan = 0;
    foreach ($this->sortedMonths as $monthKey) {
        $total = $this->totals[$monthKey] ?? 0;
        $totalAkhir = $total * 10; // ⚠️ DIKALI 10
        $totalRow[] = round($totalAkhir, 2);
        $totalKeseluruhan += $totalAkhir;
    }

    // TOTAL KESELURUHAN
    $totalRow[] = round($totalKeseluruhan, 2);
    $exportData[] = $totalRow;

    Log::info("Export data HORIZONTAL BULAN DI HEADER generated", [
        'total_rows' => count($exportData),
        'total_keseluruhan' => $totalKeseluruhan
    ]);

    return $exportData;
}

public function styles(Worksheet $sheet)
{
    $totalRows = count($this->points) + count($this->groupedData) + 10;
    
    return [
        // Style untuk header tabel (row 7) - header bulan
        7 => [
            'font' => ['bold' => true, 'size' => 12],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E6E6FA']
            ]
        ],
        
        // Style untuk row TOTAL ASPEK
        'A' . $totalRows => [
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F0F8FF']
            ]
        ],

        // Style untuk row TOTAL KESELURUHAN
        'A' . ($totalRows + 2) => [
            'font' => ['bold' => true, 'size' => 12],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E6E6FA']
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

    // Header utama - BULAN DI HEADER
    $sheet->setCellValue('A1', 'REKAPITULASI KPI - BULAN DI HEADER (TOTAL ×10)');
    $lastColumn = $this->getColumnName(count($this->sortedMonths) + 4); // +4 untuk Aspek, Sub Aspek, Bobot, TOTAL
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
    $sheet->setCellValue('D5', 'FORMAT');
    $sheet->setCellValue('E5', ': Bulan di Header');

    // Style untuk informasi
    $sheet->getStyle('A3:A5')->getFont()->setBold(true);
    $sheet->getStyle('D3:D5')->getFont()->setBold(true);

    // Auto-size columns
    foreach (range('A', $lastColumn) as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
}

private function applyBorders(AfterSheet $event)
{
    $sheet = $event->sheet->getDelegate();
    $lastColumn = $this->getColumnName(count($this->sortedMonths) + 4);
    $lastRow = count($this->points) + count($this->groupedData) + 10;

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
    $lastColumn = $this->getColumnName(count($this->sortedMonths) + 4);
    $lastRow = count($this->points) + count($this->groupedData) + 10;

    // Format angka untuk kolom bobot (kolom C)
    $sheet->getStyle('C8:C' . $lastRow)
          ->getNumberFormat()
          ->setFormatCode('#,##0.00" %"');

    // Format angka untuk kolom nilai (kolom D sampai TOTAL)
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
    
    // Beri background berbeda untuk setiap group aspek utama
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
        
        // Tambahkan row TOTAL ASPEK
        $row++;
        
        // Beri background abu-abu muda untuk group
        $groupRange = 'A' . $groupStartRow . ':' . $this->getColumnName(count($this->sortedMonths) + 4) . ($row - 1);
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