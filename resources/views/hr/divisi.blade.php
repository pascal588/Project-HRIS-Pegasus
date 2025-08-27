@extends('template.template')

@section('title', 'Divisi')

@section('content')
<link
  rel="stylesheet"
  href="{{asset('assets/plugin/datatables/responsive.dataTables.min.css')}}" />
<link
  rel="stylesheet"
  href="{{asset('assets/plugin/datatables/dataTables.bootstrap5.min.css')}}" />

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

  /* ==================== */
  /* STYLE UNTUK TABEL */
  /* ==================== */
  .table-responsive {
    border-radius: 0.25rem;
    overflow-x: auto;
    /* scroll horizontal di layar kecil */
  }

  .table th {
    background-color: #f8f9fa;
    font-weight: 600;
    padding: 12px 15px;
    white-space: nowrap;
    /* agar header tidak turun ke bawah */
  }

  .table td {
    padding: 12px 15px;
    vertical-align: middle;
    white-space: nowrap;
    /* biar tabel bisa di-scroll */
  }

  .deskripsi-cell {
    max-width: 300px;
    line-height: 1.4;
    overflow: hidden;
    text-overflow: ellipsis;
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

  .badge-score {
    font-size: 0.9rem;
    padding: 0.35em 0.65em;
    font-weight: 600;
    background-color: rgba(13, 110, 253, 0.1);
    color: #0d6efd;
    border-radius: 50px;
  }

  /* RESPONSIVE */
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
  }

  /* Action buttons */
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
</style>

<div class="body d-flex py-3">
  <div class="body d-flex py-lg-3 py-md-2">
    <div class="container-xxl">
      <div class="row align-items-center">
        <div class="border-0 mb-4">
          <div
            class="card-header py-3 no-bg bg-transparent d-flex align-items-center px-0 justify-content-between border-bottom flex-wrap">
            <h3 class="fw-bold mb-0">Divisi</h3>
            <div class="col-auto d-flex w-sm-100">
              <button
                type="button"
                class="btn btn-dark btn-set-task w-sm-100 w-100 w-md-auto"
                data-bs-toggle="modal"
                data-bs-target="#addDivisiModal">
                <i class="icofont-plus-circle me-2 fs-6"></i>Tambah
                Divisi
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
                <table
                  id="divisiDataTable"
                  class="table table-hover align-middle mb-0"
                  style="width: 100%">
                  <thead>
                    <tr>
                      <th style="width: 30px">No</th>
                      <th style="min-width: 100px">Nama</th>
                      <th style="width: 100px">Jumlah Karyawan</th>
                      <th style="min-width: 130px">Kepala Divisi</th>
                      <th style="width: 90px">KPI Lalu</th>
                      <th style="width: 90px">KPI Kini</th>
                      <th style="width: 100px" class="text-center">
                        Aksi
                      </th>
                    </tr>
                  </thead>
                  <tbody id="divisiTableBody">
                    {{-- isi tabel --}}
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- Row End -->
    </div>
  </div>

  <!-- Modal Tambah/Edit Divisi -->
  <div
    class="modal fade"
    id="addDivisiModal"
    tabindex="-1"
    aria-hidden="true"
    data-bs-backdrop="true">
    <div
      class="modal-dialog modal-dialog-centered modal-md modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title fw-bold" id="addDivisiModalLabel">
            Tambah Divisi
          </h5>
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="namaDivisi" class="form-label">Nama Divisi</label>
            <input
              type="text"
              class="form-control"
              id="namaDivisi"
              placeholder="Nama Divisi"
              required />
          </div>
          <div class="mb-3">
            <label for="kepalaDivisi" class="form-label">Kepala Divisi</label>
            <input
              type="text"
              class="form-control"
              id="kepalaDivisi"
              placeholder="Kepala Divisi" />
          </div>
        </div>
        <div class="modal-footer">
          <button
            type="button"
            class="btn btn-secondary"
            data-bs-dismiss="modal">
            Batal
          </button>
          <button
            type="button"
            class="btn btn-primary"
            id="saveDivisiBtn"
            data-bs-dismiss="modal">
            Simpan
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Konfirmasi Hapus -->
  <div
    class="modal fade"
    id="confirmDeleteModal"
    tabindex="-1"
    aria-hidden="true"
    data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Konfirmasi Hapus</h5>
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Apakah Anda yakin ingin menghapus divisi ini?
        </div>
        <div class="modal-footer">
          <button
            type="button"
            class="btn btn-secondary"
            data-bs-dismiss="modal">
            Batal
          </button>
          <button
            type="button"
            class="btn btn-danger"
            id="confirmDeleteBtn"
            data-bs-dismiss="modal">
            Hapus
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection

@section('script')
<script src="{{asset('assets/bundles/dataTables.bundle.js')}}"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    fetchDivisions();

    let editId = null;
    let deleteId = null;

    // ambil semua divisi
    function fetchDivisions() {
        fetch("/api/divisions")
            .then(res => res.json())
            .then(data => {
                let tbody = document.getElementById("divisiTableBody");
                tbody.innerHTML = "";
                data.forEach((divisi, index) => {
                    tbody.innerHTML += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${divisi.nama_divisi}</td>
                            <td>${divisi.jumlah_karyawan}</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-warning editBtn" 
                                    data-id="${divisi.id_divisi}" 
                                    data-nama="${divisi.nama_divisi}" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#addDivisiModal">
                                    Edit
                                </button>
                                <button class="btn btn-sm btn-danger deleteBtn" 
                                    data-id="${divisi.id_divisi}" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#confirmDeleteModal">
                                    Hapus
                                </button>
                            </td>
                        </tr>`;
                });
            });
    }

    // RESET modal saat klik tombol Tambah
    document.querySelector("[data-bs-target='#addDivisiModal']").addEventListener("click", function () {
        editId = null;
        document.getElementById("namaDivisi").value = "";
        document.getElementById("kepalaDivisi").value = "";
        document.getElementById("addDivisiModalLabel").innerText = "Tambah Divisi";
    });

    // klik simpan (tambah/edit)
    document.getElementById("saveDivisiBtn").addEventListener("click", function () {
        let nama = document.getElementById("namaDivisi").value;

        if(!nama) return alert("Nama divisi wajib diisi!");

        if(editId){ // mode edit
            fetch(`/api/divisions/${editId}`, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                },
                body: JSON.stringify({ nama_divisi: nama }),
            })
            .then(res => res.json())
            .then(() => {
                fetchDivisions();
                editId = null;
                document.getElementById("namaDivisi").value = "";
                document.getElementById("kepalaDivisi").value = "";
                document.getElementById("addDivisiModalLabel").innerText = "Tambah Divisi";
            });
        } else { // mode tambah
            fetch("/api/divisions", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                },
                body: JSON.stringify({ nama_divisi: nama }),
            })
            .then(res => res.json())
            .then(() => {
                fetchDivisions();
                document.getElementById("namaDivisi").value = "";
                document.getElementById("kepalaDivisi").value = "";
            });
        }
    });

    // klik edit
    document.addEventListener("click", function(e){
        if(e.target.classList.contains("editBtn")){
            editId = e.target.dataset.id;
            document.getElementById("namaDivisi").value = e.target.dataset.nama;
            document.getElementById("addDivisiModalLabel").innerText = "Edit Divisi";
        }
    });

    // klik hapus
    document.addEventListener("click", function(e){
        if(e.target.classList.contains("deleteBtn")){
            deleteId = e.target.dataset.id;
        }
    });

    document.getElementById("confirmDeleteBtn").addEventListener("click", function(){
        if(deleteId){
            fetch(`/api/divisions/${deleteId}`, {
                method: "DELETE",
                headers: { "Accept": "application/json" }
            }).then(() => {
                fetchDivisions();
                deleteId = null;
            });
        }
    });

});
</script>

@endsection