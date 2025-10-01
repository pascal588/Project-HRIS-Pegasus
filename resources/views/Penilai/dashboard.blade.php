@extends('template.template')

@section('title', 'Dashboard Penilai')

@section('content')
<div class="body d-flex">
  <div class="container-xxl">
    <div class="row g-3">

      <!-- HEADER SALAM -->
      <div class="col-12">
        <div class="card shadow-sm p-3 d-flex flex-row align-items-center">
          <img class="rounded-circle img-thumbnail me-3" 
               src="{{ Auth::user()->employee->foto ? asset('storage/' . Auth::user()->employee->foto) : asset('assets/images/profile_av.png') }}" 
              alt="profile" style="width: 60px; height: 60px;"
              onerror="this.src='{{ asset('assets/images/profile_av.png') }}'">
          <div>
            <h4 class="mb-1">Hai, Ketua divisi 👋</h4>
            <small class="text-muted">Score terbaru kamu: <span class="fw-bold text-primary" id="currentGrade">-</span></small>
          </div>
        </div>
      </div>

      <div class="row g-2">
  <!-- KIRI -->
  <div class="col-md-8">
    <div class="row g-3">
      <!-- KPI Terbaru -->
      <div class="col-md-6">
        <div class="card bg-primary text-white h-100 shadow-sm">
          <div class="card-body">
            <h6 class="fw-bold mb-3">Nilai KPI Terbaru</h6>
            <div class="d-flex justify-content-between align-items-center">
              <span class="avatar lg bg-white text-primary rounded-circle d-flex align-items-center justify-content-center">
                <i class="icofont-file-text fs-5"></i>
              </span>
              <h2 class="fw-bold mb-0" id="currentScore">0</h2>
            </div>
            <span class="d-block text-end small" id="previousScore">-</span>
          </div>
        </div>
      </div>

      <!-- Performa -->
      <div class="col-md-6">
        <div class="card h-100 bg-primary text-white shadow-sm">
          <div class="card-body">
            <h6 class="fw-bold mb-3">Performa Anda</h6>
            <div class="d-flex justify-content-between align-items-center">
              <h4><span class="avatar lg rounded-circle text-primary bg-light d-flex align-items-center justify-content-center" id="performanceGrade">-</span></h4>
              <h3 class="fw-bold mb-0" id="performanceText">-</h3>
            </div>
            <span class="d-block text-end small" id="performanceStatus">-</span>
          </div>
        </div>
      </div>

      {{-- absensi --}}
        <div class="col-12">
          <div class="card mb-3">
            <div class="card-header bg-transparent border-bottom-0 d-flex justify-content-between align-items-center">
              <h6 class="mb-0 fw-bold">Histori Absensi</h6>
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
    </div>
  </div>

  <!-- KANAN -->
 <div class="col-md-4">
  <div class="card shadow-sm h-100 bg-light">
  <div class="card-body d-flex flex-column h-100">
  <h5 class="fw-bold mb-3">📢 Informasi</h5>
  <ul class="list-unstyled flex-grow-1 d-flex flex-column justify-content-between mb-0">
    <li class="d-flex flex-column justify-content-between p-3 bg-white rounded shadow-sm mb-3" style="height: 120px;">
  <div class="d-flex justify-content-between align-items-start mb-2">
    <div>
      <h6 class="fw-bold mb-1">Anda harus menilai</h6>
      <span class="text-muted small">Belum selesai</span>
    </div>
    <h4 class="fw-bold text-primary mb-0" id="unratedCount">0</h4>
  </div>
  <div class="d-flex justify-content-end mt-auto">
    <button class="btn btn-primary btn-sm" onclick="scrollToCard('belum-dinilai')">
      <i class="icofont-eye-alt me-1"></i> Lihat
    </button>
  </div>
</li>

<li class="d-flex flex-column justify-content-between p-3 bg-white rounded shadow-sm mb-3" style="height: 120px;">
  <div class="d-flex justify-content-between align-items-start mb-2">
    <div>
      <h6 class="fw-bold mb-1">Anda harus menegur</h6>
      <span class="text-muted small">Segera ditindak</span>
    </div>
    <h4 class="fw-bold text-danger mb-0" id="warningCount">0</h4>
  </div>
  <div class="d-flex justify-content-end mt-auto">
    <button class="btn btn-primary btn-sm" onclick="scrollToCard('perlu-teguran')">
      <i class="icofont-eye-alt me-1"></i> Lihat
    </button>
  </div>
</li>

  </ul>
</div>

</div>

</div>

      <!-- GRAFIK & DETAIL KPI -->
      <div class="col-12">
        <div class="card shadow-sm p-3">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 fw-bold">Grafik & Detail KPI</h6>
            <small class="text-muted" id="kpiPeriod">-</small>
          </div>
          <div class="row g-3">
            <div class="col-md-8 border-end">
              <div id="chartKPI" style="min-height:300px;"></div>
            </div>
            <div class="col-md-4">
              <div class="table-responsive">
                <div class="fw-bold mb-2">Detail Nilai KPI Terakhir</div>
                <div id="kpiDetailLoading" class="text-center py-2">
                  <div class="spinner-border spinner-border-sm" role="status">
                    <span class="visually-hidden">Loading...</span>
                  </div>
                </div>
                <div id="kpiDetailContent">
                  <table class="table table-sm table-striped mb-0">
                    <thead>
                      <tr>
                        <th>Aspek</th>
                        <th class="text-center">Skor</th>
                        <th class="text-center">Status</th>
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
        </div>
      </div>

      <!-- STATISTIK RATA2 DIVISI + LIST KARYAWAN -->
      <div class="col-12">
          <div class="row g-3">
            <div class="card shadow-sm bg-primary p-3 d-flex flex-row align-items-center">
          <div>
            <h5 class="mb-1 text-white fw-bold ">Kinerja Karyawan</h5>
          </div>
        </div>
             <!-- Card 1: Jumlah + Donut -->
    <div class="col-md-4">
    <div class="card shadow-sm mb-3">
        <div class="card-body text-center">
            <h6 class="fw-bold">Jumlah Karyawan Divisi</h6>
            <h3 class="fw-bold text-primary" id="employee-count">0</h3>
        </div>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <div id="gender-donut"></div>
        </div>
    </div>
</div>

    <!-- Card 2: Karyawan Belum Dinilai -->
    <div class="col-md-4">
      <div class="card shadow-sm" id="belum-dinilai">
        <div class="card-body">
          <h6 class="fw-bold mb-3"><i class="icofont-users-alt-2 me-2"></i>Karyawan Belum Dinilai</h6>
          <div style="max-height: 350px; overflow-y: auto; min-height: 350px;">
            <div class="text-center py-4">
              <div class="spinner-border spinner-border-sm" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
              <p class="mt-2 small text-muted">Memuat data...</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Card 3: Karyawan Perlu Teguran -->
    <div class="col-md-4">
      <div class="card shadow-sm" id="perlu-teguran">
        <div class="card-body">
          <h6 class="fw-bold mb-3"><i class="icofont-users-alt-2 me-2"></i>Karyawan Perlu Teguran</h6>
          <div style="max-height: 350px; overflow-y: auto; min-height: 350px;">
            <div class="text-center py-4">
              <div class="spinner-border spinner-border-sm" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
              <p class="mt-2 small text-muted">Memuat data...</p>
            </div>
          </div>
        </div>
      </div>
    </div>

          </div>

          <!-- KPI DIVISI -->
        <div class="col-12 mt-4">
          <div class="card shadow-sm">
            <div class="card-body">
              <h6 class="fw-bold mb-3" id="judulDivisi">Rata-rata KPI Bulanan — Divisi IT</h6>
              <canvas id="kpiBarChart" height="100"></canvas>
            </div>
          </div>
        </div>
        </div>
        
      </div>

    </div><!-- row end -->
  </div>
  </div>

@endsection

@section('script')
<!-- Plugin Js-->
<script src="{{asset('assets/bundles/apexcharts.bundle.js')}}"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// ======================
// VARIABLES GLOBAL
// ======================
let currentDivisionId = {{ Auth::user()->employee->roles->first()->division_id ?? 0 }};
let currentEmployeeId = '{{ Auth::user()->employee->id_karyawan }}';
let allMonthlyData = {};
let kpiChart = null;

// ======================
// LOAD DATA DASHBOARD
// ======================
async function loadDashboardData() {
    try {
        // Load data KPI personal dulu
        await loadPersonalKpiData();
        
        // Load data divisi
        await loadDivisionData();
        
        // Load data absensi
        fetchAttendanceData();
        
        // Load data karyawan yang belum dinilai
        await loadUnratedEmployees();
        
        // Load data karyawan perlu teguran
        await loadEmployeesNeedWarning();
        
        // Load chart divisi
        await loadDivisionKpiChart();
        
    } catch (error) {
        console.error('Error loading dashboard data:', error);
    }
}

// ======================
// LOAD DATA KPI PERSONAL
// ======================
async function loadPersonalKpiData() {
    try {
        // 1. Load available periods dulu
        const periodsResponse = await fetch('/api/periods?kpi_published=true');
        const periodsData = await periodsResponse.json();

        if (periodsData.success && periodsData.data.length > 0) {
            const periods = periodsData.data;
            
            // 2. Load data untuk setiap periode
            allMonthlyData = {};
            
            for (const period of periods.slice(0, 12)) { // Maksimal 12 bulan
                try {
                    const response = await fetch(`/api/kpis/employee/${currentEmployeeId}/detail/${period.id_periode}`);
                    const data = await response.json();
                    
                    if (data.success) {
                        const periodData = data.data;
                        const startDate = new Date(period.tanggal_mulai);
                        const monthKey = startDate.toLocaleDateString('id-ID', { 
                            month: 'short', 
                            year: 'numeric' 
                        });
                        
                        // Simpan data lengkap
                        allMonthlyData[monthKey] = {
                            month: monthKey,
                            totalScore: periodData.kpi_summary.total_score,
                            averageScore: periodData.kpi_summary.average_score,
                            periodName: period.nama,
                            fullDate: startDate,
                            kpiDetails: periodData.kpi_details,
                            ranking: periodData.kpi_summary.ranking,
                            totalEmployees: periodData.kpi_summary.total_employees,
                            performanceStatus: periodData.kpi_summary.performance_status
                        };
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
        console.error('Error loading personal KPI data:', error);
    }
}

// ======================
// UPDATE SUMMARY DASHBOARD
// ======================
function updateDashboardSummary() {
    const monthlyArray = Object.values(allMonthlyData).sort((a, b) => b.fullDate - a.fullDate);
    
    if (monthlyArray.length > 0) {
        const latestData = monthlyArray[0];
        const previousData = monthlyArray[1] || latestData;
        
        // Update current score
        document.getElementById('currentScore').textContent = latestData.totalScore.toFixed(1);
        
        // Update previous score
        const previousScore = previousData.totalScore.toFixed(1);
        document.getElementById('previousScore').textContent = `Sebelumnya: ${previousScore}`;
        
        // Update grade
        const avgScore = latestData.averageScore;
        const gradeInfo = calculateGrade(avgScore);
        document.getElementById('currentGrade').textContent = gradeInfo.grade;
        document.getElementById('performanceGrade').textContent = gradeInfo.grade;
        document.getElementById('performanceText').textContent = gradeInfo.text;
        document.getElementById('performanceStatus').textContent = gradeInfo.status;
        
    }
}

// ======================
// CALCULATE GRADE
// ======================
function calculateGrade(score) {
    const numericScore = parseFloat(score) || 0;
    if (numericScore >= 90) return { grade: 'A', text: 'Sangat Baik', status: 'Excellent' };
    if (numericScore >= 80) return { grade: 'B', text: 'Baik', status: 'Good' };
    if (numericScore >= 70) return { grade: 'C', text: 'Cukup', status: 'Average' };
    if (numericScore >= 60) return { grade: 'D', text: 'Kurang', status: 'Below Average' };
    return { grade: 'E', text: 'Sangat Kurang', status: 'Poor' };
}

// ======================
// UPDATE GRAFIK KPI
// ======================
function updateKpiChart() {
    const monthlyArray = Object.values(allMonthlyData).sort((a, b) => a.fullDate - b.fullDate);
    
    if (monthlyArray.length === 0) {
        document.getElementById('chartKPI').innerHTML = `
            <div class="text-center p-5">
                <i class="icofont-chart-line-alt fs-1 text-muted"></i>
                <p class="text-muted mt-2">Tidak ada data KPI bulanan</p>
            </div>
        `;
        return;
    }

    const categories = monthlyArray.map(item => item.month);
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
            max: Math.max(...totalScores) * 1.1,
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
                        <strong>Total Nilai: ${totalScore.toFixed(2)}</strong>
                    </div>
                `;
                
                if (monthData.kpiDetails && monthData.kpiDetails.length > 0) {
                    tooltipHTML += `<div style="border-top: 1px solid #e0e0e0; margin-top: 6px; padding-top: 6px;">`;
                    tooltipHTML += `<div style="font-weight: 600; margin-bottom: 4px;">Detail Aspek:</div>`;
                    
                    monthData.kpiDetails.forEach(aspek => {
                        const nilai = parseFloat(aspek.score) || 0;
                        tooltipHTML += `
                            <div style="display: flex; justify-content: between; align-items: center; padding: 2px 0; font-size: 12px;">
                                <span>${aspek.aspek_kpi}:</span>
                                <strong style="margin-left: 8px;">${nilai.toFixed(1)}</strong>
                            </div>
                        `;
                    });
                    
                    tooltipHTML += `</div>`;
                }
                
                return tooltipHTML;
            }
        },
        dataLabels: { enabled: false }
    };

    kpiChart = new ApexCharts(document.querySelector("#chartKPI"), options);
    kpiChart.render();
}

// ======================
// UPDATE TABEL DETAIL KPI
// ======================
function updateKpiDetailTable() {
    const monthlyArray = Object.values(allMonthlyData).sort((a, b) => b.fullDate - a.fullDate);
    
    if (monthlyArray.length > 0) {
        const latestData = monthlyArray[0];
        const tbody = document.getElementById('kpiDetailBody');
        const periodElement = document.getElementById('kpiPeriod');
        
        periodElement.textContent = latestData.month;
        tbody.innerHTML = '';
        
        latestData.kpiDetails.forEach((item, index) => {
            const nilai = parseFloat(item.score) || 0;
            const status = item.status;
            const statusClass = getStatusClass(status);
            
            const row = `
                <tr>
                    <td class="small">${item.aspek_kpi}</td>
                    <td class="text-center fw-bold">${nilai.toFixed(1)}</td>
                    <td class="text-center"><span class="kpi-badge ${statusClass}">${status}</span></td>
                </tr>
            `;
            tbody.innerHTML += row;
        });
        
        document.getElementById('kpiDetailLoading').classList.add('d-none');
    }
}

// ======================
// LOAD DATA DIVISI
// ======================
async function loadDivisionData() {
    try {
        if (currentDivisionId === 0) {
            console.error('Divisi tidak ditemukan');
            return;
        }

        // Ambil jumlah karyawan
        const countResponse = await fetch(`/api/divisions/${currentDivisionId}/employee-count`);
        const countData = await countResponse.json();
        
        if (countData.success) {
            document.getElementById('employee-count').textContent = countData.data.total_employees || 0;
        }

        // Ambil data gender untuk donut chart
        const genderResponse = await fetch(`/api/divisions/${currentDivisionId}/gender-data`);
        const genderData = await genderResponse.json();
        
        if (genderData.success) {
            renderGenderDonutChart(genderData.data);
        }

    } catch (error) {
        console.error('Error loading division data:', error);
    }
}

// ======================
// LOAD KARYAWAN BELUM DINILAI
// ======================
async function loadUnratedEmployees() {
    try {
        const response = await fetch(`/api/kpis/division/${currentDivisionId}/unrated-employees`);
        const data = await response.json();
        
        if (data.success) {
            renderUnratedEmployees(data.data);
            
            // Update count di informasi
            const unratedCount = data.data.length || 0;
            document.getElementById('unratedCount').textContent = unratedCount;
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        console.error('Error loading unrated employees:', error);
        document.getElementById('belum-dinilai').innerHTML = `
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="icofont-users-alt-2 me-2"></i>Karyawan Belum Dinilai</h6>
                <div class="text-center text-muted py-4">
                    <i class="icofont-close-circled fs-1"></i>
                    <p class="mt-2">Gagal memuat data</p>
                </div>
            </div>
        `;
    }
}

// ======================
// RENDER KARYAWAN BELUM DINILAI
// ======================
function renderUnratedEmployees(employees) {
    const container = document.getElementById('belum-dinilai');
    let html = `
        <div class="card-body">
            <h6 class="fw-bold mb-3"><i class="icofont-users-alt-2 me-2"></i>Karyawan Belum Dinilai</h6>
            <div style="max-height: 350px; overflow-y: auto; min-height: 350px;">
    `;
    
    if (employees.length === 0) {
        html += `
            <div class="text-center text-muted py-4">
                <i class="icofont-checked fs-1"></i>
                <p class="mt-2">Semua karyawan sudah dinilai</p>
            </div>
        `;
    } else {
        employees.forEach(employee => {
            html += `
                <div class="py-2 d-flex align-items-center border-bottom flex-wrap">
                    <img class="avatar lg rounded-circle img-thumbnail" 
                         src="${employee.foto ? '/storage/' + employee.foto : '{{ asset('assets/images/profile_av.png') }}'}" 
                         alt="${employee.nama}"
                         onerror="this.src='{{ asset('assets/images/profile_av.png') }}'">
                    <div class="d-flex flex-column ps-3 flex-fill">
                        <h6 class="fw-bold mb-0 small-14">${employee.nama}</h6>
                        <span class="text-muted">${employee.position}</span>
                    </div>
                    <a class="btn btn-outline-warning btn-sm" href="/kpi/evaluate/${employee.id_karyawan}">
                        Nilai
                    </a>
                </div>
            `;
        });
    }
    
    html += `</div></div>`;
    container.innerHTML = html;
}

// ======================
// LOAD KARYAWAN PERLU TEGURAN (NILAI E)
// ======================
async function loadEmployeesNeedWarning() {
    try {
        const response = await fetch(`/api/kpis/division/${currentDivisionId}/low-performing-employees`);
        const data = await response.json();
        
        if (data.success) {
            renderEmployeesNeedWarning(data.data);
            
            // Update count di informasi
            const warningCount = data.data.length || 0;
            document.getElementById('warningCount').textContent = warningCount;
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        console.error('Error loading employees need warning:', error);
        document.getElementById('perlu-teguran').innerHTML = `
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="icofont-users-alt-2 me-2"></i>Karyawan Perlu Teguran</h6>
                <div class="text-center text-muted py-4">
                    <i class="icofont-close-circled fs-1"></i>
                    <p class="mt-2">Gagal memuat data</p>
                </div>
            </div>
        `;
    }
}

// ======================
// RENDER KARYAWAN PERLU TEGURAN (DIHAPUS TOMBOL MATA)
// ======================
function renderEmployeesNeedWarning(employees) {
    const container = document.getElementById('perlu-teguran');
    let html = `
        <div class="card-body">
            <h6 class="fw-bold mb-3"><i class="icofont-users-alt-2 me-2"></i>Karyawan Perlu Teguran</h6>
            <div style="max-height: 350px; overflow-y: auto; min-height: 350px;">
    `;
    
    if (employees.length === 0) {
        html += `
            <div class="text-center text-muted py-4">
                <i class="icofont-like fs-1"></i>
                <p class="mt-2">Tidak ada karyawan perlu teguran</p>
            </div>
        `;
    } else {
        employees.forEach(employee => {
            const scoreColor = employee.score < 50 ? 'text-danger' : 'text-warning';
            
            html += `
                <div class="py-2 d-flex align-items-center border-bottom flex-wrap">
                    <img class="avatar lg rounded-circle img-thumbnail" 
                         src="${employee.foto ? '/storage/' + employee.foto : '{{ asset('assets/images/profile_av.png') }}'}" 
                         alt="${employee.nama}"
                         onerror="this.src='{{ asset('assets/images/profile_av.png') }}'">
                    <div class="d-flex flex-column ps-3 flex-fill">
                        <h6 class="fw-bold mb-0 small-14">${employee.nama}</h6>
                        <span class="text-muted">${employee.position}</span>
                        <small class="${scoreColor}">Score: ${employee.score.toFixed(1)}</small>
                    </div>
                    <div class="d-flex gap-1">
                        <a class="btn btn-outline-success btn-sm" href="https://wa.me/${employee.phone || '62'}" target="_blank">
                            <i class="icofont-brand-whatsapp"></i>
                        </a>
                    </div>
                </div>
            `;
        });
    }
    
    html += `</div></div>`;
    container.innerHTML = html;
}

// ======================
// RENDER GENDER DONUT CHART
// ======================
function renderGenderDonutChart(genderData) {
    const series = [];
    const labels = [];
    const colors = ['#007bff', '#ff4081'];

    genderData.forEach(item => {
        labels.push(item.gender);
        series.push(item.count);
    });

    var options = {
        chart: {
            type: 'donut',
            height: 300
        },
        series: series,
        labels: labels,
        colors: colors,
        legend: {
            position: 'bottom'
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '65%',
                    labels: {
                        show: true,
                        total: {
                            show: true,
                            label: 'Total',
                            color: '#373d3f',
                            formatter: function (w) {
                                return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                            }
                        }
                    }
                }
            }
        }
    };

    var chart = new ApexCharts(document.querySelector("#gender-donut"), options);
    chart.render();
}

// ======================
// HELPER FUNCTIONS
// ======================
function getStatusClass(status) {
    const statusMap = {
        'Sangat Baik': 'badge-excellent',
        'Baik': 'badge-good', 
        'Cukup': 'badge-average',
        'Kurang': 'badge-poor',
        'Sangat Kurang': 'badge-poor'
    };
    return statusMap[status] || 'badge-average';
}

function scrollToCard(cardId) {
    document.getElementById(cardId).scrollIntoView({ 
        behavior: 'smooth',
        block: 'start'
    });
}

// ======================
// FUNGSI ABSENSI
// ======================
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

// ======================
// LOAD CHART RATA-RATA KPI DIVISI
// ======================
async function loadDivisionKpiChart() {
    try {
        const response = await fetch(`/api/kpis/division/${currentDivisionId}/stats`);
        const data = await response.json();
        
        if (data.success) {
            renderDivisionKpiChart(data.data);
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        console.error('Error loading division KPI chart:', error);
        document.getElementById('kpiBarChart').innerHTML = `
            <div class="text-center text-muted py-4">
                <i class="icofont-close-circled fs-1"></i>
                <p class="mt-2">Gagal memuat chart divisi</p>
            </div>
        `;
    }
}

// Di dashboard.blade.php - PERBAIKI renderDivisionKpiChart
function renderDivisionKpiChart(stats) {
    const ctx = document.getElementById('kpiBarChart').getContext('2d');
    
    // Jika ada data bulanan, buat line chart untuk trend bulanan
    if (stats.monthly_averages && stats.monthly_averages.length > 0) {
        const months = stats.monthly_averages.map(item => item.month);
        const averages = stats.monthly_averages.map(item => item.average_score);
        
        // Buat line chart untuk trend bulanan
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: 'Rata-rata KPI Divisi per Bulan',
                    data: averages,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    title: {
                        display: true,
                        text: `Trend Rata-rata KPI Divisi - Current: ${stats.division_average}`
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const data = stats.monthly_averages[context.dataIndex];
                                return `Rata-rata: ${context.raw} | Karyawan: ${data.total_employees} orang`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Nilai KPI Rata-rata'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Bulan'
                        }
                    }
                }
            }
        });
    } else {
        // Fallback ke chart karyawan jika tidak ada data bulanan
        const employeeNames = stats.employee_scores.map(emp => emp.nama);
        const employeeScores = stats.employee_scores.map(emp => emp.average_score);
        
        const backgroundColors = employeeScores.map(score => {
            if (score >= 90) return 'rgba(40, 167, 69, 0.8)';
            if (score >= 80) return 'rgba(23, 162, 184, 0.8)';
            if (score >= 70) return 'rgba(255, 193, 7, 0.8)';
            if (score >= 60) return 'rgba(253, 126, 20, 0.8)';
            return 'rgba(220, 53, 69, 0.8)';
        });

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: employeeNames,
                datasets: [{
                    label: 'Rata-rata Nilai KPI',
                    data: employeeScores,
                    backgroundColor: backgroundColors,
                    borderColor: backgroundColors.map(color => color.replace('0.8', '1')),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: `Rata-rata Divisi: ${stats.division_average}`
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        title: { display: true, text: 'Nilai KPI' }
                    },
                    x: {
                        title: { display: true, text: 'Karyawan' }
                    }
                }
            }
        });
    }

    // Update judul divisi
    document.getElementById('judulDivisi').textContent = 
        `Trend Rata-rata KPI Bulanan — Divisi (Current: ${stats.division_average || 0})`;
}

// ======================
// ADD CSS UNTUK BADGE
// ======================
const style = document.createElement('style');
style.textContent = `
    .kpi-badge {
        padding: 3px 8px;
        border-radius: 12px;
        font-weight: 500;
        font-size: 0.7rem;
        white-space: nowrap;
    }
    .badge-excellent {
        background-color: rgba(40, 167, 69, 0.2);
        color: #28a745;
    }
    .badge-good {
        background-color: rgba(23, 162, 184, 0.2);
        color: #17a2b8;
    }
    .badge-average {
        background-color: rgba(255, 193, 7, 0.2);
        color: #ffc107;
    }
    .badge-poor {
        background-color: rgba(220, 53, 69, 0.2);
        color: #dc3545;
    }
`;
document.head.appendChild(style);

// ======================
// INITIALIZE
// ======================
document.addEventListener('DOMContentLoaded', function() {
    loadDashboardData();
});
</script>
@endsection