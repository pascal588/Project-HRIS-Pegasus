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
  // KODE YANG SUDAH DIPERBAIKI DENGAN DEBUGGING
  // ===================================================================================

  let currentEmployeeId = {{ $employeeId ?? 'null'}};
  let kpiData = null;
  let availablePeriods = [];
  let kpiTrendChart = null;

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

  // --- BAGIAN 1: FUNGSI DETAIL KPI (BAGIAN ATAS) ---
  async function loadAvailablePeriods() {
    try {
      console.log('Memulai loadAvailablePeriods...');
      const response = await fetch('/api/periods?kpi_published=true');

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();
      console.log('Data periods:', data);

      if (!data.success) throw new Error(data.message);

      availablePeriods = data.data;
      console.log('Available periods:', availablePeriods);

      populateMonthDropdown(availablePeriods);

      if (availablePeriods.length > 0) {
        selectPeriod(availablePeriods[0]);
      } else {
        document.getElementById('kpiDetailBody').innerHTML = '<tr><td colspan="6" class="text-center">Tidak ada data KPI yang tersedia.</td></tr>';
      }
    } catch (error) {
      console.error('Error loading periods:', error);
      document.getElementById('monthList').innerHTML = '<li><h6 class="dropdown-header text-danger">Gagal memuat bulan: ' + error.message + '</h6></li>';
    }
  }

  function populateMonthDropdown(periods) {
    const monthList = document.getElementById('monthList');
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

    monthList.innerHTML = sortedMonths.map(m => `<li><a class="dropdown-item month-item" href="#" data-period-id="${m.periodId}">${m.month} ${m.year}</a></li>`).join('');

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
    document.getElementById('currentMonth').textContent = `${monthName} ${year}`;
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
    document.getElementById('periodRange').textContent = `${startFormatted} - ${endFormatted}`;
    loadKpiDetail(currentEmployeeId, period.id_periode);
  }

  async function loadKpiDetail(employeeId, periodId) {
    try {
      console.log(`Memuat detail KPI untuk employee ${employeeId}, period ${periodId}`);
      const url = `/api/kpis/employee/${employeeId}/detail/${periodId}`;
      const response = await fetch(url);

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();
      console.log('Data KPI detail:', data);

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
      document.getElementById('kpiDetailBody').innerHTML = `<tr><td colspan="6" class="text-center text-danger">Gagal memuat detail KPI: ${error.message}</td></tr>`;
    }
  }

  function updateEmployeeInfo(employee) {
    document.getElementById('employeeName').textContent = employee.nama || '-';
    document.getElementById('employeeId').textContent = employee.id_karyawan || '-';
    document.getElementById('employeeDivision').textContent = employee.division || '-';
    document.getElementById('employeePosition').textContent = employee.position || '-';
  }

  function updateKpiSummary(summary) {
    document.getElementById('totalScore').textContent = (parseFloat(summary.total_score) || 0).toFixed(2);
    document.getElementById('averageScore').textContent = (parseFloat(summary.average_score) || 0).toFixed(2);
    document.getElementById('performanceScore').textContent = (parseFloat(summary.average_score) || 0).toFixed(1);
    document.getElementById('performanceStatus').textContent = `(${summary.performance_status || '-'})`;
    document.getElementById('performanceText').textContent = `(${summary.performance_status || '-'})`;
    document.getElementById('ranking').textContent = summary.ranking || '-';
    document.getElementById('rankingText').textContent = `(Dari ${summary.total_employees} Karyawan)`;
  }

  function updateKpiDetails(details) {
    const tbody = document.getElementById('kpiDetailBody');
    const tfoot = document.getElementById('kpiDetailFooter');
    tbody.innerHTML = '';

    if (!details || details.length === 0) {
      tbody.innerHTML = '<tr><td colspan="6" class="text-center">Tidak ada rincian indikator untuk periode ini.</td></tr>';
      tfoot.innerHTML = '';
      return;
    }

    let totalBobot = 0,
      totalKontribusi = 0,
      totalNilai = 0;

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
                        <div class="progress-bar bg-primary" role="progressbar" style="width: ${kontribusi}%" aria-valuenow="${kontribusi}" aria-valuemin="0" aria-valuemax="100"></div>
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

  // --- BAGIAN BARU: FUNGSI UNTUK CHART TREN KPI (LINE CHART MINIMALIS) ---
  async function loadAndRenderKpiTrendChart(employeeId, year) {
    try {
      console.log(`Memuat chart tren KPI untuk employee ${employeeId}, tahun ${year}`);
      const response = await fetch(`/api/report/kpi/monthly-data/${employeeId}?year=${year}`);

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const result = await response.json();
      console.log('Data untuk chart:', result);

      if (!result || !result.kpi_data || result.kpi_data.length === 0) {
        const chartContainer = document.getElementById('kpiTrendChart').parentElement;
        chartContainer.innerHTML = '<div class="text-center p-5"><p>Tidak ada data KPI untuk tahun ' + year + '</p></div>';
        return;
      }

      const monthlyData = result.kpi_data;
      console.log('Monthly data untuk chart:', monthlyData);

      // Urutkan data berdasarkan bulan
      const monthOrder = ['January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
      ];

      monthlyData.sort((a, b) => {
        return monthOrder.indexOf(a.month_name) - monthOrder.indexOf(b.month_name);
      });

      // Siapkan data untuk chart
      const labels = monthlyData.map(item => item.month_name);
      const scores = monthlyData.map(item => parseFloat(item.total_score) || 0);

      // Target line (80)
      const targetData = Array(labels.length).fill(80);

      // Hancurkan chart sebelumnya jika ada
      if (kpiTrendChart) {
        kpiTrendChart.destroy();
      }

      // Buat gradient untuk area chart
      const ctx = document.getElementById('kpiTrendChart').getContext('2d');
      const gradient = ctx.createLinearGradient(0, 0, 0, 400);
      gradient.addColorStop(0, 'rgba(74, 144, 226, 0.3)');
      gradient.addColorStop(0.7, 'rgba(74, 144, 226, 0.1)');
      gradient.addColorStop(1, 'rgba(74, 144, 226, 0.01)');

      // Buat chart baru dengan desain minimalis
      kpiTrendChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: labels,
          datasets: [{
              label: 'Nilai KPI',
              data: scores,
              borderColor: '#4a90e2',
              backgroundColor: gradient,
              borderWidth: 4,
              fill: true,
              tension: 0.4,
              pointBackgroundColor: '#4a90e2',
              pointBorderColor: '#ffffff',
              pointBorderWidth: 3,
              pointRadius: 6,
              pointHoverRadius: 10,
              pointHoverBackgroundColor: '#357abd',
              pointHoverBorderColor: '#ffffff',
              pointHoverBorderWidth: 4
            },
            {
              label: 'Target Minimum',
              data: targetData,
              borderColor: '#ff6b6b',
              borderWidth: 2,
              borderDash: [6, 4],
              fill: false,
              pointRadius: 0,
              pointHoverRadius: 0
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'top',
              labels: {
                color: '#2d3748',
                font: {
                  size: 12,
                  weight: '600'
                },
                padding: 20,
                usePointStyle: true,
                pointStyle: 'circle'
              }
            },
            tooltip: {
              mode: 'index',
              intersect: false,
              backgroundColor: 'rgba(45, 55, 72, 0.95)',
              titleColor: '#f7fafc',
              bodyColor: '#f7fafc',
              borderColor: '#4a90e2',
              borderWidth: 1,
              cornerRadius: 8,
              padding: 12,
              displayColors: true,
              callbacks: {
                label: function(context) {
                  let label = context.dataset.label || '';
                  if (label) {
                    label += ': ';
                  }
                  const value = context.parsed.y;
                  label += value.toFixed(2);

                  if (context.datasetIndex === 0) {
                    const status = value >= 80 ? '✅' : value >= 70 ? '⚠️' : '❌';
                    label += ` ${status}`;
                  }
                  return label;
                }
              }
            }
          },
          scales: {
            y: {
              beginAtZero: false,
              min: 60,
              max: 100,
              grid: {
                color: 'rgba(0, 0, 0, 0.06)',
                drawBorder: false
              },
              ticks: {
                color: '#718096',
                font: {
                  size: 11,
                  weight: '500'
                },
                callback: function(value) {
                  return value + '';
                }
              },
              title: {
                display: true,
                text: 'Nilai KPI',
                color: '#4a5568',
                font: {
                  size: 13,
                  weight: '600'
                }
              }
            },
            x: {
              grid: {
                display: false,
                drawBorder: false
              },
              ticks: {
                color: '#718096',
                font: {
                  size: 11,
                  weight: '500'
                }
              },
              title: {
                display: true,
                text: 'Periode Bulanan - ' + year,
                color: '#4a5568',
                font: {
                  size: 13,
                  weight: '600'
                }
              }
            }
          },
          interaction: {
            intersect: false,
            mode: 'nearest'
          },
          elements: {
            line: {
              tension: 0.4
            }
          }
        }
      });

    } catch (error) {
      console.error('Gagal memuat chart tren KPI:', error);
      const chartContainer = document.getElementById('kpiTrendChart').parentElement;
      chartContainer.innerHTML = '<div class="text-center text-danger p-5"><p>Terjadi kesalahan saat memuat chart: ' + error.message + '</p></div>';
    }
  }


  // --- BAGIAN 2: FUNGSI UNTUK TABEL REKAP BULANAN ---
  async function loadAndRenderMonthlyRecap(employeeId, year) {
    const loader = document.getElementById('monthlyRecapLoader');
    const container = document.getElementById('monthlyRecapContainer');
    const thead = document.getElementById('monthlyKpiThead');
    const tbody = document.getElementById('monthlyKpiTbody');
    const tfoot = document.getElementById('monthlyKpiTfoot');

    loader.style.display = 'block';
    container.style.display = 'none';
    thead.innerHTML = '';
    tbody.innerHTML = '';
    tfoot.innerHTML = '';

    try {
      console.log(`Memuat rekap bulanan untuk employee ${employeeId}, tahun ${year}`);
      const response = await fetch(`/api/report/kpi/monthly-data/${employeeId}?year=${year}`);

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const result = await response.json();
      console.log('Data untuk rekap bulanan:', result);

      if (!result || !result.kpi_data || result.kpi_data.length === 0) {
        loader.innerHTML = '<p class="text-center p-5">Tidak ada data rekap KPI untuk ditampilkan pada tahun yang dipilih.</p>';
        return;
      }

      const monthlyData = result.kpi_data;
      const aspectHeaders = [...new Set(monthlyData.flatMap(m => m.details.map(d => d.aspect_name)))].sort();
      const pivotedData = monthlyData.map(month => {
        const row = {
          month: month.month_name,
          year: month.year,
          total: month.total_score,
          status: getKpiStatus(month.total_score),
          scores: {}
        };
        for (const detail of month.details) {
          row.scores[detail.aspect_name] = (row.scores[detail.aspect_name] || 0) + detail.sub_aspect_score;
        }
        return row;
      });

      // Urutkan data berdasarkan bulan
      const monthOrder = ['January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
      ];

      pivotedData.sort((a, b) => {
        return monthOrder.indexOf(a.month) - monthOrder.indexOf(b.month);
      });

      let headerHtml = '<tr><th>Bulan</th>';
      aspectHeaders.forEach(h => headerHtml += `<th>${h}</th>`);
      headerHtml += '<th>Total KPI</th><th>Status</th></tr>';
      thead.innerHTML = headerHtml;

      let bodyHtml = '';
      pivotedData.forEach(row => {
        bodyHtml += `<tr><td class="text-nowrap">${row.month}</td>`;
        aspectHeaders.forEach(h => bodyHtml += `<td class="monthly-score">${(row.scores[h] || 0).toFixed(2)}</td>`);
        bodyHtml += `<td class="monthly-total">${row.total.toFixed(2)}</td>`;
        const statusClass = getKpiStatusClass(row.status);
        bodyHtml += `<td class="text-center"><span class="kpi-badge ${statusClass}">${row.status}</span></td></tr>`;
      });
      tbody.innerHTML = bodyHtml;

      const averages = {
        total: 0
      };
      aspectHeaders.forEach(h => averages[h] = 0);
      pivotedData.forEach(row => {
        aspectHeaders.forEach(h => averages[h] += (row.scores[h] || 0));
        averages.total += row.total;
      });

      const dataCount = pivotedData.length;
      if (dataCount > 0) {
        let footerHtml = '<tr class="table-active"><th>Rata-rata</th>';
        aspectHeaders.forEach(h => footerHtml += `<th class="monthly-score">${(averages[h] / dataCount).toFixed(2)}</th>`);
        const avgTotal = averages.total / dataCount;
        const avgStatus = getKpiStatus(avgTotal);
        const avgStatusClass = getKpiStatusClass(avgStatus);
        footerHtml += `<th class="monthly-total">${avgTotal.toFixed(2)}</th>`;
        footerHtml += `<th class="text-center"><span class="kpi-badge ${avgStatusClass}">${avgStatus}</span></th></tr>`;
        tfoot.innerHTML = footerHtml;
      }

      loader.style.display = 'none';
      container.style.display = 'block';

    } catch (error) {
      console.error('Gagal memuat rekap bulanan:', error);
      loader.innerHTML = `<p class="text-center text-danger p-5">Terjadi kesalahan: ${error.message}</p>`;
    }
  }

  // --- FUNGSI UNTUK MEMUAT SEMUA DATA BERDASARKAN TAHUN ---
  function loadYearlyData(year) {
    if (!currentEmployeeId) {
      console.error('currentEmployeeId tidak tersedia');
      return;
    }

    console.log(`Memuat data untuk tahun: ${year}`);

    // Update teks dropdown tahun
    document.querySelector('#tahunDropdown').textContent = `Tahun: ${year}`;

    // Muat chart tren KPI
    loadAndRenderKpiTrendChart(currentEmployeeId, year);

    // Muat tabel rekap bulanan
    loadAndRenderMonthlyRecap(currentEmployeeId, year);
  }

  // --- INISIALISASI HALAMAN ---
  document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded, currentEmployeeId:', currentEmployeeId);

    if (currentEmployeeId) {
      // Muat data periode untuk dropdown bulan
      loadAvailablePeriods();

      const currentYear = new Date().getFullYear();
      console.log('Tahun saat ini:', currentYear);

      // Muat data untuk tahun saat ini (chart dan tabel)
      loadYearlyData(currentYear);

      // Event listener untuk dropdown tahun
      document.querySelectorAll('#tahunDropdown + .dropdown-menu a').forEach(item => {
        item.addEventListener('click', function(e) {
          e.preventDefault();
          const selectedYear = this.getAttribute('data-tahun');
          console.log('Tahun dipilih:', selectedYear);
          loadYearlyData(selectedYear);
        });
      });

      // Tombol export
      document.getElementById('exportMonthlyBtn').addEventListener('click', async function() {
        if (!currentEmployeeId) {
          alert('ID Karyawan tidak valid!');
          return;
        }

        const button = this;
        const exportUrl = `/api/report/kpi/monthly-export/${currentEmployeeId}`;

        button.textContent = 'Mengekspor...';
        button.disabled = true;

        try {
          const response = await fetch(exportUrl);

          if (!response.ok) {
            throw new Error(`Gagal mengunduh file. Status: ${response.statusText}`);
          }

          const disposition = response.headers.get('content-disposition');
          let fileName = 'laporan-kpi.xlsx';
          if (disposition && disposition.indexOf('attachment') !== -1) {
            const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
            const matches = filenameRegex.exec(disposition);
            if (matches != null && matches[1]) {
              fileName = matches[1].replace(/['"]/g, '');
            }
          }

          const blob = await response.blob();
          const url = window.URL.createObjectURL(blob);
          const a = document.createElement('a');
          a.style.display = 'none';
          a.href = url;
          a.download = fileName;
          document.body.appendChild(a);
          a.click();
          window.URL.revokeObjectURL(url);
          a.remove();

        } catch (error) {
          console.error('Terjadi kesalahan saat mengekspor:', error);
          alert('Gagal mengekspor data. Silakan coba lagi.');
        } finally {
          button.textContent = 'Export Excel';
          button.disabled = false;
        }
      });

    } else {
      console.error('currentEmployeeId tidak ditemukan');
      document.body.innerHTML = '<div class="alert alert-danger">ID Karyawan tidak ditemukan.</div>';
    }
  });
</script>
@endsection