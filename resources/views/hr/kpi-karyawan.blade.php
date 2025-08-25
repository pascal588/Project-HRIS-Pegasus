@extends('template.template')

@section('title', 'KPI Karyawan')

@section('content')
<!-- plugin table data  -->
<link
  rel="stylesheet"
  href="{{asset('assets/plugin/datatables/responsive.dataTables.min.css')}}" />
<link
  rel="stylesheet"
  href="{{asset('assets/plugin/datatables/dataTables.bootstrap5.min.css')}}" />
<style>
  .card-info .col {
    padding-left: 10px;
    padding-right: 20px;
  }

  .filter-dropdowns .dropdown {
    margin-right: 0;
  }

  @media (min-width: 768px) {
    .filter-dropdowns .dropdown {
      margin-right: 0.5rem;
    }

    .filter-dropdowns .dropdown:last-child {
      margin-right: 0;
    }
  }

  .employee-card {
    border-radius: 10px;
    transition: all 0.3s ease;
    height: 100%;
  }

  .employee-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
  }

  .employee-score {
    font-size: 2rem;
    font-weight: bold;
  }

  .carousel-control-prev-icon,
  .carousel-control-next-icon {
    width: 2.5rem;
    height: 2.5rem;
    background-size: 1.5rem;
  }

  .carousel-indicators [data-bs-target] {
    background-color: #333;
  }

  @media (max-width: 767.98px) {
    .card-info .col {
      margin-bottom: 10px;
    }

    .filter-dropdowns .dropdown {
      margin-bottom: 10px;
    }

    .employee-card {
      margin-bottom: 20px;
    }
  }

  .badge-score {
    font-size: 0.9rem;
    padding: 0.35em 0.65em;
    font-weight: 600;
    background-color: rgba(13, 110, 253, 0.1);
    color: #0d6efd;
    border-radius: 50px;
  }

  .employee-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }

  .employee-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
  }

  /* Default (desktop & tablet) */
  .card.stat-card .card-body {
    padding: 1rem;
    min-height: 100px;
  }

  /* HP (≤576px) */
  @media (max-width: 576px) {
    .card.stat-card {
      font-size: 0.75rem;
      /* teks kecil */
    }

    .card.stat-card .card-body {
      padding: 0.3rem 0.4rem;
      /* padding super kecil */
      min-height: 60px;
      /* tinggi card lebih pendek */
    }

    .card.stat-card i {
      font-size: 1rem;
      /* ikon kecil */
      margin-right: 0.25rem;
    }

    .card.stat-card h6 {
      font-size: 0.65rem;
      margin-bottom: 0;
    }

    .card.stat-card span {
      font-size: 0.7rem;
      line-height: 1.1;
    }
  }

  /* Desktop: 4 kartu rata */
  .employee-card {
    width: 23%;
    /* hampir 4 kolom sejajar */
  }

  /* Tablet */
  @media (max-width: 992px) {
    .employee-card {
      width: 40%;
      /* 2 kartu bisa masuk dalam layar */
    }
  }

  /* Mobile */
  @media (max-width: 576px) {
    .employee-card {
      width: 70%;
      /* 1 kartu besar tapi masih bisa di-slide ke samping */
    }
  }

  /* Scroll horizontal smooth */
  .best-employee-wrapper {
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
  }

  .best-employee-wrapper::-webkit-scrollbar {
    display: none;
  }

  /* Tabel bisa discroll di layar kecil */
  .table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }

  /* Supaya kolom tidak pecah ke bawah */
  #kpiTable {
    white-space: nowrap;
  }

  .form-select.form-select-sm {
    height: 28px;
    /* default ~31px */
    padding: 2px 8px;
    font-size: 0.8rem;
  }

  .form-label.small {
    font-size: 0.75rem;
    margin-bottom: 2px;
    /* jarak label ke input makin rapat */
  }
</style>

<div class="body d-flex py-lg-3 py-md-2">
  <div class="container-xxl">
    <div class="row align-items-center">
      <div class="border-0 mb-4">
        <div
          class="card-header py-3 no-bg bg-transparent d-flex align-items-center px-0 justify-content-between border-bottom flex-wrap">
          <h3 class="fw-bold mb-0">Peringkat KPI Karyawan</h3>

          <div class="d-flex align-items-center flex-wrap mt-3">
            <!-- Card Informasi -->
            <div class="container-fluid px-0">
              <div class="row g-3 mb-3">
                <div class="col-6 col-md-3">
                  <div class="card bg-primary h-100 w-100 stat-card">
                    <div
                      class="card-body text-white d-flex align-items-center">
                      <i class="icofont-star fs-3"></i>
                      <div class="d-flex flex-column ms-3">
                        <h6 class="mb-0">Skor Tertinggi</h6>
                        <span class="text-white">95</span>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-6 col-md-3">
                  <div class="card bg-primary h-100 w-100 stat-card">
                    <div
                      class="card-body text-white d-flex align-items-center">
                      <i class="icofont-ui-rating fs-3"></i>
                      <div class="d-flex flex-column ms-3">
                        <h6 class="mb-0">Skor Rata-rata</h6>
                        <span class="text-white">84.25</span>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-6 col-md-3">
                  <div class="card bg-primary h-100 w-100 stat-card">
                    <div
                      class="card-body text-white d-flex align-items-center">
                      <i class="icofont-warning fs-3"></i>
                      <div class="d-flex flex-column ms-3">
                        <h6 class="mb-0">Skor Terendah</h6>
                        <span class="text-white">65</span>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-6 col-md-3">
                  <div class="card bg-primary h-100 w-100 stat-card">
                    <div
                      class="card-body text-white d-flex align-items-center">
                      <i class="icofont-users fs-3"></i>
                      <div class="d-flex flex-column ms-3">
                        <h6 class="mb-0">Total Karyawan</h6>
                        <span class="text-white">4</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Dropdowns -->
            <div class="container-xxl mt-2">
              <div class="card shadow-sm w-100">
                <div class="card-header bg-primary text-white py-2">
                  <h5 class="mb-0">Filter Data</h5>
                </div>
                <div class="card-body p-2">
                  <form class="row g-2">
                    <!-- Bulan -->
                    <div class="col-6 col-md-3">
                      <label class="form-label small mb-1">Bulan</label>
                      <select
                        id="filterBulan"
                        class="form-select form-select-sm">
                        <option value="all">Semua</option>
                        <option value="Januari">Januari</option>
                        <option value="Februari">Februari</option>
                        <option value="Maret">Maret</option>
                      </select>
                    </div>

                    <!-- Tahun -->
                    <div class="col-6 col-md-3">
                      <label class="form-label small mb-1">Tahun</label>
                      <select
                        id="filterTahun"
                        class="form-select form-select-sm">
                        <option value="all">Semua</option>
                        <option value="2023">2023</option>
                        <option value="2024">2024</option>
                        <option value="2025">2025</option>
                      </select>
                    </div>

                    <!-- Divisi -->
                    <div class="col-6 col-md-3">
                      <label class="form-label small mb-1">Divisi</label>
                      <select
                        id="filterDivisi"
                        class="form-select form-select-sm">
                        <option value="all">Semua</option>
                        <option value="IT">IT</option>
                        <option value="HRD">HRD</option>
                        <option value="Finance">Finance</option>
                      </select>
                    </div>

                    <!-- Jabatan -->
                    <div class="col-6 col-md-3">
                      <label class="form-label small mb-1">Jabatan</label>
                      <select
                        id="filterJabatan"
                        class="form-select form-select-sm">
                        <option value="all">Semua</option>
                        <option value="Manager">Manager</option>
                        <option value="Staff">Staff</option>
                        <option value="Intern">Intern</option>
                      </select>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Row end  -->
    <div class="row clearfix g-3">
      <div class="col-sm-12">
        <div class="card mb-3">
          <div class="card-body">
            <!-- Bungkus tabel dengan table-responsive -->
            <div class="table-responsive">
              <table
                id="kpiTable"
                class="table table-hover table-striped align-middle mb-0"
                style="width: 100%">
                <thead class="table-light">
                  <tr>
                    <th>No</th>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Divisi</th>
                    <th>Jabatan</th>
                    <th>Status</th>
                    <th>Skor KPI</th>
                    <th>Periode</th>
                    <th style="width: 80px">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>01</td>
                    <td>001</td>
                    <td>Bambang</td>
                    <td>Pemasaran</td>
                    <td>Karyawan</td>
                    <td><span class="badge bg-success">Aktif</span></td>
                    <td><span class="badge-score">92</span></td>
                    <td>Agustus 2025</td>
                    <td>
                      <a
                        href="{{route('hr.detail-kpi')}}"
                        class="btn btn-outline-secondary btn-sm"
                        title="Detail">
                        <i class="icofont-eye-alt"></i>
                      </a>
                    </td>
                  </tr>
                  <tr>
                    <td>02</td>
                    <td>002</td>
                    <td>Siti</td>
                    <td>Keuangan</td>
                    <td>Kepala Divisi</td>
                    <td><span class="badge bg-success">Aktif</span></td>
                    <td><span class="badge-score">85</span></td>
                    <td>Agustus 2025</td>
                    <td>
                      <a
                        href="HR-detail-kpi.html?id=002"
                        class="btn btn-outline-secondary btn-sm"
                        title="Detail">
                        <i class="icofont-eye-alt"></i>
                      </a>
                    </td>
                  </tr>
                  <tr>
                    <td>03</td>
                    <td>003</td>
                    <td>Andi</td>
                    <td>Pemasaran</td>
                    <td>Karyawan</td>
                    <td><span class="badge bg-success">Aktif</span></td>
                    <td><span class="badge-score">65</span></td>
                    <td>Agustus 2025</td>
                    <td>
                      <a
                        href="HR-detail-kpi.html?id=003"
                        class="btn btn-outline-secondary btn-sm"
                        title="Detail">
                        <i class="icofont-eye-alt"></i>
                      </a>
                    </td>
                  </tr>
                  <tr>
                    <td>04</td>
                    <td>004</td>
                    <td>Dewi</td>
                    <td>Keuangan</td>
                    <td>Karyawan</td>
                    <td><span class="badge bg-success">Aktif</span></td>
                    <td><span class="badge-score">95</span></td>
                    <td>Agustus 2024</td>
                    <td>
                      <a
                        href="HR-detail-kpi.html?id=004"
                        class="btn btn-outline-secondary btn-sm"
                        title="Detail">
                        <i class="icofont-eye-alt"></i>
                      </a>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <!-- End table-responsive -->
          </div>
        </div>
      </div>
    </div>

    <!-- Row End -->
  </div>
  <!-- Karyawan Terbaik Section -->
  <div class="container-xxl">
    <div class="row mt-4">
      <div class="col-12">
        <div class="card shadow-sm">
          <div
            class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">🌟 Karyawan Terbaik</h5>
            <small class="text-light">Geser untuk melihat lainnya →</small>
          </div>
          <div class="card-body">
            <div
              class="best-employee-wrapper d-flex flex-nowrap overflow-auto px-2">
              <!-- Item -->
              <div class="employee-card flex-shrink-0 me-3">
                <div
                  class="card h-100 text-center p-3 border-0 shadow-sm">
                  <img
                    src="assets/images/profile_av.png"
                    class="rounded-circle mx-auto mb-3"
                    width="80"
                    height="80"
                    alt="Employee" />
                  <h6 class="fw-bold mb-1">Dewi</h6>
                  <small class="text-muted">95)</small>
                </div>
              </div>

              <div class="employee-card flex-shrink-0 me-3">
                <div
                  class="card h-100 text-center p-3 border-0 shadow-sm">
                  <img
                    src="assets/images/profile_av.png"
                    class="rounded-circle mx-auto mb-3"
                    width="80"
                    height="80"
                    alt="Employee" />
                  <h6 class="fw-bold mb-1">Bambang</h6>
                  <small class="text-muted">92</small>
                </div>
              </div>

              <div class="employee-card flex-shrink-0 me-3">
                <div
                  class="card h-100 text-center p-3 border-0 shadow-sm">
                  <img
                    src="assets/images/profile_av.png"
                    class="rounded-circle mx-auto mb-3"
                    width="80"
                    height="80"
                    alt="Employee" />
                  <h6 class="fw-bold mb-1">Siti</h6>
                  <small class="text-muted">85</small>
                </div>
              </div>

              <div class="employee-card flex-shrink-0 me-3">
                <div
                  class="card h-100 text-center p-3 border-0 shadow-sm">
                  <img
                    src="assets/images/profile_av.png"
                    class="rounded-circle mx-auto mb-3"
                    width="80"
                    height="80"
                    alt="Employee" />
                  <h6 class="fw-bold mb-1">Andi</h6>
                  <small class="text-muted">65</small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection


@section('script')
<script src="{{asset('assets/bundles/dataTables.bundle.js')}}"></script>
<script>
  $(document).ready(function() {
    var table = $("#kpiTable").DataTable({
      responsive: false,
      autoWidth: false,
      scrollX: true,
      columnDefs: [{
          targets: [0, 1, 5, 8],
          orderable: false
        },
        {
          targets: [6],
          type: "num"
        },
      ],
      order: [
        [6, "desc"]
      ],
      language: {
        search: "Cari:",
        paginate: {
          first: "Pertama",
          last: "Terakhir",
          next: "Selanjutnya",
          previous: "Sebelumnya",
        },
        info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
        infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
        lengthMenu: "Tampilkan _MENU_ data per halaman",
        emptyTable: "Tidak ada data yang tersedia",
        zeroRecords: "Tidak ditemukan data yang sesuai",
      },
    });

    // Fungsi filter
    function applyFilters() {
      var bulan = $("#filterBulan").val();
      var tahun = $("#filterTahun").val();
      var divisi = $("#filterDivisi").val();
      var jabatan = $("#filterJabatan").val();

      // Contoh: asumsikan index kolom DataTable
      // bulan = col 2, tahun = col 7, divisi = col 3, jabatan = col 4
      if (bulan !== "all") {
        table.column(2).search("^" + bulan + "$", true, false);
      } else {
        table.column(2).search("");
      }

      if (tahun !== "all") {
        table.column(7).search("^" + tahun + "$", true, false);
      } else {
        table.column(7).search("");
      }

      if (divisi !== "all") {
        table.column(3).search("^" + divisi + "$", true, false);
      } else {
        table.column(3).search("");
      }

      if (jabatan !== "all") {
        table.column(4).search("^" + jabatan + "$", true, false);
      } else {
        table.column(4).search("");
      }

      table.draw();
    }

    // Event listener untuk setiap select
    $("#filterBulan, #filterTahun, #filterDivisi, #filterJabatan").on(
      "change",
      function() {
        applyFilters();
      }
    );

    // Pencarian global bawaan
    $('input[type="search"]').keyup(function() {
      table.search($(this).val()).draw();
    });
  });

  $(document).ready(function() {
    // Data contoh (bisa diganti dengan data dari API/database)
    const bestEmployees = [{
        id: "004",
        name: "Dewi",
        division: "Keuangan",
        score: 95,
        photo: "assets/images/profile_av.png",
      },
      {
        id: "001",
        name: "Bambang",
        division: "Pemasaran",
        score: 92,
        photo: "assets/images/profile_av.png",
      },
      {
        id: "002",
        name: "Siti",
        division: "Keuangan",
        score: 85,
        photo: "assets/images/profile_av.png",
      },
      {
        id: "003",
        name: "Andi",
        division: "Pemasaran",
        score: 65,
        photo: "assets/images/profile_av.png",
      },
    ];

    // Urutkan karyawan berdasarkan skor tertinggi
    bestEmployees.sort((a, b) => b.score - a.score);

    // Ambil 4 karyawan terbaik
    const topEmployees = bestEmployees.slice(0, 4);

    // Generate card untuk setiap karyawan
    const carouselInner = $("#bestEmployeeCarousel .carousel-inner");
    carouselInner.empty();

    // Buat item carousel
    const carouselItem = $("<div>").addClass("carousel-item active");
    const row = $("<div>").addClass("row g-3");

    topEmployees.forEach((employee) => {
      const col = $("<div>").addClass("col-md-3 col-sm-6");

      const card = $(`
      <div class="card employee-card border-0 shadow-sm">
        <div class="card-body text-center p-4">
          <img src="${employee.photo}" class="avatar lg rounded-circle mb-3" alt="${employee.name}">
          <h5 class="mb-1">${employee.name}</h5>
          <p class="text-muted mb-2">ID: ${employee.id}</p>
          <p class="text-muted mb-3">${employee.division}</p>
          <div class="employee-score text-primary">${employee.score}</div>
          <span class="badge bg-primary bg-opacity-10 text-primary">KPI Score</span>
        </div>
      </div>
    `);

      col.append(card);
      row.append(col);
    });

    carouselItem.append(row);
    carouselInner.append(carouselItem);

    // Inisialisasi carousel
    new bootstrap.Carousel(
      document.getElementById("bestEmployeeCarousel"), {
        interval: 5000,
        wrap: true,
      }
    );
  });
</script>
@endsection