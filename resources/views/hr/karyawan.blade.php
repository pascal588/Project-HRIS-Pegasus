@extends('template.template')

@section('title', 'Karyawan')

@section('content')
<!-- plugin table data  -->
<link rel="stylesheet" href="{{asset('assets/plugin/datatables/responsive.dataTables.min.css')}}" />
<link rel="stylesheet" href="{{asset('assets/plugin/datatables/dataTables.bootstrap5.min.css')}}" />

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

  .jabatan-badge {
    display: inline-flex;
    align-items: center;
    background-color: #e9ecef;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
    margin: 0.1rem;
  }

  .jabatan-badge .remove-jabatan {
    margin-left: 0.5rem;
    cursor: pointer;
    color: #dc3545;
  }

  .swal2-container {
    z-index: 99999 !important;
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
  }

  /* Fix untuk dropdown pagination */
  #myProjectTable_wrapper .dataTables_length select {
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    padding: 0.375rem 2.25rem 0.375rem 0.75rem;
  }
</style>

<div class="body d-flex py-3">
  <div class="body d-flex py-lg-3 py-md-2">
    <div class="container-xxl">
      <!-- Page Header -->
      <div class="row align-items-center">
        <div class="border-0 mb-4">
          <div class="card-header py-3 no-bg bg-transparent d-flex align-items-center px-0 justify-content-between border-bottom flex-wrap">
            <h3 class="fw-bold mb-0">Data Karyawan</h3>
            <div class="col-auto d-flex w-sm-100 gap-2">
              <button type="button" class="btn btn-dark btn-set-task w-sm-100 w-100 w-md-auto" data-bs-toggle="modal" data-bs-target="#addkaryawan">
                <i class="icofont-plus-circle me-2 fs-6"></i>Tambah Karyawan
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
              <select class="form-select" id="divisiKaryawan" required>
                <option value="" disabled selected>Pilih Divisi</option>
              </select>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Jabatan</label>
              <select class="form-select" id="jabatanKaryawan" required>
                <option value="" disabled selected>Pilih Jabatan</option>
              </select>
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
              <input type="password" class="form-control" id="passwordKaryawan" value="12345678" readonly style="background-color: #f8f9fa;">
              <small class="text-muted">Password default: 12345678</small>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Konfirmasi Password</label>
              <input type="password" class="form-control" id="confirmPasswordKaryawan" value="12345678" readonly style="background-color: #f8f9fa;">
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

<!-- Modal Edit Karyawan - DIPERBAIKI dengan fitur kelola jabatan -->
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

            <!-- Status & Divisi -->
            <div class="col-md-6 mb-3">
              <label class="form-label">Status</label>
              <select class="form-select" id="editStatus" required>
                <option value="Aktif">Aktif</option>
                <option value="Non-Aktif">Non-Aktif</option>
                <option value="Cuti">Cuti</option>
              </select>
            </div>
            
            <div class="col-md-6 mb-3">
              <label class="form-label">Divisi</label>
              <select class="form-select" id="editDivisi" required>
                <option value="" disabled selected>Pilih Divisi</option>
              </select>
            </div>

            <!-- Kelola Jabatan -->
            <div class="col-12 mb-3">
              <label class="form-label">Jabatan</label>
              <div class="d-flex gap-2 mb-2">
                <select class="form-select" id="editJabatanSelect">
                  <option value="" selected>Pilih Jabatan</option>
                </select>
                <button type="button" class="btn btn-primary add-jabatan-btn" id="tambahJabatanEditBtn">Tambah</button>
              </div>
              <div class="jabatan-list" id="editJabatanList"></div>
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
@endsection

@section('script')
<script src="{{asset('assets/bundles/dataTables.bundle.js')}}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
  // ==================== KONSTAN DAN UTILITY ====================
  const API_ROUTES = {
    employees: '/api/employees',
    divisions: '/api/divisions',
    roles: '/api/roles'
  };

  const DEFAULT_CONFIG = {
    password: '12345678',
    defaultPhoto: {
      male: 'assets/images/xs/avatar1.jpg',
      female: 'assets/images/xs/avatar2.jpg'
    }
  };

  // Cache untuk data yang sering digunakan
  const dataCache = {
    divisions: null,
    roles: null
  };

  // ==================== UTILITY FUNCTIONS ====================
  function showAlert(icon, title, text) {
    return Swal.fire({
      icon: icon,
      title: title,
      text: text,
      confirmButtonColor: '#3085d6',
    });
  }

  function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('id-ID', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  }

  // Fungsi untuk mengubah nama menjadi format kapital setiap kata
  function formatNamaKapital(nama) {
    if (!nama) return '';
    
    return nama
      .toLowerCase()
      .split(' ')
      .map(word => word.charAt(0).toUpperCase() + word.slice(1))
      .join(' ')
      .replace(/\s+/g, ' ')
      .trim();
  }

  // Validasi email
  function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  }

  // Validasi nomor telepon Indonesia
  function validatePhone(phone) {
    const phoneRegex = /^(\+62|62|0)[1-9][0-9]{6,12}$/;
    return phoneRegex.test(phone.replace(/\s/g, ''));
  }

  // Validasi ID karyawan (contoh: harus alphanumeric, min 3 karakter)
  function validateIdKaryawan(id) {
    const idRegex = /^[a-zA-Z0-9]{3,}$/;
    return idRegex.test(id);
  }

  // ==================== DATA MANAGEMENT ====================
  class DataManager {
    static async loadData(url) {
      try {
        const response = await $.get(url);
        return response.data || response;
      } catch (error) {
        console.error(`Gagal load data dari ${url}:`, error);
        throw error;
      }
    }

    static async loadOptions(url, target, placeholder, key = 'id', value = 'nama') {
      try {
        const data = await this.loadData(url);
        let options = `<option value="" disabled selected>${placeholder}</option>`;
        
        data.forEach(item => {
          options += `<option value="${item[key]}">${item[value]}</option>`;
        });
        
        $(target).html(options);
        return data;
      } catch (error) {
        $(target).html(`<option value="">Gagal memuat data</option>`);
        throw error;
      }
    }

    static async cacheInitialData() {
      try {
        const [divisions, roles] = await Promise.all([
          this.loadData(API_ROUTES.divisions),
          this.loadData(API_ROUTES.roles)
        ]);

        dataCache.divisions = divisions;
        dataCache.roles = roles;

        return { divisions, roles };
      } catch (error) {
        console.error('Gagal cache initial data:', error);
        throw error;
      }
    }
  }

  // ==================== KARYAWAN MANAGEMENT ====================
  class KaryawanManager {
    constructor() {
      this.table = null;
      this.managedRoles = [];
      this.init();
    }

    init() {
      this.initializeDataTable();
      this.bindEvents();
      this.loadInitialData();
    }

    initializeDataTable() {
      this.table = $("#myProjectTable").DataTable({
        processing: true,
        serverSide: false,
        ajax: {
          url: API_ROUTES.employees,
          dataSrc: 'data',
          error: (xhr, error, thrown) => {
            console.error('Error loading table data:', error);
            showAlert('error', 'Error', 'Gagal memuat data karyawan');
          }
        },
        columns: [
          { 
            data: null, 
            render: (d, t, r, m) => m.row + 1,
            className: 'text-center'
          },
          { 
            data: 'id_karyawan',
            className: 'text-center'
          },
          { 
            data: 'nama'
          },
          { 
            data: null,
            render: (data) => {
              if (data.roles?.length > 0) {
                const divisions = [...new Set(data.roles.map(role => role.division?.nama_divisi).filter(Boolean))];
                return divisions.join(', ') || '-';
              }
              return '-';
            }
          },
          { 
            data: null,
            render: (data) => {
              if (data.roles?.length > 0) {
                return data.roles.map(role => role.nama_jabatan).join(', ');
              }
              return '-';
            }
          },
          { 
            data: 'status',
            render: d => {
              const statusMap = {
                'Aktif': 'bg-success',
                'Cuti': 'bg-warning',
                'Non-Aktif': 'bg-danger'
              };
              return `<span class="badge ${statusMap[d] || 'bg-secondary'}">${d}</span>`;
            },
            className: 'text-center'
          },
          { 
            data: 'user.email'
          },
          { 
            data: 'created_at',
            render: d => new Date(d).toLocaleDateString('id-ID'),
            className: 'text-center'
          },
          { 
            data: null,
            render: (data) => `
              <div class="action-buttons">
                <button class="btn btn-outline-info btn-detail" data-id="${data.id_karyawan}" title="Detail">
                  <i class="icofont-eye-alt"></i>
                </button>
                <button class="btn btn-outline-primary edit-btn" data-id="${data.id_karyawan}" title="Edit">
                  <i class="icofont-edit"></i>
                </button>
                <button class="btn btn-outline-danger deleterow" data-id="${data.id_karyawan}" title="Hapus">
                  <i class="icofont-ui-delete"></i>
                </button>
              </div>`,
            className: 'text-center'
          }
        ],
        language: {
          emptyTable: "Tidak ada data karyawan",
          info: "Menampilkan _START_ hingga _END_ dari _TOTAL_ karyawan",
          infoEmpty: "Menampilkan 0 hingga 0 dari 0 karyawan",
          infoFiltered: "(disaring dari _MAX_ total karyawan)",
          lengthMenu: "Tampilkan _MENU_ karyawan",
          loadingRecords: "Memuat...",
          processing: "Memproses...",
          search: "Cari:",
          zeroRecords: "Tidak ditemukan data yang sesuai"
        }
      });
    }

    bindEvents() {
      // Auto-format nama menjadi kapital
      $('#namaKaryawan, #editNama').on('blur', (e) => {
        const formattedName = formatNamaKapital($(e.target).val());
        $(e.target).val(formattedName);
      });

      // Tambah Karyawan
      $('#tambahKaryawanBtn').on('click', () => this.tambahKaryawan());
      
      // Edit Karyawan
      $('#myProjectTable').on('click', '.edit-btn', (e) => this.handleEditKaryawan(e));
      $('#simpanEditBtn').on('click', () => this.simpanEditKaryawan());
      
      // Detail Karyawan
      $('#myProjectTable').on('click', '.btn-detail', (e) => this.showDetailKaryawan(e));
      
      // Hapus Karyawan
      $('#myProjectTable').on('click', '.deleterow', (e) => this.hapusKaryawan(e));
      
      // Kelola Jabatan di Modal Edit
      $('#tambahJabatanEditBtn').on('click', () => this.tambahJabatanEdit());
      $('#editJabatanList').on('click', '.remove-jabatan', (e) => this.hapusJabatanEdit(e));
      
      // Load jabatan berdasarkan divisi
      $('#editDivisi').on('change', () => this.loadJabatanByDivisi());
      $('#divisiKaryawan').on('change', () => this.loadJabatanByDivisi('add'));

      // Reset form ketika modal ditutup
      $('#addkaryawan').on('hidden.bs.modal', () => {
        $('#formTambahKaryawan')[0].reset();
        $('#jabatanKaryawan').html('<option value="" selected>Pilih Jabatan</option>');
      });

      $('#editkaryawan').on('hidden.bs.modal', () => {
        this.managedRoles = [];
        $('#editJabatanList').empty();
      });
    }

    async loadInitialData() {
      try {
        await DataManager.cacheInitialData();
        
        // Load dropdown divisi
        await DataManager.loadOptions(API_ROUTES.divisions, '#divisiKaryawan', 'Pilih Divisi', 'id_divisi', 'nama_divisi');
        await DataManager.loadOptions(API_ROUTES.divisions, '#editDivisi', 'Pilih Divisi', 'id_divisi', 'nama_divisi');
        
      } catch (error) {
        showAlert('error', 'Error', 'Gagal memuat data awal');
      }
    }

    async loadJabatanByDivisi(context = 'edit') {
      const divisiId = context === 'add' ? $('#divisiKaryawan').val() : $('#editDivisi').val();
      const targetSelect = context === 'add' ? '#jabatanKaryawan' : '#editJabatanSelect';

      if (!divisiId) {
        $(targetSelect).html('<option value="" selected>Pilih Jabatan</option>');
        return;
      }

      try {
        const filteredRoles = dataCache.roles.filter(role => role.division_id == divisiId);
        let options = '<option value="" selected>Pilih Jabatan</option>';
        
        if (filteredRoles.length === 0) {
          options = '<option value="" disabled selected>Belum ada jabatan untuk divisi ini</option>';
        } else {
          filteredRoles.forEach(role => {
            options += `<option value="${role.id_jabatan}">${role.nama_jabatan}</option>`;
          });
        }
        
        $(targetSelect).html(options);
      } catch (error) {
        console.error('Gagal load jabatan:', error);
        $(targetSelect).html('<option value="" disabled selected>Gagal memuat data jabatan</option>');
      }
    }

    validateFormTambahKaryawan(formData) {
      const errors = [];

      // Validasi required fields
      if (!formData.id_karyawan) errors.push('ID karyawan harus diisi');
      if (!formData.nama) errors.push('Nama lengkap harus diisi');
      if (!formData.divisi_id) errors.push('Divisi harus dipilih');
      if (!formData.jabatan_id) errors.push('Jabatan harus dipilih');
      if (!formData.gender) errors.push('Jenis kelamin harus dipilih');
      if (!formData.no_telp) errors.push('Nomor telepon harus diisi');
      if (!formData.email) errors.push('Email harus diisi');

      // Validasi format
      if (formData.id_karyawan && !validateIdKaryawan(formData.id_karyawan)) {
        errors.push('ID karyawan harus alfanumerik dan minimal 3 karakter');
      }

      if (formData.email && !validateEmail(formData.email)) {
        errors.push('Format email tidak valid');
      }

      if (formData.no_telp && !validatePhone(formData.no_telp)) {
        errors.push('Format nomor telepon Indonesia tidak valid');
      }

      // Validasi jabatan tersedia
      if (formData.divisi_id) {
        const jabatanTersedia = dataCache.roles.filter(role => role.division_id == formData.divisi_id);
        if (jabatanTersedia.length === 0) {
          errors.push('Divisi yang dipilih belum memiliki jabatan. Silakan buat jabatan terlebih dahulu di master data jabatan.');
        }
      }

      return errors;
    }

    validateFormEditKaryawan(formData) {
      const errors = [];

      // Validasi required fields
      if (!formData.nama) errors.push('Nama lengkap harus diisi');
      if (!formData.divisi_id) errors.push('Divisi harus dipilih');
      if (!formData.gender) errors.push('Jenis kelamin harus dipilih');
      if (!formData.no_telp) errors.push('Nomor telepon harus diisi');
      if (!formData.email) errors.push('Email harus diisi');
      if (!formData.status) errors.push('Status harus dipilih');

      // Validasi format
      if (formData.email && !validateEmail(formData.email)) {
        errors.push('Format email tidak valid');
      }

      if (formData.no_telp && !validatePhone(formData.no_telp)) {
        errors.push('Format nomor telepon Indonesia tidak valid');
      }

      // Validasi minimal satu jabatan
      if (formData.role_ids.length === 0) {
        errors.push('Pilih minimal satu jabatan');
      }

      // Validasi jabatan tersedia
      if (formData.divisi_id) {
        const jabatanTersedia = dataCache.roles.filter(role => role.division_id == formData.divisi_id);
        if (jabatanTersedia.length === 0) {
          errors.push('Divisi yang dipilih belum memiliki jabatan. Silakan buat jabatan terlebih dahulu di master data jabatan.');
        }
      }

      return errors;
    }

    async tambahKaryawan() {
      const formData = {
        id_karyawan: $('#idKaryawan').val().trim(),
        nama: $('#namaKaryawan').val().trim(),
        divisi_id: $('#divisiKaryawan').val(),
        jabatan_id: $('#jabatanKaryawan').val(),
        gender: $('#genderKaryawan').val(),
        no_telp: $('#telpKaryawan').val().trim(),
        email: $('#emailKaryawan').val().trim(),
        status: 'Aktif'
      };

      // Validasi form
      const errors = this.validateFormTambahKaryawan(formData);
      if (errors.length > 0) {
        showAlert('warning', 'Validasi Gagal', errors.join('<br>'));
        return;
      }

      // Format nama menjadi kapital
      formData.nama = formatNamaKapital(formData.nama);

      try {
        const payload = {
          id_karyawan: formData.id_karyawan,
          nama: formData.nama,
          gender: formData.gender,
          no_telp: formData.no_telp,
          email: formData.email,
          role_id: formData.jabatan_id,
          status: formData.status
        };

        // Tampilkan loading
        $('#tambahKaryawanBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span> Menyimpan...');

        const response = await $.ajax({
          url: API_ROUTES.employees,
          type: 'POST',
          contentType: 'application/json',
          data: JSON.stringify(payload)
        });

        if (response.success) {
          $('#formTambahKaryawan')[0].reset();
          $('#addkaryawan').modal('hide');
          this.table.ajax.reload(null, false);
          showAlert('success', 'Berhasil', 'Karyawan berhasil ditambahkan!');
        } else {
          throw new Error(response.message);
        }
      } catch (error) {
        const errorMessage = error.responseJSON?.message || error.message;
        showAlert('error', 'Gagal', 'Gagal tambah karyawan: ' + errorMessage);
      } finally {
        // Reset button state
        $('#tambahKaryawanBtn').prop('disabled', false).html('Simpan');
      }
    }

    handleEditKaryawan(event) {
      const rowData = this.table.row($(event.currentTarget).closest('tr')).data();
      
      // Isi form edit
      $('#editId').val(rowData.id_karyawan);
      $('#editNama').val(rowData.nama);
      $('#editGender').val(rowData.gender);
      $('#editStatus').val(rowData.status || 'Aktif');
      $('#editTelp').val(rowData.no_telp);
      $('#editEmail').val(rowData.user.email);

      // Set divisi dan jabatan
      const currentDivisi = rowData.roles?.[0]?.division_id;
      if (currentDivisi) {
        $('#editDivisi').val(currentDivisi);
        this.loadJabatanByDivisi('edit').then(() => {
          // Set jabatan yang sudah ada
          this.managedRoles = rowData.roles?.map(role => ({
            id: role.id_jabatan,
            nama: role.nama_jabatan,
            division_id: role.division_id
          })) || [];
          this.renderJabatanList();
        });
      } else {
        $('#editDivisi').val('');
        this.managedRoles = [];
        this.renderJabatanList();
      }

      $('#editkaryawan').modal('show');
    }

    tambahJabatanEdit() {
      const selectedJabatan = $('#editJabatanSelect option:selected');
      const jabatanId = selectedJabatan.val();
      const jabatanNama = selectedJabatan.text();
      const divisiId = $('#editDivisi').val();

      if (!jabatanId || jabatanId === '' || !divisiId) {
        showAlert('warning', 'Peringatan', 'Pilih divisi dan jabatan terlebih dahulu!');
        return;
      }

      // Cek apakah jabatan sudah dipilih
      if (this.managedRoles.find(role => role.id === jabatanId)) {
        showAlert('warning', 'Peringatan', 'Jabatan ini sudah ditambahkan!');
        return;
      }

      this.managedRoles.push({
        id: jabatanId,
        nama: jabatanNama,
        division_id: divisiId
      });

      this.renderJabatanList();
    }

    hapusJabatanEdit(event) {
      const index = $(event.currentTarget).data('index');
      this.managedRoles.splice(index, 1);
      this.renderJabatanList();
    }

    renderJabatanList() {
      const jabatanList = $('#editJabatanList');
      jabatanList.empty();

      if (this.managedRoles.length === 0) {
        jabatanList.append('<div class="text-muted small">Belum ada jabatan yang dipilih</div>');
        return;
      }

      this.managedRoles.forEach((role, index) => {
        jabatanList.append(`
          <div class="jabatan-badge">
            ${role.nama}
            <span class="remove-jabatan" data-index="${index}" title="Hapus jabatan">×</span>
          </div>
        `);
      });
    }

    async simpanEditKaryawan() {
      const id = $('#editId').val();
      const roleIds = this.managedRoles.map(role => role.id);

      const formData = {
        nama: $('#editNama').val().trim(),
        no_telp: $('#editTelp').val().trim(),
        email: $('#editEmail').val().trim(),
        gender: $('#editGender').val(),
        status: $('#editStatus').val()
      };

      // Validasi form
      const errors = this.validateFormEditKaryawan({
        ...formData,
        divisi_id: $('#editDivisi').val(),
        role_ids: roleIds
      });
      if (errors.length > 0) {
        showAlert('warning', 'Validasi Gagal', errors.join('<br>'));
        return;
      }

      // Format nama menjadi kapital
      formData.nama = formatNamaKapital(formData.nama);

      try {
        // Tampilkan loading
        $('#simpanEditBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span> Menyimpan...');

        // 1. Update data dasar karyawan
        const updateResponse = await $.ajax({
          url: `${API_ROUTES.employees}/${id}`,
          type: 'PUT',
          contentType: 'application/json',
          data: JSON.stringify(formData)
        });

        if (!updateResponse.success) {
          throw new Error(updateResponse.message || 'Gagal update data karyawan');
        }

        // 2. Update roles secara terpisah jika ada role yang dipilih
        if (roleIds.length > 0) {
          const rolesPayload = {
            role_ids: roleIds
          };

          const rolesResponse = await $.ajax({
            url: `${API_ROUTES.employees}/${id}/roles`,
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(rolesPayload)
          });

          if (!rolesResponse.success) {
            throw new Error(rolesResponse.message || 'Gagal update roles karyawan');
          }
        }

        // Sukses
        $('#editkaryawan').modal('hide');
        this.table.ajax.reload(null, false);
        this.managedRoles = [];
        showAlert('success', 'Berhasil', 'Data karyawan berhasil diperbarui!');

      } catch (error) {
        console.error('Error update karyawan:', error);
        const errorMessage = error.responseJSON?.message || error.message || 'Gagal update data karyawan';
        showAlert('error', 'Gagal', 'Gagal update data: ' + errorMessage);
      } finally {
        // Reset button state
        $('#simpanEditBtn').prop('disabled', false).html('Simpan Perubahan');
      }
    }

    showDetailKaryawan(event) {
      const rowData = this.table.row($(event.currentTarget).closest('tr')).data();
      
      // Set data dasar
      $('#detailId').text(rowData.id_karyawan);
      $('#detailIdBadge').text('ID: ' + rowData.id_karyawan);
      $('#detailNama').text(rowData.nama);
      $('#detailNamaFull').text(rowData.nama);
      $('#detailGender').text(rowData.gender);
      $('#detailGenderFull').text(rowData.gender);
      $('#detailEmail').text(rowData.user.email);
      $('#detailTelp').text(rowData.no_telp);

      // Set status
      const statusMap = {
        'Aktif': 'bg-success',
        'Cuti': 'bg-warning',
        'Non-Aktif': 'bg-danger'
      };
      $('#detailStatusBadge').html(`<span class="badge ${statusMap[rowData.status]}">${rowData.status}</span>`);
      $('#detailStatusFull').text(rowData.status || 'Aktif');

      // Set divisi dan jabatan
      if (rowData.roles?.length > 0) {
        const divisions = [...new Set(rowData.roles.map(role => role.division?.nama_divisi).filter(Boolean))];
        $('#detailDivisi').text(divisions.join(', ') || '-');
        $('#detailJabatan').text(rowData.roles.map(role => role.nama_jabatan).join(', '));
      } else {
        $('#detailDivisi').text('-');
        $('#detailJabatan').text('-');
      }

      $('#detailJoinDate').text(formatDate(rowData.created_at));

      // Set foto
      let photoUrl;
      if (rowData.foto) {
        photoUrl = '/storage/' + rowData.foto;
      } else {
        photoUrl = rowData.gender === 'Wanita' ? 
          DEFAULT_CONFIG.defaultPhoto.female : 
          DEFAULT_CONFIG.defaultPhoto.male;
      }

      // Set WhatsApp
      const whatsappBtn = $('#whatsappBtn');
      if (rowData.no_telp) {
        const phoneNumber = rowData.no_telp.replace(/\D/g, '');
        whatsappBtn.attr('href', `https://wa.me/${phoneNumber}`).show();
      } else {
        whatsappBtn.hide();
      }

      $('#detailPhoto').attr('src', photoUrl);
      $('#detailkaryawan').modal('show');
    }

    hapusKaryawan(event) {
      const id = $(event.currentTarget).data('id');
      const rowData = this.table.row($(event.currentTarget).closest('tr')).data();
      
      Swal.fire({
        title: 'Hapus Karyawan?',
        html: `Apakah yakin ingin menghapus karyawan <strong>${rowData.nama}</strong> (${rowData.id_karyawan})?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true
      }).then(async (result) => {
        if (result.isConfirmed) {
          try {
            await $.ajax({
              url: `${API_ROUTES.employees}/${id}`,
              type: 'DELETE'
            });
            
            this.table.ajax.reload(null, false);
            showAlert('success', 'Berhasil', 'Karyawan berhasil dihapus!');
          } catch (error) {
            const errorMessage = error.responseJSON?.message || error.message;
            showAlert('error', 'Gagal', 'Gagal menghapus karyawan: ' + errorMessage);
          }
        }
      });
    }
  }

  // ==================== INITIALIZATION ====================
  $(document).ready(function() {
    // Set CSRF token untuk semua AJAX request
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });

    new KaryawanManager();
  });
</script>
@endsection