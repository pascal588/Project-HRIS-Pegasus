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
  .monthly-table {
    border-collapse: separate;
    border-spacing: 0;
    width: 100%;
    font-size: 0.875rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  }

  .monthly-table th {
    position: sticky;
    top: 0;
    background-color: #f8f9fa;
    z-index: 10;
    font-weight: 600;
    color: #2d3748;
    padding: 12px 15px;
    border-bottom: 2px solid #e2e8f0;
    text-align: center;
    white-space: nowrap;
  }

  .monthly-table td {
    padding: 10px 15px;
    border-bottom: 1px solid #e2e8f0;
    vertical-align: middle;
  }

  .monthly-table tbody tr {
    transition: background-color 0.15s ease;
  }

  .monthly-table tbody tr:hover {
    background-color: #f7fafc;
  }

  .monthly-table tbody tr:nth-child(even) {
    background-color: #fafbfc;
  }

  .monthly-table tbody tr:nth-child(even):hover {
    background-color: #f1f5f9;
  }

  .monthly-score {
    font-weight: 500;
    text-align: center;
    color: #4a5568;
  }

  .monthly-total {
    font-weight: 600;
    text-align: center;
    background-color: #f8f9fa;
    color: #2d3748;
  }

  .monthly-table tfoot tr {
    background-color: #edf2f7;
  }

  .monthly-table tfoot th {
    font-weight: 600;
    color: #2d3748;
    padding: 12px 15px;
    border-top: 2px solid #e2e8f0;
  }

  .table-container {
    max-height: 500px;
    overflow-y: auto;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
  }

  /* Header tabel yang tetap */
  .monthly-table thead th:first-child {
    border-top-left-radius: 8px;
  }

  .monthly-table thead th:last-child {
    border-top-right-radius: 8px;
  }

  /* Footer tabel */
  .monthly-table tfoot th:first-child {
    border-bottom-left-radius: 8px;
  }

  .monthly-table tfoot th:last-child {
    border-bottom-right-radius: 8px;
  }

  /* Kolom bulan */
  .monthly-table td:first-child {
    font-weight: 500;
    color: #4a5568;
    white-space: nowrap;
  }

  /* Responsif untuk mobile */
  @media (max-width: 768px) {
    .monthly-table {
      font-size: 0.8rem;
    }

    .monthly-table th,
    .monthly-table td {
      padding: 8px 10px;
    }

    .table-container {
      max-height: 400px;
    }
  }

  @media (max-width: 576px) {
    .monthly-table {
      font-size: 0.75rem;
    }

    .monthly-table th,
    .monthly-table td {
      padding: 6px 8px;
    }

    .kpi-badge {
      padding: 3px 6px;
      font-size: 0.7rem;
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
                  <li>
                    <h6 class="dropdown-header">Memuat bulan...</h6>
                  </li>
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
        Tahun: <span id="currentYear">{{ date('Y') }}</span>
      </button>
      <ul class="dropdown-menu" aria-labelledby="tahunDropdown" id="yearList">
        <li>
          <h6 class="dropdown-header">Memuat tahun...</h6>
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
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title">Rekapan KPI Bulanan</h5>
            {{-- Tombol Export akan kita aktifkan nanti jika diperlukan --}}
            <button class="btn btn-sm btn-primary" id="exportMonthlyBtn">Export Excel</button>
          </div>
          <div class="card-body">
            <div id="monthlyRecapLoader" class="text-center p-5">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
              <p class="mt-2">Memuat data rekapitulasi...</p>
            </div>
            <div class="table-container" id="monthlyRecapContainer" style="display: none;">
              <table class="table monthly-table" id="monthlyKpiTable">
                <thead id="monthlyKpiThead">
                </thead>
                <tbody id="monthlyKpiTbody">
                </tbody>
                <tfoot id="monthlyKpiTfoot">
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
  // ===================================================================================
// FIXED VERSION - DENGAN NULL CHECKING
// ===================================================================================

let currentEmployeeId = {{ $employeeId ?? 'null'}};
let kpiData = null;
let availablePeriods = [];
let kpiTrendChart = null;
let currentYear = new Date().getFullYear();

// Fungsi helper status
function getKpiStatus(score) {
    const numeric = parseFloat(score) || 0;
    if (numeric >= 90) return 'Sangat Baik';
    if (numeric >= 80) return 'Baik';
    if (numeric >= 70) return 'Cukup';
    if (numeric >= 50) return 'Kurang';
    return 'Sangat Kurang';
}

function getKpiStatusClass(status) {
    const map = {
        'Sangat Baik': 'badge-excellent',
        'Baik': 'badge-good',
        'Cukup': 'badge-average',
        'Kurang': 'badge-poor',
        'Sangat Kurang': 'badge-poor'
    };
    return map[status] || 'badge-average';
}

function getChartElement() {
    const chart = document.getElementById('kpiTrendChart');
    if (!chart) {
        console.error('❌ Element chart tidak ditemukan: #kpiTrendChart');
        console.log('🔍 Mencari element yang tersedia...');
        
        // Debug: Log semua element dengan ID mengandung 'chart'
        const allElements = document.querySelectorAll('[id*="chart"]');
        console.log('Element chart yang tersedia:', allElements);
        
        return null;
    }
    return chart;
}

function getChartContainer() {
    const chart = getChartElement();
    if (!chart) return null;
    
    const container = chart.parentElement;
    if (!container) {
        console.error('❌ Container chart tidak ditemukan');
        return null;
    }
    return container;
}

// --- BAGIAN 1: LOAD DROPDOWN TAHUN ---
async function loadAvailableYears() {
    try {
        console.log('🔄 Memuat dropdown tahun...');
        const response = await fetch('/api/kpis/available-years');
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        console.log('📅 Data tahun dari API:', data);

        if (data.success && data.data && data.data.length > 0) {
            populateYearDropdown(data.data);
        } else {
            generateFallbackYears();
        }
    } catch (error) {
        console.error('❌ Error loading years:', error);
        generateFallbackYears();
    }
}

function generateFallbackYears() {
    const years = [];
    const currentYear = new Date().getFullYear();
    for (let year = 2023; year <= currentYear + 1; year++) {
        years.push(year);
    }
    populateYearDropdown(years);
}

function populateYearDropdown(years) {
    const yearList = document.getElementById('yearList');
    if (!yearList) {
        console.error('❌ Element #yearList tidak ditemukan');
        return;
    }
    
    const sortedYears = [...years].sort((a, b) => b - a);
    
    let dropdownHTML = '';
    sortedYears.forEach(year => {
        dropdownHTML += `
            <li>
                <a class="dropdown-item year-item" href="#" data-tahun="${year}">
                    ${year}
                </a>
            </li>
        `;
    });
    
    yearList.innerHTML = dropdownHTML;

    // Set tahun default ke yang terbaru
    if (sortedYears.length > 0) {
        const latestYear = sortedYears[0];
        currentYear = latestYear;
        const currentYearElement = document.getElementById('currentYear');
        if (currentYearElement) {
            currentYearElement.textContent = latestYear;
        }
        console.log('✅ Tahun default:', latestYear);
        
        // Load data untuk tahun default
        loadYearlyData(currentYear);
    }
}

// --- BAGIAN 2: LOAD PERIODE BULANAN ---
async function loadAvailablePeriods() {
    try {
        console.log('🔄 Memulai loadAvailablePeriods...');
        const response = await fetch('/api/periods?kpi_published=true');

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        console.log('📅 Data periods:', data);

        if (!data.success) throw new Error(data.message);

        availablePeriods = data.data;
        console.log('✅ Available periods:', availablePeriods);

        populateMonthDropdown(availablePeriods);

        if (availablePeriods.length > 0) {
            selectPeriod(availablePeriods[0]);
        } else {
            const kpiDetailBody = document.getElementById('kpiDetailBody');
            if (kpiDetailBody) {
                kpiDetailBody.innerHTML = '<tr><td colspan="6" class="text-center">Tidak ada data KPI yang tersedia.</td></tr>';
            }
        }
    } catch (error) {
        console.error('❌ Error loading periods:', error);
        const monthList = document.getElementById('monthList');
        if (monthList) {
            monthList.innerHTML = '<li><h6 class="dropdown-header text-danger">Gagal memuat bulan: ' + error.message + '</h6></li>';
        }
    }
}

function populateMonthDropdown(periods) {
    const monthList = document.getElementById('monthList');
    if (!monthList) {
        console.error('❌ Element #monthList tidak ditemukan');
        return;
    }

    if (periods.length === 0) {
        monthList.innerHTML = '<li><h6 class="dropdown-header">Tidak ada data</h6></li>';
        return;
    }

    const sortedMonths = periods.map(period => ({
        month: new Date(period.tanggal_mulai).toLocaleDateString('id-ID', {
            month: 'long'
        }),
        year: new Date(period.tanggal_mulai).getFullYear(),
        periodId: period.id_periode,
        periodData: period
    })).sort((a, b) => new Date(b.periodData.tanggal_mulai) - new Date(a.periodData.tanggal_mulai));

    monthList.innerHTML = sortedMonths.map(m => 
        `<li><a class="dropdown-item month-item" href="#" data-period-id="${m.periodId}">${m.month} ${m.year}</a></li>`
    ).join('');

    document.querySelectorAll('.month-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const periodId = this.getAttribute('data-period-id');
            const selectedPeriod = periods.find(p => p.id_periode == periodId);
            if (selectedPeriod) selectPeriod(selectedPeriod);
        });
    });
}

function selectPeriod(period) {
    const startDate = new Date(period.tanggal_mulai);
    const monthName = startDate.toLocaleDateString('id-ID', {
        month: 'long'
    });
    const year = startDate.getFullYear();
    
    const currentMonthElement = document.getElementById('currentMonth');
    if (currentMonthElement) {
        currentMonthElement.textContent = `${monthName} ${year}`;
    }
    
    const startFormatted = startDate.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
    const endFormatted = new Date(period.tanggal_selesai).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
    
    const periodRangeElement = document.getElementById('periodRange');
    if (periodRangeElement) {
        periodRangeElement.textContent = `${startFormatted} - ${endFormatted}`;
    }
    
    loadKpiDetail(currentEmployeeId, period.id_periode);
}

// --- BAGIAN 3: LOAD DETAIL KPI ---
async function loadKpiDetail(employeeId, periodId) {
    try {
        console.log(`📊 Memuat detail KPI untuk employee ${employeeId}, period ${periodId}`);
        const url = `/api/kpis/employee/${employeeId}/detail/${periodId}`;
        const response = await fetch(url);

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        console.log('✅ Data KPI detail:', data);

        if (data.success) {
            kpiData = data.data;
            updateEmployeeInfo(kpiData.employee);
            updateKpiSummary(kpiData.kpi_summary);
            updateKpiDetails(kpiData.kpi_details);
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        console.error('❌ Error loading KPI detail:', error);
        const kpiDetailBody = document.getElementById('kpiDetailBody');
        if (kpiDetailBody) {
            kpiDetailBody.innerHTML = 
                `<tr><td colspan="6" class="text-center text-danger">Gagal memuat detail KPI: ${error.message}</td></tr>`;
        }
    }
}

function updateEmployeeInfo(employee) {
    const elements = {
        employeeName: document.getElementById('employeeName'),
        employeeId: document.getElementById('employeeId'),
        employeeDivision: document.getElementById('employeeDivision'),
        employeePosition: document.getElementById('employeePosition')
    };

    Object.keys(elements).forEach(key => {
        if (elements[key]) {
            elements[key].textContent = employee[key.replace('employee', '').toLowerCase()] || '-';
        }
    });
}

function updateKpiSummary(summary) {
    const elements = {
        totalScore: document.getElementById('totalScore'),
        averageScore: document.getElementById('averageScore'),
        performanceScore: document.getElementById('performanceScore'),
        performanceStatus: document.getElementById('performanceStatus'),
        performanceText: document.getElementById('performanceText'),
        ranking: document.getElementById('ranking'),
        rankingText: document.getElementById('rankingText')
    };

    if (elements.totalScore) elements.totalScore.textContent = (parseFloat(summary.total_score) || 0).toFixed(2);
    if (elements.averageScore) elements.averageScore.textContent = (parseFloat(summary.average_score) || 0).toFixed(2);
    if (elements.performanceScore) elements.performanceScore.textContent = (parseFloat(summary.average_score) || 0).toFixed(1);
    if (elements.performanceStatus) elements.performanceStatus.textContent = `(${summary.performance_status || '-'})`;
    if (elements.performanceText) elements.performanceText.textContent = `(${summary.performance_status || '-'})`;
    if (elements.ranking) elements.ranking.textContent = summary.ranking || '-';
    if (elements.rankingText) elements.rankingText.textContent = `(Dari ${summary.total_employees} Karyawan)`;
}

function updateKpiDetails(details) {
    const tbody = document.getElementById('kpiDetailBody');
    const tfoot = document.getElementById('kpiDetailFooter');
    
    if (!tbody) {
        console.error('❌ Element #kpiDetailBody tidak ditemukan');
        return;
    }

    tbody.innerHTML = '';

    if (!details || details.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center">Tidak ada rincian indikator untuk periode ini.</td></tr>';
        if (tfoot) tfoot.innerHTML = '';
        return;
    }

    let totalBobot = 0, totalKontribusi = 0, totalNilai = 0;

    details.forEach(item => {
        const nilai = parseFloat(item.score) || 0;
        const bobot = parseFloat(item.bobot) || 0;
        const kontribusi = bobot > 0 ? (nilai / bobot) * 100 : 0;
        const status = getKpiStatus(kontribusi);
        const statusClass = getKpiStatusClass(status);
        
        tbody.innerHTML += `
            <tr>
                <td>${item.aspek_kpi}</td>
                <td>${bobot.toFixed(1)}%</td>
                <td>${nilai.toFixed(2)}</td>
                <td>${kontribusi.toFixed(2)}%</td>
                <td><span class="kpi-badge ${statusClass}">${status}</span></td>
                <td>
                    <div class="progress kpi-progress">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: ${kontribusi}%" 
                             aria-valuenow="${kontribusi}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div class="progress-percentage">${kontribusi.toFixed(1)}%</div>
                </td>
            </tr>`;
        
        totalBobot += bobot;
        totalKontribusi += kontribusi;
        totalNilai += nilai;
    });

    const kontribusiTotal = details.length > 0 ? (totalKontribusi / details.length) : 0;
    const overallStatus = getKpiStatus(kontribusiTotal);
    const overallStatusClass = getKpiStatusClass(overallStatus);
    
    if (tfoot) {
        tfoot.innerHTML = `
            <tr class="table-active">
                <th>Total</th>
                <th>${totalBobot.toFixed(1)}%</th>
                <th>${totalNilai.toFixed(2)}</th>
                <th>${kontribusiTotal.toFixed(2)}%</th>
                <th><span class="kpi-badge ${overallStatusClass}">${overallStatus}</span></th>
                <th>...</th>
            </tr>`;
    }
}

async function loadAndRenderKpiTrendChart(employeeId, year) {
    try {
        console.log(`📈 Memuat chart tren KPI untuk employee ${employeeId}, tahun ${year}`);
        
        // Tunggu sebentar untuk memastikan DOM siap
        await new Promise(resolve => setTimeout(resolve, 100));
        
        // Cek element chart
        const chartElement = getChartElement();
        if (!chartElement) {
            console.error('❌ Tidak bisa memuat chart: element tidak ditemukan, mencoba create...');
            createChartElementIfMissing();
            return;
        }

        // Lanjutkan dengan logic chart yang ada...
        const monthlyScores = await getMonthlyScoresFromPeriods(employeeId, year);
        
        if (monthlyScores.length === 0) {
            showNoDataChart(year);
            return;
        }

        createChartFromScores(monthlyScores, year);

    } catch (error) {
        console.error('❌ Gagal memuat chart tren KPI:', error);
        showNoDataChart(year);
    }
}

function createChartElementIfMissing() {
    const chartContainer = document.querySelector('.chart-container');
    if (chartContainer && !document.getElementById('kpiTrendChart')) {
        console.log('🛠️ Creating missing chart element...');
        chartContainer.innerHTML = '<canvas id="kpiTrendChart"></canvas>';
    }
}

// Fungsi ambil data bulanan dari periods
async function getMonthlyScoresFromPeriods(employeeId, year) {
    console.log(`📊 Mengambil data bulanan dari periods untuk tahun ${year}`);
    
    const monthlyScores = [];
    const monthOrder = ['January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ];

    // Filter periods berdasarkan tahun
    const yearlyPeriods = availablePeriods.filter(period => {
        const periodYear = new Date(period.tanggal_mulai).getFullYear();
        return periodYear === year;
    });

    console.log(`📅 Periods untuk tahun ${year}:`, yearlyPeriods);

    if (yearlyPeriods.length === 0) {
        console.log('📭 Tidak ada periods untuk tahun ini');
        return monthlyScores;
    }

    // Urutkan periods berdasarkan bulan
    yearlyPeriods.sort((a, b) => new Date(a.tanggal_mulai) - new Date(b.tanggal_mulai));

    // Ambil data KPI untuk setiap period
    for (const period of yearlyPeriods) {
        try {
            const response = await fetch(`/api/kpis/employee/${employeeId}/detail/${period.id_periode}`);
            const data = await response.json();
            
            if (data.success) {
                const monthName = new Date(period.tanggal_mulai).toLocaleDateString('en-US', { month: 'long' });
                const monthShort = new Date(period.tanggal_mulai).toLocaleDateString('id-ID', { month: 'short' });
                const totalScore = data.data.kpi_summary.total_score;
                
                monthlyScores.push({
                    month_name: monthName,
                    month_short: monthShort,
                    total_score: totalScore,
                    period_name: period.nama,
                    period_date: period.tanggal_mulai
                });
                
                console.log(`✅ Data untuk ${monthName}: ${totalScore}`);
            }
        } catch (error) {
            console.log(`❌ Gagal load period ${period.id_periode}:`, error.message);
        }
    }

    // Urutkan berdasarkan bulan
    monthlyScores.sort((a, b) => monthOrder.indexOf(a.month_name) - monthOrder.indexOf(b.month_name));
    
    console.log('📈 Data monthly scores:', monthlyScores);
    return monthlyScores;
}

// Fungsi buat chart dari scores
function createChartFromScores(monthlyScores, year) {
    console.log('🎨 Membuat chart dari data real:', monthlyScores);

    const chartElement = getChartElement();
    if (!chartElement) {
        console.error('❌ Tidak bisa membuat chart: element tidak ditemukan');
        return;
    }

    // Siapkan data untuk chart
    const labels = monthlyScores.map(item => item.month_short);
    const scores = monthlyScores.map(item => parseFloat(item.total_score) || 0);

    console.log('🏷️ Labels:', labels);
    console.log('📊 Scores:', scores);

    // Hancurkan chart sebelumnya jika ada
    if (kpiTrendChart) {
        kpiTrendChart.destroy();
    }

    // Buat chart
    const ctx = chartElement.getContext('2d');
    
    kpiTrendChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Nilai KPI',
                data: scores,
                borderColor: '#4a90e2',
                backgroundColor: 'rgba(74, 144, 226, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#4a90e2',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 6,
                pointHoverRadius: 8
            },
            {
                label: 'Target (80)',
                data: Array(labels.length).fill(80),
                borderColor: '#ff6b6b',
                borderWidth: 2,
                borderDash: [5, 5],
                fill: false,
                pointRadius: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: `Perkembangan Nilai KPI - ${year}`,
                    font: {
                        size: 16
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += context.parsed.y.toFixed(2);
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    min: 0,
                    max: 100,
                    title: {
                        display: true,
                        text: 'Nilai KPI'
                    },
                    ticks: {
                        stepSize: 10
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

    console.log('✅ Chart berhasil dibuat dengan data real');
}

// Fungsi tampilkan chart "no data"
function showNoDataChart(year) {
    console.log('📭 Tidak ada data untuk chart, menampilkan pesan');

    const container = getChartContainer();
    if (!container) {
        console.error('❌ Tidak bisa menampilkan pesan no data: container tidak ditemukan');
        return;
    }

    container.innerHTML = `
        <div class="text-center p-5">
            <i class="icofont-chart-line fs-1 text-muted"></i>
            <h5 class="mt-3 text-muted">Perkembangan Nilai KPI</h5>
            <p class="text-muted">Tidak ada data KPI untuk tahun ${year}</p>
            <small class="text-muted">Data akan muncul setelah penilaian KPI dilakukan</small>
        </div>
    `;
}

// --- BAGIAN 5: TABEL REKAP BULANAN - DENGAN NULL CHECKING ---
async function loadAndRenderMonthlyRecap(employeeId, year) {
    const loader = document.getElementById('monthlyRecapLoader');
    const container = document.getElementById('monthlyRecapContainer');
    const thead = document.getElementById('monthlyKpiThead');
    const tbody = document.getElementById('monthlyKpiTbody');
    const tfoot = document.getElementById('monthlyKpiTfoot');

    if (!loader || !container || !thead || !tbody) {
        console.error('❌ Element tabel tidak ditemukan');
        return;
    }

    loader.style.display = 'block';
    container.style.display = 'none';

    try {
        console.log(`📋 Memuat rekap bulanan untuk employee ${employeeId}, tahun ${year}`);
        
        // GUNAKAN DATA DARI PERIODS
        const monthlyScores = await getMonthlyScoresFromPeriods(employeeId, year);

        if (monthlyScores.length === 0) {
            showNoDataTable(year);
            return;
        }

        // Buat tabel
        thead.innerHTML = `
            <tr>
                <th>Bulan</th>
                <th>Periode</th>
                <th>Total Nilai KPI</th>
                <th>Status</th>
            </tr>
        `;

        let bodyHtml = '';
        let totalNilai = 0;

        monthlyScores.forEach(item => {
            const totalScore = parseFloat(item.total_score) || 0;
            const status = getKpiStatus(totalScore);
            const statusClass = getKpiStatusClass(status);
            
            bodyHtml += `
                <tr>
                    <td class="fw-bold">${item.month_name}</td>
                    <td>${item.period_name}</td>
                    <td class="monthly-score">${totalScore.toFixed(2)}</td>
                    <td class="text-center"><span class="kpi-badge ${statusClass}">${status}</span></td>
                </tr>
            `;
            
            totalNilai += totalScore;
        });

        tbody.innerHTML = bodyHtml;

        // Footer dengan rata-rata
        const rataRata = totalNilai / monthlyScores.length;
        const rataRataStatus = getKpiStatus(rataRata);
        const rataRataStatusClass = getKpiStatusClass(rataRataStatus);
        
        if (tfoot) {
            tfoot.innerHTML = `
                <tr class="table-active">
                    <th colspan="2" class="text-end">Rata-rata Tahun ${year}:</th>
                    <th class="monthly-total">${rataRata.toFixed(2)}</th>
                    <th class="text-center"><span class="kpi-badge ${rataRataStatusClass}">${rataRataStatus}</span></th>
                </tr>
            `;
        }

        loader.style.display = 'none';
        container.style.display = 'block';
        console.log('✅ Tabel rekap berhasil dibuat');

    } catch (error) {
        console.error('❌ Gagal memuat rekap bulanan:', error);
        showNoDataTable(year);
    }
}

// Fungsi tampilkan tabel "no data"
function showNoDataTable(year) {
    const loader = document.getElementById('monthlyRecapLoader');
    const container = document.getElementById('monthlyRecapContainer');
    
    if (!loader) {
        console.error('❌ Element loader tidak ditemukan');
        return;
    }

    loader.innerHTML = `
        <div class="text-center p-5">
            <i class="icofont-table fs-1 text-muted"></i>
            <h5 class="mt-3 text-muted">Rekapan KPI Bulanan</h5>
            <p class="text-muted">Tidak ada data KPI untuk tahun ${year}</p>
            <small class="text-muted">Data akan muncul setelah penilaian KPI dilakukan</small>
        </div>
    `;
    loader.style.display = 'block';
    if (container) {
        container.style.display = 'none';
    }
}

// --- BAGIAN 6: FUNGSI UTAMA ---
function loadYearlyData(year) {
    if (!currentEmployeeId) {
        console.error('❌ currentEmployeeId tidak tersedia');
        return;
    }

    console.log(`🎯 Memuat data untuk tahun: ${year}`);
    currentYear = year;

    // Muat chart tren KPI
    loadAndRenderKpiTrendChart(currentEmployeeId, year);

    // Muat tabel rekap bulanan
    loadAndRenderMonthlyRecap(currentEmployeeId, year);
}

// --- BAGIAN 7: INISIALISASI HALAMAN ---
window.addEventListener('load', function() {
    console.log('🚀 Window Fully Loaded, currentEmployeeId:', currentEmployeeId);
    
    // Cek element chart
    const chartElement = getChartElement();
    if (!chartElement) {
        console.error('❌ Chart element masih tidak ditemukan setelah load');
        return;
    }

    // Cek semua element penting
    const requiredElements = [
        'yearList', 'monthList', 'kpiTrendChart', 'monthlyRecapLoader', 
        'monthlyRecapContainer', 'kpiDetailBody'
    ];

    requiredElements.forEach(id => {
        if (!document.getElementById(id)) {
            console.error(`❌ Element #${id} tidak ditemukan di DOM`);
        }
    });

    if (currentEmployeeId) {
        // 1. Load dropdown tahun DULU
        loadAvailableYears();
        
        // 2. Load dropdown bulan
        loadAvailablePeriods();

        // 3. Event listener untuk dropdown tahun
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('year-item')) {
                e.preventDefault();
                const selectedYear = e.target.getAttribute('data-tahun');
                console.log('📅 Tahun dipilih:', selectedYear);
                const currentYearElement = document.getElementById('currentYear');
                if (currentYearElement) {
                    currentYearElement.textContent = selectedYear;
                }
                loadYearlyData(parseInt(selectedYear));
            }
        });

// Ganti bagian export button di detail-kpi.blade.php
const exportBtn = document.getElementById('exportMonthlyBtn');
if (exportBtn) {
    exportBtn.addEventListener('click', function() {
        if (!currentEmployeeId) {
            alert('❌ ID Karyawan tidak valid!');
            return;
        }

        const button = this;
        const originalText = button.textContent;
        button.textContent = '⏳ Mengekspor...';
        button.disabled = true;

        try {
            console.log(`📤 Memulai export untuk employee: ${currentEmployeeId}, tahun: ${currentYear}`);
            
            // GUNAKAN ENDPOINT YANG BARU
            const exportUrl = `/api/kpis/export-monthly/${currentEmployeeId}/${currentYear}`;
            
            console.log('🔗 Export URL:', exportUrl);
            
            // Buat elemen <a> sementara untuk download
            const link = document.createElement('a');
            link.href = exportUrl;
            link.target = '_blank';
            link.style.display = 'none';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            console.log('✅ Export berhasil diproses!');
            
        } catch (error) {
            console.error('❌ Gagal mengekspor:', error);
            alert('Gagal mengekspor: ' + error.message);
        } finally {
            setTimeout(() => {
                button.textContent = originalText;
                button.disabled = false;
            }, 3000);
        }
    });
}

// Helper function untuk extract filename dari response header
function getFilenameFromResponse(response) {
    const contentDisposition = response.headers.get('content-disposition');
    if (contentDisposition) {
        const filenameMatch = contentDisposition.match(/filename="(.+)"/);
        if (filenameMatch) {
            return filenameMatch[1];
        }
    }
    return null;
}

    } else {
        console.error('❌ currentEmployeeId tidak ditemukan');
        const body = document.body;
        if (body) {
            body.innerHTML = '<div class="alert alert-danger">ID Karyawan tidak ditemukan.</div>';
        }
    }
});

// Error handling global
window.addEventListener('error', function(e) {
    console.error('💥 Global Error:', e.error);
    console.error('💥 Error details:', e.message, e.filename, e.lineno);
});

console.log('✅ JavaScript detail-kpi.js loaded successfully!');
</script>
@endsection