    public function getScoresByYear($year)
    {
        try {
            // Cari semua periode di tahun tersebut
            $periods = Period::where('kpi_published', true)
                ->whereYear('tanggal_mulai', $year)
                ->pluck('id_periode');

            if ($periods->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'Tidak ada data KPI untuk tahun ' . $year
                ]);
            }

            // Ambil data KPI untuk semua periode di tahun tersebut
            $employeeScores = DB::table('kpis_has_employees')
                ->whereIn('periode_id', $periods)
                ->join('employees', 'kpis_has_employees.employees_id_karyawan', '=', 'employees.id_karyawan')
                ->select(
                    'employees.id_karyawan',
                    'employees.nama',
                    'employees.status',
                    'employees.foto',
                    DB::raw('AVG(kpis_has_employees.nilai_akhir) as avg_score') // Rata-rata score per tahun
                )
                ->where('employees.status', 'Aktif')
                ->groupBy('employees.id_karyawan', 'employees.nama', 'employees.status', 'employees.foto')
                ->orderBy('avg_score', 'desc')
                ->get();

            $formattedData = [];

            foreach ($employeeScores as $emp) {
                $employeeDetails = Employee::with(['roles.division'])->find($emp->id_karyawan);
                $division = '-';
                $position = '-';
                
                if ($employeeDetails && $employeeDetails->roles->count() > 0) {
                    $division = $employeeDetails->roles[0]->division->nama_divisi ?? '-';
                    $position = $employeeDetails->roles[0]->nama_jabatan ?? '-';
                }

                $formattedData[] = [
                'id_karyawan' => $emp->id_karyawan,
                'nama' => $emp->nama,
                'status' => $emp->status,
                'score' => floatval($emp->avg_score),
                'period' => 'Tahun ' . $year,
                'period_month' => 'Yearly',
                'period_month_number' => 0,
                'period_year' => $year,
                'photo' => $emp->foto ?? 'assets/images/profile_av.png',
                'division' => $division,
                'position' => $position,
                'period_id' => 'yearly_' . $year,
                // ⚠️ TAMBAH: Info bulan untuk yearly report
                'monthly_breakdown' => $this->getMonthlyBreakdown($emp->id_karyawan, $year)
            ];
            }

            return response()->json([
                'success' => true,
                'data' => $formattedData,
                'debug_info' => [
                    'year' => $year,
                    'periods_count' => $periods->count(),
                    'total_employees' => count($formattedData)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch KPI data by year: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch KPI data: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getMonthlyBreakdown($employeeId, $year)
{
    $monthlyData = [];
    
    for ($month = 1; $month <= 12; $month++) {
        $periods = Period::where('kpi_published', true)
            ->whereYear('tanggal_mulai', $year)
            ->whereMonth('tanggal_mulai', $month)
            ->pluck('id_periode');
            
        if ($periods->isNotEmpty()) {
            $monthlyScore = DB::table('kpis_has_employees')
                ->whereIn('periode_id', $periods)
                ->where('employees_id_karyawan', $employeeId)
                ->avg('nilai_akhir');
                
            $monthlyData[] = [
                'month' => $month,
                'month_name' => date('F', mktime(0, 0, 0, $month, 1)),
                'score' => $monthlyScore ? floatval($monthlyScore) : null
            ];
        }
    }
    
    return $monthlyData;
}
    
    // Private methods
    private function calculateAttendanceScore($employeeId, $periodeId, $pointBobot, $attendanceConfig = null)
    {
        try {
            // Ambil data absensi
            $attendances = Attendance::where('employee_id', $employeeId)
                ->where('periode_id', $periodeId)
                ->get();

            // Hitung berdasarkan jenis absensi
            $hadir = $attendances->where('status', 'Present at workday (PW)')->count();
            $sakit = $attendances->where('status', 'Sick (S)')->count();
            $izin = $attendances->where('status', 'Permission (I)')->count();
            $mangkir = $attendances->where('status', 'Absent (A)')->count();
            $terlambat = $attendances->where('late', '>', 0)->count();

            // Total hari kerja dalam periode
            $totalDays = $attendances->count();

            // ⚠️ RUMUS DINAMIS - DITERIMA DARI FRONTEND
            $config = $attendanceConfig ?? [
                'hadir_multiplier' => 3,
                'sakit_multiplier' => 0,
                'izin_multiplier' => 0,
                'mangkir_multiplier' => -3,
                'terlambat_multiplier' => -2,
                'workday_multiplier' => 2
            ];

            // Hitung total point (x)
            $totalPoints = ($hadir * $config['hadir_multiplier']) +
                ($sakit * $config['sakit_multiplier']) +
                ($izin * $config['izin_multiplier']) +
                ($mangkir * $config['mangkir_multiplier']) +
                ($terlambat * $config['terlambat_multiplier']);

            // Hitung total point maksimal (y)
            $maxPoints = $totalDays * $config['workday_multiplier'];

            // Hitung persentase kehadiran
            $attendancePercent = $maxPoints > 0 ? ($totalPoints / $maxPoints) * 100 : 0;

            // ⚠️ KONVERSI KE SKALA 0-100 (sesuai rumus Excel)
            if ($attendancePercent >= 100) {
                $score = 10;
            } elseif ($attendancePercent >= 90) {
                $score = 8;
            } elseif ($attendancePercent >= 80) {
                $score = 6;
            } elseif ($attendancePercent >= 65) {
                $score = 4;
            } elseif ($attendancePercent >= 50) {
                $score = 2;
            } else {
                $score = 0;
            }

            return $score;
        } catch (\Exception $e) {
            Log::error('Error calculating attendance score: ' . $e->getMessage());
            return 0;
        }
    }
    private function saveAttendanceScore($employeeId, $periodeId, $pointId, $finalScore)
    {
        try {
            Log::info("=== START SAVE ATTENDANCE SCORE ===", [
                'employee_id' => $employeeId,
                'periode_id' => $periodeId,
                'point_id' => $pointId,
                'final_score' => $finalScore
            ]);

            $point = KpiPoint::find($pointId);
            if (!$point) {
                Log::error("KPI Point not found: {$pointId}");
                return false;
            }

            $kpiId = $point->kpis_id_kpi;
            $originalBobot = $point->bobot; // Simpan bobot asli

            Log::info("Found KPI Point:", [
                'point_name' => $point->nama,
                'kpi_id' => $kpiId,
                'point_bobot' => $originalBobot
            ]);

            // Cari atau buat record kpis_has_employees
            $kpisHasEmployeeId = DB::table('kpis_has_employees')
                ->where('kpis_id_kpi', $kpiId)
                ->where('employees_id_karyawan', $employeeId)
                ->where('periode_id', $periodeId)
                ->value('id');

            if (!$kpisHasEmployeeId) {
                $kpisHasEmployeeId = DB::table('kpis_has_employees')->insertGetId([
                    'kpis_id_kpi' => $kpiId,
                    'employees_id_karyawan' => $employeeId,
                    'periode_id' => $periodeId,
                    'tahun' => date('Y'),
                    'bulan' => date('m'),
                    'nilai_akhir' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // ⚠️ PERBAIKAN: Simpan nilai absensi di kolom nilai_absensi, bobot tetap asli
            $existingRecord = DB::table('kpi_points_has_employee')
                ->where('kpis_has_employee_id', $kpisHasEmployeeId)
                ->where('kpi_point_id', $pointId)
                ->first();

            if ($existingRecord) {
                $updated = DB::table('kpi_points_has_employee')
                    ->where('kpis_has_employee_id', $kpisHasEmployeeId)
                    ->where('kpi_point_id', $pointId)
                    ->update([
                        'bobot' => $originalBobot, // ✅ Bobot asli
                        'nilai_absensi' => $finalScore, // ✅ Nilai absensi di kolom baru
                        'updated_at' => now(),
                    ]);

                Log::info("Updated kpi_points_has_employee:", [
                    'updated' => $updated,
                    'kpis_has_employee_id' => $kpisHasEmployeeId,
                    'point_id' => $pointId,
                    'bobot' => $originalBobot,
                    'nilai_absensi' => $finalScore
                ]);
            } else {
                $inserted = DB::table('kpi_points_has_employee')->insert([
                    'kpis_has_employee_id' => $kpisHasEmployeeId,
                    'kpi_point_id' => $pointId,
                    'bobot' => $originalBobot, // ✅ Bobot asli
                    'nilai_absensi' => $finalScore, // ✅ Nilai absensi di kolom baru
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                Log::info("Inserted kpi_points_has_employee:", [
                    'inserted' => $inserted,
                    'kpis_has_employee_id' => $kpisHasEmployeeId,
                    'point_id' => $pointId,
                    'bobot' => $originalBobot,
                    'nilai_absensi' => $finalScore
                ]);
            }

            // Hitung ulang nilai akhir KPI
            $this->calculateSingleKpiFinalScore($kpiId, $employeeId, $periodeId);

            Log::info("Attendance score saved in 'nilai_absensi' column, bobot preserved");

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to save attendance score: ' . $e->getMessage());
            return false;
        }
    }
    
private function calculateSingleKpiFinalScore($kpiId, $employeeId, $periodeId)
{
    try {
        $kpi = Kpi::with(['points.questions'])->find($kpiId);
        if (!$kpi) return 0;

        $totalAspekScore = 0;

        foreach ($kpi->points as $point) {
            $pointScore = 0;
            $isAbsensi = stripos($point->nama, 'absensi') !== false;

            if ($isAbsensi) {
                // Ambil nilai_absensi (0-100)
                $kpisHasEmployeeId = DB::table('kpis_has_employees')
                    ->where('kpis_id_kpi', $kpiId)
                    ->where('employees_id_karyawan', $employeeId)
                    ->where('periode_id', $periodeId)
                    ->value('id');

                if ($kpisHasEmployeeId) {
                    $pointRecord = DB::table('kpi_points_has_employee')
                        ->where('kpis_has_employee_id', $kpisHasEmployeeId)
                        ->where('kpi_point_id', $point->id_point)
                        ->first();

                    $pointScore = $pointRecord->nilai_absensi ?? 0;
                }
            } else {
                // Untuk non-absensi
                $pointTotal = 0;
                $answeredQuestions = 0;

                foreach ($point->questions as $q) {
                    $score = KpiQuestionHasEmployee::where('employees_id_karyawan', $employeeId)
                        ->where('kpi_question_id_question', $q->id_question)
                        ->where('periode_id', $periodeId)
                        ->first();

                    if ($score && $score->nilai !== null) {
                        // Konversi 1-4 ke 0-100
                        $questionScore = (($score->nilai - 1) / 3) * 100;
                        $pointTotal += $questionScore;
                        $answeredQuestions++;
                    }
                }

                $pointScore = $answeredQuestions > 0 ? ($pointTotal / $answeredQuestions) : 0;
            }

            $pointBobot = floatval($point->bobot) ?? 0;

            // ⚠️ RUMUS YANG BENAR: Kontribusi = (Nilai Point × Bobot Point) / 100
            $pointContribution = ($pointScore * $pointBobot) / 100;
            $totalAspekScore += $pointContribution;

            Log::info("Point calculation:", [
                'point_name' => $point->nama,
                'point_score' => $pointScore,
                'point_bobot' => $pointBobot,
                'contribution' => $pointContribution,
                'formula' => "({$pointScore} × {$pointBobot}) / 100 = {$pointContribution}"
            ]);
        }

        // ⚠️ PERBAIKAN: Nilai akhir KPI = total kontribusi semua point
        $finalAspekScore = $totalAspekScore;

        // Update database
        DB::table('kpis_has_employees')
            ->where('kpis_id_kpi', $kpiId)
            ->where('employees_id_karyawan', $employeeId)
            ->where('periode_id', $periodeId)
            ->update(['nilai_akhir' => $finalAspekScore]);

        Log::info("Final KPI Score for {$kpi->nama}:", [
            'total_contribution' => $finalAspekScore,
            'formula' => 'Σ(Point Score × Point Bobot) / 100'
        ]);

        return $finalAspekScore;
    } catch (\Exception $e) {
        Log::error('Error calculating KPI score: ' . $e->getMessage());
        return 0;
    }
}

    private function calculateAllFinalScores($employeeId, $periodeId)
    {
        try {
            Log::info("=== CALCULATE ALL FINAL SCORES ===", [
                'employee_id' => $employeeId,
                'periode_id' => $periodeId
            ]);

            $employee = Employee::with(['roles.division'])->find($employeeId);
            $divisionId = null;

            if ($employee->roles && count($employee->roles) > 0) {
                $divisionId = $employee->roles[0]->division_id ?? null;
            }

            Log::info("Employee division:", ['division_id' => $divisionId]);

            $kpis = Kpi::where('periode_id', $periodeId)
                ->where(function ($query) use ($divisionId) {
                    $query->where('is_global', true);

                    if ($divisionId) {
                        $query->orWhereHas('divisions', function ($q) use ($divisionId) {
                            $q->where('divisions.id_divisi', $divisionId);
                        });
                    }
                })
                ->with(['points.questions'])
                ->get();

            Log::info("KPI to calculate:", [
                'total_kpis' => $kpis->count(),
                'kpi_names' => $kpis->pluck('nama')
            ]);

            foreach ($kpis as $kpi) {
                Log::info("🔍 Calculating KPI: {$kpi->nama} (ID: {$kpi->id_kpi})");

                $totalAspekScore = 0;
                $totalBobotPoint = 0;

                foreach ($kpi->points as $point) {
                    $pointScore = 0;
                    $isAbsensi = stripos($point->nama, 'absensi') !== false;

                    Log::info("  📊 Point: {$point->nama} (Absensi: {$isAbsensi})");

                    if ($isAbsensi) {
                        // Ambil dari nilai_absensi (skala 0-100)
                        $kpisHasEmployeeId = DB::table('kpis_has_employees')
                            ->where('kpis_id_kpi', $kpi->id_kpi)
                            ->where('employees_id_karyawan', $employeeId)
                            ->where('periode_id', $periodeId)
                            ->value('id');

                        Log::info("  📍 KPI Has Employee ID: {$kpisHasEmployeeId}");

                        if ($kpisHasEmployeeId) {
                            $pointRecord = DB::table('kpi_points_has_employee')
                                ->where('kpis_has_employee_id', $kpisHasEmployeeId)
                                ->where('kpi_point_id', $point->id_point)
                                ->first();

                            if ($pointRecord) {
                                $pointScore = ($pointRecord->nilai_absensi ?? 0) / 10; // Konversi ke 0-10
                                Log::info("  ✅ Absensi score from DB: {$pointRecord->nilai_absensi} → {$pointScore}/10");
                            } else {
                                Log::warning("  ❌ No absensi record found for point: {$point->id_point}");
                            }
                        }
                    } else {
                        // Untuk non-absensi, hitung dari jawaban questions
                        $pointTotal = 0;
                        $answeredQuestions = 0;

                        foreach ($point->questions as $q) {
                            $score = KpiQuestionHasEmployee::where('employees_id_karyawan', $employeeId)
                                ->where('kpi_question_id_question', $q->id_question)
                                ->where('periode_id', $periodeId)
                                ->first();

                            if ($score && $score->nilai !== null) {
                                $pointTotal += $score->nilai;
                                $answeredQuestions++;
                                Log::info("  ✅ Question answered: {$q->id_question} = {$score->nilai}");
                            } else {
                                Log::warning("  ❌ Question not answered: {$q->id_question}");
                            }
                        }

                        if ($answeredQuestions > 0) {
                            $avgQuestionScore = $pointTotal / $answeredQuestions;
                            $pointScore = $avgQuestionScore * 2.5; // Konversi ke 0-10
                            Log::info("  📈 Point score calculated: {$pointTotal}/{$answeredQuestions} = {$avgQuestionScore} → {$pointScore}/10");
                        } else {
                            Log::warning("  ❌ No questions answered for point: {$point->nama}");
                        }
                    }

                    $pointBobot = floatval($point->bobot) ?? 0;
                    $pointContribution = ($pointScore * $pointBobot) / 100;
                    $totalAspekScore += $pointContribution;
                    $totalBobotPoint += $pointBobot;

                    Log::info("  🧮 Point contribution: {$pointScore} × {$pointBobot}% = {$pointContribution}");
                }

                // ⚠️ PERBAIKAN: Kalikan dengan 10 untuk konversi ke skala 0-100
                $finalAspekScore = $totalBobotPoint > 0 ? ($totalAspekScore * 10) : 0;

                Log::info("🎯 FINAL KPI SCORE for '{$kpi->nama}': {$totalAspekScore} × 10 = {$finalAspekScore}");

                // Update atau create record di kpis_has_employees
                $existingRecord = DB::table('kpis_has_employees')
                    ->where('kpis_id_kpi', $kpi->id_kpi)
                    ->where('employees_id_karyawan', $employeeId)
                    ->where('periode_id', $periodeId)
                    ->first();

                if ($existingRecord) {
                    DB::table('kpis_has_employees')
                        ->where('kpis_id_kpi', $kpi->id_kpi)
                        ->where('employees_id_karyawan', $employeeId)
                        ->where('periode_id', $periodeId)
                        ->update([
                            'nilai_akhir' => $finalAspekScore, // Sekarang dalam skala 0-100
                            'updated_at' => now()
                        ]);
                    Log::info("  ✅ Updated existing record");
                } else {
                    DB::table('kpis_has_employees')->insert([
                        'kpis_id_kpi' => $kpi->id_kpi,
                        'employees_id_karyawan' => $employeeId,
                        'periode_id' => $periodeId,
                        'tahun' => date('Y'),
                        'bulan' => date('m'),
                        'nilai_akhir' => $finalAspekScore, // Sekarang dalam skala 0-100
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    Log::info("  ✅ Created new record");
                }
            }

            Log::info("✅ ALL FINAL SCORES CALCULATED");
            return true;
        } catch (\Exception $e) {
            Log::error('❌ Error calculating final scores: ' . $e->getMessage());
            return false;
        }
    }
    private function getEmployeeKpiDetails($employeeId, $periodId)
    {
        try {
            $employee = Employee::with(['roles.division'])->find($employeeId);
            $divisionId = null;

            if ($employee->roles && count($employee->roles) > 0) {
                $divisionId = $employee->roles[0]->division_id ?? null;
            }

            // Gunakan query yang SAMA dengan getAllEmployeeKpis
            $kpis = Kpi::where('periode_id', $periodId)
                ->where(function ($query) use ($divisionId) {
                    $query->where('is_global', true);

                    if ($divisionId) {
                        $query->orWhereHas('divisions', function ($q) use ($divisionId) {
                            $q->where('divisions.id_divisi', $divisionId);
                        });
                    }
                })
                ->get();

            $details = [];

            foreach ($kpis as $kpi) {
                $score = DB::table('kpis_has_employees')
                    ->where('kpis_id_kpi', $kpi->id_kpi)
                    ->where('employees_id_karyawan', $employeeId)
                    ->where('periode_id', $periodId)
                    ->value('nilai_akhir');

                $details[] = [
                    'kpi_name' => $kpi->nama,
                    'is_global' => $kpi->is_global,
                    'score' => floatval($score) ?? 0
                ];
            }

            return $details;
        } catch (\Exception $e) {
            Log::error('Error getting KPI details: ' . $e->getMessage());
            return [];
        }
    }
    private function getStatusByContribution($contribution)
    {
        $numericContribution = floatval($contribution);

        if ($numericContribution >= 90) return 'Sangat Baik';
        if ($numericContribution >= 80) return 'Baik'; 
        if ($numericContribution >= 70) return 'Cukup';
        if ($numericContribution >= 50) return 'Kurang';
        return 'Sangat Kurang';
    }

    // STANDARDISASI UNTUK SEMUA METHOD
    private function getScoreStatus($score)
    {
        $numericScore = floatval($score);
        
        if ($numericScore >= 90) return 'Sangat Baik';
        if ($numericScore >= 80) return 'Baik';
        if ($numericScore >= 70) return 'Cukup'; 
        if ($numericScore >= 50) return 'Kurang';
        return 'Sangat Kurang';
    }

    // STANDARDISASI UNTUK GRADE HURUF
    private function getLetterGrade($score)
    {
        $numericScore = floatval($score);
        
        if ($numericScore >= 90) return 'A';
        if ($numericScore >= 80) return 'B';
        if ($numericScore >= 70) return 'C';
        if ($numericScore >= 50) return 'D';
        return 'E';
    }   

    private function getMonthFromPeriod($startDate, $endDate)
{
    try {
        $start = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate);
        
        // Jika periode mencakup dua bulan, ambil bulan dari tanggal mulai
        // Contoh: 7 Jul 2025 - 7 Aug 2025 → return "July"
        if ($start->month != $end->month) {
            return $start->format('F'); // July
        }
        
        // Jika dalam bulan yang sama, return bulan tersebut
        return $start->format('F');
    } catch (\Exception $e) {
        return date('F'); // Fallback ke bulan sekarang
    }
}

private function getMonthNumberFromPeriod($startDate, $endDate)
{
    try {
        $start = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate);
        
        // Prioritaskan bulan dari tanggal mulai
        if ($start->month != $end->month) {
            return $start->month; // 7
        }
        
        return $start->month;
    } catch (\Exception $e) {
        return date('n'); // Fallback
    }
}

public function getNonHeadEmployeesKpis(Request $request)
{
    try {
        Log::info("=== getNonHeadEmployeesKpis - NON-HEAD in ACTIVE PERIODS ===");

        // ⚠️ PERBAIKAN: Hanya periode AKTIF
        $activePeriods = Period::where('kpi_published', true)
            ->where('attendance_uploaded', true)
            ->where('status', 'active') // ⚠️ FILTER HANYA YANG AKTIF
            ->orderBy('tanggal_mulai', 'desc')
            ->get();

        Log::info("Active periods found: " . $activePeriods->count());

        if ($activePeriods->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => [],
                'message' => 'Tidak ada periode AKTIF dengan absensi dan KPI yang dipublish'
            ]);
        }

        $allEmployeeData = [];

        foreach ($activePeriods as $period) {
            Log::info("Processing ACTIVE period: {$period->nama} (ID: {$period->id_periode})");

            // 1. Ambil semua karyawan yang AKTIF dan BUKAN Kepala Divisi
            $nonHeadEmployees = DB::table('employees')
                ->join('roles_has_employees', 'employees.id_karyawan', '=', 'roles_has_employees.employee_id')
                ->join('roles', 'roles_has_employees.role_id', '=', 'roles.id_jabatan')
                ->select(
                    'employees.id_karyawan',
                    'employees.nama',
                    'employees.status',
                    'employees.foto',
                    'roles.nama_jabatan as position',
                    'roles.division_id'
                )
                ->where('employees.status', 'Aktif')
                ->where('roles.nama_jabatan', 'not like', '%Kepala Divisi%')
                ->get();

            Log::info("Non-head employees found: " . $nonHeadEmployees->count());

            foreach ($nonHeadEmployees as $emp) {
                // 2. CEK APAKAH MEMILIKI ABSENSI DI PERIODE INI
                $hasAttendance = DB::table('attendances')
                    ->where('employee_id', $emp->id_karyawan)
                    ->where('periode_id', $period->id_periode)
                    ->exists();

                if (!$hasAttendance) {
                    Log::info("No attendance for employee {$emp->id_karyawan} in period {$period->id_periode}");
                    continue;
                }

                // 3. ✅ CUMA TAMPILIN YANG BELUM DINILAI
                $hasKpiScore = DB::table('kpis_has_employees')
                    ->where('employees_id_karyawan', $emp->id_karyawan)
                    ->where('periode_id', $period->id_periode)
                    ->where('nilai_akhir', '>', 0)
                    ->exists();

                if ($hasKpiScore) {
                    Log::info("Employee {$emp->id_karyawan} already has KPI score in period {$period->id_periode}");
                    continue;
                }

                // Ambil detail divisi
                $division = '-';
                $divisionId = $emp->division_id;
                
                if ($divisionId) {
                    $divisionData = DB::table('divisions')
                        ->where('id_divisi', $divisionId)
                        ->first();
                    $division = $divisionData ? $divisionData->nama_divisi : '-';
                }

                $periodMonth = date('F', strtotime($period->tanggal_mulai));
                $periodYear = date('Y', strtotime($period->tanggal_mulai));

                // ✅ PERBAIKAN PATH FOTO
                $fotoPath = $emp->foto;
                if (!$fotoPath || $fotoPath === '' || $fotoPath === 'null') {
                    $fotoPath = 'assets/images/profile_av.png';
                } else {
                    // Handle foto path yang proper
                    if (filter_var($fotoPath, FILTER_VALIDATE_URL)) {
                        // Already a full URL
                    } elseif (strpos($fotoPath, 'storage/') === 0) {
                        $fotoPath = asset($fotoPath);
                    } elseif (strpos($fotoPath, 'profile-photos/') === 0) {
                        $fotoPath = asset('storage/' . $fotoPath);
                    } else {
                        $fotoPath = asset('storage/' . $fotoPath);
                    }
                }

                $employeeData = [
                    'id_karyawan' => $emp->id_karyawan,
                    'nama' => $emp->nama,
                    'status' => $emp->status,
                    'score' => 0,
                    'period' => $period->nama,
                    'period_month' => $periodMonth,
                    'period_month_number' => date('n', strtotime($period->tanggal_mulai)),
                    'period_year' => $periodYear,
                    'photo' => $fotoPath,
                    'division' => $division,
                    'division_id' => $divisionId,
                    'position' => $emp->position,
                    'period_id' => $period->id_periode,
                    'employee_type' => 'non_head'
                ];

                $allEmployeeData[] = $employeeData;
                Log::info("✅ Added UNRATED NON-HEAD employee:", [
                    'nama' => $emp->nama,
                    'period' => $period->nama
                ]);
            }
        }

        Log::info("Final UNRATED NON-HEAD employees in ACTIVE periods:", [
            'total_unrated_non_head' => count($allEmployeeData),
            'active_periods_processed' => $activePeriods->count()
        ]);

        return response()->json([
            'success' => true,
            'data' => $allEmployeeData,
            'debug_info' => [
                'total_active_periods' => $activePeriods->count(),
                'total_unrated_non_head_employees' => count($allEmployeeData),
                'note' => 'Hanya menampilkan karyawan NON-Kepala Divisi yang BELUM dinilai di periode AKTIF'
            ]
        ]);

    } catch (\Exception $e) {
        Log::error('Failed to fetch UNRATED NON-HEAD KPI data: ' . $e->getMessage());
        Log::error('Error trace: ' . $e->getTraceAsString());
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch NON-HEAD KPI data: ' . $e->getMessage(),
            'error_details' => $e->getTraceAsString() // Untuk debugging
        ], 500);
    }
}

public function exportMonthlyKpi($employeeId, $year = null)
{
    try {
        \Log::info("=== EXPORT MONTHLY KPI - SYNCHRONIZED WITH TABLE ===");

        $employee = Employee::with(['roles.division'])->find($employeeId);
        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        // Dapatkan divisi karyawan
        $employeeDivisionId = null;
        if ($employee->roles && count($employee->roles) > 0) {
            $employeeDivisionId = $employee->roles[0]->division_id ?? null;
        }

        $exportYear = $year ?: date('Y');

        // Ambil semua periode yang sudah dipublish untuk tahun tersebut
        $periods = Period::where('kpi_published', true)
            ->whereYear('tanggal_mulai', $exportYear)
            ->orderBy('tanggal_mulai', 'asc')
            ->get();

        if ($periods->isEmpty()) {
            return response("
                <script>
                    alert('Tidak ada data KPI untuk tahun {$exportYear}');
                    window.history.back();
                </script>
            ");
        }

        $exportData = [];
        $monthlyTotals = [];
        $allSubAspekNames = [];

        foreach ($periods as $period) {
            $monthName = \Carbon\Carbon::parse($period->tanggal_mulai)->format('F Y');
            $monthKey = \Carbon\Carbon::parse($period->tanggal_mulai)->format('Y-m');
            
            // ✅ PAKAI METHOD YANG SUDAH DIPERBAIKI
            $kpiData = $this->getKpiDataByDivision($employeeId, $period->id_periode, $employeeDivisionId);
            
            $monthTotal = 0;

            foreach ($kpiData as $subAspek) {
                $subAspekName = $subAspek['sub_aspek_name'];
                $score = $subAspek['score']; // Sudah dalam bentuk kontribusi
                $aspekUtama = $subAspek['aspek_utama'];
                $bobot = $subAspek['bobot'];
                
                // Simpan semua nama sub aspek
                $fullName = "{$aspekUtama} - {$subAspekName}";
                if (!in_array($fullName, $allSubAspekNames)) {
                    $allSubAspekNames[] = $fullName;
                }
                
                // Simpan data per sub aspek per bulan
                if (!isset($exportData[$fullName])) {
                    $exportData[$fullName] = [
                        'aspek_utama' => $aspekUtama,
                        'sub_aspek_name' => $subAspekName,
                        'full_name' => $fullName,
                        'bobot' => $bobot,
                        'raw_scores' => [], // Simpan raw score untuk debug
                        'scores' => []
                    ];
                }
                
                $exportData[$fullName]['scores'][$monthKey] = [
                    'score' => $score,
                    'month_name' => $monthName
                ];
                
                $exportData[$fullName]['raw_scores'][$monthKey] = $subAspek['raw_score'] ?? 0;
                
                $monthTotal += $score;
            }

            $monthlyTotals[$monthKey] = [
                'total' => $monthTotal,
                'month_name' => $monthName
            ];

            \Log::info("📅 MONTHLY TOTAL CALCULATED:", [
                'month' => $monthName,
                'total_score' => $monthTotal,
                'period_id' => $period->id_periode
            ]);
        }

        // ✅ VERIFIKASI: Bandingkan dengan nilai di tabel
        $this->verifyExportWithTable($employeeId, $exportYear, $monthlyTotals);

        if (empty($exportData)) {
            return response("
                <script>
                    alert('Tidak ada data KPI yang ditemukan untuk karyawan ini di divisinya');
                    window.history.back();
                </script>
            ");
        }

        // Format data untuk export
        $pivotedData = $this->formatExportDataWithSubAspek($exportData, $monthlyTotals, $allSubAspekNames);

        // Generate filename
        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $employee->nama);
        $fileName = "KPI_Detail_{$safeName}_{$exportYear}.xlsx";

        return Excel::download(
            new MonthlyKpiExport($employee, $pivotedData, $exportYear),
            $fileName
        );

    } catch (\Exception $e) {
        \Log::error("EXPORT ERROR: " . $e->getMessage());
        \Log::error("Stack trace: " . $e->getTraceAsString());
        
        return response("
            <script>
                alert('Error saat mengekspor data: " . addslashes($e->getMessage()) . "');
                window.history.back();
            </script>
        ");
    }
}

// ✅ METHOD BARU UNTUK VERIFIKASI
private function verifyExportWithTable($employeeId, $year, $monthlyTotals)
{
    try {
        // Ambil data dari API yang sama dengan tabel
        $tableData = DB::table('kpis_has_employees')
            ->where('employees_id_karyawan', $employeeId)
            ->whereYear('created_at', $year)
            ->select('periode_id', DB::raw('SUM(nilai_akhir) as total_score'))
            ->groupBy('periode_id')
            ->get()
            ->keyBy('periode_id');

        \Log::info("🔍 VERIFICATION - Table vs Export:", [
            'employee_id' => $employeeId,
            'year' => $year,
            'table_data' => $tableData->toArray(),
            'export_totals' => $monthlyTotals
        ]);

        foreach ($monthlyTotals as $monthKey => $exportTotal) {
            $periodId = Period::whereYear('tanggal_mulai', $year)
                ->where(\DB::raw("DATE_FORMAT(tanggal_mulai, '%Y-%m')"), $monthKey)
                ->value('id_periode');

            if ($periodId && isset($tableData[$periodId])) {
                $tableScore = $tableData[$periodId]->total_score;
                $exportScore = $exportTotal['total'];
                $difference = abs($tableScore - $exportScore);

                \Log::info("📊 SCORE COMPARISON:", [
                    'month' => $monthKey,
                    'period_id' => $periodId,
                    'table_score' => $tableScore,
                    'export_score' => $exportScore,
                    'difference' => $difference
                ]);

                if ($difference > 0.01) { // Toleransi kecil untuk floating point
                    \Log::warning("⚠️ SCORE MISMATCH DETECTED:", [
                        'month' => $monthKey,
                        'table' => $tableScore,
                        'export' => $exportScore,
                        'difference' => $difference
                    ]);
                }
            }
        }

    } catch (\Exception $e) {
        \Log::error("Verification error: " . $e->getMessage());
    }
}

private function getKpiDataByDivision($employeeId, $periodId, $employeeDivisionId)
{
    $kpiData = [];
    
    // Ambil KPI berdasarkan divisi - PAKAI LOGIC YANG SAMA DENGAN TABEL
    $kpis = Kpi::where('periode_id', $periodId)
        ->where(function ($query) use ($employeeDivisionId) {
            if ($employeeDivisionId) {
                $query->whereHas('divisions', function ($q) use ($employeeDivisionId) {
                    $q->where('divisions.id_divisi', $employeeDivisionId);
                });
            }
            if (!$query->getQuery()->wheres) {
                $query->orWhere('is_global', true);
            }
        })
        ->with(['points.questions'])
        ->get();

    \Log::info("🔍 Processing KPI for export - SAME LOGIC AS TABLE");

    foreach ($kpis as $kpi) {
        $aspekUtama = $kpi->nama;
        
        // Ambil kpis_has_employees ID
        $kpisHasEmployeeId = DB::table('kpis_has_employees')
            ->where('kpis_id_kpi', $kpi->id_kpi)
            ->where('employees_id_karyawan', $employeeId)
            ->where('periode_id', $periodId)
            ->value('id');

        foreach ($kpi->points as $point) {
            $subAspekName = $point->nama;
            $pointScore = 0;
            $isAbsensi = stripos($point->nama, 'absensi') !== false 
                      || stripos($point->nama, 'kehadiran') !== false;

            \Log::info("🎯 Point Analysis:", [
                'point_name' => $subAspekName,
                'is_absensi' => $isAbsensi
            ]);

            // ✅ 1. PROSES ABSENSI - AMBIL DARI nilai_absensi (0-100)
            if ($isAbsensi && $kpisHasEmployeeId) {
                $pointRecord = DB::table('kpi_points_has_employee')
                    ->where('kpis_has_employee_id', $kpisHasEmployeeId)
                    ->where('kpi_point_id', $point->id_point)
                    ->first();

                $pointScore = $pointRecord->nilai_absensi ?? 0;
                
                \Log::info("📊 ABSENSI SCORE:", [
                    'point' => $subAspekName,
                    'score' => $pointScore,
                    'from_db' => 'kpi_points_has_employee.nilai_absensi'
                ]);
            } 
            // ✅ 2. PROSES QUESTIONS - PAKAI RUMUS YANG SAMA DENGAN TABEL
            else if ($point->questions->count() > 0) {
                $pointTotal = 0;
                $answeredQuestions = 0;

                foreach ($point->questions as $question) {
                    $answer = KpiQuestionHasEmployee::where('employees_id_karyawan', $employeeId)
                        ->where('kpi_question_id_question', $question->id_question)
                        ->where('periode_id', $periodId)
                        ->first();

                    if ($answer && $answer->nilai !== null) {
                        // ✅ KONVERSI YANG SAMA: (nilai 1-4) → (0-100)
                        $questionScore = (($answer->nilai - 1) / 3) * 100;
                        $pointTotal += $questionScore;
                        $answeredQuestions++;
                        
                        \Log::info("  ✅ Question converted:", [
                            'question_id' => $question->id_question,
                            'original_value' => $answer->nilai,
                            'converted_value' => $questionScore
                        ]);
                    }
                }

                if ($answeredQuestions > 0) {
                    $pointScore = $pointTotal / $answeredQuestions;
                }

                \Log::info("📊 QUESTIONS SCORE:", [
                    'point' => $subAspekName,
                    'questions_answered' => $answeredQuestions,
                    'average_score' => $pointScore
                ]);
            } else {
                \Log::info("❌ POINT SKIPPED:", [
                    'point' => $subAspekName,
                    'reason' => 'Not absensi and no questions'
                ]);
                continue;
            }

            // ✅ HITUNG KONTRIBUSI DENGAN BOBOT (SAMA DENGAN TABEL)
            $pointBobot = floatval($point->bobot) ?? 0;
            $pointContribution = ($pointScore * $pointBobot) / 100;

            \Log::info("🧮 FINAL POINT CALCULATION:", [
                'point' => $subAspekName,
                'raw_score' => $pointScore,
                'bobot' => $pointBobot,
                'contribution' => $pointContribution,
                'formula' => "({$pointScore} × {$pointBobot}) / 100"
            ]);

            $kpiData[] = [
                'aspek_utama' => $aspekUtama,
                'sub_aspek_name' => $subAspekName,
                'score' => $pointContribution, // ✅ PAKAI KONTRIBUSI, BUKAN RAW SCORE
                'bobot' => $pointBobot,
                'raw_score' => $pointScore // Simpan juga untuk debug
            ];
        }
    }

    // ✅ HITUNG TOTAL (SAMA DENGAN CARA TABEL)
    $totalScore = array_sum(array_column($kpiData, 'score'));
    
    \Log::info("🎯 FINAL EXPORT DATA - SYNCHRONIZED WITH TABLE:", [
        'total_points' => count($kpiData),
        'total_score' => $totalScore,
        'data' => $kpiData
    ]);

    return $kpiData;
}

// Di KpiEvaluationController.php - method getEmployeeKpiDetail
private function getKpiDataWithSubAspek($employeeId, $periodId)
{
    $kpiData = [];
    
    // Ambil semua KPI untuk employee di periode ini
    $kpis = Kpi::where('periode_id', $periodId)
        ->with(['points.questions'])
        ->get();

    foreach ($kpis as $kpi) {
        $aspekUtama = $kpi->nama;
        $kpiBobot = floatval($kpi->bobot);
        
        // Ambil nilai akhir KPI dari tabel
        $kpiFinalScore = DB::table('kpis_has_employees')
            ->where('kpis_id_kpi', $kpi->id_kpi)
            ->where('employees_id_karyawan', $employeeId)
            ->where('periode_id', $periodId)
            ->value('nilai_akhir');

        // 1. TOTAL ASPEK UTAMA
        if ($kpiFinalScore) {
            $kpiData[] = [
                'aspek_kpi' => $aspekUtama,
                'sub_aspek_name' => 'TOTAL_ASPEK',
                'score' => floatval($kpiFinalScore),
                'bobot' => $kpiBobot,
                'is_total_aspek' => true
            ];
        }

        // 2. DETAIL SUB-ASPEK
        $kpisHasEmployeeId = DB::table('kpis_has_employees')
            ->where('kpis_id_kpi', $kpi->id_kpi)
            ->where('employees_id_karyawan', $employeeId)
            ->where('periode_id', $periodId)
            ->value('id');

        foreach ($kpi->points as $point) {
            $subAspekName = $point->nama;
            $pointScore = 0;
            $isAbsensi = stripos($point->nama, 'absensi') !== false;

            if ($isAbsensi && $kpisHasEmployeeId) {
                // Untuk absensi
                $pointRecord = DB::table('kpi_points_has_employee')
                    ->where('kpis_has_employee_id', $kpisHasEmployeeId)
                    ->where('kpi_point_id', $point->id_point)
                    ->first();
                $pointScore = $pointRecord->nilai_absensi ?? 0;
            } else {
                // Untuk non-absensi
                $pointTotal = 0;
                $answeredQuestions = 0;

                foreach ($point->questions as $question) {
                    $answer = KpiQuestionHasEmployee::where('employees_id_karyawan', $employeeId)
                        ->where('kpi_question_id_question', $question->id_question)
                        ->where('periode_id', $periodId)
                        ->first();

                    if ($answer && $answer->nilai !== null) {
                        $questionScore = (($answer->nilai - 1) / 3) * 100;
                        $pointTotal += $questionScore;
                        $answeredQuestions++;
                    }
                }
                $pointScore = $answeredQuestions > 0 ? ($pointTotal / $answeredQuestions) : 0;
            }

            $pointBobot = floatval($point->bobot) ?? 0;
            
            $kpiData[] = [
                'aspek_kpi' => $aspekUtama,
                'sub_aspek_name' => $subAspekName,
                'score' => $pointScore,
                'bobot' => $pointBobot,
                'is_total_aspek' => false
            ];
        }
    }

    return $kpiData;
}

private function formatExportDataWithSubAspek($exportData, $monthlyTotals, $allSubAspekNames)
{
    // Urutkan bulan
    $sortedMonths = array_keys($monthlyTotals);
    usort($sortedMonths, function($a, $b) {
        return strtotime($a) - strtotime($b);
    });

    // Format nama bulan untuk display
    $formattedMonths = [];
    foreach ($sortedMonths as $monthKey) {
        $formattedMonths[$monthKey] = $monthlyTotals[$monthKey]['month_name'];
    }

    // Siapkan data scores
    $scores = [];
    foreach ($allSubAspekNames as $fullName) {
        if (isset($exportData[$fullName])) {
            $subAspekData = $exportData[$fullName];
            $scores[$fullName] = [];
            
            foreach ($sortedMonths as $monthKey) {
                $score = $subAspekData['scores'][$monthKey]['score'] ?? 0;
                $scores[$fullName][$monthKey] = $score;
            }
        }
    }

    // Siapkan data totals
    $totals = [];
    foreach ($sortedMonths as $monthKey) {
        $totals[$monthKey] = $monthlyTotals[$monthKey]['total'] ?? 0;
    }

    // Siapkan grouping by aspek utama
    $groupedData = [];
    foreach ($exportData as $fullName => $data) {
        $aspekUtama = $data['aspek_utama'];
        if (!isset($groupedData[$aspekUtama])) {
            $groupedData[$aspekUtama] = [];
        }
        $groupedData[$aspekUtama][] = $fullName;
    }

    return [
        'points' => $exportData, // Semua data sub aspek
        'months' => $formattedMonths,
        'scores' => $scores,
        'totals' => $totals,
        'sorted_months' => $sortedMonths,
        'grouped_data' => $groupedData // Untuk grouping di Excel
    ];
}
}
