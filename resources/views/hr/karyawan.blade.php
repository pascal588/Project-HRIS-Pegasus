@extends('template.template')

@section('title', 'Karyawan')

@section('content')
<!-- plugin table data  -->
<link
  rel="stylesheet"
  href="{{asset('assets/plugin/datatables/responsive.dataTables.min.css')}}" />
<link
  rel="stylesheet"
  href="{{asset('assets/plugin/datatables/dataTables.bootstrap5.min.css')}}" />

<style>
  .card {
    border-radius: 10px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    margin-bottom: 10px;
  }

  .card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #eaeaea;
    padding: 1rem 1.5rem;
  }

  .card .card-body {
    padding: 1rem;
  }

  .modal-header {
    border-bottom: 1px solid #eaeaea;
  }

  .modal-footer {
    border-top: 1px solid #eaeaea;
  }

  .form-label {
    font-weight: 500;
    margin-bottom: 0.5rem;
  }

  .form-control,
  .form-select {
    border-radius: 5px;
    padding: 0.5rem 0.75rem;
  }

  .table th {
    background-color: #f8f9fa;
    font-weight: 600;
    color: #495057;
  }

  .badge {
    font-weight: 500;
    padding: 0.5em 0.75em;
  }

  .btn-outline-primary {
    border-color: #4e73df;
    color: #4e73df;
  }

  .btn-outline-primary:hover {
    background-color: #4e73df;
    color: white;
  }

  .btn-outline-danger {
    border-color: #e74a3b;
    color: #e74a3b;
  }

  .btn-outline-danger:hover {
    background-color: #e74a3b;
    color: white;
  }

  .btn-outline-success {
    border-color: #28a745;
    color: #28a745;
  }

  .btn-outline-success:hover {
    background-color: #28a745;
    color: white;
  }

  .table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }

  #myProjectTable {
    width: 100%;
    border-collapse: collapse;
    white-space: nowrap;
    font-size: 0.95rem;
  }

  #myProjectTable thead th {
    background-color: #f8f9fa;
    font-weight: 600;
    color: #495057;
    text-align: center;
    padding: 0.75rem;
    border-bottom: 2px solid #dee2e6;
    vertical-align: middle;
  }

  #myProjectTable tbody td {
    padding: 0.75rem;
    vertical-align: middle;
    border-bottom: 1px solid #e9ecef;
  }

  #myProjectTable tbody tr:nth-child(even) {
    background-color: #fdfdfd;
  }

  #myProjectTable tbody tr:hover {
    background-color: #f1f3f9;
  }

  .action-buttons {
    display: flex;
    gap: 0.4rem;
    justify-content: center;
    flex-wrap: wrap;
  }

  .action-buttons .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.8rem;
  }

  .gap-2 {
    gap: 0.5rem;
  }

  .btn-outline-dark {
    border-color: #343a40;
    color: #343a40;
  }

  .btn-outline-dark:hover {
    background-color: #343a40;
    color: white;
  }

  .jabatan-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 0.5rem;
  }

  .jabatan-badge {
    display: inline-flex;
    align-items: center;
    background-color: #e9ecef;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
  }

  .jabatan-badge .remove-jabatan {
    margin-left: 0.5rem;
    cursor: pointer;
    color: #dc3545;
  }

  .jabatan-container {
    margin-bottom: 1rem;
  }

  .add-jabatan-btn {
    white-space: nowrap;
  }

  @media (max-width: 992px) {
    .deskripsi-cell {
      max-width: 200px;
    }
  }

  @media (max-width: 768px) {

    .table th,
    .table td {
      padding: 8px 10px;
      font-size: 0.875rem;
    }

    .deskripsi-cell {
      max-width: 150px;
    }
  }

  @media (max-width: 576px) {

    #myProjectTable th,
    #myProjectTable td {
      font-size: 11px;
      padding: 4px 6px;
    }

    #myProjectTable .badge {
      font-size: 10px;
      padding: 2px 6px;
    }

    #myProjectTable .action-buttons .btn {
      font-size: 10px;
      padding: 2px 6px;
    }

    .card .card-body {
      padding: 8px;
    }

    .btn-group-sm>.btn {
      padding: 0.2rem 0.4rem;
      font-size: 0.75rem;
    }

    .deskripsi-cell {
      max-width: 120px;
    }

    .btn-set-task {
      width: 100% !important;
      margin-top: 0.5rem;
    }

    .gap-2 {
      gap: 0.3rem;
    }

    .btn-outline-dark,
    .btn-dark {
      font-size: 0.75rem;
      padding: 0.3rem 0.6rem;
    }

    .add-jabatan-btn {
      font-size: 0.7rem;
      padding: 0.3rem 0.5rem;
    }
  }
</style>

<div class="body d-flex py-3">
  <div class="body d-flex py-lg-3 py-md-2">
    <div class="container-xxl">
      <!-- Page Header -->
      <div class="row align-items-center">
        <div class="border-0 mb-4">
          <div
            class="card-header py-3 no-bg bg-transparent d-flex align-items-center px-0 justify-content-between border-bottom flex-wrap">
            <h3 class="fw-bold mb-0">Data Karyawan</h3>
            <div class="col-auto d-flex w-sm-100 gap-2">
              <button
                type="button"
                class="btn btn-dark btn-set-task w-sm-100 w-100 w-md-auto"
                data-bs-toggle="modal"
                data-bs-target="#addkaryawan">
                <i class="icofont-plus-circle me-2 fs-6"></i>Tambah Karyawan
              </button>
              <button
                type="button"
                class="btn btn-outline-success btn-set-task w-sm-100 w-100 w-md-auto"
                data-bs-toggle="modal"
                data-bs-target="#buatJabatanModal">
                <i class="icofont-plus-circle me-2 fs-6"></i>Tambah Jabatan
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Data Table -->
      <div class="row clearfix g-3">
        <div class="col-sm-12">
          <div class="card mb-3">
            <div class="card-body">
              <div class="table-responsive">
                <table id="myProjectTable" class="table table-hover align-middle mb-0">
                  <thead>
                    <tr>
                      <th>No</th>
                      <th>ID</th>
                      <th>Nama</th>
                      <th>Divisi</th>
                      <th>Jabatan</th>
                      <th>Status</th>
                      <th>Email</th>
                      <th>Tanggal Masuk</th>
                      <th>Aksi</th>
                    </tr>
                  </thead>
                  <tbody></tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Tambah Karyawan -->
<div class="modal fade" id="addkaryawan" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Tambah Karyawan Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formTambahKaryawan">
          <div class="row">
            <div class="col-md-12 mb-3">
              <label class="form-label">ID karyawan</label>
              <input type="text" class="form-control" id="idKaryawan" name="id_karyawan" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Nama Lengkap</label>
              <input type="text" class="form-control" id="namaKaryawan" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Divisi</label>
              <select class="form-select" id="divisiKaryawan" required></select>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Jabatan</label>
              <input type="text" class="form-control" value="Karyawan" readonly style="background-color: #f8f9fa;">
              <small class="text-muted">Jabatan default: Karyawan</small>
              <input type="hidden" id="jabatanKaryawan" value=""> <!-- Akan diisi otomatis dengan ID role Karyawan -->
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Jenis Kelamin</label>
              <select class="form-select" id="genderKaryawan" required>
                <option value="" disabled selected>Pilih Jenis Kelamin</option>
                <option value="Pria">Pria</option>
                <option value="Wanita">Wanita</option>
              </select>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">No. Telepon</label>
              <input type="tel" class="form-control" id="telpKaryawan" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Email</label>
              <input type="email" class="form-control" id="emailKaryawan" required>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Password</label>
              <input
                type="password"
                class="form-control"
                id="passwordKaryawan"
                value="12345678"
                readonly
                style="background-color: #f8f9fa;">
              <small class="text-muted">Password default: 12345678</small>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Konfirmasi Password</label>
              <input
                type="password"
                class="form-control"
                id="confirmPasswordKaryawan"
                value="12345678"
                readonly
                style="background-color: #f8f9fa;">
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button class="btn btn-primary" id="tambahKaryawanBtn">Simpan</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Detail Karyawan -->
<div class="modal fade" id="detailkaryawan" tabindex="-1" aria-labelledby="detailkaryawanLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detailkaryawanLabel">Detail Data Karyawan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row mb-4">
          <div class="col-md-4 text-center">
            <div class="photo-container mb-3">
              <img id="detailPhoto" class="employee-photo rounded-circle" 
                   src="https://via.placeholder.com/150" alt="Foto Karyawan" 
                   style="width: 150px; height: 150px; object-fit: cover; border: 3px solid #f8f9fa; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            </div>
            <div class="employee-id-badge">
              <span class="badge bg-primary fs-6" id="detailIdBadge">ID: -</span>
            </div>
          </div>
          <div class="col-md-8">
            <h4 id="detailNama" class="mb-3">-</h4>
            <div class="detail-badge-container mb-1">
              <span class="badge bg-info fs-6 p-2" id="detailGender">-</span>
              <span class="badge fs-5 p-2" id="detailStatusBadge">-</span>
            </div>
            <div class="employee-contact">
              <p class="mb-1"><i class="icofont-email me-2"></i><span id="detailEmail">-</span></p>
              <p class="mb-0"><i class="icofont-phone me-2"></i><span id="detailTelp">-</span></p>
            </div>
            <div class="mt-3">
              <a id="whatsappBtn" class="btn btn-success text-white" target="_blank">
                <i class="icofont-whatsapp me-2"></i>Hubungi via WhatsApp
              </a>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6">
            <div class="card mb-3">
              <div class="card-header bg-light">
                <h6 class="mb-0"><i class="icofont-briefcase me-2"></i>Informasi Pekerjaan</h6>
              </div>
              <div class="card-body">
                <div class="detail-item mb-2">
                  <div class="detail-label small text-muted">Divisi</div>
                  <div class="detail-value fw-medium" id="detailDivisi">-</div>
                </div>
                <div class="detail-item mb-2">
                  <div class="detail-label small text-muted">Jabatan</div>
                  <div class="detail-value fw-medium" id="detailJabatan">-</div>
                </div>
                <div class="detail-item mb-0">
                  <div class="detail-label small text-muted">Tanggal Masuk</div>
                  <div class="detail-value fw-medium" id="detailJoinDate">-</div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="card">
              <div class="card-header bg-light">
                <h6 class="mb-0"><i class="icofont-user me-2"></i>Informasi Pribadi</h6>
              </div>
              <div class="card-body">
                <div class="detail-item mb-2">
                  <div class="detail-label small text-muted">ID Karyawan</div>
                  <div class="detail-value fw-medium" id="detailId">-</div>
                </div>
                <div class="detail-item mb-2">
                  <div class="detail-label small text-muted">Nama Lengkap</div>
                  <div class="detail-value fw-medium" id="detailNamaFull">-</div>
                </div>
                <div class="detail-item mb-2">
                  <div class="detail-label small text-muted">Jenis Kelamin</div>
                  <div class="detail-value fw-medium" id="detailGenderFull">-</div>
                </div>
                <div class="detail-item mb-0">
                  <div class="detail-label small text-muted">Status Karyawan</div>
                  <div class="detail-value fw-medium" id="detailStatusFull">-</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Edit Karyawan - DIPERBAIKI dengan menambahkan field divisi -->
<div class="modal fade" id="editkaryawan" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Data Karyawan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formEditKaryawan">
          <input type="hidden" id="editId">

          <div class="row g-3">
            <!-- Nama & Email -->
            <div class="col-md-6 mb-3">
              <label class="form-label">Nama Lengkap</label>
              <input type="text" class="form-control" id="editNama" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Email</label>
              <input type="email" class="form-control" id="editEmail" required>
            </div>

            <!-- Telepon & Gender -->
            <div class="col-md-6 mb-3">
              <label class="form-label">No. Telepon</label>
              <input type="tel" class="form-control" id="editTelp" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Jenis Kelamin</label>
              <select class="form-select" id="editGender" required>
                <option value="" disabled selected>Pilih...</option>
                <option value="Pria">Pria</option>
                <option value="Wanita">Wanita</option>
              </select>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Status</label>
              <select class="form-select" id="editStatus" required>
                <option value="Aktif">Aktif</option>
                <option value="Non-Aktif">Non-Aktif</option>
                <option value="Cuti">Cuti</option>
              </select>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button class="btn btn-primary" id="simpanEditBtn">Simpan Perubahan</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Manage Jabatan Karyawan - MODIFIED -->
<div class="modal fade" id="manageJabatanModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Kelola Jabatan Karyawan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="manageKaryawanId">
        <input type="hidden" id="manageKaryawanDivisi">

        <div class="mb-3">
          <h6>Karyawan: <span id="manageKaryawanNama" class="fw-bold"></span></h6>
        </div>

        <!-- Pilih Divisi - TAMBAHAN BARU -->
        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label">Divisi</label>
            <select class="form-select" id="manageDivisiSelect" required>
              <option value="" selected disabled>Pilih Divisi</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Divisi Saat Ini</label>
            <input type="text" class="form-control" id="currentDivisiText" readonly>
          </div>
        </div>

        <div class="jabatan-container">
          <label class="form-label">Jabatan</label>
          <div class="d-flex gap-2 mb-2">
            <select class="form-select" id="manageJabatanSelect">
              <option value="" selected>Pilih Jabatan</option>
            </select>
            <button type="button" class="btn btn-primary add-jabatan-btn" id="tambahJabatanBtn">Tambah</button>
          </div>
          <div class="jabatan-list" id="manageJabatanList"></div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button class="btn btn-primary" id="simpanJabatanBtn">Simpan Perubahan</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Buat Jabatan Baru - MODIFIED -->
<div class="modal fade" id="buatJabatanModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Kelola Jabatan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <!-- Form Buat Jabatan Baru -->
        <div class="card mb-4">
          <div class="card-header">
            <h6 class="mb-0">Tambah Jabatan Baru</h6>
          </div>
          <div class="card-body">
            <form id="formBuatJabatan">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Divisi</label>
                  <select class="form-select" id="divisiJabatanBaru" required>
                    <option value="" selected disabled>Pilih Divisi</option>
                  </select>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Nama Jabatan</label>
                  <input type="text" class="form-control" id="namaJabatanBaru" required>
                </div>
              </div>
              <button type="button" class="btn btn-primary" id="simpanJabatanBaruBtn">Tambah Jabatan</button>
            </form>
          </div>
        </div>

        <!-- Daftar Jabatan Existing -->
        <div class="card">
          <div class="card-header">
            <h6 class="mb-0">Daftar Jabatan</h6>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <label class="form-label">Filter by Divisi</label>
              <select class="form-select" id="filterDivisi">
                <option value="" selected>Semua Divisi</option>
              </select>
            </div>
            <div class="table-responsive">
              <table class="table table-sm" id="jabatanTable">
                <thead>
                  <tr>
                    <th>Nama Jabatan</th>
                    <th>Divisi</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody id="jabatanListBody">

                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('script')
<script src="{{asset('assets/bundles/dataTables.bundle.js')}}"></script>
<script>
  $(document).ready(function() {
    // Set CSRF token untuk semua AJAX request
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });

    // Utility function untuk load select option
    function loadOptions(url, target, placeholder, key = 'id', value = 'nama') {
      $.get(url)
        .done(res => {
          let opts = `<option value="" disabled selected>${placeholder}</option>`;
          const data = res.data || res;

          data.forEach(d => {
            opts += `<option value="${d[key]}">${d[value]}</option>`;
          });
          $(target).html(opts);
        })
        .fail(err => {
          console.error(`Gagal load data dari ${url}:`, err);
          $(target).html(`<option value="">Gagal memuat data</option>`);
        });
    }

    // Inisialisasi DataTable
    var table = $("#myProjectTable").DataTable({
      ajax: {
        url: '/api/employees',
        dataSrc: 'data'
      },
      columns: [{
          data: null,
          render: (d, t, r, m) => m.row + 1
        },
        {
          data: 'id_karyawan'
        },
        {
          data: 'nama'
        },
        {
          data: null,
          render: (data, type, row) => {
            if (data.roles && data.roles.length > 0) {
              const divisions = [...new Set(data.roles.map(role => role.division?.nama_divisi).filter(Boolean))];
              return divisions.join(', ') || '-';
            }
            return '-';
          }
        },
        {
          data: null,
          render: (data, type, row) => {
            if (data.roles && data.roles.length > 0) {
              return data.roles.map(role => role.nama_jabatan).join(', ');
            }
            return '-';
          }
        },
        {
          data: 'status',
          render: d => {
            if (d === 'Aktif') return '<span class="badge bg-success">Aktif</span>';
            if (d === 'Cuti') return '<span class="badge bg-warning">Cuti</span>';
            return '<span class="badge bg-danger">Non-Aktif</span>';
          }
        },
        {
          data: 'user.email'
        },
        {
          data: 'created_at',
          render: d => new Date(d).toLocaleDateString('id-ID')
        },
        {
          data: null,
          render: (data, type, row) => `
          <div class="action-buttons">
            <button class="btn btn-outline-info btn-detail" data-id="${row.id_karyawan}">
              <i class="icofont-eye-alt"></i>
            </button>
            <button class="btn btn-outline-primary edit-btn" data-id="${row.id_karyawan}">
              <i class="icofont-edit"></i>
            </button>
            <button class="btn btn-outline-success manage-jabatan-btn" data-id="${row.id_karyawan}" data-nama="${row.nama}">
              <i class="icofont-ui-settings"></i>
            </button>
            <button class="btn btn-outline-danger deleterow" data-id="${row.id_karyawan}">
              <i class="icofont-ui-delete"></i>
            </button>
          </div>`
        }
      ]
    });

    // Load dropdown data - DIPERBAIKI dengan menambahkan #editDivisi
    loadOptions('/api/divisions', '#divisiKaryawan, #divisiJabatanBaru, #filterDivisi, #manageDivisiSelect', 'Pilih Divisi', 'id_divisi', 'nama_divisi');

    // Handler ketika divisi diubah di modal manage
    $('#manageDivisiSelect').change(function() {
      const divisiId = $(this).val();
      if (divisiId) {
        // Load jabatan untuk divisi yang dipilih
        loadJabatanOptionsForManage(divisiId);

        // Hapus jabatan yang tidak sesuai dengan divisi baru
        managedRoles = managedRoles.filter(role => role.division_id == divisiId);
        renderManagedJabatanList();
      }
    });

    // Fungsi untuk load jabatan di modal manage
    function loadJabatanOptionsForManage(divisiId = null) {
      $.get('/api/roles')
        .done(res => {
          let opts = '<option value="" selected>Pilih Jabatan</option>';
          const data = res.data || res;

          data.forEach(d => {
            if (!divisiId || d.division_id == divisiId) {
              opts += `<option value="${d.id_jabatan}" data-division="${d.division_id}">${d.nama_jabatan}</option>`;
            }
          });
          $('#manageJabatanSelect').html(opts);
        })
        .fail(err => {
          console.error('Gagal load data jabatan:', err);
          $('#manageJabatanSelect').html('<option value="">Gagal memuat data</option>');
        });
    }

    // Variabel untuk menyimpan jabatan yang dipilih di modal manage
    let managedRoles = [];

    // Handler tombol manage jabatan
    $(document).on('click', '.manage-jabatan-btn', function() {
      const karyawanId = $(this).data('id');
      const karyawanNama = $(this).data('nama');

      // Get data karyawan dari table
      const rowData = table.row($(this).closest('tr')).data();

      $('#manageKaryawanId').val(karyawanId);
      $('#manageKaryawanNama').text(karyawanNama);

      // Cari divisi dari roles yang ada
      let currentDivisiId = null;

      if (rowData.roles && rowData.roles.length > 0) {
        currentDivisiId = rowData.roles[0].division_id;
        $('#currentDivisiText').val(rowData.roles[0].division?.nama_divisi || '-');
      }

      $('#manageDivisiSelect').val(currentDivisiId || '');

      // Set roles yang sudah ada
      managedRoles = [];
      if (rowData.roles && rowData.roles.length > 0) {
        managedRoles = rowData.roles.map(role => ({
          id: role.id_jabatan,
          nama: role.nama_jabatan,
          division_id: role.division_id
        }));
      }

      // Load jabatan untuk divisi ini
      loadJabatanOptionsForManage(currentDivisiId);
      renderManagedJabatanList();

      $('#manageJabatanModal').modal('show');
    });

// Handler tombol tambah jabatan di modal manage
$('#tambahJabatanBtn').click(function() {
    const selectedJabatan = $('#manageJabatanSelect option:selected');
    const jabatanId = selectedJabatan.val();
    const jabatanNama = selectedJabatan.text();
    const divisiId = selectedJabatan.data('division');
    const selectedDivisiId = $('#manageDivisiSelect').val();

    if (!jabatanId) {
        alert('Pilih jabatan terlebih dahulu!');
        return;
    }

    if (!selectedDivisiId) {
        alert('Pilih divisi terlebih dahulu!');
        return;
    }

    // Pastikan jabatan sesuai dengan divisi yang dipilih
    if (divisiId != selectedDivisiId) {
        alert('Jabatan ini tidak sesuai dengan divisi yang dipilih!');
        return;
    }

    // CEK KHUSUS UNTUK JABATAN KEPALA DIVISI
    if (jabatanNama.toLowerCase().includes('kepala') || jabatanNama.toLowerCase().includes('head')) {
        // Hapus semua kepala divisi yang sudah ada di divisi ini
        managedRoles = managedRoles.filter(role => 
            !(role.nama.toLowerCase().includes('kepala') || role.nama.toLowerCase().includes('head')) ||
            role.division_id != selectedDivisiId
        );
    }

    if (managedRoles.find(role => role.id === jabatanId)) {
        alert('Jabatan ini sudah ditambahkan!');
        return;
    }

    managedRoles.push({
        id: jabatanId,
        nama: jabatanNama,
        division_id: divisiId
    });

    renderManagedJabatanList();
});

    // Fungsi untuk render list jabatan di modal manage
    function renderManagedJabatanList() {
      const jabatanList = $('#manageJabatanList');
      jabatanList.empty();

      managedRoles.forEach((role, index) => {
        jabatanList.append(`
          <div class="jabatan-badge">
            ${role.nama}
            <span class="remove-jabatan" data-index="${index}">×</span>
          </div>
        `);
      });
    }

    // Handler hapus jabatan dari list di modal manage
    $(document).on('click', '#manageJabatanList .remove-jabatan', function() {
      const index = $(this).data('index');
      managedRoles.splice(index, 1);
      renderManagedJabatanList();
    });

    // Simpan perubahan jabatan
// Simpan perubahan jabatan
$('#simpanJabatanBtn').click(function() {
    const karyawanId = $('#manageKaryawanId').val();
    const divisiId = $('#manageDivisiSelect').val();
    const roleIds = managedRoles.map(role => role.id);

    if (!divisiId) {
        alert('Pilih divisi terlebih dahulu!');
        return;
    }

    if (roleIds.length === 0) {
        alert('Pilih setidaknya satu jabatan!');
        return;
    }

    // CEK APAKAH ADA JABATAN KEPALA DIVISI YANG BARU
    const kepalaDivisiRoles = managedRoles.filter(role => 
        role.nama.toLowerCase().includes('kepala') || role.nama.toLowerCase().includes('head')
    );

    if (kepalaDivisiRoles.length > 0) {
        // Jika ya, hapus kepala divisi sebelumnya dari divisi yang sama
        const kepalaDivisiId = kepalaDivisiRoles[0].id;
        
        // Kirim informasi tambahan untuk menghapus kepala divisi lama
        $.ajax({
            url: '/api/employees/' + karyawanId + '/roles',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                role_ids: roleIds,
                division_id: divisiId,
                is_head_update: true,
                head_role_id: kepalaDivisiId
            }),
            dataType: 'json'
        })
        .done((response) => {
            if (response.success) {
                $('#manageJabatanModal').modal('hide');
                managedRoles = [];
                table.ajax.reload(null, false);
                alert('Jabatan berhasil diperbarui! Kepala divisi sebelumnya telah dicabut.');
            } else {
                alert('Gagal update jabatan: ' + (response.message || 'Unknown error'));
            }
        })
        .fail(err => {
            const errorMsg = err.responseJSON?.message || err.statusText;
            alert('Gagal update jabatan: ' + errorMsg);
            console.error('Error details:', err);
        });
    } else {
        // Jika tidak ada jabatan kepala divisi, simpan seperti biasa
        $.ajax({
            url: '/api/employees/' + karyawanId + '/roles',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                role_ids: roleIds,
                division_id: divisiId
            }),
            dataType: 'json'
        })
        .done((response) => {
            if (response.success) {
                $('#manageJabatanModal').modal('hide');
                managedRoles = [];
                table.ajax.reload(null, false);
                alert('Jabatan berhasil diperbarui!');
            } else {
                alert('Gagal update jabatan: ' + (response.message || 'Unknown error'));
            }
        })
        .fail(err => {
            const errorMsg = err.responseJSON?.message || err.statusText;
            alert('Gagal update jabatan: ' + errorMsg);
            console.error('Error details:', err);
        });
    }
});

    // FUNGSI UNTUK MODAL TAMBAH JABATAN
    // Load daftar jabatan untuk modal tambah jabatan
    function loadDaftarJabatan(divisiId = null) {
      $.get('/api/roles')
        .done(res => {
          const data = res.data || res;
          let html = '';

          const filteredData = divisiId ?
            data.filter(jabatan => jabatan.division_id == divisiId) :
            data;

          if (filteredData.length === 0) {
            html = '<tr><td colspan="3" class="text-center">Tidak ada data jabatan</td></tr>';
          } else {
            filteredData.forEach(jabatan => {
              html += `
                <tr>
                  <td>${jabatan.nama_jabatan}</td>
                  <td>${jabatan.division?.nama_divisi || '-'}</td>
                  <td>
                    <button class="btn btn-sm btn-danger hapus-jabatan-btn" data-id="${jabatan.id_jabatan}">
                      <i class="icofont-ui-delete"></i> Hapus
                    </button>
                  </td>
                </tr>
              `;
            });
          }

          $('#jabatanListBody').html(html);
        })
        .fail(err => {
          console.error('Gagal load data jabatan:', err);
          $('#jabatanListBody').html('<tr><td colspan="3" class="text-center">Gagal memuat data</td></tr>');
        });
    }

    // Filter jabatan berdasarkan divisi di modal tambah jabatan
    $('#filterDivisi').change(function() {
      const divisiId = $(this).val();
      loadDaftarJabatan(divisiId);
    });

    // Handler hapus jabatan di modal tambah jabatan
    $(document).on('click', '.hapus-jabatan-btn', function() {
      const jabatanId = $(this).data('id');
      const jabatanNama = $(this).closest('tr').find('td:first').text();

      if (confirm(`Apakah yakin ingin menghapus jabatan "${jabatanNama}"?`)) {
        $.ajax({
            url: '/api/roles/' + jabatanId,
            type: 'DELETE'
          })
          .done(response => {
            if (response.success) {
              alert('Jabatan berhasil dihapus!');
              loadDaftarJabatan($('#filterDivisi').val());
            } else {
              alert('Gagal menghapus jabatan: ' + (response.message || 'Unknown error'));
            }
          })
          .fail(err => {
            const errorMsg = err.responseJSON?.message || err.statusText;
            alert('Gagal menghapus jabatan: ' + errorMsg);
          });
      }
    });

    // Handler buat jabatan baru
    $('#simpanJabatanBaruBtn').off('click').on('click', function() {
      const namaJabatan = $('#namaJabatanBaru').val();
      const divisiId = $('#divisiJabatanBaru').val();

      if (!namaJabatan || !divisiId) {
        alert('Nama jabatan dan divisi harus diisi!');
        return;
      }

      $(this).prop('disabled', true);

      const payload = {
        nama_jabatan: namaJabatan,
        division_id: divisiId
      };

      $.ajax({
          url: '/api/roles',
          type: 'POST',
          contentType: 'application/json',
          data: JSON.stringify(payload),
          dataType: 'json'
        })
        .done(response => {
          if (response.success) {
            alert('Jabatan berhasil dibuat!');
            $('#formBuatJabatan')[0].reset();
            // Refresh daftar jabatan
            loadDaftarJabatan($('#filterDivisi').val());
          }
        })
        .fail(err => {
          alert('Gagal membuat jabatan: ' + (err.responseJSON?.message || err.statusText));
        })
        .always(() => {
          $(this).prop('disabled', false);
        });
    });

    // Load daftar jabatan saat modal tambah jabatan dibuka
    $('#buatJabatanModal').on('show.bs.modal', function() {
      loadDaftarJabatan();
    });

    // TAMBAH KARYAWAN - FIXED (auto bikin role Karyawan di divisi manapun)
    $('#tambahKaryawanBtn').click(function() {
  const idKaryawan = $('#idKaryawan').val(); 
  const nama = $('#namaKaryawan').val();
  const divisiId = $('#divisiKaryawan').val();
  const gender = $('#genderKaryawan').val();
  const telp = $('#telpKaryawan').val();
  const email = $('#emailKaryawan').val();

  if (!idKaryawan || !nama || !divisiId || !gender || !telp || !email) {
    alert('Semua field harus diisi!');
    return;
  }

  // Cari role "Karyawan" yang sudah ada di divisi ini
  $.get('/api/roles')
    .done(rolesResponse => {
      const roles = rolesResponse.data || rolesResponse;
      
      // Cari role Karyawan di divisi yang dipilih
      const roleKaryawan = roles.find(role => 
        role.nama_jabatan.toLowerCase() === 'karyawan' && 
        role.division_id == divisiId
      );

      if (roleKaryawan) {
        // Jika ditemukan, gunakan role yang sudah ada
        simpanKaryawan(roleKaryawan.id_jabatan, idKaryawan, nama, gender, telp, email);
      } else {
        // Jika tidak ditemukan, buat role baru
        buatRoleKaryawanBaru(divisiId, idKaryawan, nama, gender, telp, email);
      }
    })
    .fail(err => {
      alert('Gagal memuat data roles: ' + (err.responseJSON?.message || err.statusText));
    });
});

    // Fungsi untuk cari role Karyawan yang sudah ada
    function cariRoleKaryawan(divisiId,idKaryawan, nama, gender, telp, email) {
      $.get('/api/roles')
        .done(rolesResponse => {
          const roles = rolesResponse.data || rolesResponse;

          // Cari role Karyawan di divisi yang dipilih
          let roleKaryawan = roles.find(role =>
            role.nama_jabatan.toLowerCase() === 'karyawan' &&
            role.division_id == divisiId
          );

          if (roleKaryawan) {
            // Jika ditemukan, simpan karyawan
            simpanKaryawan(roleKaryawan.id_jabatan,idKaryawan, nama, gender, telp, email);
          } else {
            // Jika tidak ditemukan, cari di divisi manapun
            roleKaryawan = roles.find(role => role.nama_jabatan.toLowerCase() === 'karyawan');

            if (roleKaryawan) {
              simpanKaryawan(roleKaryawan.id_jabatan,idKaryawan, nama, gender, telp, email);
            } else {
              alert('Tidak dapat menemukan atau membuat role "Karyawan". Silahkan coba lagi.');
            }
          }
        })
        .fail(err => {
          alert('Gagal memuat data roles: ' + (err.responseJSON?.message || err.statusText));
        });
    }

    // Fungsi untuk buat role Karyawan baru
function buatRoleKaryawanBaru(divisiId, idKaryawan, nama, gender, telp, email) {
  $.ajax({
    url: '/api/roles',
    type: 'POST',
    contentType: 'application/json',
    data: JSON.stringify({
      nama_jabatan: 'Karyawan',
      division_id: divisiId
    }),
    dataType: 'json'
  })
  .done(roleResponse => {
    if (roleResponse.success) {
      // Role berhasil dibuat, sekarang simpan karyawan
      const roleId = roleResponse.data.id_jabatan;
      simpanKaryawan(roleId, idKaryawan, nama, gender, telp, email);
    } else {
      alert('Gagal membuat role Karyawan: ' + (roleResponse.message || 'Unknown error'));
    }
  })
  .fail(err => {
    alert('Gagal membuat role Karyawan: ' + (err.responseJSON?.message || err.statusText));
  });
}

    // Fungsi untuk simpan karyawan
    function simpanKaryawan(roleId, idKaryawan, nama, gender, telp, email) {
  const payload = {
    id_karyawan: idKaryawan,
    nama: nama,
    gender: gender,
    no_telp: telp,
    email: email,
    role_id: roleId,
    status: 'Aktif'
  };

  $.ajax({
    url: '/api/employees',
    type: 'POST',
    contentType: 'application/json',
    data: JSON.stringify(payload),
    dataType: 'json'
  })
  .done((response) => {
    if (response.success) {
      $('#formTambahKaryawan')[0].reset();
      $('#addkaryawan').modal('hide');
      table.ajax.reload(null, false);
      alert('Karyawan berhasil ditambahkan!');
    } else {
      alert('Gagal tambah karyawan: ' + (response.message || 'Unknown error'));
    }
  })
  .fail(err => {
    const errorMsg = err.responseJSON?.message || err.statusText;
    alert('Gagal tambah karyawan: ' + errorMsg);
  });
}

    // EDIT KARYAWAN
    $('#myProjectTable').on('click', '.edit-btn', function() {
      const rowData = table.row($(this).closest('tr')).data();
      $('#editId').val(rowData.id_karyawan);
      $('#editNama').val(rowData.nama);
      $('#editGender').val(rowData.gender);
      $('#editStatus').val(rowData.status || 'Aktif'); // PASTIKAN STATUS TERISI
      $('#editTelp').val(rowData.no_telp);
      $('#editEmail').val(rowData.user.email);

      $('#editkaryawan').modal('show');
    });

    // SIMPAN EDIT KARYAWAN - DIPERBAIKI (tanpa divisi)
    $('#simpanEditBtn').click(function() {
      const id = $('#editId').val();

      const payload = {
        nama: $('#editNama').val(),
        no_telp: $('#editTelp').val(),
        email: $('#editEmail').val(),
        gender: $('#editGender').val(),
        status: $('#editStatus').val() // KIRIM STATUS
      };

      $.ajax({
          url: '/api/employees/' + id,
          type: 'PUT',
          contentType: 'application/json',
          data: JSON.stringify(payload),
          dataType: 'json'
        })
        .done((response) => {
          if (response.success) {
            $('#editkaryawan').modal('hide');
            table.ajax.reload(null, false);
            alert('Data berhasil diperbarui!');
          } else {
            alert('Gagal update: ' + (response.message || 'Unknown error'));
          }
        })
        .fail(err => {
          const errorMsg = err.responseJSON?.message || err.statusText;
          alert('Gagal update: ' + errorMsg);
        });
    });

    // Hapus Karyawan
    $('#myProjectTable').on('click', '.deleterow', function() {
      const id = $(this).data('id');
      if (confirm('Apakah yakin ingin menghapus?')) {
        $.ajax({
            url: '/api/employees/' + id,
            type: 'DELETE'
          })
          .done(() => table.ajax.reload(null, false))
          .fail(err => alert('Gagal hapus: ' + (err.responseJSON?.message || err.statusText)));
      }
    });

    // Detail Karyawan
    $('#myProjectTable').on('click', '.btn-detail', function() {
      const rowData = table.row($(this).closest('tr')).data();
      
      // Set data dasar
      $('#detailId').text(rowData.id_karyawan);
      $('#detailIdBadge').text('ID: ' + rowData.id_karyawan);
      $('#detailNama').text(rowData.nama);
      $('#detailNamaFull').text(rowData.nama);
      $('#detailGender').text(rowData.gender);
      $('#detailGenderFull').text(rowData.gender);
      $('#detailEmail').text(rowData.user.email);
      $('#detailTelp').text(rowData.no_telp);

      // Set status dengan badge yang sesuai
      let statusBadge = '';
      if (rowData.status === 'Aktif') {
        statusBadge = '<span class="badge bg-success">Aktif</span>';
      } else if (rowData.status === 'Cuti') {
        statusBadge = '<span class="badge bg-warning">Cuti</span>';
      } else {
        statusBadge = '<span class="badge bg-danger">Non-Aktif</span>';
      }
      
      $('#detailStatusBadge').html(statusBadge);
      $('#detailStatusFull').html(rowData.status || 'Aktif');
      $('#detailStatusFull').removeClass().addClass('detail-value fw-medium');
      
      if (rowData.roles && rowData.roles.length > 0) {
        const divisions = [...new Set(rowData.roles.map(role => role.division?.nama_divisi).filter(Boolean))];
        $('#detailDivisi').text(divisions.join(', ') || '-');
        $('#detailJabatan').text(rowData.roles.map(role => role.nama_jabatan).join(', '));
      } else {
        $('#detailDivisi').text('-');
        $('#detailJabatan').text('-');
      }


      $('#detailJoinDate').text(new Date(rowData.created_at).toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      }));


      let photoUrl;
      if (rowData.foto) {
        photoUrl = '/storage/' + rowData.foto;
      } else {
        // Default avatar berdasarkan gender
        if (rowData.gender === 'Wanita') {
          photoUrl = 'assets/images/xs/avatar2.jpg';
        } else {
          photoUrl = 'assets/images/xs/avatar1.jpg';
        }
      }

      // Atur tombol WhatsApp
    const whatsappBtn = $('#whatsappBtn');
    if (rowData.no_telp) {
      const phoneNumber = rowData.no_telp.replace(/\D/g, ''); // Hapus karakter non-digit
      whatsappBtn.attr('href', `https://wa.me/${phoneNumber}`);
      whatsappBtn.show();
    } else {
      whatsappBtn.hide();
    }


      $('#detailPhoto').attr('src', photoUrl);
      
      $('#detailkaryawan').modal('show');
    });

    loadJabatanOptionsForAdd();
    loadDaftarJabatan();
  });
</script>
@endsection