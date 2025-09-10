@extends('template.template')

@section('title', 'Divisi')

@section('content')
<link rel="stylesheet" href="{{asset('assets/plugin/datatables/responsive.dataTables.min.css')}}" />
<link rel="stylesheet" href="{{asset('assets/plugin/datatables/dataTables.bootstrap5.min.css')}}" />

<style>
  .modal-body .form-group {
    margin-bottom: 1rem;
  }

  .modal-body label {
    font-weight: 500;
  }

  @media (max-width: 576px) {
    .modal-dialog {
      max-width: 95%;
      margin: 0.5rem auto;
    }
  }

  .table-responsive {
    border-radius: 0.25rem;
    overflow-x: auto;
  }

  .table th {
    background-color: #f8f9fa;
    font-weight: 600;
    padding: 12px 15px;
    white-space: nowrap;
  }

  .table td {
    padding: 12px 15px;
    vertical-align: middle;
    white-space: nowrap;
  }

  .table-hover tbody tr:hover {
    background-color: rgba(13, 110, 253, 0.05);
  }

  .btn-group-sm>.btn {
    padding: 0.25rem 0.5rem;
  }

  .table thead th {
    position: sticky;
    top: 0;
    background-color: #f8f9fa;
    z-index: 10;
  }

  .action-buttons {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    justify-content: center;
  }

  .action-buttons .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
  }

  .card {
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
  }
  
  #loadingSpinner {
    display: none;
    text-align: center;
    padding: 20px;
  }
  
  .current-head {
    background-color: #d4edda !important;
  }
  
  .head-badge {
    background-color: #28a745;
    color: white;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 0.75rem;
    margin-left: 8px;
  }
</style>

<div class="body d-flex py-3">
  <div class="body d-flex py-lg-3 py-md-2">
    <div class="container-xxl">
      <div class="row align-items-center">
        <div class="border-0 mb-4">
          <div class="card-header py-3 no-bg bg-transparent d-flex align-items-center px-0 justify-content-between border-bottom flex-wrap">
            <h3 class="fw-bold mb-0">Divisi</h3>
            <div class="col-auto d-flex w-sm-100">
              <button type="button" class="btn btn-dark btn-set-task w-sm-100 w-100 w-md-auto" id="addDivisiBtn">
                <i class="icofont-plus-circle me-2 fs-6"></i>Tambah Divisi
              </button>
            </div>
          </div>
        </div>
      </div>
      <!-- Row end  -->
      <div class="row clearfix g-3">
        <div class="col-sm-12">
          <div class="card mb-3">
            <div class="card-body">
              <div class="table-responsive">
                <table id="divisiDataTable" class="table table-hover align-middle mb-0" style="width: 100%">
                  <thead>
                    <tr>
                      <th>No</th>
                      <th>ID divisi</th>
                      <th>Nama</th>
                      <th>Jumlah Karyawan</th>
                      <th>Kepala Divisi</th>
                      <th>KPI Lalu</th>
                      <th>KPI Kini</th>
                      <th class="text-center">Aksi</th>
                    </tr>
                  </thead>
                  <tbody id="divisiTableBody">
                    <!-- Data akan diisi oleh DataTables -->
                  </tbody>
                </table>
              </div>
              <div id="loadingSpinner">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Memuat data divisi...</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- Row End -->
    </div>
  </div>

  <!-- Modal Tambah/Edit Divisi -->
  <div class="modal fade" id="addDivisiModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered modal-md modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title fw-bold" id="addDivisiModalLabel">Tambah Divisi</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="idDivisiInput" class="form-label">ID Divisi</label>
            <input type="text" class="form-control" id="idDivisiInput" placeholder="ID Divisi" required />
          </div>
          <div class="mb-3">
            <label for="namaDivisi" class="form-label">Nama Divisi</label>
            <input type="text" class="form-control" id="namaDivisi" placeholder="Nama Divisi" required />
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-primary" id="saveDivisiBtn">Simpan</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Kelola Kepala Divisi -->
  <div class="modal fade" id="manageHeadModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title fw-bold" id="manageHeadModalLabel">Kelola Kepala Divisi</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <h6>Divisi: <span id="currentDivisionName"></span></h6>
            <p class="text-muted">Pilih karyawan yang akan menjadi kepala divisi</p>
          </div>
          <div class="table-responsive">
            <table class="table table-hover" id="employeesTable">
              <thead>
                <tr>
                  <th>Nama Karyawan</th>
                  <th>Jabatan</th>
                  <th>Status</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody id="employeesTableBody">
                <!-- Data karyawan akan diisi oleh JavaScript -->
              </tbody>
            </table>
          </div>
          <div id="employeesLoading" class="text-center py-3">
            <div class="spinner-border spinner-border-sm" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
            <span class="ms-2">Memuat data karyawan...</span>
          </div>
          <div id="noEmployees" class="text-center py-3" style="display: none;">
            <p>Tidak ada karyawan dalam divisi ini.</p>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Konfirmasi Hapus -->
  <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Konfirmasi Hapus</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Apakah Anda yakin ingin menghapus divisi ini?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Hapus</button>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection

@section('script')
<script src="{{asset('assets/bundles/dataTables.bundle.js')}}"></script>
<script>
  document.addEventListener("DOMContentLoaded", function() {
    let editId = null;
    let deleteId = null;
    let currentDivisionId = null;

    // Tampilkan loading spinner
    document.getElementById('loadingSpinner').style.display = 'block';
    
    // Inisialisasi DataTable
    let table = $('#divisiDataTable').DataTable({
      responsive: true,
      },
      ajax: {
        url: "/api/divisions",
        dataSrc: function(json) {
          // Sembunyikan loading spinner setelah data dimuat
          document.getElementById('loadingSpinner').style.display = 'none';
          
          if (json.success) {
            return json.data;
          } else {
            alert('Gagal memuat data: ' + (json.message || 'Unknown error'));
            return [];
          }
        },
        error: function(xhr, error, thrown) {
          document.getElementById('loadingSpinner').style.display = 'none';
          console.error('API Error:', error, thrown);
          alert('Gagal memuat data divisi. Silakan coba lagi.');
        }
      },
      columns: [
        { data: null, render: (data, type, row, meta) => meta.row + 1 },
        { data: "id_divisi" },
        { data: "nama_divisi" },
        { data: "jumlah_karyawan", defaultContent: "0" },
        { 
          data: "kepala_divisi", 
          render: function(data, type, row) {
            return data && data !== '-' ? `${data} <span class="head-badge">Kepala</span>` : '-';
          }
        },
        { data: null, defaultContent: "-" }, // KPI Lalu
        { data: null, defaultContent: "-" }, // KPI Kini
        {
          data: null,
          className: "text-center",
          render: function(data, type, row) {
            return `
              <div class="action-buttons">
                <button class="btn btn-outline-info btn-edit" data-id="${data.id_divisi}" data-nama="${data.nama_divisi}">
                  <i class="icofont-edit"></i>
                </button>
                <button class="btn btn-outline-success btn-manage-head" data-id="${data.id_divisi}" data-nama="${data.nama_divisi}">
                  <i class="icofont-users"></i>
                </button>
                <button class="btn btn-outline-danger btn-delete" data-id="${data.id_divisi}">
                  <i class="icofont-ui-delete"></i>
                </button>
              </div>
            `;
          }
        }
      ],
      columnDefs: [{
        targets: -1,
        orderable: false,
        searchable: false
      }]
    });

    // Event listener untuk tombol tambah
    document.getElementById("addDivisiBtn").addEventListener("click", function() {
      editId = null;
      document.getElementById("addDivisiModalLabel").innerText = "Tambah Divisi";
      document.getElementById("idDivisiInput").value = "";
      document.getElementById("namaDivisi").value = "";
      
      new bootstrap.Modal(document.getElementById('addDivisiModal')).show();
    });

    // Event delegation untuk tombol edit
    document.addEventListener("click", function(e) {
      if (e.target.closest(".btn-edit")) {
        const button = e.target.closest(".btn-edit");
        editId = button.getAttribute("data-id");
        const divisionName = button.getAttribute("data-nama");
        
        document.getElementById("addDivisiModalLabel").innerText = "Edit Divisi";
        document.getElementById("idDivisiInput").value = editId;
        document.getElementById("namaDivisi").value = divisionName;
        
        new bootstrap.Modal(document.getElementById('addDivisiModal')).show();
      }
      
      // Tombol kelola kepala divisi
      if (e.target.closest(".btn-manage-head")) {
        const button = e.target.closest(".btn-manage-head");
        currentDivisionId = button.getAttribute("data-id");
        const divisionName = button.getAttribute("data-nama");
        
        document.getElementById("manageHeadModalLabel").innerText = `Kelola Kepala Divisi - ${divisionName}`;
        document.getElementById("currentDivisionName").textContent = divisionName;
        
        // Load data karyawan divisi
        loadDivisionEmployees(currentDivisionId);
        
        new bootstrap.Modal(document.getElementById('manageHeadModal')).show();
      }
    });

    // Fungsi untuk memuat karyawan divisi
    function loadDivisionEmployees(divisionId) {
      document.getElementById('employeesLoading').style.display = 'block';
      document.getElementById('noEmployees').style.display = 'none';
      document.getElementById('employeesTableBody').innerHTML = '';
      
      // Coba endpoint utama dulu, jika gagal coba endpoint fallback
      const apiUrls = [
        `/api/divisions/${divisionId}/employees`,
        `/debug/division/${divisionId}/employees`
      ];
      
      let currentApiIndex = 0;
      
      const tryFetch = () => {
        if (currentApiIndex >= apiUrls.length) {
          // Semua URL sudah dicoba dan gagal
          document.getElementById('employeesLoading').style.display = 'none';
          document.getElementById('noEmployees').style.display = 'block';
          document.getElementById('noEmployees').innerHTML = `
            <p>Gagal memuat data karyawan. Silakan coba lagi nanti.</p>
            <button class="btn btn-sm btn-primary mt-2" onclick="loadDivisionEmployees(${divisionId})">
              Coba Lagi
            </button>
          `;
          return;
        }
        
        const url = apiUrls[currentApiIndex];
        currentApiIndex++;
        
        fetch(url)
          .then(response => {
            if (!response.ok) {
              throw new Error('Network response was not ok: ' + response.statusText);
            }
            return response.json();
          })
          .then(data => {
            document.getElementById('employeesLoading').style.display = 'none';
            
            if (data.success && data.data && data.data.length > 0) {
              const employeesTableBody = document.getElementById('employeesTableBody');
              employeesTableBody.innerHTML = '';
              
              // Untuk sementara, kita asumsikan kepala divisi adalah yang memiliki role "Kepala-divisi"
              data.data.forEach(employee => {
                const isHead = employee.roles && employee.roles.some(role => role.nama_jabatan === 'Kepala-divisi');
                const row = document.createElement('tr');
                if (isHead) row.classList.add('current-head');
                
                // Gabungkan semua role menjadi string
                const roleNames = employee.roles ? 
                  employee.roles.map(role => role.nama_jabatan).join(', ') : 
                  'Tidak ada role';
                
                row.innerHTML = `
                  <td>${employee.nama}</td>
                  <td>${roleNames}</td>
                  <td>${isHead ? '<span class="badge bg-success">Kepala Divisi</span>' : '-'}</td>
                  <td>
                    ${!isHead ? 
                      `<button class="btn btn-sm btn-primary set-head-btn" data-employee-id="${employee.id_karyawan}">
                        Jadikan Kepala
                      </button>` : 
                      `<button class="btn btn-sm btn-danger remove-head-btn" data-employee-id="${employee.id_karyawan}">
                        Hapus Status
                      </button>`
                    }
                  </td>
                `;
                
                employeesTableBody.appendChild(row);
              });
              
              // Tambahkan event listeners untuk tombol aksi
              document.querySelectorAll('.set-head-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                  const employeeId = this.getAttribute('data-employee-id');
                  setDivisionHead(divisionId, employeeId, true);
                });
              });
              
              document.querySelectorAll('.remove-head-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                  const employeeId = this.getAttribute('data-employee-id');
                  setDivisionHead(divisionId, employeeId, false);
                });
              });
            } else {
              document.getElementById('noEmployees').style.display = 'block';
              if (data.message) {
                document.getElementById('noEmployees').innerHTML = `
                  <p>${data.message}</p>
                  <button class="btn btn-sm btn-primary mt-2" onclick="loadDivisionEmployees(${divisionId})">
                    Coba Lagi
                  </button>
                `;
              }
            }
          })
          .catch(error => {
            console.error("Error dengan URL:", url, error);
            // Coba URL berikutnya
            tryFetch();
          });
      };
      
      // Mulai proses fetch
      tryFetch();
    }
    
    // Fungsi untuk mengatur kepala divisi
    function setDivisionHead(divisionId, employeeId, isHead) {
      const apiUrls = [
        `/api/divisions/${divisionId}/head`,
        `/debug/division/${divisionId}/head`
      ];
      
      let currentApiIndex = 0;
      
      const tryFetch = () => {
        if (currentApiIndex >= apiUrls.length) {
          alert('Semua endpoint gagal. Silakan coba lagi nanti.');
          return;
        }
        
        const url = apiUrls[currentApiIndex];
        currentApiIndex++;
        
        fetch(url, {
          method: "PUT",
          headers: {
            "Content-Type": "application/json",
            "Accept": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
          },
          body: JSON.stringify({
            employee_id: employeeId,
            is_head: isHead
          })
        })
        .then(response => {
          if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.statusText);
          }
          return response.json();
        })
        .then(data => {
          if (data.success) {
            alert(data.message || 'Status kepala divisi berhasil diperbarui');
            // Reload data karyawan
            loadDivisionEmployees(divisionId);
            // Reload tabel divisi
            table.ajax.reload(null, false);
          } else {
            alert(data.message || 'Gagal memperbarui status kepala divisi');
          }
        })
        .catch(error => {
          console.error("Error dengan URL:", url, error);
          // Coba URL berikutnya
          tryFetch();
        });
      };
      
      // Mulai proses fetch
      tryFetch();
    }

    // Event delegation untuk tombol hapus
    document.addEventListener("click", function(e) {
      if (e.target.closest(".btn-delete")) {
        const button = e.target.closest(".btn-delete");
        deleteId = button.getAttribute("data-id");
        new bootstrap.Modal(document.getElementById('confirmDeleteModal')).show();
      }
    });

    // Simpan data divisi
    document.getElementById("saveDivisiBtn").addEventListener("click", function() {
      const idDivisi = document.getElementById("idDivisiInput").value;
      const namaDivisi = document.getElementById("namaDivisi").value;

      if (!idDivisi || !namaDivisi) {
        alert("ID dan Nama divisi wajib diisi!");
        return;
      }

      const payload = {
        id_divisi: idDivisi,
        nama_divisi: namaDivisi
      };

      const method = editId ? "PUT" : "POST";
      const url = editId ? `/api/divisions/${editId}` : "/api/divisions";

      fetch(url, {
        method: method,
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json",
          "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify(payload)
      })
      .then(response => {
        if (!response.ok) {
          throw new Error('Network response was not ok: ' + response.statusText);
        }
        return response.json();
      })
      .then(data => {
        if (data.success || data.message) {
          table.ajax.reload();
          bootstrap.Modal.getInstance(document.getElementById('addDivisiModal')).hide();
          alert(editId ? "Divisi berhasil diperbarui" : "Divisi berhasil ditambahkan");
        } else {
          alert("Terjadi kesalahan saat menyimpan data");
        }
      })
      .catch(error => {
        console.error("Error:", error);
        alert("Terjadi kesalahan saat menyimpan data: " + error.message);
      });
    });

    // Konfirmasi hapus divisi
    document.getElementById("confirmDeleteBtn").addEventListener("click", function() {
      if (deleteId) {
        fetch(`/api/divisions/${deleteId}`, {
          method: "DELETE",
          headers: {
            "Accept": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
          }
        })
        .then(response => {
          if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.statusText);
          }
          return response.json();
        })
        .then(data => {
          if (data.message) {
            table.ajax.reload();
            alert("Divisi berhasil dihapus");
          } else {
            alert("Terjadi kesalahan saat menghapus data");
          }
        })
        .catch(error => {
          console.error("Error:", error);
          alert("Terjadi kesalahan saat menghapus data: " + error.message);
        });
        
        bootstrap.Modal.getInstance(document.getElementById('confirmDeleteModal')).hide();
      }
    });
  });
</script>
@endsection