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
    /* dipindah ke sini, biar gak dobel di bawah */
  }

  .card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #eaeaea;
    padding: 1rem 1.5rem;
  }

  .card .card-body {
    padding: 1rem;
    /* default, nanti diubah di media query */
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

  /* ---------------------- TABLE STYLING ---------------------- */

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

  /* Wrapper agar tabel bisa discroll di layar kecil */
  .table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }

  #myProjectTable {
    width: 100%;
    border-collapse: collapse;
    white-space: nowrap;
    /* cegah teks pecah ke bawah */
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

  /* ---------------------- RESPONSIVE ---------------------- */
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
      /* card body lebih kecil */
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
            <div class="col-auto d-flex w-sm-100">
              <button
                type="button"
                class="btn btn-dark btn-set-task w-sm-100 w-100 w-md-auto"
                data-bs-toggle="modal"
                data-bs-target="#addkaryawan">
                <i class="icofont-plus-circle me-2 fs-6"></i>Tambah
                Karyawan
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
                <table
                  id="myProjectTable"
                  class="table table-hover align-middle mb-0"
                  style="width: 100%">
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
                  <tbody>
                    <tr>
                      <td data-label="No">01</td>
                      <td data-label="ID">001</td>
                      <td data-label="Nama">Bambang</td>
                      <td data-label="Divisi">Pemasaran</td>
                      <td data-label="Jabatan">Karyawan</td>
                      <td data-label="Status">
                        <span class="badge bg-success">Aktif</span>
                      </td>
                      <td data-label="Email">bambang@example.com</td>
                      <td data-label="Tanggal Masuk">Agustus 2025</td>
                      <td data-label="Aksi">
                        <div class="action-buttons">
                          <button
                            class="btn btn-outline-info btn-detail"
                            data-bs-toggle="modal"
                            data-bs-target="#detailkaryawan"
                            data-id="001"
                            title="Detail">
                            <i class="icofont-eye-alt"></i>
                          </button>
                          <button
                            class="btn btn-outline-primary edit-btn"
                            data-bs-toggle="modal"
                            data-bs-target="#editkaryawan"
                            data-id="001"
                            title="Edit">
                            <i class="icofont-edit"></i>
                          </button>
                          <button
                            class="btn btn-outline-danger deleterow"
                            data-id="001"
                            title="Hapus">
                            <i class="icofont-ui-delete"></i>
                          </button>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td data-label="No">02</td>
                      <td data-label="ID">002</td>
                      <td data-label="Nama">Siti</td>
                      <td data-label="Divisi">Keuangan</td>
                      <td data-label="Jabatan">Kepala Divisi</td>
                      <td data-label="Status">
                        <span class="badge bg-success">Aktif</span>
                      </td>
                      <td data-label="Email">siti@example.com</td>
                      <td data-label="Tanggal Masuk">Agustus 2025</td>
                      <td data-label="Aksi">
                        <div class="action-buttons">
                          <button
                            class="btn btn-outline-info btn-detail"
                            data-bs-toggle="modal"
                            data-bs-target="#detailkaryawan"
                            data-id="002"
                            title="Detail">
                            <i class="icofont-eye-alt"></i>
                          </button>
                          <button
                            class="btn btn-outline-primary edit-btn"
                            data-bs-toggle="modal"
                            data-bs-target="#editkaryawan"
                            data-id="002"
                            title="Edit">
                            <i class="icofont-edit"></i>
                          </button>
                          <button
                            class="btn btn-outline-danger deleterow"
                            data-id="002"
                            title="Hapus">
                            <i class="icofont-ui-delete"></i>
                          </button>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td data-label="No">03</td>
                      <td data-label="ID">003</td>
                      <td data-label="Nama">Andi</td>
                      <td data-label="Divisi">Pemasaran</td>
                      <td data-label="Jabatan">Karyawan</td>
                      <td data-label="Status">
                        <span class="badge bg-success">Aktif</span>
                      </td>
                      <td data-label="Email">andi@example.com</td>
                      <td data-label="Tanggal Masuk">Agustus 2025</td>
                      <td data-label="Aksi">
                        <div class="action-buttons">
                          <button
                            class="btn btn-outline-info btn-detail"
                            data-bs-toggle="modal"
                            data-bs-target="#detailkaryawan"
                            data-id="003"
                            title="Detail">
                            <i class="icofont-eye-alt"></i>
                          </button>
                          <button
                            class="btn btn-outline-primary edit-btn"
                            data-bs-toggle="modal"
                            data-bs-target="#editkaryawan"
                            data-id="003"
                            title="Edit">
                            <i class="icofont-edit"></i>
                          </button>
                          <button
                            class="btn btn-outline-danger deleterow"
                            data-id="003"
                            title="Hapus">
                            <i class="icofont-ui-delete"></i>
                          </button>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td data-label="No">04</td>
                      <td data-label="ID">004</td>
                      <td data-label="Nama">Dewi</td>
                      <td data-label="Divisi">Keuangan</td>
                      <td data-label="Jabatan">Karyawan</td>
                      <td data-label="Status">
                        <span class="badge bg-success">Aktif</span>
                      </td>
                      <td data-label="Email">dewi@example.com</td>
                      <td data-label="Tanggal Masuk">Agustus 2024</td>
                      <td data-label="Aksi">
                        <div class="action-buttons">
                          <button
                            class="btn btn-outline-info btn-detail"
                            data-bs-toggle="modal"
                            data-bs-target="#detailkaryawan"
                            data-id="004"
                            title="Detail">
                            <i class="icofont-eye-alt"></i>
                          </button>
                          <button
                            class="btn btn-outline-primary edit-btn"
                            data-bs-toggle="modal"
                            data-bs-target="#editkaryawan"
                            data-id="004"
                            title="Edit">
                            <i class="icofont-edit"></i>
                          </button>
                          <button
                            class="btn btn-outline-danger deleterow"
                            data-id="004"
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
    </div>
  </div>
</div>
@endsection

<!-- Modal Tambah Karyawan -->
<div
  class="modal fade"
  id="addkaryawan"
  tabindex="-1"
  aria-labelledby="addkaryawanLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addkaryawanLabel">
          Tambah Karyawan Baru
        </h5>
        <button
          type="button"
          class="btn-close"
          data-bs-dismiss="modal"
          aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="formTambahKaryawan">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="namaKaryawan" class="form-label">Nama Lengkap</label>
              <input
                type="text"
                class="form-control"
                id="namaKaryawan"
                required />
            </div>
            <div class="col-md-6 mb-3">
              <label for="divisiKaryawan" class="form-label">Divisi</label>
              <select class="form-select" id="divisiKaryawan" required>
                <option value="" selected disabled>Pilih Divisi</option>
                <option value="Pemasaran">Pemasaran</option>
                <option value="Keuangan">Keuangan</option>
                <option value="IT">IT</option>
                <option value="HR">HR</option>
                <option value="Operasional">Operasional</option>
              </select>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="jabatanKaryawan" class="form-label">Jabatan</label>
              <input
                type="text"
                class="form-control"
                id="jabatanKaryawan"
                required />
            </div>
            <div class="col-md-6 mb-3">
              <label for="genderKaryawan" class="form-label">Jenis Kelamin</label>
              <select class="form-select" id="genderKaryawan" required>
                <option value="" selected disabled>
                  Pilih Jenis Kelamin
                </option>
                <option value="Laki-laki">Laki-laki</option>
                <option value="Perempuan">Perempuan</option>
              </select>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="telpKaryawan" class="form-label">No. Telepon</label>
              <input
                type="tel"
                class="form-control"
                id="telpKaryawan"
                required />
            </div>
            <div class="col-md-6 mb-3">
              <label for="emailKaryawan" class="form-label">Email</label>
              <input
                type="email"
                class="form-control"
                id="emailKaryawan"
                required />
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="passwordKaryawan" class="form-label">Password</label>
              <input
                type="password"
                class="form-control"
                id="passwordKaryawan"
                required />
            </div>
            <div class="col-md-6 mb-3">
              <label for="confirmPasswordKaryawan" class="form-label">Konfirmasi Password</label>
              <input
                type="password"
                class="form-control"
                id="confirmPasswordKaryawan"
                required />
            </div>
          </div>

          <div class="mb-3">
            <label for="alamatKaryawan" class="form-label">Alamat</label>
            <textarea
              class="form-control"
              id="alamatKaryawan"
              rows="3"></textarea>
          </div>
        </form>
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
          id="tambahKaryawanBtn">
          Simpan
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Detail Karyawan -->
<div
  class="modal fade"
  id="detailkaryawan"
  tabindex="-1"
  aria-labelledby="detailkaryawanLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detailkaryawanLabel">
          Detail Data Karyawan
        </h5>
        <button
          type="button"
          class="btn-close"
          data-bs-dismiss="modal"
          aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="photo-container">
          <img
            id="detailPhoto"
            class="employee-photo"
            src="https://via.placeholder.com/150"
            alt="Foto Karyawan" />
        </div>

        <div class="row">
          <div class="col-md-6">
            <div class="detail-section">
              <h6>Informasi Pribadi</h6>
              <div class="detail-item">
                <div class="detail-label">ID Karyawan</div>
                <div class="detail-value" id="detailId">-</div>
              </div>
              <div class="detail-item">
                <div class="detail-label">Nama Lengkap</div>
                <div class="detail-value" id="detailNama">-</div>
              </div>
              <div class="detail-item">
                <div class="detail-label">Jenis Kelamin</div>
                <div class="detail-value" id="detailGender">-</div>
              </div>
              <div class="detail-item">
                <div class="detail-label">Email</div>
                <div class="detail-value" id="detailEmail">-</div>
              </div>
              <div class="detail-item">
                <div class="detail-label">No. Telepon</div>
                <div class="detail-value" id="detailTelp">-</div>
              </div>
              <div class="detail-item">
                <div class="detail-label">Alamat</div>
                <div class="detail-value" id="detailAlamat">-</div>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="detail-section">
              <h6>Informasi Pekerjaan</h6>
              <div class="detail-item">
                <div class="detail-label">Divisi</div>
                <div class="detail-value" id="detailDivisi">-</div>
              </div>
              <div class="detail-item">
                <div class="detail-label">Jabatan</div>
                <div class="detail-value" id="detailJabatan">-</div>
              </div>
              <div class="detail-item">
                <div class="detail-label">Status</div>
                <div class="detail-value" id="detailStatus">-</div>
              </div>
              <div class="detail-item">
                <div class="detail-label">Tanggal Masuk</div>
                <div class="detail-value" id="detailJoinDate">-</div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button
          type="button"
          class="btn btn-secondary"
          data-bs-dismiss="modal">
          Tutup
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Edit Karyawan -->
<div
  class="modal fade"
  id="editkaryawan"
  tabindex="-1"
  aria-labelledby="editkaryawanLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editkaryawanLabel">
          Edit Data Karyawan
        </h5>
        <button
          type="button"
          class="btn-close"
          data-bs-dismiss="modal"
          aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="formEditKaryawan">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="editId" class="form-label">ID Karyawan</label>
              <input
                type="text"
                class="form-control"
                id="editId"
                readonly />
            </div>
            <div class="col-md-6 mb-3">
              <label for="editNama" class="form-label">Nama Lengkap</label>
              <input
                type="text"
                class="form-control"
                id="editNama"
                required />
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="editDivisi" class="form-label">Divisi</label>
              <select class="form-select" id="editDivisi" required>
                <option value="Pemasaran">Pemasaran</option>
                <option value="Keuangan">Keuangan</option>
                <option value="IT">IT</option>
                <option value="HR">HR</option>
                <option value="Operasional">Operasional</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label for="editJabatan" class="form-label">Jabatan</label>
              <input
                type="text"
                class="form-control"
                id="editJabatan"
                required />
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="editStatus" class="form-label">Status</label>
              <select class="form-select" id="editStatus" required>
                <option value="Aktif">Aktif</option>
                <option value="Non-Aktif">Non-Aktif</option>
                <option value="Cuti">Cuti</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label for="editTelp" class="form-label">No. Telepon</label>
              <input
                type="tel"
                class="form-control"
                id="editTelp"
                required />
            </div>
          </div>

          <div class="mb-3">
            <label for="editEmail" class="form-label">Email</label>
            <input
              type="email"
              class="form-control"
              id="editEmail"
              required />
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button
          type="button"
          class="btn btn-secondary"
          data-bs-dismiss="modal">
          Batal
        </button>
        <button type="button" class="btn btn-primary" id="simpanEditBtn">
          Simpan Perubahan
        </button>
      </div>
    </div>
  </div>
</div>

@section('script')
<script src="{{asset('assets/bundles/dataTables.bundle.js')}}"></script>
<script>
  // Data contoh untuk simulasi
  const employeeData = {
    "001": {
      nama: "Bambang",
      gender: "Laki-laki",
      divisi: "Pemasaran",
      jabatan: "Karyawan",
      status: "Aktif",
      email: "bambang@example.com",
      telp: "08123456789",
      alamat: "Jl. Merdeka No. 123, Jakarta",
      joinDate: "Agustus 2025",
    },
    "002": {
      nama: "Siti",
      gender: "Perempuan",
      divisi: "Keuangan",
      jabatan: "Kepala Divisi",
      status: "Aktif",
      email: "siti@example.com",
      telp: "08129876543",
      alamat: "Jl. Sudirman No. 456, Jakarta",
      joinDate: "Agustus 2025",
    },
    "003": {
      nama: "Andi",
      gender: "Laki-laki",
      divisi: "Pemasaran",
      jabatan: "Karyawan",
      status: "Aktif",
      email: "andi@example.com",
      telp: "08111222333",
      alamat: "Jl. Thamrin No. 789, Jakarta",
      joinDate: "Agustus 2025",
    },
    "004": {
      nama: "Dewi",
      gender: "Perempuan",
      divisi: "Keuangan",
      jabatan: "Karyawan",
      status: "Aktif",
      email: "dewi@example.com",
      telp: "08144555666",
      alamat: "Jl. Gatot Subroto No. 101, Jakarta",
      joinDate: "Agustus 2024",
    },
  };

  $(document).ready(function() {
    // Inisialisasi DataTable dengan opsi yang lebih lengkap
    var table = $("#myProjectTable").DataTable({
      responsive: false, // jangan collapse
      autoWidth: false,
      scrollX: true,
      dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
      language: {
        search: "Cari:",
        searchPlaceholder: "Masukkan kata kunci...",
        lengthMenu: "Tampilkan _MENU_ data per halaman",
        info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
        infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
        infoFiltered: "(disaring dari _MAX_ total data)",
        paginate: {
          first: "Pertama",
          last: "Terakhir",
          next: "Selanjutnya",
          previous: "Sebelumnya",
        },
      },
      columnDefs: [{
          responsivePriority: 1,
          targets: 0
        }, // No
        {
          responsivePriority: 2,
          targets: 2
        }, // Nama
        {
          responsivePriority: 3,
          targets: -1
        }, // Aksi
        {
          orderable: false,
          targets: -1
        }, // Non-orderable untuk kolom aksi
      ],
      order: [
        [0, "asc"]
      ], // Urutkan berdasarkan kolom pertama (No)
    });

    // Variabel untuk menyimpan data yang sedang diedit
    var editingRow = null;

    // Fungsi untuk menampilkan detail karyawan
    $("#myProjectTable").on("click", ".btn-detail", function() {
      const id = $(this).data("id");
      const data = employeeData[id];

      if (data) {
        $("#detailId").text(id);
        $("#detailNama").text(data.nama);
        $("#detailGender").text(data.gender);
        $("#detailEmail").text(data.email);
        $("#detailTelp").text(data.telp);
        $("#detailAlamat").text(data.alamat);
        $("#detailDivisi").text(data.divisi);
        $("#detailJabatan").text(data.jabatan);
        $("#detailStatus").text(data.status);
        $("#detailJoinDate").text(data.joinDate);
      }
    });

    // Fungsi untuk menambahkan karyawan
    $("#tambahKaryawanBtn").click(function() {
      // Ambil nilai dari input
      const nama = $("#namaKaryawan").val();
      const divisi = $("#divisiKaryawan").val();
      const jabatan = $("#jabatanKaryawan").val();
      const gender = $("#genderKaryawan").val();
      const notelp = $("#telpKaryawan").val();
      const email = $("#emailKaryawan").val();
      const password = $("#passwordKaryawan").val();
      const confirmPassword = $("#confirmPasswordKaryawan").val();
      const alamat = $("#alamatKaryawan").val();

      // Validasi input
      if (
        !nama ||
        !divisi ||
        !jabatan ||
        !gender ||
        !notelp ||
        !email ||
        !password ||
        !confirmPassword
      ) {
        alert("Harap isi semua field yang diperlukan!");
        return;
      }

      // Validasi password
      if (password !== confirmPassword) {
        alert("Password dan konfirmasi password tidak cocok!");
        return;
      }

      // Generate ID baru (contoh sederhana)
      const newId = "00" + (table.rows().count() + 1);
      const currentDate = new Date();
      const monthNames = [
        "Januari",
        "Februari",
        "Maret",
        "April",
        "Mei",
        "Juni",
        "Juli",
        "Agustus",
        "September",
        "Oktober",
        "November",
        "Desember",
      ];
      const joinDate =
        monthNames[currentDate.getMonth()] +
        " " +
        currentDate.getFullYear();

      // Tambahkan baris baru ke tabel
      table.row
        .add([
          table.rows().count() + 1,
          newId,
          nama,
          divisi,
          jabatan,
          '<span class="badge bg-success">Aktif</span>',
          email,
          joinDate,
          `<div class="action-buttons">
              <button class="btn btn-outline-info btn-detail" data-bs-toggle="modal" data-bs-target="#detailkaryawan" data-id="${newId}" title="Detail">
                <i class="icofont-eye-alt"></i>
              </button>
              <button class="btn btn-outline-primary edit-btn" data-bs-toggle="modal" data-bs-target="#editkaryawan" data-id="${newId}" title="Edit">
                <i class="icofont-edit"></i>
              </button>
              <button class="btn btn-outline-danger deleterow" data-id="${newId}" title="Hapus">
                <i class="icofont-ui-delete"></i>
              </button>
            </div>`,
        ])
        .draw();

      // Simpan data karyawan baru
      employeeData[newId] = {
        nama: nama,
        gender: gender,
        divisi: divisi,
        jabatan: jabatan,
        status: "Aktif",
        email: email,
        telp: notelp,
        alamat: alamat,
        joinDate: joinDate,
      };

      // Reset form dan tutup modal
      $("#formTambahKaryawan")[0].reset();
      $("#addkaryawan").modal("hide");

      // Untuk koneksi backend, kita bisa mengirim data ke server di sini
      sendDataToBackend({
        id: newId,
        nama: nama,
        divisi: divisi,
        jabatan: jabatan,
        gender: gender,
        notelp: notelp,
        email: email,
        password: password,
        alamat: alamat,
        status: "Aktif",
        joinDate: joinDate,
      });
    });

    // Fungsi untuk mengisi form edit saat tombol edit diklik
    $("#myProjectTable").on("click", ".edit-btn", function() {
      const row = $(this).closest("tr");
      const rowData = table.row(row).data();
      const id = $(this).data("id");
      const data = employeeData[id];

      // Simpan referensi baris yang sedang diedit
      editingRow = row;

      // Isi form edit dengan data dari baris yang dipilih
      $("#editId").val(id);
      $("#editNama").val(data.nama);
      $("#editDivisi").val(data.divisi);
      $("#editJabatan").val(data.jabatan);
      $("#editStatus").val(data.status);
      $("#editTelp").val(data.telp);
      $("#editEmail").val(data.email);
    });

    // Fungsi untuk menyimpan perubahan edit
    $("#simpanEditBtn").click(function() {
      if (!editingRow) return;

      const id = $("#editId").val();

      // Update data di objek employeeData
      employeeData[id].nama = $("#editNama").val();
      employeeData[id].divisi = $("#editDivisi").val();
      employeeData[id].jabatan = $("#editJabatan").val();
      employeeData[id].status = $("#editStatus").val();
      employeeData[id].telp = $("#editTelp").val();
      employeeData[id].email = $("#editEmail").val();

      // Update data di tabel
      const newStatus = $("#editStatus").val();
      const newDivisi = $("#editDivisi").val();

      table.cell(editingRow, 2).data($("#editNama").val());
      table.cell(editingRow, 3).data(newDivisi);
      table.cell(editingRow, 4).data($("#editJabatan").val());
      table
        .cell(editingRow, 5)
        .data(
          '<span class="badge bg-' +
          (newStatus === "Aktif" ?
            "success" :
            newStatus === "Non-Aktif" ?
            "danger" :
            "warning") +
          '">' +
          newStatus +
          "</span>"
        );
      table.cell(editingRow, 6).data($("#editEmail").val());

      // Untuk koneksi backend, kita bisa mengirim data yang diupdate ke server di sini
      updateDataInBackend({
        id: id,
        nama: $("#editNama").val(),
        divisi: newDivisi,
        jabatan: $("#editJabatan").val(),
        status: newStatus,
        notelp: $("#editTelp").val(),
        email: $("#editEmail").val(),
      });

      // Tutup modal edit
      $("#editkaryawan").modal("hide");
      editingRow = null;
    });

    // Fungsi untuk menghapus baris
    $("#myProjectTable").on("click", ".deleterow", function() {
      const row = $(this).closest("tr");
      const rowData = table.row(row).data();
      const id = $(this).data("id");

      if (
        confirm(
          "Apakah Anda yakin ingin menghapus karyawan " + rowData[2] + "?"
        )
      ) {
        // Hapus data dari objek employeeData
        delete employeeData[id];

        // Untuk koneksi backend, kita bisa mengirim permintaan hapus ke server di sini
        deleteDataInBackend(id);

        table.row(row).remove().draw();
      }
    });

    // Fungsi untuk mengirim data ke backend (contoh implementasi)
    function sendDataToBackend(data) {
      console.log("Mengirim data ke backend:", data);
      // Implementasi AJAX ke endpoint backend
      /*
      $.ajax({
        url: '/api/karyawan',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(response) {
          console.log('Data berhasil disimpan:', response);
          // Anda bisa refresh tabel atau tambahkan baris baru berdasarkan response
        },
        error: function(xhr, status, error) {
          console.error('Gagal menyimpan data:', error);
          alert('Gagal menyimpan data karyawan');
        }
      });
      */
    }

    // Fungsi untuk update data di backend
    function updateDataInBackend(data) {
      console.log("Memperbarui data di backend:", data);
      // Implementasi AJAX ke endpoint backend
      /*
      $.ajax({
        url: '/api/karyawan/' + data.id,
        type: 'PUT',
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(response) {
          console.log('Data berhasil diperbarui:', response);
        },
        error: function(xhr, status, error) {
          console.error('Gagal memperbarui data:', error);
          alert('Gagal memperbarui data karyawan');
        }
      });
      */
    }

    // Fungsi untuk hapus data di backend
    function deleteDataInBackend(id) {
      console.log("Menghapus data di backend dengan ID:", id);
      // Implementasi AJAX ke endpoint backend
      /*
      $.ajax({
        url: '/api/karyawan/' + id,
        type: 'DELETE',
        success: function(response) {
          console.log('Data berhasil dihapus:', response);
        },
        error: function(xhr, status, error) {
          console.error('Gagal menghapus data:', error);
          alert('Gagal menghapus data karyawan');
        }
      });
      */
    }
  });
</script>
@endsection