@extends('template.template')

@section('title', 'Absensi')

@section('content')
<!-- CSS Tabel -->
<link rel="stylesheet" href="{{ asset('assets/plugin/datatables/responsive.dataTables.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugin/datatables/dataTables.bootstrap5.min.css') }}">

<!-- Body -->
<div class="body d-flex py-lg-3 py-md-2">
  <div class="container-xxl">
    <div class="row align-items-center">
      <div class="border-0 mb-4">
        <div
          class="card-header py-3 no-bg bg-transparent d-flex align-items-center px-0 justify-content-between border-bottom flex-wrap">
          <h3 class="fw-bold mb-0">Absensi Karyawan</h3>
        </div>

        <!-- Filter -->
        <div class="d-flex justify-content-between align-items-center mb-2 mt-2">
          <div class="d-flex gap-2 ms-auto">
            <select id="bulanSelect" class="form-select">
              <option value="">Bulan</option>
              <option value="semua">Semua</option>
              <option value="Jan">Januari</option>
              <option value="Feb">Februari</option>
              <option value="Mar">Maret</option>
              <option value="Apr">April</option>
            </select>
          </div>
        </div>

        <!-- Ringkasan Absen -->
        <div class="row g-3 mb-3 row-cols-2 row-cols-sm-2 row-cols-md-2 row-cols-lg-2 row-cols-xl-4">
  <div class="col">
    <div class="card bg-primary">
      <div class="card-body text-white d-flex align-items-center">
        <i class="icofont-checked fs-3"></i>
        <div class="d-flex flex-column ms-3">
          <h6 class="mb-0">Hadir</h6>
          <span class="fw-bold text-white">550</span>
        </div>
      </div>
    </div>
  </div>
  <div class="col">
    <div class="card bg-primary">
      <div class="card-body text-white d-flex align-items-center">
        <i class="icofont-beach-bed fs-3"></i>
        <div class="d-flex flex-column ms-3">
          <h6 class="mb-0">Izin/Sakit</h6>
          <span class="fw-bold text-white">210</span>
        </div>
      </div>
    </div>
  </div>
  <div class="col">
    <div class="card bg-primary">
      <div class="card-body text-white d-flex align-items-center">
        <i class="icofont-ban fs-3"></i>
        <div class="d-flex flex-column ms-3">
          <h6 class="mb-0">Mangkir</h6>
          <span class="fw-bold text-white">8456</span>
        </div>
      </div>
    </div>
  </div>
  <div class="col">
    <div class="card bg-primary">
      <div class="card-body text-white d-flex align-items-center">
        <i class="icofont-stopwatch fs-3"></i>
        <div class="d-flex flex-column ms-3">
          <h6 class="mb-0">Terlambat</h6>
          <span class="fw-bold text-white">88</span>
        </div>
      </div>
    </div>
  </div>
</div>

        <!-- /Ringkasan -->
      </div>
    </div>
  </div>

  <!-- Tabel -->
  <div class="row clearfix g-3">
    <div class="col-sm-12">
      <div class="card mb-3">
        <div class="card-body">
          <table id="myProjectTable" class="table table-hover align-middle mb-0" style="width:100%">
            <thead class="table-secondary text-center">
              <tr>
                <th>Tahun</th>
                <th>Bulan</th>
                <th>Hadir</th>
                <th>Izin/Sakit</th>
                <th>Mangkir</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody class="text-center">
              <tr>
                <td><span class="fw-bold">2022</span></td>
                <td><span class="fw-bold">Februari</span></td>
                <td><span class="fw-bold">10</span></td>
                <td><span class="fw-bold">100</span></td>
                <td><span class="fw-bold">1</span></td>
                <td>
                  <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal"
                    data-bs-target="#showabsen">
                    <i class="icofont-edit text-success"></i>
                  </button>
                </td>
              </tr>
              <tr>
                <td><span class="fw-bold">2023</span></td>
                <td><span class="fw-bold">Februari</span></td>
                <td><span class="fw-bold">0</span></td>
                <td><span class="fw-bold">3</span></td>
                <td><span class="fw-bold">1</span></td>
                <td>
                  <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal"
                    data-bs-target="#showabsen">
                    <i class="icofont-edit text-success"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Grafik + Ringkasan -->
  <div class="card mt-3 shadow-sm">
  <div class="card-body">
    <div class="row gy-3">
      <!-- Grafik -->
      <div class="col-lg-9 col-12">
        <h6 class="mb-3 fw-bold">Tren Absensi Bulanan</h6>
        <div id="chartAbsensi" style="min-height:260px;"></div>
      </div>

      <!-- Ringkasan -->
<div class="col-lg-3 col-12 border-lg-start">
  <div class="row g-3">
    <div class="col-12">
      <div class="card shadow-sm text-center p-3 h-100">
        <h6 class="mb-1">Informasi Absensi</h6>
        <h5 class="fw-bold text-primary">Tahun 2023</h5>
      </div>
    </div>
    <div class="col-6">
      <div class="card shadow-sm text-center p-3 h-100">
        <h6 class="mb-1">Kehadiran</h6>
        <h3 class="fw-bold text-success">95%</h3>
        <small class="text-muted">20 Hari Hadir</small>
      </div>
    </div>
    <div class="col-6">
      <div class="card shadow-sm text-center p-3 h-100">
        <h6 class="mb-1">Izin</h6>
        <h3 class="fw-bold text-warning">2</h3>
        <small class="text-muted">Izin tidak masuk</small>
      </div>
    </div>
    <div class="col-6">
      <div class="card shadow-sm text-center p-3 h-100">
        <h6 class="mb-1">Sakit</h6>
        <h3 class="fw-bold text-info">1</h3>
        <small class="text-muted">Sakit tidak masuk</small>
      </div>
    </div>
    <div class="col-6">
      <div class="card shadow-sm text-center p-3 h-100">
        <h6 class="mb-1">Mangkir</h6>
        <h3 class="fw-bold text-danger">0</h3>
        <small class="text-muted">Tanpa Keterangan</small>
      </div>
    </div>
  </div>
</div>

    </div>
  </div>
</div>


</div>

<!-- Modal -->
<div class="modal fade" id="showabsen" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Data Absensi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0">
        <table class="table table-bordered table-striped mb-0">
          <thead class="table-secondary text-center">
            <tr>
              <th>Tanggal</th>
              <th>Status</th>
              <th>Jam Masuk</th>
              <th>Jam Keluar</th>
              <th>Lama Kerja</th>
            </tr>
          </thead>
          <tbody class="text-center">
            <tr>
              <td>1</td>
              <td>Hadir</td>
              <td>08:00</td>
              <td>17:00</td>
              <td>9 Jam</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('script')
<!-- Plugin Js Tabel-->
<script src="{{ asset('assets/bundles/dataTables.bundle.js') }}"></script>
<script src="{{ asset('assets/bundles/apexcharts.bundle.js') }}"></script>
<script>
  $(document).ready(function () {
    $('#myProjectTable').addClass('nowrap').DataTable({
      responsive: true,
      columnDefs: [
        { targets: -1, orderable: false, searchable: false, className: 'dt-body-right all' }
      ]
    });
  });

  document.addEventListener('DOMContentLoaded', function () {
    const absensiOpts = {
      chart: { type: 'bar', height: 260, stacked: true },
      series: [
        { name: 'Hadir', data: [20, 18, 22, 21] },
        { name: 'Izin', data: [2, 1, 0, 1] },
        { name: 'Sakit', data: [1, 0, 2, 1] },
        { name: 'Mangkir', data: [0, 1, 0, 0] }
      ],
      xaxis: { categories: ['Jan', 'Feb', 'Mar', 'Apr'] },
      dataLabels: { enabled: false },
      plotOptions: { bar: { columnWidth: '45%' } }
    };
    new ApexCharts(document.querySelector('#chartAbsensi'), absensiOpts).render();
  });
</script>

<style>
  /* Biar di tablet & hp ringkasan turun ke bawah */
  @media (max-width: 991px) {


        .row {
        margin-left: 0 !important;
        margin-right: 0 !important;
        }

    .ringkasan {
      border-left: none !important;
      margin-top: 1rem;
    }
  }
</style>
@endsection
