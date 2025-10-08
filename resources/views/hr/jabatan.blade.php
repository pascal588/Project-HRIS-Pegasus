@extends('template.template')

@section('title', 'Manajemen Jabatan')

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

  .loading-spinner {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid #f3f3f3;
    border-top: 3px solid #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
  }

  @keyframes spin {
    0% {
      transform: rotate(0deg);
    }

    100% {
      transform: rotate(360deg);
    }
  }

  .pagination-info {
    font-size: 0.875rem;
    color: #6c757d;
  }

  @media (max-width: 576px) {
    .action-buttons {
      flex-direction: column;
    }

    .action-buttons .btn {
      width: 100%;
      margin-bottom: 0.25rem;
    }
  }
</style>

<div class="body d-flex py-3">
  <div class="body d-flex py-lg-3 py-md-2">
    <div class="container-xxl">
      <!-- Page Header -->
      <div class="row align-items-center">
        <div class="border-0 mb-4">
          <div class="card-header py-3 no-bg bg-transparent d-flex align-items-center px-0 justify-content-between border-bottom flex-wrap">
            <h3 class="fw-bold mb-0">Jabatan</h3>
            <div class="col-auto d-flex w-sm-100 gap-2">
              <button type="button" class="btn btn-dark btn-set-task w-sm-100 w-100 w-md-auto" id="addJabatanBtn">
                <i class="icofont-plus-circle me-2 fs-6"></i>Tambah Jabatan
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Filter Section -->
      <div class="row clearfix g-3">
        <div class="col-sm-12">
          <div class="card mb-3">
            <div class="card-body">
              <div class="row g-3">
                <div class="col-md-4">
                  <label class="form-label">Filter Divisi</label>
                  <select class="form-select" id="filterDivisi">
                    <option value="">Semua Divisi</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Cari Jabatan</label>
                  <input type="text" class="form-control" id="searchJabatan" placeholder="Masukkan nama jabatan...">
                </div>
                <div class="col-md-4">
                  <label class="form-label">&nbsp;</label>
                  <button class="btn btn-outline-secondary w-100" id="resetFilter">
                    <i class="icofont-refresh me-2"></i>Reset Filter
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Data Table -->
      <div class="row clearfix g-3">
        <div class="col-sm-12">
          <div class="card mb-3">
            <div class="card-body">
              <!-- Loading Indicator -->
              <div id="loadingIndicator" class="text-center py-4" style="display: none;">
                <div class="loading-spinner"></div>
                <p class="mt-2">Memuat data...</p>
              </div>

              <div class="table-responsive" id="tableContainer">
                <table id="jabatanTable" class="table table-hover align-middle mb-0">
                  <thead>
                    <tr>
                      <th>No</th>
                      <th>Nama Jabatan</th>
                      <th>Divisi</th>
                      <th>Jumlah Karyawan</th>
                      <th>Status</th>
                      <th>Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <!-- Data akan diisi via JavaScript -->
                  </tbody>
                </table>
              </div>

              <!-- Pagination Info -->
              <div class="row mt-3">
                <div class="col-md-6">
                  <div id="paginationInfo" class="pagination-info"></div>
                </div>
                <div class="col-md-6">
                  <nav>
                    <ul class="pagination justify-content-end" id="pagination">
                      <!-- Pagination akan diisi via JavaScript -->
                    </ul>
                  </nav>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Tambah/Edit Jabatan -->
<div class="modal fade" id="jabatanModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">Tambah Jabatan Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="jabatanForm">
          <input type="hidden" id="editId">

          <div class="mb-3">
            <label for="nama_jabatan" class="form-label">Nama Jabatan <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="nama_jabatan" name="nama_jabatan"
              placeholder="Contoh: Staff Keuangan" required maxlength="50">
            <div class="invalid-feedback" id="namaError"></div>
          </div>

          <div class="mb-3">
            <label for="division_id" class="form-label">Divisi <span class="text-danger">*</span></label>
            <select class="form-select" id="division_id" name="division_id" required>
              <option value="">Pilih Divisi...</option>
            </select>
            <div class="invalid-feedback" id="divisiError"></div>
          </div>

          <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-select" id="status" name="status">
              <option value="Aktif">Aktif</option>
              <option value="Non-Aktif">Non-Aktif</option>
            </select>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" id="saveJabatanBtn">
          <span class="spinner-border spinner-border-sm d-none" id="saveSpinner"></span>
          Simpan
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Konfirmasi Hapus</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Apakah Anda yakin ingin menghapus jabatan <strong id="deleteJabatanName"></strong>?</p>
        <p class="text-danger">Data yang dihapus tidak dapat dikembalikan!</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
          <span class="spinner-border spinner-border-sm d-none" id="deleteSpinner"></span>
          Hapus
        </button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('script')
<script src="{{asset('assets/bundles/dataTables.bundle.js')}}"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
  class JabatanManager {
    constructor() {
      this.currentPage = 1;
      this.perPage = 10;
      this.totalRecords = 0;
      this.filterDivisi = '';
      this.searchQuery = '';
      this.init();
    }

    init() {
      this.loadDivisiOptions();
      this.loadJabatanData();
      this.bindEvents();
    }

    // Method untuk format kapital per kata
    formatCapitalize(text) {
      if (!text) return '';
      return text.toLowerCase().replace(/\b\w/g, char => char.toUpperCase());
    }

    // Load divisi options untuk dropdown
    async loadDivisiOptions() {
      try {
        console.log('🔄 Loading divisi options...');

        const response = await fetch('/api/divisions');
        const result = await response.json();

        console.log('📦 Divisi API Response:', result);

        if (!response.ok) {
          throw new Error(result.message || `HTTP error! status: ${response.status}`);
        }

        if (result.success && Array.isArray(result.data)) {
          this.populateDivisiSelectors(result.data);
        } else {
          throw new Error('Format response divisi tidak valid');
        }
      } catch (error) {
        console.error('❌ Error loading divisions:', error);
        this.showAlert('error', 'Gagal memuat data divisi: ' + error.message);
      }
    }

    populateDivisiSelectors(divisions) {
      const filterDivisi = $('#filterDivisi');
      const modalDivisi = $('#division_id');

      let filterOptions = '<option value="">Semua Divisi</option>';
      let modalOptions = '<option value="">Pilih Divisi...</option>';

      divisions.forEach(divisi => {
        const divisiId = divisi.id_divisi;
        const divisiNama = divisi.nama_divisi;

        filterOptions += `<option value="${divisiId}">${divisiNama}</option>`;
        modalOptions += `<option value="${divisiId}">${divisiNama}</option>`;
      });

      filterDivisi.html(filterOptions);
      modalDivisi.html(modalOptions);
    }

    // Load data jabatan dari API
    async loadJabatanData() {
      this.showLoading(true);

      const params = new URLSearchParams({
        page: this.currentPage,
        per_page: this.perPage,
        divisi: this.filterDivisi,
        search: this.searchQuery
      });

      try {
        console.log('🔄 Loading jabatan data...', params.toString());

        const response = await fetch(`/api/roles?${params}`);
        const result = await response.json();

        console.log('📦 Jabatan API Response:', result);

        if (!response.ok) {
          throw new Error(result.message || `HTTP error! status: ${response.status}`);
        }

        if (result.success) {
          this.renderTable(result.data);
          this.renderPagination(result.meta);
          this.totalRecords = result.meta?.total || 0;
        } else {
          throw new Error(result.message || 'Format response tidak valid');
        }
      } catch (error) {
        console.error('❌ Error loading jabatan:', error);
        this.showAlert('error', 'Gagal memuat data jabatan: ' + error.message);
      } finally {
        this.showLoading(false);
      }
    }

    // Render table dengan data dari API
    renderTable(jabatanData) {
      const tbody = $('#jabatanTable tbody');
      tbody.empty();

      if (!jabatanData || jabatanData.length === 0) {
        tbody.html(`
        <tr>
          <td colspan="6" class="text-center py-4">
            <i class="icofont-inbox me-2"></i>Tidak ada data jabatan
          </td>
        </tr>
      `);
        return;
      }

      jabatanData.forEach((jabatan, index) => {
        const nomor = (this.currentPage - 1) * this.perPage + index + 1;

        // ✅ SESUAI DENGAN RESPONSE API
        const jabatanId = jabatan.id_jabatan;
        const jabatanNama = jabatan.nama_jabatan;
        const divisiNama = jabatan.division?.nama_divisi || '-';
        const status = jabatan.status || 'Aktif';
        const jumlahKaryawan = jabatan.jumlah_karyawan || 0;

        const statusBadge = status === 'Aktif' ?
          '<span class="badge bg-success">Aktif</span>' :
          '<span class="badge bg-danger">Non-Aktif</span>';

        const row = `
        <tr>
          <td>${nomor}</td>
          <td>${jabatanNama}</td>
          <td>${divisiNama}</td>
          <td>${jumlahKaryawan}</td>
          <td>${statusBadge}</td>
          <td>
            <div class="action-buttons">
              <button class="btn btn-outline-primary btn-sm btn-edit" 
                      data-id="${jabatanId}"
                      data-nama="${jabatanNama}"
                      data-divisi="${jabatan.division_id}"
                      data-status="${status}">
                <i class="icofont-edit"></i>
              </button>
              <button class="btn btn-outline-danger btn-sm btn-delete" 
                      data-id="${jabatanId}"
                      data-nama="${jabatanNama}">
                <i class="icofont-ui-delete"></i>
              </button>
            </div>
          </td>
        </tr>
      `;
        tbody.append(row);
      });
    }

    // Render pagination
    renderPagination(meta) {
      const pagination = $('#pagination');
      const info = $('#paginationInfo');

      if (!meta) {
        pagination.empty();
        info.text(`Menampilkan ${this.totalRecords} data`);
        return;
      }

      const {
        current_page,
        last_page,
        total,
        from,
        to
      } = meta;

      // Pagination info
      info.text(`Menampilkan ${from} sampai ${to} dari ${total} data`);

      // Pagination buttons
      let paginationHtml = '';

      // Previous button
      paginationHtml += `
      <li class="page-item ${current_page === 1 ? 'disabled' : ''}">
        <a class="page-link" href="#" data-page="${current_page - 1}">
          <i class="icofont-rounded-left"></i>
        </a>
      </li>
    `;

      // Page numbers
      for (let i = 1; i <= last_page; i++) {
        if (i === 1 || i === last_page || (i >= current_page - 2 && i <= current_page + 2)) {
          paginationHtml += `
          <li class="page-item ${i === current_page ? 'active' : ''}">
            <a class="page-link" href="#" data-page="${i}">${i}</a>
          </li>
        `;
        } else if (i === current_page - 3 || i === current_page + 3) {
          paginationHtml += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
      }

      // Next button
      paginationHtml += `
      <li class="page-item ${current_page === last_page ? 'disabled' : ''}">
        <a class="page-link" href="#" data-page="${current_page + 1}">
          <i class="icofont-rounded-right"></i>
        </a>
      </li>
    `;

      pagination.html(paginationHtml);
    }

    // Event bindings
    bindEvents() {
      // Add button
      $('#addJabatanBtn').click(() => this.showModal());

      // Save button
      $('#saveJabatanBtn').click(() => this.saveJabatan());

      // Filter events
      $('#filterDivisi').change(() => {
        this.filterDivisi = $('#filterDivisi').val();
        this.currentPage = 1;
        this.loadJabatanData();
      });

      $('#searchJabatan').on('input', this.debounce(() => {
        this.searchQuery = $('#searchJabatan').val();
        this.currentPage = 1;
        this.loadJabatanData();
      }, 500));

      $('#resetFilter').click(() => {
        $('#filterDivisi').val('');
        $('#searchJabatan').val('');
        this.filterDivisi = '';
        this.searchQuery = '';
        this.currentPage = 1;
        this.loadJabatanData();
      });

      // Pagination
      $(document).on('click', '.page-link', (e) => {
        e.preventDefault();
        const page = $(e.target).data('page');
        if (page && page !== this.currentPage) {
          this.currentPage = page;
          this.loadJabatanData();
        }
      });

      // Edit and Delete buttons (event delegation)
      $(document).on('click', '.btn-edit', (e) => {
        const button = $(e.currentTarget);
        this.showModal(
          button.data('id'),
          button.data('nama'),
          button.data('divisi'),
          button.data('status')
        );
      });

      $(document).on('click', '.btn-delete', (e) => {
        const button = $(e.currentTarget);
        this.showDeleteModal(button.data('id'), button.data('nama'));
      });

      // Form submission
      $('#jabatanForm').on('submit', (e) => {
        e.preventDefault();
        this.saveJabatan();
      });
    }

    // Show modal untuk tambah/edit
    showModal(id = null, nama = '', divisiId = '', status = 'Aktif') {
      $('#editId').val(id || '');
      $('#nama_jabatan').val(nama);
      $('#division_id').val(divisiId);
      $('#status').val(status || 'Aktif');

      // Setup auto-capitalize untuk input nama jabatan
      this.setupAutoCapitalize();

      $('#modalTitle').text(id ? 'Edit Jabatan' : 'Tambah Jabatan Baru');
      this.clearValidation();

      const modal = new bootstrap.Modal(document.getElementById('jabatanModal'));
      modal.show();
    }

    // Setup auto-capitalize untuk input field
    setupAutoCapitalize() {
      // Hapus event listener sebelumnya jika ada
      $('#nama_jabatan').off('input.capitalize');

      // Tambahkan event listener untuk auto-capitalize
      $('#nama_jabatan').on('input.capitalize', (e) => {
        const input = e.target;
        const cursorPosition = input.selectionStart;
        const originalValue = input.value;

        // Format value dengan kapital setiap kata
        const formattedValue = this.formatCapitalize(originalValue);

        // Update value hanya jika berbeda
        if (formattedValue !== originalValue) {
          input.value = formattedValue;

          // Kembalikan cursor ke posisi semula
          const newPosition = cursorPosition + (formattedValue.length - originalValue.length);
          input.setSelectionRange(newPosition, newPosition);
        }
      });
    }

    // Save jabatan (create/update)
    async saveJabatan() {
      // Format nama jabatan sebelum dikirim
      const rawNama = $('#nama_jabatan').val().trim();
      const formattedNama = this.formatCapitalize(rawNama);

      // Update value di form dengan yang sudah diformat
      if (rawNama !== formattedNama) {
        $('#nama_jabatan').val(formattedNama);
      }

      const formData = {
        nama_jabatan: formattedNama,
        division_id: $('#division_id').val(),
        status: $('#status').val() || 'Aktif'
      };

      const editId = $('#editId').val();

      // Client-side validation
      if (!this.validateForm(formData)) {
        return;
      }

      this.toggleSaveButton(true);

      try {
        const url = editId ? `/api/roles/${editId}` : '/api/roles';
        const method = editId ? 'PUT' : 'POST';

        console.log('💾 Saving jabatan:', {
          url,
          method,
          formData
        });

        const response = await fetch(url, {
          method: method,
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
          },
          body: JSON.stringify(formData)
        });

        const result = await response.json();
        console.log('💾 Save Response:', result);

        if (!response.ok) {
          throw new Error(result.message || `HTTP error! status: ${response.status}`);
        }

        if (result.success) {
          this.showAlert('success', result.message);
          $('#jabatanModal').modal('hide');
          this.loadJabatanData();
        } else {
          throw new Error(result.message || 'Terjadi kesalahan tidak diketahui');
        }
      } catch (error) {
        console.error('❌ Error saving jabatan:', error);
        this.showAlert('error', 'Gagal menyimpan jabatan: ' + error.message);
      } finally {
        this.toggleSaveButton(false);
      }
    }

    // Validasi form
    validateForm(data) {
      let isValid = true;
      this.clearValidation();

      if (!data.nama_jabatan) {
        this.showFieldError('nama_jabatan', 'Nama jabatan wajib diisi');
        isValid = false;
      } else if (data.nama_jabatan.length > 50) {
        this.showFieldError('nama_jabatan', 'Nama jabatan maksimal 50 karakter');
        isValid = false;
      }

      if (!data.division_id) {
        this.showFieldError('division_id', 'Divisi wajib dipilih');
        isValid = false;
      }

      return isValid;
    }

    showFieldError(field, message) {
      $(`#${field}`).addClass('is-invalid');
      $(`#${field}Error`).text(message);
    }

    clearValidation() {
      $('.is-invalid').removeClass('is-invalid');
      $('.invalid-feedback').text('');
    }

    // Show delete confirmation modal
    showDeleteModal(id, nama) {
      $('#deleteJabatanName').text(nama);
      $('#confirmDeleteBtn').data('id', id);

      const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
      modal.show();
    }

    // Delete jabatan
    async deleteJabatan() {
      const id = $('#confirmDeleteBtn').data('id');

      this.toggleDeleteButton(true);

      try {
        console.log('🗑️ Deleting jabatan:', id);

        const response = await fetch(`/api/roles/${id}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
          }
        });

        const result = await response.json();
        console.log('🗑️ Delete Response:', result);

        if (!response.ok) {
          throw new Error(result.message || `HTTP error! status: ${response.status}`);
        }

        if (result.success) {
          this.showAlert('success', result.message);
          $('#deleteModal').modal('hide');
          this.loadJabatanData();
        } else {
          throw new Error(result.message || 'Gagal menghapus jabatan');
        }
      } catch (error) {
        console.error('❌ Error deleting jabatan:', error);
        this.showAlert('error', 'Gagal menghapus jabatan: ' + error.message);
      } finally {
        this.toggleDeleteButton(false);
      }
    }

    // Utility methods
    showLoading(show) {
      if (show) {
        $('#loadingIndicator').show();
        $('#tableContainer').hide();
      } else {
        $('#loadingIndicator').hide();
        $('#tableContainer').show();
      }
    }

    toggleSaveButton(loading) {
      const button = $('#saveJabatanBtn');
      const spinner = $('#saveSpinner');

      if (loading) {
        spinner.removeClass('d-none');
        button.prop('disabled', true);
      } else {
        spinner.addClass('d-none');
        button.prop('disabled', false);
      }
    }

    toggleDeleteButton(loading) {
      const button = $('#confirmDeleteBtn');
      const spinner = $('#deleteSpinner');

      if (loading) {
        spinner.removeClass('d-none');
        button.prop('disabled', true);
      } else {
        spinner.addClass('d-none');
        button.prop('disabled', false);
      }
    }

    showAlert(icon, message) {
      Swal.fire({
        icon: icon,
        title: icon === 'success' ? 'Sukses!' : 'Error!',
        text: message,
        timer: 3000,
        showConfirmButton: false
      });
    }

    debounce(func, wait) {
      let timeout;
      return function executedFunction(...args) {
        const later = () => {
          clearTimeout(timeout);
          func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
      };
    }
  }

  // Initialize when document is ready
  $(document).ready(function() {
    const jabatanManager = new JabatanManager();

    // Bind delete confirmation
    $('#confirmDeleteBtn').click(function() {
      jabatanManager.deleteJabatan();
    });
  });
</script>
@endsection