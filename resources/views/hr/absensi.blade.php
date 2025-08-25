@extends('template.template')

@section('title', 'Absensi Karyawan')

@section('content')
<style>
  .card {
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
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
</style>

<div class="body d-flex py-3">
  <!-- Body: Body -->
  <div class="body d-flex py-lg-3 py-md-2">
    <div class="container-xxl">
      <div class="row align-items-center">
        <div class="border-0 mb-2">
          <div
            class="card-header py-3 no-bg bg-transparent d-flex align-items-center px-0 justify-content-between border-bottom flex-wrap">
            <h3 class="fw-bold mb-0">Absensi Karyawan</h3>

            <div class="d-flex align-items-center flex-wrap mt-3">
              <!-- card informasi -->
              <div class="container-fluid px-0">
                <div class="row g-3 mb-3">
                  <div class="col-6 col-md-3">
                    <div class="card bg-primary h-100 w-100 stat-card">
                      <div
                        class="card-body text-white d-flex align-items-center">
                        <i class="icofont-checked fs-3"></i>
                        <div class="d-flex flex-column ms-3">
                          <h6 class="mb-0">Jumlah Kehadiran</h6>
                          <span>550</span>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="col-6 col-md-3">
                    <div class="card bg-primary h-100 w-100 stat-card">
                      <div
                        class="card-body text-white d-flex align-items-center">
                        <i class="icofont-beach-bed fs-3"></i>
                        <div class="d-flex flex-column ms-3">
                          <h6 class="mb-0">Jumlah Izin/Sakit</h6>
                          <span>210</span>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="col-6 col-md-3">
                    <div class="card bg-primary h-100 w-100 stat-card">
                      <div
                        class="card-body text-white d-flex align-items-center">
                        <i class="icofont-ban fs-3"></i>
                        <div class="d-flex flex-column ms-3">
                          <h6 class="mb-0">Jumlah Mangkir</h6>
                          <span>8456</span>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="col-6 col-md-3">
                    <div class="card bg-primary h-100 w-100 stat-card">
                      <div
                        class="card-body text-white d-flex align-items-center">
                        <i class="icofont-stopwatch fs-3"></i>
                        <div class="d-flex flex-column ms-3">
                          <h6 class="mb-0">Jumlah Terlambat</h6>
                          <span>88</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Card Filter Data -->
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
                        <select class="form-select form-select-sm">
                          <option>Semua</option>
                          <option>Januari</option>
                          <option>Februari</option>
                          <option>Maret</option>
                        </select>
                      </div>

                      <!-- Tahun -->
                      <div class="col-6 col-md-3">
                        <label class="form-label small mb-1">Tahun</label>
                        <select class="form-select form-select-sm">
                          <option>2023</option>
                          <option>2024</option>
                          <option>2025</option>
                        </select>
                      </div>

                      <!-- Divisi -->
                      <div class="col-6 col-md-3">
                        <label class="form-label small mb-1">Divisi</label>
                        <select class="form-select form-select-sm">
                          <option>Semua</option>
                          <option>IT</option>
                          <option>HRD</option>
                          <option>Finance</option>
                        </select>
                      </div>

                      <!-- Jabatan -->
                      <div class="col-6 col-md-3">
                        <label class="form-label small mb-1">Jabatan</label>
                        <select class="form-select form-select-sm">
                          <option>Semua</option>
                          <option>Manager</option>
                          <option>Staff</option>
                          <option>Intern</option>
                        </select>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- Row end  -->
        <div class="container-xxl mt-3">
          <div class="row clearfix g-3">
            <div class="col-sm-12">
              <div class="card mb-2">
                <div class="card-body">
                  <!-- Bungkus tabel dengan table-responsive -->
                  <div class="table-responsive">
                    <table
                      id="myProjectTable"
                      class="table table-hover table-striped align-middle mb-0"
                      style="width: 100%">
                      <thead>
                        <tr>
                          <th>Nama</th>
                          <th>Divisi</th>
                          <th>Hadir</th>
                          <th>Izin</th>
                          <th>Mangkir</th>
                          <th>Terlambat</th>
                          <th style="width: 80px">Actions</th>
                          <!-- fixed width -->
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td><span class="fw-bold">mamaowd</span></td>
                          <td><span class="fw-bold ms-1">ui</span></td>
                          <td><span class="fw-bold ms-1">23</span></td>
                          <td><span class="fw-bold ms-1">0</span></td>
                          <td><span class="fw-bold ms-1">0</span></td>
                          <td><span class="fw-bold ms-1">0</span></td>
                          <td>
                            <a
                              href="{{route('hr.detail-absensi')}}"
                              class="btn btn-outline-secondary btn-sm">
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
        </div>

        <!-- Row End -->
      </div>
    </div>

    <!-- Plugin Js tabel-->
  </div>
</div>
@endsection

@section('script')
<script src="{{asset('assets/bundles/apexcharts.bundle.js')}}"></script>
@endsection