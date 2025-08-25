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
                      <th style="min-width: 200px">Deskripsi</th>
                      <th style="width: 100px" class="text-center">
                        Aksi
                      </th>
                    </tr>
                  </thead>
                  <tbody id="divisiTableBody">
                    <!-- Data akan diisi oleh JavaScript -->
                    <tr>
                      <td data-label="No">1</td>
                      <td data-label="Nama">Marketing</td>
                      <td data-label="Jumlah Karyawan">20</td>
                      <td data-label="Kepala Divisi">Herlambang</td>
                      <td data-label="KPI Lalu">81</td>
                      <td data-label="KPI Ini">85</td>
                      <td data-label="Deskripsi" class="deskripsi-cell">
                        Lorem ipsum dolor sit amet consectetur
                        adipisicing elit. Aspernatur sed minus
                        repellendus doloribus cupiditate tenetur illum,
                        culpa nemo laborum, eligendi praesentium quaerat
                        perspiciatis hic dolorem? Impedit aliquid saepe
                        tenetur ipsum.
                      </td>
                      <td data-label="Aksi" class="text-center">
                        <div class="action-buttons">
                          <button
                            type="button"
                            class="btn btn-outline-primary edit-btn"
                            data-id="1"
                            title="Edit">
                            <i class="icofont-edit"></i>
                          </button>
                          <button
                            type="button"
                            class="btn btn-outline-danger deleterow"
                            data-id="1"
                            title="Hapus">
                            <i class="icofont-ui-delete"></i>
                          </button>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td data-label="No">2</td>
                      <td data-label="Nama">IT</td>
                      <td data-label="Jumlah Karyawan">15</td>
                      <td data-label="Kepala Divisi">Budi Santoso</td>
                      <td data-label="KPI Lalu">78</td>
                      <td data-label="KPI Ini">82</td>
                      <td data-label="Deskripsi" class="deskripsi-cell">
                        Lorem ipsum dolor, sit amet consectetur adipisicing elit. Iure numquam laudantium sequi unde labore amet facere culpa porro nobis natus quidem distinctio, ipsum suscipit voluptatum vitae veniam quisquam aliquid in.
                      </td>
                      <td data-label="Aksi" class="text-center">
                        <div class="action-buttons">
                          <button
                            type="button"
                            class="btn btn-outline-primary edit-btn"
                            data-id="2"
                            title="Edit">
                            <i class="icofont-edit"></i>
                          </button>
                          <button
                            type="button"
                            class="btn btn-outline-danger deleterow"
                            data-id="2"
                            title="Hapus">
                            <i class="icofont-ui-delete"></i>
                          </button>
                        </div>
                      </td>
                    </tr>
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
          <div class="mb-3">
            <label for="deskripsiDivisi" class="form-label">Deskripsi</label>
            <textarea
              class="form-control"
              id="deskripsiDivisi"
              rows="3"
              placeholder="Deskripsi Divisi"></textarea>
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
  $(document).ready(function() {
    // Data contoh divisi
    let divisiData = [{
        id: 1,
        nama: "Marketing",
        jumlahKaryawan: 20,
        kepalaDivisi: "Herlambang",
        kpiBulanLalu: 81,
        kpiBulanIni: 85,
        deskripsi: "Lorem ipsum dolor sit amet consectetur adipisicing elit. Aspernatur sed minus repellendus doloribus cupiditate tenetur illum, culpa nemo laborum, eligendi praesentium quaerat perspiciatis hic dolorem? Impedit aliquid saepe tenetur ipsum.",
      },
      {
        id: 2,
        nama: "IT",
        jumlahKaryawan: 15,
        kepalaDivisi: "Budi Santoso",
        kpiBulanLalu: 78,
        kpiBulanIni: 82,
        deskripsi: "Lorem ipsum dolor sit amet consectetur adipisicing elit. Aspernatur sed minus repellendus doloribus cupiditate tenetur illum, culpa nemo laborum, eligendi praesentium quaerat perspiciatis hic dolorem? Impedit aliquid saepe tenetur ipsum.",
      },
    ];

    let currentEditId = null;
    let currentDeleteId = null;
    let dataTable = null;

    // Fungsi untuk memuat data ke tabel
    function loadDataToTable() {
      const tbody = document.getElementById("divisiTableBody");
      tbody.innerHTML = "";

      divisiData.forEach((divisi, index) => {
        const row = document.createElement("tr");
        row.innerHTML = `
              <td>${index + 1}</td>
              <td>${divisi.nama}</td>
              <td>${divisi.jumlahKaryawan}</td>
              <td>${divisi.kepalaDivisi}</td>
              <td>${divisi.kpiBulanLalu}</td>
              <td>${divisi.kpiBulanIni}</td>
              <td class="deskripsi-cell">${divisi.deskripsi}</td>
              <td class="aksi-cell">
                <div class="btn-group" role="group">
                  <button type="button" class="btn btn-outline-secondary edit-btn" data-id="${
                    divisi.id
                  }">
                    <i class="icofont-edit text-success"></i>
                  </button>
                  <button type="button" class="btn btn-outline-secondary delete-btn" data-id="${
                    divisi.id
                  }">
                    <i class="icofont-ui-delete text-danger"></i>
                  </button>
                </div>
              </td>
            `;
        tbody.appendChild(row);
      });

      // Inisialisasi ulang DataTable setelah data dimuat
      initDataTable();
    }

    // Fungsi untuk mendapatkan ID berikutnya
    function getNextId() {
      return divisiData.length > 0 ?
        Math.max(...divisiData.map((d) => d.id)) + 1 :
        1;
    }

    // Event untuk tombol Tambah Divisi
    $(".btn-set-task").click(function() {
      currentEditId = null;
      $("#addDivisiModalLabel").text("Tambah Divisi");
      $("#namaDivisi").val("");
      $("#kepalaDivisi").val("");
      $("#deskripsiDivisi").val("");
      $("#addDivisiModal").modal("show");
    });

    // Event untuk tombol Simpan (Tambah/Edit)
    $("#saveDivisiBtn").click(function() {
      const nama = $("#namaDivisi").val().trim();
      const kepala = $("#kepalaDivisi").val().trim();
      const deskripsi = $("#deskripsiDivisi").val().trim();

      if (!nama) {
        alert("Nama Divisi harus diisi!");
        return;
      }

      if (currentEditId) {
        // Edit data existing
        const index = divisiData.findIndex((d) => d.id === currentEditId);
        if (index !== -1) {
          divisiData[index] = {
            ...divisiData[index],
            nama: nama,
            kepalaDivisi: kepala || "-",
            deskripsi: deskripsi || "-",
          };
        }
      } else {
        // Tambah data baru
        const newDivisi = {
          id: getNextId(),
          nama: nama,
          jumlahKaryawan: 0,
          kepalaDivisi: kepala || "-",
          kpiBulanLalu: 0,
          kpiBulanIni: 0,
          deskripsi: deskripsi || "-",
        };
        divisiData.push(newDivisi);
      }

      loadDataToTable();
      $("#addDivisiModal").modal("hide");
    });

    // Event untuk tombol Edit (menggunakan event delegation)
    $(document).on("click", ".edit-btn", function() {
      const id = $(this).data("id");
      const divisi = divisiData.find((d) => d.id === id);

      if (divisi) {
        currentEditId = id;
        $("#addDivisiModalLabel").text("Edit Divisi");
        $("#namaDivisi").val(divisi.nama);
        $("#kepalaDivisi").val(divisi.kepalaDivisi);
        $("#deskripsiDivisi").val(divisi.deskripsi);
        $("#addDivisiModal").modal("show");
      }
    });

    // Event untuk tombol Hapus (menggunakan event delegation)
    $(document).on("click", ".delete-btn", function() {
      currentDeleteId = $(this).data("id");
      $("#confirmDeleteModal").modal("show");
    });

    // Event untuk tombol Konfirmasi Hapus
    $("#confirmDeleteBtn").click(function() {
      if (currentDeleteId) {
        divisiData = divisiData.filter((d) => d.id !== currentDeleteId);
        loadDataToTable();
        $("#confirmDeleteModal").modal("hide");
        currentDeleteId = null;
      }
    });

    // Fungsi untuk menutup modal dengan tombol Enter pada form
    $("#namaDivisi, #kepalaDivisi, #deskripsiDivisi").on(
      "keydown",
      function(event) {
        if (event.key === "Enter") {
          event.preventDefault();
          $("#saveDivisiBtn").click();
        }
      }
    );

    // Fungsi untuk tombol Cancel/Batal
    $('.btn-secondary[data-bs-dismiss="modal"]').on("click", function() {
      $(this).closest(".modal").modal("hide");
    });

    // Fungsi untuk menutup modal ketika klik di luar area modal
    $(document).on("click", function(event) {
      if ($(event.target).hasClass("modal")) {
        $(".modal").modal("hide");
      }
    });

    // Mencegah modal tertutup saat mengklik di dalam area modal
    $(".modal-content").on("click", function(event) {
      event.stopPropagation();
    });

    // Fungsi untuk menutup modal dengan tombol Escape
    $(document).on("keydown", function(event) {
      if (event.key === "Escape") {
        $(".modal").modal("hide");
      }
    });
    // Muat data awal ke tabel
    loadDataToTable();
  });
</script>
@endsection