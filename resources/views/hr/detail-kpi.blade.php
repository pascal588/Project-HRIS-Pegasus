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
  // KODE YANG SUDAH DIPERBAIKI DAN DISEMPURNAKAN
  // ===================================================================================

  // [FIX] Hapus kurung kurawal {} ekstra di sekitar Blade directive
  let currentEmployeeId = {{ $employeeId ?? 'null' }};
  let kpiData = null;
  let availablePeriods = [];

  // [IMPROVEMENT] Menggabungkan fungsi helper status menjadi satu set yang efisien
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
      const response = await fetch('/api/periods?kpi_published=true');
      const data = await response.json();
      if (!data.success) throw new Error(data.message);

      availablePeriods = data.data;
      populateMonthDropdown(availablePeriods);

      if (availablePeriods.length > 0) {
        selectPeriod(availablePeriods[0]);
      } else {
        document.getElementById('kpiDetailBody').innerHTML = '<tr><td colspan="6" class="text-center">Tidak ada data KPI yang tersedia.</td></tr>';
      }
    } catch (error) {
      console.error('Error loading periods:', error);
      document.getElementById('monthList').innerHTML = '<li><h6 class="dropdown-header text-danger">Gagal memuat bulan</h6></li>';
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
      const url = `/api/kpis/employee/${employeeId}/detail/${periodId}`;
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


  // --- BAGIAN 2: FUNGSI UNTUK TABEL REKAP BULANAN (BAGIAN BAWAH) ---
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
      const response = await fetch(`/api/hr/kpi/monthly-data/${employeeId}?year=${year}`);
      const result = await response.json();
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

      let headerHtml = '<tr><th>Bulan</th><th>Tahun</th>';
      aspectHeaders.forEach(h => headerHtml += `<th>${h}</th>`);
      headerHtml += '<th>Total KPI</th><th>Status</th></tr>';
      thead.innerHTML = headerHtml;

      let bodyHtml = '';
      pivotedData.forEach(row => {
        bodyHtml += `<tr><td>${row.month}</td><td>${row.year}</td>`;
        aspectHeaders.forEach(h => bodyHtml += `<td class="monthly-score">${(row.scores[h] || 0).toFixed(2)}</td>`);
        bodyHtml += `<td class="monthly-total">${row.total.toFixed(2)}</td>`;
        const statusClass = getKpiStatusClass(row.status);
        bodyHtml += `<td><span class="kpi-badge ${statusClass}">${row.status}</span></td></tr>`;
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
        let footerHtml = '<tr class="table-active"><th>Rata-rata</th><th></th>';
        aspectHeaders.forEach(h => footerHtml += `<th>${(averages[h] / dataCount).toFixed(2)}</th>`);
        const avgTotal = averages.total / dataCount;
        const avgStatus = getKpiStatus(avgTotal);
        const avgStatusClass = getKpiStatusClass(avgStatus);
        footerHtml += `<th>${avgTotal.toFixed(2)}</th>`;
        footerHtml += `<th><span class="kpi-badge ${avgStatusClass}">${avgStatus}</span></th></tr>`;
        tfoot.innerHTML = footerHtml;
      }
      loader.style.display = 'none';
      container.style.display = 'block';
    } catch (error) {
      console.error('Gagal memuat rekap bulanan:', error);
      loader.innerHTML = `<p class="text-center text-danger p-5">Terjadi kesalahan: ${error.message}</p>`;
    }
  }

  // --- INISIALISASI HALAMAN ---
  document.addEventListener('DOMContentLoaded', function() {
    if (currentEmployeeId) {
      loadAvailablePeriods();
      const currentYear = new Date().getFullYear();
      document.querySelector('#tahunDropdown').textContent = `Tahun: ${currentYear}`;
      loadAndRenderMonthlyRecap(currentEmployeeId, currentYear);

      document.querySelectorAll('#tahunDropdown + .dropdown-menu a').forEach(item => {
        item.addEventListener('click', function(e) {
          e.preventDefault();
          const selectedYear = this.getAttribute('data-tahun');
          document.querySelector('#tahunDropdown').textContent = `Tahun: ${selectedYear}`;
          loadAndRenderMonthlyRecap(currentEmployeeId, selectedYear);
        });
      });

      document.getElementById('exportExcelBtn').addEventListener('click', function() {
        alert('Fitur export Excel akan diimplementasi');
      });

      document.getElementById('exportMonthlyBtn').addEventListener('click', async function() {
    // 1. Validasi ID Karyawan
    if (!currentEmployeeId) {
        alert('ID Karyawan tidak valid!');
        return;
    }

    const button = this;
    const exportUrl = `/api/hr/kpi/monthly-export/${currentEmployeeId}`;

    // 2. Beri feedback ke pengguna
    button.textContent = 'Mengekspor...';
    button.disabled = true;

    try {
        // 3. Lakukan request ke server secara asynchronous
        const response = await fetch(exportUrl);

        // Jika response gagal (misal: error 404 atau 500)
        if (!response.ok) {
            throw new Error(`Gagal mengunduh file. Status: ${response.statusText}`);
        }

        // 4. Ambil nama file dari header 'Content-Disposition'
        const disposition = response.headers.get('content-disposition');
        let fileName = 'laporan-kpi.xlsx'; // Nama default
        if (disposition && disposition.indexOf('attachment') !== -1) {
            const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
            const matches = filenameRegex.exec(disposition);
            if (matches != null && matches[1]) {
                fileName = matches[1].replace(/['"]/g, '');
            }
        }

        // 5. Ubah response menjadi Blob (objek file)
        const blob = await response.blob();

        // 6. Buat URL sementara untuk file yang ada di memori browser
        const url = window.URL.createObjectURL(blob);

        // 7. Buat link <a> "tak terlihat" untuk memicu download
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = url;
        a.download = fileName; // Gunakan nama file dari server
        document.body.appendChild(a);

        // 8. "Klik" link tersebut secara otomatis
        a.click();

        // 9. Lakukan pembersihan
        window.URL.revokeObjectURL(url);
        a.remove();

    } catch (error) {
        console.error('Terjadi kesalahan saat mengekspor:', error);
        alert('Gagal mengekspor data. Silakan coba lagi.');
    } finally {
        // 10. Kembalikan kondisi tombol seperti semula
        button.textContent = 'Export Excel';
        button.disabled = false;
    }
});

    } else {
      document.body.innerHTML = '<div class="alert alert-danger">ID Karyawan tidak ditemukan.</div>';
    }
  });
</script>
@endsection