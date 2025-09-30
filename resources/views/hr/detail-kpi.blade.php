@extends('template.template')

@section('title', 'Detail KPI')

@section('content')
<style>
  /* Style untuk grafik */
  .chart-container {
    position: relative;
    height: 200px;
    margin-bottom: 30px;
  }

  /* Style untuk card statistik */
  .stat-card {
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 20px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    color: white;
  }

  .stat-card .icon {
    font-size: 2rem;
    margin-bottom: 10px;
  }

  .stat-card .title {
    font-size: 0.9rem;
    color: rgba(255, 255, 255, 0.8);
  }

  .stat-card .value {
    font-size: 1.5rem;
    font-weight: bold;
  }

  /* Style untuk tabel detail */
  .detail-table th {
    background-color: #f8f9fa;
    font-weight: 600;
  }

  /* Tambahan style untuk KPI */
  .kpi-progress {
    height: 10px;
    border-radius: 5px;
  }

  .progress-percentage {
    font-size: 0.8rem;
    margin-top: 3px;
    text-align: right;
  }

  .kpi-badge {
    padding: 5px 10px;
    border-radius: 20px;
    font-weight: 500;
    font-size: 0.8rem;
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

  /* Style untuk tabel bulanan */
  .monthly-table th {
    position: sticky;
    top: 0;
    background-color: #f8f9fa;
    z-index: 10;
  }

  .monthly-score {
    font-weight: bold;
    text-align: center;
  }

  .monthly-total {
    font-weight: bold;
    background-color: #f8f9fa;
  }

  .table-container {
    max-height: 500px;
    overflow-y: auto;
  }

  @media (max-width: 576px) {
    .stat-card {
      padding: 10px;
    }

    .stat-card .icon {
      font-size: 1.5rem;
      margin-bottom: 5px;
    }

    .stat-card .title {
      font-size: 0.75rem;
    }

    .stat-card .value {
      font-size: 1rem;
    }

    .card-body h4 {
      font-size: 1.1rem;
    }

    .card-body span {
      display: block;
      margin-bottom: 5px;
    }
  }
</style>

<div class="body d-flex py-3">
  <div class="container-xxl">
<!-- Header Info -->
<div class="row mb-4">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-start">
          <!-- Info Karyawan -->
          <div class="mb-2">
            <h4 class="fw-bold mb-0">Detail KPI Karyawan</h4>
            <div class="d-flex flex-wrap align-items-center mt-2">
              <span class="me-3"><strong>Nama:</strong> <span id="employeeName">-</span></span>
              <span class="me-3"><strong>ID Karyawan:</strong> <span id="employeeId">-</span></span>
              <span class="me-3"><strong>Divisi:</strong> <span id="employeeDivision">-</span></span>
              <span class="me-3"><strong>Jabatan:</strong> <span id="employeePosition">-</span></span>
              <span><strong>Periode:</strong> <span id="periodRange">-</span></span>
            </div>
          </div>

          <!-- Dropdown Bulan -->
          <div class="dropdown mt-2 mb-3">
            <button class="btn btn-primary dropdown-toggle" type="button" id="monthDropdown" data-bs-toggle="dropdown" aria-expanded="false">
              Bulan: <span id="currentMonth">Pilih Bulan</span>
            </button>
            <ul class="dropdown-menu" aria-labelledby="monthDropdown" id="monthList">
              <li><h6 class="dropdown-header">Memuat bulan...</h6></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

    <!-- Statistik Ringkasan KPI -->
<div class="row mb-4 text-center">
  <div class="col-6 col-sm-4 col-md-3">
    <div class="stat-card bg-primary">
      <div class="icon"><i class="icofont-checked"></i></div>
      <div class="title">Total Nilai KPI</div>
      <div class="value">
        <span id="totalScore">0</span> <small class="fs-6" id="performanceStatus">(-)</small>
      </div>
    </div>
  </div>
  <div class="col-6 col-sm-4 col-md-3">
    <div class="stat-card bg-primary">
      <div class="icon"><i class="icofont-trophy"></i></div>
      <div class="title">Rata-rata</div>
      <div class="value">
        <span id="averageScore">0</span> <small class="fs-6">(Target: 80)</small>
      </div>
    </div>
  </div>
  <div class="col-6 col-sm-4 col-md-3">
    <div class="stat-card bg-primary">
      <div class="icon"><i class="icofont-chart-line"></i></div>
      <div class="title">Performa</div>
      <div class="value">
        <span id="performanceScore">0</span> <small class="fs-6" id="performanceText">(-)</small>
      </div>
    </div>
  </div>
  <div class="col-6 col-sm-4 col-md-3">
    <div class="stat-card bg-primary">
      <div class="icon"><i class="icofont-medal"></i></div>
      <div class="title">Peringkat</div>
      <div class="value">
        <span id="ranking">-</span> <small class="fs-6" id="rankingText">(-)</small>
      </div>
    </div>
  </div>
</div>

    <!-- Detail Indikator KPI -->
<div class="row mb-4">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title">Indikator KPI</h5>
        <button class="btn btn-sm btn-primary" id="exportExcelBtn">Export Excel</button>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table detail-table" id="kpiDetailTable">
            <thead>
              <tr>
                <th>Aspek KPI</th>
                <th>Bobot</th>
                <th>Nilai</th>
                <th>Kontribusi</th>
                <th>Status</th>
                <th>Progress</th>
              </tr>
            </thead>
            <tbody id="kpiDetailBody">
              <!-- Data akan diisi oleh JavaScript -->
              <tr>
                <td colspan="6" class="text-center">Memuat data...</td>
              </tr>
            </tbody>
            <tfoot id="kpiDetailFooter">
              <!-- Total akan diisi oleh JavaScript -->
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

    <!-- Dropdown Tahun -->
    <div class="dropdown mt-2 mb-3">
      <button
        class="btn btn-primary dropdown-toggle"
        type="button"
        id="tahunDropdown"
        data-bs-toggle="dropdown"
        aria-expanded="false">
        Tahun: 2023
      </button>
      <ul class="dropdown-menu" aria-labelledby="tahunDropdown">
        <li>
          <h6 class="dropdown-header">Pilih Tahun</h6>
        </li>
        <li>
          <a class="dropdown-item" href="#" data-tahun="2023">2023</a>
        </li>
        <li>
          <a class="dropdown-item" href="#" data-tahun="2022">2022</a>
        </li>
        <li>
          <a class="dropdown-item" href="#" data-tahun="2021">2021</a>
        </li>
      </ul>
    </div>

    <!-- Grafik Perkembangan KPI -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="card">
          <div
            class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title">Perkembangan Nilai KPI</h5>
          </div>
          <div class="card-body">
            <div class="chart-container">
              <canvas id="kpiTrendChart"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Rekapan KPI Bulanan -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="card">
          <div
            class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title">Rekapan KPI Bulanan</h5>
            <button
              class="btn btn-sm btn-primary"
              id="exportMonthlyBtn">
              Export Excel
            </button>
          </div>
          <div class="card-body">
            <div class="table-container">
              <table class="table monthly-table" id="monthlyKpiTable">
                <thead>
                  <tr>
                    <th>Bulan</th>
                    <th>Tahun</th>
                    <th>Produktivitas</th>
                    <th>Kualitas Kerja</th>
                    <th>Kedisiplinan</th>
                    <th>Inisiatif</th>
                    <th>Kerjasama Tim</th>
                    <th>Total KPI</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>Januari</td>
                    <td class="tahun-cell"></td>
                    <!-- << kolom Tahun baru -->
                    <td class="monthly-score">85</td>
                    <td class="monthly-score">82</td>
                    <td class="monthly-score">90</td>
                    <td class="monthly-score">78</td>
                    <td class="monthly-score">85</td>
                    <td class="monthly-total">82.0</td>
                    <td>
                      <span class="kpi-badge badge-good">Baik</span>
                    </td>
                  </tr>
                  <tr>
                    <td>Febuari</td>
                    <td class="tahun-cell"></td>
                    <!-- << kolom Tahun baru -->
                    <td class="monthly-score">85</td>
                    <td class="monthly-score">82</td>
                    <td class="monthly-score">90</td>
                    <td class="monthly-score">78</td>
                    <td class="monthly-score">85</td>
                    <td class="monthly-total">82.0</td>
                    <td>
                      <span class="kpi-badge badge-good">Baik</span>
                    </td>
                  </tr>
                  <tr>
                    <td>Maret</td>
                    <td class="tahun-cell"></td>
                    <!-- << kolom Tahun baru -->
                    <td class="monthly-score">85</td>
                    <td class="monthly-score">82</td>
                    <td class="monthly-score">90</td>
                    <td class="monthly-score">78</td>
                    <td class="monthly-score">85</td>
                    <td class="monthly-total">82.0</td>
                    <td>
                      <span class="kpi-badge badge-good">Baik</span>
                    </td>
                  </tr>
                  <tr>
                    <td>April</td>
                    <td class="tahun-cell"></td>
                    <!-- << kolom Tahun baru -->
                    <td class="monthly-score">85</td>
                    <td class="monthly-score">82</td>
                    <td class="monthly-score">90</td>
                    <td class="monthly-score">78</td>
                    <td class="monthly-score">85</td>
                    <td class="monthly-total">82.0</td>
                    <td>
                      <span class="kpi-badge badge-good">Baik</span>
                    </td>
                  </tr>
                  <tr>
                    <td>Mei</td>
                    <td class="tahun-cell"></td>
                    <!-- << kolom Tahun baru -->
                    <td class="monthly-score">85</td>
                    <td class="monthly-score">82</td>
                    <td class="monthly-score">90</td>
                    <td class="monthly-score">78</td>
                    <td class="monthly-score">85</td>
                    <td class="monthly-total">82.0</td>
                    <td>
                      <span class="kpi-badge badge-good">Baik</span>
                    </td>
                  </tr>
                  <tr>
                    <td>Juni</td>
                    <td class="tahun-cell"></td>
                    <!-- << kolom Tahun baru -->
                    <td class="monthly-score">85</td>
                    <td class="monthly-score">82</td>
                    <td class="monthly-score">90</td>
                    <td class="monthly-score">78</td>
                    <td class="monthly-score">85</td>
                    <td class="monthly-total">82.0</td>
                    <td>
                      <span class="kpi-badge badge-good">Baik</span>
                    </td>
                  </tr>
                  <tr>
                    <td>Juli</td>
                    <td class="tahun-cell"></td>
                    <!-- << kolom Tahun baru -->
                    <td class="monthly-score">85</td>
                    <td class="monthly-score">82</td>
                    <td class="monthly-score">90</td>
                    <td class="monthly-score">78</td>
                    <td class="monthly-score">85</td>
                    <td class="monthly-total">82.0</td>
                    <td>
                      <span class="kpi-badge badge-good">Baik</span>
                    </td>
                  </tr>
                  <tr>
                    <td>Agustus</td>
                    <td class="tahun-cell"></td>
                    <!-- << kolom Tahun baru -->
                    <td class="monthly-score">85</td>
                    <td class="monthly-score">82</td>
                    <td class="monthly-score">90</td>
                    <td class="monthly-score">78</td>
                    <td class="monthly-score">85</td>
                    <td class="monthly-total">82.0</td>
                    <td>
                      <span class="kpi-badge badge-good">Baik</span>
                    </td>
                  </tr>
                  <tr>
                    <td>September</td>
                    <td class="tahun-cell"></td>
                    <!-- << kolom Tahun baru -->
                    <td class="monthly-score">85</td>
                    <td class="monthly-score">82</td>
                    <td class="monthly-score">90</td>
                    <td class="monthly-score">78</td>
                    <td class="monthly-score">85</td>
                    <td class="monthly-total">82.0</td>
                    <td>
                      <span class="kpi-badge badge-good">Baik</span>
                    </td>
                  </tr>
                  <tr>
                    <td>Oktober</td>
                    <td class="tahun-cell"></td>
                    <!-- << kolom Tahun baru -->
                    <td class="monthly-score">85</td>
                    <td class="monthly-score">82</td>
                    <td class="monthly-score">90</td>
                    <td class="monthly-score">78</td>
                    <td class="monthly-score">85</td>
                    <td class="monthly-total">82.0</td>
                    <td>
                      <span class="kpi-badge badge-good">Baik</span>
                    </td>
                  </tr>
                  <tr>
                    <td>November</td>
                    <td class="tahun-cell"></td>
                    <!-- << kolom Tahun baru -->
                    <td class="monthly-score">85</td>
                    <td class="monthly-score">82</td>
                    <td class="monthly-score">90</td>
                    <td class="monthly-score">78</td>
                    <td class="monthly-score">85</td>
                    <td class="monthly-total">82.0</td>
                    <td>
                      <span class="kpi-badge badge-good">Baik</span>
                    </td>
                  </tr>
                  <tr>
                    <td>Desember</td>
                    <td class="tahun-cell"></td>
                    <!-- << kolom Tahun baru -->
                    <td class="monthly-score">85</td>
                    <td class="monthly-score">82</td>
                    <td class="monthly-score">90</td>
                    <td class="monthly-score">78</td>
                    <td class="monthly-score">85</td>
                    <td class="monthly-total">82.0</td>
                    <td>
                      <span class="kpi-badge badge-good">Baik</span>
                    </td>
                  </tr>
                </tbody>
                <tfoot>
                  <tr class="table-active">
                    <th>Rata-rata</th>
                    <th></th>
                    <!-- Tahun -->
                    <th>88.8</th>
                    <th>85.8</th>
                    <th>92.0</th>
                    <th>83.0</th>
                    <th>83.0</th>
                    <th>86.5</th>
                    <th>
                      <span class="kpi-badge badge-good">Baik</span>
                    </th>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('script')
<script src="{{asset('assets/bundles/apexcharts.bundle.js')}}"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
let currentEmployeeId = {{ $employeeId ?? 'null' }};
let currentPeriodId = null;
let kpiData = null;
let availablePeriods = [];

// Load available periods untuk dropdown bulan
async function loadAvailablePeriods() {
    try {
        // Ambil semua periode yang sudah dipublish KPI-nya
        const response = await fetch('/api/periods?kpi_published=true');
        const data = await response.json();

        if (data.success) {
            availablePeriods = data.data;
            populateMonthDropdown(availablePeriods);
            
            // Auto-load bulan terbaru
            if (availablePeriods.length > 0) {
                const latestPeriod = availablePeriods[0];
                selectPeriod(latestPeriod);
            }
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        console.error('Error loading periods:', error);
        document.getElementById('monthList').innerHTML = '<li><h6 class="dropdown-header text-danger">Gagal memuat data bulan</h6></li>';
    }
}

// Populate dropdown bulan
function populateMonthDropdown(periods) {
    const monthList = document.getElementById('monthList');
    
    if (periods.length === 0) {
        monthList.innerHTML = '<li><h6 class="dropdown-header">Tidak ada data KPI</h6></li>';
        return;
    }

    let dropdownHTML = '';
    
    // Group periods by bulan-tahun untuk menghindari duplikat
    const monthYearMap = new Map();
    
    periods.forEach(period => {
        const startDate = new Date(period.tanggal_mulai);
        const monthYear = {
            month: startDate.toLocaleDateString('id-ID', { month: 'long' }),
            year: startDate.getFullYear(),
            periodId: period.id_periode,
            periodData: period
        };
        
        const key = `${monthYear.month}-${monthYear.year}`;
        if (!monthYearMap.has(key)) {
            monthYearMap.set(key, monthYear);
        }
    });

    // Convert Map to Array dan urutkan berdasarkan tahun dan bulan
    const sortedMonths = Array.from(monthYearMap.values()).sort((a, b) => {
        if (a.year !== b.year) return b.year - a.year;
        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                       'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        return months.indexOf(b.month) - months.indexOf(a.month);
    });

    sortedMonths.forEach(monthYear => {
        dropdownHTML += `
            <li>
                <a class="dropdown-item month-item" href="#" data-period-id="${monthYear.periodId}">
                    ${monthYear.month} ${monthYear.year}
                </a>
            </li>
        `;
    });
    
    monthList.innerHTML = dropdownHTML;

    // Add event listeners untuk setiap item bulan
    document.querySelectorAll('.month-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const periodId = this.getAttribute('data-period-id');
            const selectedPeriod = periods.find(p => p.id_periode == periodId);
            
            if (selectedPeriod) {
                selectPeriod(selectedPeriod);
            }
        });
    });
}

// Function ketika periode dipilih
function selectPeriod(period) {
    currentPeriodId = period.id_periode;
    
    // Update tampilan bulan
    const startDate = new Date(period.tanggal_mulai);
    const monthName = startDate.toLocaleDateString('id-ID', { month: 'long' });
    const year = startDate.getFullYear();
    
    document.getElementById('currentMonth').textContent = `${monthName} ${year}`;
    
    // Update tampilan periode (tanggal mulai - tanggal selesai)
    const startFormatted = startDate.toLocaleDateString('id-ID', { 
        day: '2-digit', 
        month: 'short', 
        year: 'numeric' 
    });
    const endDate = new Date(period.tanggal_selesai);
    const endFormatted = endDate.toLocaleDateString('id-ID', { 
        day: '2-digit', 
        month: 'short', 
        year: 'numeric' 
    });
    
    document.getElementById('periodRange').textContent = `${startFormatted} - ${endFormatted}`;
    
    // Load data KPI untuk periode yang dipilih
    loadKpiDetail(currentEmployeeId, currentPeriodId);
}

// Load data KPI detail
async function loadKpiDetail(employeeId, periodId = null) {
    try {
        let url = `/api/kpis/employee/${employeeId}/detail`;
        if (periodId) {
            url += `/${periodId}`;
        }

        const response = await fetch(url);
        const data = await response.json();

        if (data.success) {
            kpiData = data.data;
            updateEmployeeInfo(kpiData.employee);
            updateKpiSummary(kpiData.kpi_summary);
            updateKpiDetails(kpiData.kpi_details);
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        console.error('Error loading KPI detail:', error);
        alert('Gagal memuat data KPI: ' + error.message);
    }
}

// Update info karyawan
function updateEmployeeInfo(employee) {
    document.getElementById('employeeName').textContent = employee.nama;
    document.getElementById('employeeId').textContent = employee.id_karyawan;
    document.getElementById('employeeDivision').textContent = employee.division;
    document.getElementById('employeePosition').textContent = employee.position;
}

// Update summary KPI
function updateKpiSummary(summary) {
    const totalScore = parseFloat(summary.total_score) || 0;
    const averageScore = parseFloat(summary.average_score) || 0;
    
    document.getElementById('totalScore').textContent = totalScore.toFixed(2);
    document.getElementById('averageScore').textContent = averageScore.toFixed(2);
    document.getElementById('performanceScore').textContent = averageScore.toFixed(1);
    document.getElementById('performanceStatus').textContent = `(${summary.performance_status})`;
    document.getElementById('performanceText').textContent = `(${summary.performance_status})`;
    document.getElementById('ranking').textContent = summary.ranking;
    document.getElementById('rankingText').textContent = `(Dari ${summary.total_employees} Karyawan)`;
}

// ⚠️ PERBAIKAN: Di function updateKpiDetails()
function updateKpiDetails(details) {
    const tbody = document.getElementById('kpiDetailBody');
    const tfoot = document.getElementById('kpiDetailFooter');
    
    tbody.innerHTML = '';
    
    let totalBobot = 0;
    let totalKontribusi = 0;
    let totalNilaiTerbobot = 0;

    details.forEach(item => {
        const score = parseFloat(item.score) || 0;        
        const bobot = parseFloat(item.bobot) || 0;        
        const contribution = parseFloat(item.contribution) || 0; 
        
        // Nilai terbobot = score × (bobot/100)
        const nilaiTerbobot = score * (bobot / 100);
        
        const statusClass = getStatusClass(item.status);
        
        const row = `
            <tr>
                <td>${item.aspek_kpi}</td>
                <td>${bobot.toFixed(1)}%</td>
                <td>${score.toFixed(2)}</td>              
                <td>${contribution.toFixed(2)}%</td>      
                <td><span class="kpi-badge ${statusClass}">${item.status}</span></td>
                <td>
                    <div class="progress kpi-progress">
                        <div class="progress-bar bg-primary" role="progressbar" 
                            style="width: ${score}%"       
                            aria-valuenow="${score}" 
                            aria-valuemin="0" 
                            aria-valuemax="100"></div>
                    </div>
                    <div class="progress-percentage">${score.toFixed(1)}%</div>
                </td>
            </tr>
        `;
        
        tbody.innerHTML += row;
        
        totalBobot += bobot;
        totalKontribusi += contribution;
        totalNilaiTerbobot += nilaiTerbobot;
    });

    // ⚠️ PERBAIKAN CRITICAL: RUMUS YANG BENAR
    // averageScore = totalKontribusi (karena sudah weighted)
    const averageScore = totalKontribusi;
    
    const overallAchievement = totalKontribusi;
    const overallStatus = getOverallStatus(overallAchievement);
    const overallStatusClass = getStatusClass(overallStatus);
    
    tfoot.innerHTML = `
        <tr class="table-active">
            <th>Total</th>
            <th>${totalBobot.toFixed(1)}%</th>
            <th>${averageScore.toFixed(2)}</th>          <!-- ⚠️ PAKAI totalKontribusi -->
            <th>${totalKontribusi.toFixed(2)}%</th>       
            <th><span class="kpi-badge ${overallStatusClass}">${overallStatus}</span></th>
            <th>
                <div class="progress kpi-progress">
                    <div class="progress-bar bg-primary" role="progressbar" 
                         style="width: ${averageScore}%"   
                         aria-valuenow="${averageScore}" 
                         aria-valuemin="0" 
                         aria-valuemax="100"></div>
                </div>
                <div class="progress-percentage">${averageScore.toFixed(1)}%</div>
            </th>
        </tr>
    `;
    
    // ⚠️ DEBUG: Console log untuk cek perhitungan
    console.log("DEBUG PERHITUNGAN:");
    console.log("Total Bobot:", totalBobot);
    console.log("Total Kontribusi:", totalKontribusi);
    console.log("Total Nilai Terbobot:", totalNilaiTerbobot);
    console.log("Average Score:", averageScore);
}

// ⚠️ PERBAIKAN: Threshold status
function getOverallStatus(score) {
    const numericScore = parseFloat(score) || 0;
    if (numericScore >= 85) return 'Excellent';
    if (numericScore >= 75) return 'Good';
    if (numericScore >= 65) return 'Average';
    if (numericScore >= 50) return 'Below Average';
    return 'Poor';
}

function getStatusClass(status) {
    const statusMap = {
        'Excellent': 'badge-excellent',
        'Good': 'badge-good',
        'Average': 'badge-average',
        'Below Average': 'badge-average', // bisa gunakan class yang sama
        'Poor': 'badge-poor'
    };
    return statusMap[status] || 'badge-average';
}

function getOverallStatus(score) {
    const numericScore = parseFloat(score) || 0;
    if (numericScore >= 90) return 'Excellent';
    if (numericScore >= 80) return 'Good';
    if (numericScore >= 70) return 'Average';
    return 'Poor';
}

// Load data ketika halaman siap
document.addEventListener('DOMContentLoaded', function() {
    if (currentEmployeeId) {
        loadAvailablePeriods();
    }
    
    // Event listener untuk export Excel
    document.getElementById('exportExcelBtn').addEventListener('click', function() {
        exportToExcel();
    });
});

// Function untuk export Excel
function exportToExcel() {
    alert('Fitur export Excel akan diimplementasi');
}
</script>
@endsection