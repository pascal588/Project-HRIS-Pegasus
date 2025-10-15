@extends('template.template')

@section('title', 'Dashboard Karyawan')

@section('content')
<div class="body d-flex">
  <div class="container-xxl">
    <div class="row g-3">

      <!-- Header Salam -->
      <div class="col-12">
        <div class="card shadow-sm p-3 d-flex flex-row align-items-center">
          <img class="rounded-circle img-thumbnail me-3" 
               src="{{ Auth::user()->employee->foto ? asset('storage/' . Auth::user()->employee->foto) : asset('assets/images/profile_av.png') }}" 
              alt="profile" style="width: 60px; height: 60px;"
              onerror="this.src='{{ asset('assets/images/profile_av.png') }}'">
          <div>
            <h4 class="mb-1">Hai, {{ Auth::user()->Employee->nama }} 👋</h4>
            <small class="text-muted">Score terbaru kamu: <span class="fw-bold text-primary" id="currentGrade">-</span></small>
          </div>
        </div>
      </div>

      <!-- Ringkasan KPI -->
      <div class="col-md-4">
        <div class="card bg-primary text-white h-100 shadow-sm">
          <div class="card-body">
            <h6 class="fw-bold mb-3">Nilai KPI Terbaru</h6>
            <div class="d-flex justify-content-between align-items-center">
              <span class="avatar lg bg-white text-primary rounded-circle d-flex align-items-center justify-content-center">
                <i class="icofont-file-text fs-5"></i>
              </span>
              <h2 class="fw-bold" id="currentScore">0</h2>
            </div>
            <span class="d-block text-end small" id="previousScore">-</span>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card h-100 shadow-sm">
          <div class="card-body">
            <h6 class="fw-bold mb-3">Performa Anda</h6>
            <div class="d-flex justify-content-between align-items-center">
              <h4><span class="avatar lg rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" id="performanceGrade">-</span></h4>
              <h3 class="fw-bold mb-0" id="performanceText">-</h3>
            </div>
            <span class="d-block text-end small text-muted" id="performanceStatus">-</span>
          </div>
        </div>
      </div>

      <div class="col-md-4 ">
        <div class="card bg-primary text-white h-100 shadow-sm">
          <div class="card-body">
            <h6 class="fw-bold mb-3">Ranking Anda</h6>
            <div class="d-flex justify-content-between align-items-center">
              <span class="avatar lg bg-white text-primary rounded-circle d-flex align-items-center justify-content-center">
                <i class="icofont-chart-line fs-4"></i>
              </span>
              <h2 class="fw-bold mb-0" id="rankingPosition">-</h2>
            </div>
            <span class="d-block text-end small" id="rankingText">-</span>
          </div>
        </div>
      </div>

      <!-- Kiri (8 kolom) -->
      <div class="col-xl-8 col-lg-12">
        <div class="row g-3">

        {{-- absensi --}}
        <div class="col-12">
          <div class="card mb-3">
            <div class="card-header bg-transparent border-bottom-0 d-flex justify-content-between align-items-center">
              <h6 class="mb-0 fw-bold">Histori Absensi</h6>
              <!-- Hapus dropdown pemilihan periode -->
            </div>
            <div class="card-body">
              <div id="attendanceLoading" class="text-center py-3">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Memuat data absensi...</p>
              </div>
              <div id="attendanceError" class="alert alert-danger d-none" role="alert"></div>
              <div id="attendanceContent" class="d-none">
                <div class="row g-2">
                  <div class="col-3">
                    <div class="card text-center p-2">
                      <i class="icofont-checked fs-3 text-success"></i>
                      <h6 class="fw-bold small mt-2 mb-0">Hadir</h6>
                      <span class="text-muted" id="presentCount">0</span>
                    </div>
                  </div>
                  <div class="col-3">
                    <div class="card text-center p-2">
                      <i class="icofont-ban fs-3 text-danger"></i>
                      <h6 class="fw-bold small mt-2 mb-0">Mangkir</h6>
                      <span class="text-muted" id="absentCount">0</span>
                    </div>
                  </div>
                  <div class="col-3">
                    <div class="card text-center p-2">
                      <i class="icofont-beach-bed fs-3 text-warning"></i>
                      <h6 class="fw-bold small mt-2 mb-0">Izin/Cuti</h6>
                      <span class="text-muted" id="permissionCount">0</span>
                    </div>
                  </div>
                  <div class="col-3">
                    <div class="card text-center p-2">
                      <i class="icofont-stopwatch fs-3 text-primary"></i>
                      <h6 class="fw-bold small mt-2 mb-0">Terlambat</h6>
                      <span class="text-muted" id="lateCount">0</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

          <!-- Grafik KPI -->
          <div class="col-12">
            <div class="card shadow-sm p-3">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Grafik Tren KPI</h6>
                <small class="text-muted">Arahkan kursor untuk detail aspek</small>
              </div>
              <div id="chartKpi" style="min-height:300px;"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Kanan (4 kolom) -->
      <div class="col-xl-4 col-lg-12">

        <!-- Top Karyawan -->
        <div class="card p-3">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="fw-bold mb-0">Top 3 Karyawan</h6>
            <span class="text-muted small" id="topEmployeePeriod">-</span>
          </div>

          <div class="list-group list-group-flush" style="max-height: 250px; overflow-y: auto;" id="topEmployeesList">
            <div class="text-center py-3">
              <div class="spinner-border spinner-border-sm" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
              <p class="mt-2 small text-muted">Memuat data...</p>
            </div>
          </div>
        </div>

        
        <!-- Detail KPI -->
        <div class="card mb-3 mt-3">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h6 class="mb-0">Detail KPI Terbaru</h6>
              <small class="text-muted" id="kpiDetailPeriod">-</small>
            </div>
            <div id="kpiDetailLoading" class="text-center py-2">
              <div class="spinner-border spinner-border-sm" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </div>
            <div id="kpiDetailContent">
              <table class="table table-sm table-striped mb-0">
                <thead>
                  <tr>
                    <th>No</th>
                    <th>Aspek</th>
                    <th>Skor</th>
                  </tr>
                </thead>
                <tbody id="kpiDetailBody">
                  <!-- Data akan diisi oleh JavaScript -->
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div><!-- Row End -->
  </div>
</div>
@endsection

@section('script')
    <script src="{{ asset('assets/bundles/apexcharts.bundle.js') }}"></script>
    <script>
let currentEmployeeId = '{{ Auth::user()->employee->id_karyawan }}';
let kpiChart = null;
let allMonthlyData = {};

// Load data untuk dashboard
async function loadDashboardData() {
    try {
        // Load data KPI
        await loadKpiData();
        
        // Load data absensi
        fetchAttendanceData();
        
        // Load top employees
        loadTopEmployees();
        
    } catch (error) {
        console.error('Error loading dashboard data:', error);
    }
}

// Load data KPI untuk grafik dan summary - GUNAKAN CARA MANUAL ×10
async function loadKpiData() {
    try {
        // 1. Load available periods dulu
        const periodsResponse = await fetch('/api/periods?kpi_published=true');
        const periodsData = await periodsResponse.json();

        if (periodsData.success && periodsData.data.length > 0) {
            const periods = periodsData.data;
            
            // 2. Load data untuk setiap periode - KITA HITUNG MANUAL ×10
            allMonthlyData = {};
            
            for (const period of periods.slice(0, 12)) { // Maksimal 12 bulan
                try {
                    // GUNAKAN API DETAIL
                    const response = await fetch(`/api/kpis/employee/${currentEmployeeId}/detail/${period.id_periode}`);
                    const data = await response.json();
                    
                    if (data.success) {
                        const periodData = data.data;
                        const startDate = new Date(period.tanggal_mulai);
                        const monthKey = startDate.toLocaleDateString('id-ID', { 
                            month: 'short', 
                            year: 'numeric' 
                        });
                        
                        // ⚠️ PERBAIKAN: KITA KALI 10 MANUAL DI SINI
                        const rawTotalScore = parseFloat(periodData.kpi_summary.total_score) || 0;
                        const multipliedTotalScore = rawTotalScore * 10;
                        
                        // Kalikan juga detail aspek dengan 10
                        const multipliedKpiDetails = periodData.kpi_details.map(detail => {
                            return {
                                ...detail,
                                score: (parseFloat(detail.score) || 0) * 10,
                                kontribusi: (parseFloat(detail.kontribusi) || 0) * 10
                            };
                        });

                        allMonthlyData[monthKey] = {
                            month: monthKey,
                            totalScore: multipliedTotalScore, // ⚠️ SUDAH ×10
                            averageScore: multipliedTotalScore, 
                            periodName: period.nama,
                            fullDate: startDate,
                            kpiDetails: multipliedKpiDetails, // Detail sudah ×10
                            ranking: periodData.kpi_summary.ranking,
                            totalEmployees: periodData.kpi_summary.total_employees,
                            performanceStatus: periodData.kpi_summary.performance_status,
                            // Simpan juga data mentah untuk debug
                            debug: {
                                rawTotalScore: rawTotalScore,
                                multipliedTotalScore: multipliedTotalScore
                            }
                        };

                        console.log(`✅ Data for ${monthKey}:`, {
                            raw: rawTotalScore,
                            multiplied: multipliedTotalScore,
                            details: multipliedKpiDetails.length
                        });
                    }
                } catch (error) {
                    console.error(`Error loading data for period ${period.id_periode}:`, error);
                }
            }
            
            // 3. Update UI dengan data terbaru
            updateDashboardSummary();
            updateKpiChart();
            updateKpiDetailTable();
            
        } else {
            throw new Error('Tidak ada data periode yang tersedia');
        }
    } catch (error) {
        console.error('Error loading KPI data:', error);
    }
}

// UPDATE DASHBOARD SUMMARY - PAKAI NILAI YANG SUDAH ×10
function updateDashboardSummary() {
    const monthlyArray = Object.values(allMonthlyData).sort((a, b) => b.fullDate - a.fullDate);
    
    if (monthlyArray.length > 0) {
        const latestData = monthlyArray[0];
        const previousData = monthlyArray[1] || latestData;
        
        // ⚠️ LANGSUNG PAKAI NILAI YANG SUDAH ×10
        const currentScore = parseFloat(latestData.totalScore) || 0;
        const previousScore = parseFloat(previousData.totalScore) || 0;
        
        console.log("🔍 KARYAWAN - Using multiplied values:", {
            currentScore: currentScore,
            previousScore: previousScore,
            debug: latestData.debug
        });
        
        // Update current score
        document.getElementById('currentScore').textContent = currentScore.toFixed(1);
        
        // Update previous score
        if (previousData !== latestData) {
            document.getElementById('previousScore').textContent = `Sebelumnya: ${previousScore.toFixed(1)}`;
        } else {
            document.getElementById('previousScore').textContent = `Data pertama`;
        }
        
        // Hitung grade
        const gradeInfo = calculateGrade(currentScore);
        document.getElementById('currentGrade').textContent = gradeInfo.grade;
        document.getElementById('performanceGrade').textContent = gradeInfo.grade;
        document.getElementById('performanceText').textContent = gradeInfo.text;
        document.getElementById('performanceStatus').textContent = gradeInfo.status;
        
        // Update ranking
        document.getElementById('rankingPosition').textContent = `${latestData.ranking}/${latestData.totalEmployees}`;
        document.getElementById('rankingText').textContent = `Dari ${latestData.totalEmployees} karyawan`;
        
        console.log("🎯 KARYAWAN Final Summary:", {
            finalScore: currentScore,
            grade: gradeInfo.grade
        });
    }
}

// Calculate grade
function calculateGrade(score) {
    const numericScore = parseFloat(score) || 0;
    
    if (numericScore >= 90) return { grade: 'A', text: 'Sangat Baik', status: 'Sangat Baik' };
    if (numericScore >= 80) return { grade: 'B', text: 'Baik', status: 'Baik' };
    if (numericScore >= 70) return { grade: 'C', text: 'Cukup', status: 'Cukup' };
    if (numericScore >= 50) return { grade: 'D', text: 'Kurang', status: 'Kurang' };
    return { grade: 'E', text: 'Sangat Kurang', status: 'Sangat Kurang' };
}

// Update grafik KPI - PAKAI NILAI YANG SUDAH ×10
function updateKpiChart() {
    const monthlyArray = Object.values(allMonthlyData).sort((a, b) => a.fullDate - a.fullDate);
    
    if (monthlyArray.length === 0) {
        document.getElementById('chartKpi').innerHTML = `
            <div class="text-center p-5">
                <i class="icofont-chart-line-alt fs-1 text-muted"></i>
                <p class="text-muted mt-2">Tidak ada data KPI bulanan</p>
            </div>
        `;
        return;
    }

    const categories = monthlyArray.map(item => item.month);
    
    // ⚠️ PAKAI NILAI YANG SUDAH ×10
    const totalScores = monthlyArray.map(item => parseFloat(item.totalScore) || 0);

    if (kpiChart) {
        kpiChart.destroy();
    }

    const options = {
        chart: {
            type: 'line',
            height: 300,
            zoom: { enabled: false },
            toolbar: { show: true }
        },
        series: [{
            name: 'Total Nilai KPI',
            data: totalScores
        }],
        stroke: {
            width: 3,
            curve: 'smooth'
        },
        markers: {
            size: 5,
            hover: { size: 7 }
        },
        xaxis: {
            categories: categories,
            labels: { style: { fontSize: '12px' } }
        },
        yaxis: {
            title: { text: 'Total Nilai KPI' },
            min: 0,
            max: 100,
            labels: { formatter: function(val) { return val.toFixed(0); } }
        },
        colors: ['#0d6efd'],
        grid: {
            borderColor: '#f1f1f1',
            strokeDashArray: 4
        },
        legend: { position: 'top' },
        tooltip: {
            custom: function({ series, seriesIndex, dataPointIndex, w }) {
                const monthData = monthlyArray[dataPointIndex];
                const totalScore = series[seriesIndex][dataPointIndex];
                
                let tooltipHTML = `
                    <div class="apexcharts-tooltip-title" style="font-weight: bold; margin-bottom: 8px;">
                        ${monthData.month}
                    </div>
                    <div style="padding: 4px 0;">
                        <strong>Total Nilai: ${totalScore.toFixed(1)}</strong>
                    </div>
                `;
                
                if (monthData.kpiDetails && monthData.kpiDetails.length > 0) {
                    tooltipHTML += `<div style="border-top: 1px solid #e0e0e0; margin-top: 6px; padding-top: 6px;">`;
                    tooltipHTML += `<div style="font-weight: 600; margin-bottom: 4px;">Detail Aspek:</div>`;
                    
                    monthData.kpiDetails.forEach(aspek => {
                        // Tampilkan hanya total aspek utama
                        if (aspek.is_total_aspek) {
                            const nilai = parseFloat(aspek.score) || 0;
                            tooltipHTML += `
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 2px 0; font-size: 12px;">
                                    <span>${aspek.aspek_kpi}:</span>
                                    <strong style="margin-left: 8px;">${nilai.toFixed(1)}</strong>
                                </div>
                            `;
                        }
                    });
                    
                    tooltipHTML += `</div>`;
                }
                
                return tooltipHTML;
            }
        },
        dataLabels: { enabled: false }
    };

    kpiChart = new ApexCharts(document.querySelector("#chartKpi"), options);
    kpiChart.render();
    
    console.log("📈 KARYAWAN Chart data:", {
        categories: categories,
        scores: totalScores
    });
}

// Update tabel detail KPI - PAKAI NILAI YANG SUDAH ×10
function updateKpiDetailTable() {
    const monthlyArray = Object.values(allMonthlyData).sort((a, b) => b.fullDate - a.fullDate);
    
    if (monthlyArray.length > 0) {
        const latestData = monthlyArray[0];
        const tbody = document.getElementById('kpiDetailBody');
        const periodElement = document.getElementById('kpiDetailPeriod');
        
        periodElement.textContent = latestData.month;
        tbody.innerHTML = '';
        
        if (latestData.kpiDetails && latestData.kpiDetails.length > 0) {
            // Filter hanya total aspek utama
            const totalAspekItems = latestData.kpiDetails.filter(item => item.is_total_aspek);
            
            totalAspekItems.forEach((item, index) => {
                // ⚠️ PAKAI NILAI YANG SUDAH ×10
                const displayNilai = parseFloat(item.score) || 0;
                
                const row = `
                    <tr>
                        <td>${index + 1}</td>
                        <td class="small">${item.aspek_kpi}</td>
                        <td class="fw-bold">${displayNilai.toFixed(1)}</td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });
            
            // Total row
            const displayTotal = parseFloat(latestData.totalScore) || 0;
            
            const totalRow = `
                <tr class="table-primary">
                    <td colspan="2" class="small fw-bold">TOTAL NILAI KPI</td>
                    <td class="fw-bold">${displayTotal.toFixed(1)}</td>
                </tr>
            `;
            tbody.innerHTML += totalRow;
        } else {
            tbody.innerHTML = `
                <tr>
                    <td colspan="3" class="text-center text-muted py-3">
                        <i class="icofont-info-circle"></i>
                        <p class="small mt-2">Tidak ada data aspek KPI</p>
                    </td>
                </tr>
            `;
        }
        
        document.getElementById('kpiDetailLoading').classList.add('d-none');
    }
}

// Load top employees - PERBAIKAN: KALI 10 JUGA
async function loadTopEmployees() {
    try {
        const response = await fetch('/api/kpis/top-employees/3');
        const data = await response.json();
        
        console.log("🔍 Top Employees API Response:", data);
        
        if (data.success) {
            // ⚠️ PERBAIKAN: KALI 10 UNTUK TOP EMPLOYEES JUGA
            const multipliedData = data.data.map(employee => ({
                ...employee,
                score: (parseFloat(employee.score) || 0) * 10
            }));
            
            updateTopEmployees(multipliedData);
        } else {
            // Fallback ke method lama jika method baru tidak ada
            const fallbackResponse = await fetch('/api/kpis/all-employees');
            const fallbackData = await fallbackResponse.json();
            
            if (fallbackData.success) {
                // ⚠️ PERBAIKAN: KALI 10 UNTUK FALLBACK JUGA
                const multipliedFallbackData = fallbackData.data.map(employee => ({
                    ...employee,
                    score: (parseFloat(employee.score) || 0) * 10
                }));
                
                updateTopEmployees(multipliedFallbackData);
            }
        }
    } catch (error) {
        console.error('Error loading top employees:', error);
        document.getElementById('topEmployeesList').innerHTML = `
            <div class="text-center py-3 text-muted">
                <i class="icofont-close-circled"></i>
                <p class="small mt-2">Gagal memuat data</p>
            </div>
        `;
    }
}

// Update top employees list - PERBAIKAN: TAMPILKAN NILAI YANG SUDAH ×10
function updateTopEmployees(employees) {
    const listElement = document.getElementById('topEmployeesList');
    const periodElement = document.getElementById('topEmployeePeriod');
    
    console.log("🎯 Top Employees Data (after ×10):", employees);
    
    if (employees.length > 0) {
        const latestPeriod = employees[0]?.period_month + ' ' + employees[0]?.period_year;
        periodElement.textContent = latestPeriod;
        
        listElement.innerHTML = '';
        
        // Ambil hanya 3 karyawan dengan score tertinggi
        const topThreeEmployees = employees
            .sort((a, b) => b.score - a.score)
            .slice(0, 3);
        
        console.log("🏆 Final Top 3:", topThreeEmployees);
        
        topThreeEmployees.forEach((employee, index) => {
            const rankClass = index === 0 ? 'bg-warning text-dark' : 
                            index === 1 ? 'bg-secondary text-white' : 
                            'bg-info text-white';
            
            // ⚠️ PERBAIKAN: TAMPILKAN NILAI YANG SUDAH ×10
            const displayScore = parseFloat(employee.score) || 0;
            
            const item = `
                <div class="list-group-item d-flex align-items-center">
                    <span class="avatar rounded-circle me-3 ${rankClass} d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        ${index + 1}
                    </span>
                    
                    <img class="avatar rounded-circle me-3" 
                         src="${employee.photo}" 
                         alt="${employee.nama}" width="40" height="40"
                         onerror="this.src='{{ asset('assets/images/profile_av.png') }}'">
                    
                    <div class="flex-fill" style="min-width: 0;">
                        <h6 class="mb-0 small-14 fw-bold text-truncate">
                            ${employee.nama}
                        </h6>
                        <small class="text-muted">${employee.division}</small>
                    </div>

                    <div class="ms-2" style="flex-shrink: 0; min-width: 50px; text-align: right;">
                        <span class="fw-bold text-primary">${displayScore.toFixed(0)}</span>
                    </div>
                </div>
            `;
            listElement.innerHTML += item;
        });
    } else {
        listElement.innerHTML = `
            <div class="text-center py-3 text-muted">
                <i class="icofont-info-circle"></i>
                <p class="small mt-2">Tidak ada data karyawan</p>
            </div>
        `;
    }
}

// Fungsi untuk mengambil data absensi
function fetchAttendanceData() {
    let url = `/api/attendances/employee/${currentEmployeeId}`;
    
    document.getElementById('attendanceLoading').classList.remove('d-none');
    document.getElementById('attendanceError').classList.add('d-none');
    document.getElementById('attendanceContent').classList.add('d-none');
    
    fetch(url)
      .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
      })
      .then(data => {
        if (data.success) {
          document.getElementById('presentCount').textContent = data.summary.hadir || 0;
          document.getElementById('absentCount').textContent = data.summary.mangkir || 0;
          document.getElementById('permissionCount').textContent = data.summary.izin || 0;
          document.getElementById('lateCount').textContent = data.summary.jumlah_terlambat || 0;
          
          document.getElementById('attendanceLoading').classList.add('d-none');
          document.getElementById('attendanceContent').classList.remove('d-none');
        } else {
          throw new Error(data.message || 'Failed to fetch attendance data');
        }
      })
      .catch(error => {
        console.error('Error fetching attendance data:', error);
        document.getElementById('attendanceLoading').classList.add('d-none');
        document.getElementById('attendanceError').classList.remove('d-none');
        document.getElementById('attendanceError').textContent = 'Gagal memuat data absensi: ' + error.message;
      });
}

// Load data ketika halaman siap
document.addEventListener('DOMContentLoaded', function() {
    loadDashboardData();
});
</script>
@endsection