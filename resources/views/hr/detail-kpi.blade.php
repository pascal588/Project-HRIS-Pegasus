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
            <div
              class="d-flex flex-wrap justify-content-between align-items-start">
              <!-- Info Karyawan -->
              <div class="mb-2">
                <h4 class="fw-bold mb-0">Detail KPI Karyawan</h4>
                <div class="d-flex flex-wrap align-items-center mt-2">
                  <span class="me-3"><strong>Nama:</strong> John Doe</span>
                  <span class="me-3"><strong>ID Karyawan:</strong> EMP-00123</span>
                  <span class="me-3"><strong>Divisi:</strong> IT</span>
                  <span><strong>Jabatan:</strong> Staff</span>
                </div>
              </div>

              <!-- Dropdown Periode -->
              <div class="dropdown mt-2 mb-3">
                <button
                  class="btn btn-primary dropdown-toggle"
                  type="button"
                  id="bulanDropdown"
                  data-bs-toggle="dropdown"
                  aria-expanded="false">
                  Bulan: Januari
                </button>
                <ul
                  class="dropdown-menu"
                  aria-labelledby="bulanDropdown">
                  <li>
                    <h6 class="dropdown-header">Pilih Bulan</h6>
                  </li>
                  <li>
                    <a
                      class="dropdown-item"
                      href="#"
                      data-bulan="Januari">Januari</a>
                  </li>
                  <li>
                    <a
                      class="dropdown-item"
                      href="#"
                      data-bulan="Februari">Februari</a>
                  </li>
                  <li>
                    <a class="dropdown-item" href="#" data-bulan="Maret">Maret</a>
                  </li>
                  <li>
                    <a class="dropdown-item" href="#" data-bulan="Maret">April</a>
                  </li>
                  <li>
                    <a class="dropdown-item" href="#" data-bulan="Maret">Mei</a>
                  </li>
                  <li>
                    <a class="dropdown-item" href="#" data-bulan="Maret">Juni</a>
                  </li>

                  <li>
                    <a class="dropdown-item" href="#" data-bulan="Maret">Juli</a>
                  </li>

                  <li>
                    <a class="dropdown-item" href="#" data-bulan="Maret">Agustus</a>
                  </li>

                  <li>
                    <a class="dropdown-item" href="#" data-bulan="Maret">September</a>
                  </li>

                  <li>
                    <a class="dropdown-item" href="#" data-bulan="Maret">Oktober</a>
                  </li>

                  <li>
                    <a class="dropdown-item" href="#" data-bulan="Maret">November</a>
                  </li>

                  <li>
                    <a class="dropdown-item" href="#" data-bulan="Maret">Desember</a>
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
            88.5 <small class="fs-6">(Bagus)</small>
          </div>
        </div>
      </div>
      <div class="col-6 col-sm-4 col-md-3">
        <div class="stat-card bg-primary">
          <div class="icon"><i class="icofont-trophy"></i></div>
          <div class="title">Target</div>
          <div class="value">
            85.0 <small class="fs-6">(Melebihi)</small>
          </div>
        </div>
      </div>
      <div class="col-6 col-sm-4 col-md-3">
        <div class="stat-card bg-primary">
          <div class="icon"><i class="icofont-chart-line"></i></div>
          <div class="title">Performa</div>
          <div class="value">
            7.8 <small class="fs-6">(Di Atas Rata-rata)</small>
          </div>
        </div>
      </div>
      <div class="col-6 col-sm-4 col-md-3">
        <div class="stat-card bg-primary">
          <div class="icon"><i class="icofont-medal"></i></div>
          <div class="title">Peringkat</div>
          <div class="value">
            12 <small class="fs-6">(Dari 50 Karyawan)</small>
          </div>
        </div>
      </div>
    </div>

    <!-- Detail Indikator KPI -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="card">
          <div
            class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title">Indikator KPI</h5>
            <button class="btn btn-sm btn-primary">Export Excel</button>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table detail-table">
                <thead>
                  <tr>
                    <th>Indikator</th>
                    <th>Bobot</th>
                    <th>Target</th>
                    <th>Pencapaian</th>
                    <th>Nilai</th>
                    <th>Status</th>
                    <th>Progress</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>Produktivitas</td>
                    <td>30%</td>
                    <td>90%</td>
                    <td>95%</td>
                    <td>28.5</td>
                    <td>
                      <span class="kpi-badge badge-excellent">Bagus</span>
                    </td>
                    <td>
                      <div class="progress kpi-progress">
                        <div
                          class="progress-bar bg-primary"
                          role="progressbar"
                          style="width: 95%"
                          aria-valuenow="95"
                          aria-valuemin="0"
                          aria-valuemax="100"></div>
                      </div>
                      <div class="progress-percentage">95%</div>
                    </td>
                  </tr>
                  <tr>
                    <td>Kualitas Kerja</td>
                    <td>25%</td>
                    <td>85%</td>
                    <td>88%</td>
                    <td>22.0</td>
                    <td>
                      <span class="kpi-badge badge-good">Baik</span>
                    </td>
                    <td>
                      <div class="progress kpi-progress">
                        <div
                          class="progress-bar bg-primary"
                          role="progressbar"
                          style="width: 88%"
                          aria-valuenow="88"
                          aria-valuemin="0"
                          aria-valuemax="100"></div>
                      </div>
                      <div class="progress-percentage">88%</div>
                    </td>
                  </tr>
                  <tr>
                    <td>Kedisiplinan</td>
                    <td>20%</td>
                    <td>100%</td>
                    <td>92%</td>
                    <td>18.4</td>
                    <td>
                      <span class="kpi-badge badge-good">Baik</span>
                    </td>
                    <td>
                      <div class="progress kpi-progress">
                        <div
                          class="progress-bar bg-primary"
                          role="progressbar"
                          style="width: 92%"
                          aria-valuenow="92"
                          aria-valuemin="0"
                          aria-valuemax="100"></div>
                      </div>
                      <div class="progress-percentage">92%</div>
                    </td>
                  </tr>
                  <tr>
                    <td>Inisiatif</td>
                    <td>15%</td>
                    <td>80%</td>
                    <td>85%</td>
                    <td>12.75</td>
                    <td>
                      <span class="kpi-badge badge-good">Baik</span>
                    </td>
                    <td>
                      <div class="progress kpi-progress">
                        <div
                          class="progress-bar bg-primary"
                          role="progressbar"
                          style="width: 85%"
                          aria-valuenow="85"
                          aria-valuemin="0"
                          aria-valuemax="100"></div>
                      </div>
                      <div class="progress-percentage">85%</div>
                    </td>
                  </tr>
                  <tr>
                    <td>Kerjasama Tim</td>
                    <td>10%</td>
                    <td>90%</td>
                    <td>87%</td>
                    <td>8.7</td>
                    <td>
                      <span class="kpi-badge badge-good">Baik</span>
                    </td>
                    <td>
                      <div class="progress kpi-progress">
                        <div
                          class="progress-bar bg-primary"
                          role="progressbar"
                          style="width: 87%"
                          aria-valuenow="87"
                          aria-valuemin="0"
                          aria-valuemax="100"></div>
                      </div>
                      <div class="progress-percentage">87%</div>
                    </td>
                  </tr>
                </tbody>
                <tfoot>
                  <tr class="table-active">
                    <th>Total</th>
                    <th>100%</th>
                    <th>-</th>
                    <th>-</th>
                    <th>88.5</th>
                    <th>
                      <span class="kpi-badge badge-excellent">Bagus</span>
                    </th>
                    <th>
                      <div class="progress kpi-progress">
                        <div
                          class="progress-bar bg-primary"
                          role="progressbar"
                          style="width: 88.5%"
                          aria-valuenow="88.5"
                          aria-valuemin="0"
                          aria-valuemax="100"></div>
                      </div>
                      <div class="progress-percentage">88.5%</div>
                    </th>
                  </tr>
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
  // Inisialisasi grafik
  document.addEventListener("DOMContentLoaded", function() {
    // Grafik Trend KPI
    const trendCtx = document
      .getElementById("kpiTrendChart")
      .getContext("2d");
    let trendChart = new Chart(trendCtx, {
      type: "line",
      data: {
        labels: [
          "Jan",
          "Feb",
          "Mar",
          "Apr",
          "Mei",
          "Jun",
          "Jul",
          "Agu",
          "Sep",
          "Okt",
          "Nov",
          "Des",
        ],
        datasets: [{
            label: "Nilai KPI",
            data: [
              82, 83.2, 84.3, 85.1, 86.0, 86.9, 86.7, 87.5, 88.2, 87.6,
              88.3, 88.9,
            ],
            borderColor: "#0d6efd",
            backgroundColor: "rgba(13, 110, 253, 0.1)",
            borderWidth: 2,
            tension: 0.1,
            fill: true,
          },
          {
            label: "Target",
            data: [80, 81, 82, 83, 84, 85, 85, 85, 85, 85, 85, 85],
            borderColor: "#fd7e14",
            backgroundColor: "transparent",
            borderWidth: 2,
            borderDash: [5, 5],
            tension: 0.1,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: false,
            min: 75,
            max: 95,
          },
        },
      },
    });

    // Fungsi untuk ekspor ke Excel
    document
      .getElementById("exportMonthlyBtn")
      .addEventListener("click", function() {
        // Ambil tabel yang akan diekspor
        const table = document.getElementById("monthlyKpiTable");

        // Konversi tabel ke worksheet Excel
        const workbook = XLSX.utils.table_to_book(table, {
          sheet: "Rekapan KPI Bulanan",
        });

        // Ekspor ke file Excel
        XLSX.writeFile(workbook, "Rekapan_KPI_Bulanan.xlsx");
      });
  });

  // helper: set tahun di tombol + isi kolom tahun
  function setTahun(tahun) {
    const btn = document.getElementById("tahunDropdown");
    btn.textContent = "Tahun: " + tahun;

    // isi semua sel tahun
    document.querySelectorAll("#monthlyKpiTable tbody tr").forEach((tr) => {
      // kalau belum ada sel tahun (antisipasi), sisipkan setelah kolom Bulan
      let tahunCell = tr.querySelector(".tahun-cell");
      if (!tahunCell) {
        const tds = tr.querySelectorAll("td");
        tahunCell = document.createElement("td");
        tahunCell.className = "tahun-cell";
        tr.insertBefore(tahunCell, tds[1]); // posisikan jadi kolom ke-2
      }
      tahunCell.textContent = tahun;
    });
  }

  // klik item dropdown tahun
  // === Dropdown Bulan ===
  function setBulan(bulan) {
    const btn = document.getElementById("bulanDropdown");
    btn.textContent = "Bulan: " + bulan;

    // TODO: update isi card KPI & indikator KPI sesuai bulan
    // misalnya ambil data dari array/data JSON
  }

  document
    .querySelectorAll("#bulanDropdown + .dropdown-menu .dropdown-item")
    .forEach((item) => {
      item.addEventListener("click", function(e) {
        e.preventDefault();
        setBulan(this.dataset.bulan);
      });
    });

  // set default bulan = Januari
  document.addEventListener("DOMContentLoaded", function() {
    setBulan("Januari");
  });

  // === Dropdown Tahun ===
  function setTahun(tahun) {
    const btn = document.getElementById("tahunDropdown");
    btn.textContent = "Tahun: " + tahun;

    // update kolom Tahun di tabel
    document.querySelectorAll("#monthlyKpiTable tbody tr").forEach((tr) => {
      let tahunCell = tr.querySelector(".tahun-cell");
      if (!tahunCell) {
        const tds = tr.querySelectorAll("td");
        tahunCell = document.createElement("td");
        tahunCell.className = "tahun-cell";
        tr.insertBefore(tahunCell, tds[1]);
      }
      tahunCell.textContent = tahun;
    });

    // TODO: update chart sesuai tahun (bisa pakai dataset berbeda per tahun)
  }

  document
    .querySelectorAll("#tahunDropdown + .dropdown-menu .dropdown-item")
    .forEach((item) => {
      item.addEventListener("click", function(e) {
        e.preventDefault();
        setTahun(this.dataset.tahun);
      });
    });

  // default tahun = dari button
  document.addEventListener("DOMContentLoaded", function() {
    const btnText = document.getElementById("tahunDropdown").textContent;
    const initialYear = (btnText.match(/\d{4}/) || [
      new Date().getFullYear(),
    ])[0];
    setTahun(initialYear);
  });
</script>
@endsection