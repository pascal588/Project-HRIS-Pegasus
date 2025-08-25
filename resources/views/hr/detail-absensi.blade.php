@extends('template.template')

@section('title', 'Detail Absen')

@section('content')
<style>
      /* Stat Card */
      .stat-card {
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 20px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        text-align: center;
      }
      .stat-card .icon {
        font-size: 2rem;
        margin-bottom: 10px;
      }
      .stat-card .title {
        font-size: 0.9rem;
        color: #6c757d;
      }
      .stat-card .value {
        font-size: 1.5rem;
        font-weight: bold;
      }

      /* Detail Table */
      .detail-table th {
        background-color: #f8f9fa;
        font-weight: 600;
        white-space: nowrap;
      }

      /* RESPONSIVE FIX */
      @media (max-width: 768px) {
        .stat-card .value {
          font-size: 1.2rem;
        }
        .stat-card .icon {
          font-size: 1.5rem;
        }
        .card-body h4 {
          font-size: 1.2rem;
        }
        .card-body span {
          display: block;
          margin-bottom: 4px;
        }
      }

      @media (max-width: 576px) {
        .stat-card {
          padding: 8px;
        }
        .stat-card .icon {
          font-size: 1.2rem;
          margin-bottom: 4px;
        }
        .stat-card .title {
          font-size: 0.7rem;
        }
        .stat-card .value {
          font-size: 1rem;
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
                      class="d-flex flex-wrap align-items-start justify-content-between"
                    >
                      <div class="mb-3 mb-md-0">
                        <h4 class="fw-bold mb-2">Detail Absensi Karyawan</h4>
                        <div class="d-flex flex-wrap">
                          <span class="me-3"
                            ><strong>Nama:</strong> John Doe</span
                          >
                          <span class="me-3"
                            ><strong>ID Karyawan:</strong> EMP-00123</span
                          >
                          <span class="me-3"><strong>Divisi:</strong> IT</span>
                          <span><strong>Jabatan:</strong> Staff</span>
                        </div>
                      </div>
                      <div class="dropdown">
                        <button
                          class="btn btn-primary dropdown-toggle"
                          type="button"
                          id="periodeDropdown"
                          data-bs-toggle="dropdown"
                          aria-expanded="false"
                        >
                          Periode: Januari 2023
                        </button>
                        <ul
                          class="dropdown-menu"
                          aria-labelledby="periodeDropdown"
                        >
                          <li>
                            <h6 class="dropdown-header">Pilih Periode</h6>
                          </li>
                          <li>
                            <a class="dropdown-item" href="#">Januari 2023</a>
                          </li>
                          <li>
                            <a class="dropdown-item" href="#">Desember 2022</a>
                          </li>
                          <li>
                            <a class="dropdown-item" href="#">November 2022</a>
                          </li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Statistik Ringkasan -->
            <div class="row mb-4 text-center">
              <div class="col-6 col-md-3">
                <div class="stat-card bg-primary text-white">
                  <div class="icon"><i class="icofont-checked"></i></div>
                  <div class="title">Hadir</div>
                  <div class="value">20 Hari</div>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="stat-card bg-primary text-white">
                  <div class="icon"><i class="icofont-beach-bed"></i></div>
                  <div class="title">Izin/Sakit</div>
                  <div class="value">2 Hari</div>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="stat-card bg-primary text-white">
                  <div class="icon"><i class="icofont-ban"></i></div>
                  <div class="title">Mangkir</div>
                  <div class="value">0 Hari</div>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="stat-card bg-primary text-white">
                  <div class="icon"><i class="icofont-stopwatch"></i></div>
                  <div class="title">Terlambat</div>
                  <div class="value">3 Kali</div>
                </div>
              </div>
            </div>

            <!-- Tabel Detail Absensi -->
            <div class="row">
              <div class="col-12">
                <div class="card">
                  <div
                    class="card-header d-flex flex-wrap justify-content-between align-items-center"
                  >
                    <h5 class="card-title mb-2 mb-md-0">
                      Rincian Absensi Harian
                    </h5>
                    <button class="btn btn-sm btn-primary">Export Excel</button>
                  </div>
                  <div class="card-body">
                    <div class="table-responsive">
                      <table
                        class="table detail-table table-bordered table-hover"
                      >
                        <thead>
                          <tr>
                            <th>Tanggal</th>
                            <th>Hari</th>
                            <th>Status</th>
                            <th>Jam Masuk</th>
                            <th>Jam Keluar</th>
                            <th>Lama Kerja</th>
                            <th>Keterangan</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr>
                            <td>02/01/2023</td>
                            <td>Senin</td>
                            <td><span class="badge bg-success">Hadir</span></td>
                            <td>08:05</td>
                            <td>17:10</td>
                            <td>9 jam 5 menit</td>
                            <td>-</td>
                          </tr>
                          <tr>
                            <td>03/01/2023</td>
                            <td>Selasa</td>
                            <td><span class="badge bg-success">Hadir</span></td>
                            <td>08:00</td>
                            <td>17:00</td>
                            <td>9 jam</td>
                            <td>-</td>
                          </tr>
                          <tr>
                            <td>04/01/2023</td>
                            <td>Rabu</td>
                            <td><span class="badge bg-warning">Izin</span></td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td>Sakit</td>
                          </tr>
                          <tr>
                            <td>05/01/2023</td>
                            <td>Kamis</td>
                            <td><span class="badge bg-success">Hadir</span></td>
                            <td>08:15</td>
                            <td>17:05</td>
                            <td>8 jam 50 menit</td>
                            <td>Terlambat 15 menit</td>
                          </tr>
                          <tr>
                            <td>06/01/2023</td>
                            <td>Jumat</td>
                            <td><span class="badge bg-success">Hadir</span></td>
                            <td>08:00</td>
                            <td>16:30</td>
                            <td>8 jam 30 menit</td>
                            <td>-</td>
                          </tr>
                        </tbody>
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
@endsection

