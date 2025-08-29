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
              <select class="form-select" id="jabatanKaryawan" required></select>
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
              <input type="password" class="form-control" id="passwordKaryawan" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Konfirmasi Password</label>
              <input type="password" class="form-control" id="confirmPasswordKaryawan" required>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Alamat</label>
            <textarea class="form-control" id="alamatKaryawan" rows="3"></textarea>
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
<div class="modal fade" id="editkaryawan" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Karyawan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formEditKaryawan">
          <input type="hidden" id="editId">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Nama Lengkap</label>
              <input type="text" class="form-control" id="editNama" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Divisi</label>
              <select class="form-select" id="editDivisi" required></select>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Jabatan</label>
              <select class="form-select" id="editJabatan" required></select>
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
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">No. Telepon</label>
              <input type="tel" class="form-control" id="editTelp" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Email</label>
              <input type="email" class="form-control" id="editEmail" required>
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

@section('script')
<script src="{{asset('assets/bundles/dataTables.bundle.js')}}"></script>
<script>
  $(document).ready(function() {

    // --- Inisialisasi DataTable ---
    var table = $("#myProjectTable").DataTable({
      ajax: {
        url: '/api/employees',
        dataSrc: 'data'
      },
      columns: [{
          data: null,
          render: (data, type, row, meta) => meta.row + 1
        },
        {
          data: 'id_karyawan'
        },
        {
          data: 'nama'
        },
        {
          data: 'roles.0.division.nama_divisi',
          defaultContent: '-'
        },
        {
          data: 'roles.0.nama_jabatan',
          defaultContent: '-'
        },
        {
          data: null,
          render: () => '<span class="badge bg-success">Aktif</span>'
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
                  <button class="btn btn-outline-info btn-detail" data-id="${row.id_karyawan}"><i class="icofont-eye-alt"></i></button>
                  <button class="btn btn-outline-primary edit-btn" data-id="${row.id_karyawan}"><i class="icofont-edit"></i></button>
                  <button class="btn btn-outline-danger deleterow" data-id="${row.id_karyawan}"><i class="icofont-ui-delete"></i></button>
                </div>`
        }
      ]
    });

    // --- Ambil data Divisi dan Jabatan via AJAX ---
    function loadDivisi() {
    $.ajax({
        url: '/api/divisions',
        method: 'GET',
        success: function(res) {
            let options = '<option value="" disabled selected>Pilih Divisi</option>';
            res.data.forEach(d => {
                options += `<option value="${d.id_divisi}">${d.nama_divisi}</option>`;
            });
            $('#divisiKaryawan, #editDivisi').html(options);
        },
        error: function(err) {
            console.error('Gagal load divisi:', err);
            alert('Gagal mengambil data divisi');
        }
    });
}

function loadJabatan() {
    $.ajax({
        url: '/api/role',
        method: 'GET',
        success: function(res) {
            let options = '<option value="" disabled selected>Pilih Jabatan</option>';
            res.data.forEach(d => {
                options += `<option value="${d.id_jabatan}">${d.nama_jabatan}</option>`;
            });
            $('#jabatanKaryawan, #editJabatan').html(options);
        },
        error: function(err) {
            console.error('Gagal load jabatan:', err);
            alert('Gagal mengambil data jabatan');
        }
    });
}


    loadJabatan();
    loadDivisi();

    // --- Tambah Karyawan ---
    $('#tambahKaryawanBtn').click(function() {
      const payload = {
        nama: $('#namaKaryawan').val(),
        gender: $('#genderKaryawan').val(),
        no_telp: $('#telpKaryawan').val(),
        email: $('#emailKaryawan').val(),
        password: $('#passwordKaryawan').val(),
        password_confirmation: $('#confirmPasswordKaryawan').val(),
        role_id: $('#jabatanKaryawan').val(),
        division_id: $('#divisiKaryawan').val(),
        alamat: $('#alamatKaryawan').val()
      };
      $.post('/api/employees', payload)
        .done(() => {
          $('#formTambahKaryawan')[0].reset();
          $('#addkaryawan').modal('hide');
          table.ajax.reload();
        }).fail(err => alert('Gagal tambah karyawan: ' + err.responseText));
    });

    // --- Edit Karyawan (tampilkan modal) ---
    $('#myProjectTable').on('click', '.edit-btn', function() {
      const rowData = table.row($(this).closest('tr')).data();
      $('#editId').val(rowData.id_karyawan);
      $('#editNama').val(rowData.nama);
      $('#editDivisi').val(rowData.roles[0]?.division.id_divisi || '');
      $('#editJabatan').val(rowData.roles[0]?.id_jabatan || '');
      $('#editStatus').val('Aktif');
      $('#editTelp').val(rowData.no_telp);
      $('#editEmail').val(rowData.user.email);
      $('#editkaryawan').modal('show');
    });

    // --- Simpan Edit ---
    $('#simpanEditBtn').click(function() {
      const id = $('#editId').val();
      const payload = {
        nama: $('#editNama').val(),
        no_telp: $('#editTelp').val(),
        email: $('#editEmail').val(),
        role_id: $('#editJabatan').val(),
        division_id: $('#editDivisi').val()
      };
      $.ajax({
        url: '/api/employees/' + id,
        type: 'PUT',
        data: payload
      }).done(() => {
        $('#editkaryawan').modal('hide');
        table.ajax.reload();
      }).fail(err => alert('Gagal update: ' + err.responseText));
    });

    // --- Hapus ---
    $('#myProjectTable').on('click', '.deleterow', function() {
      const id = $(this).data('id');
      if (confirm('Apakah yakin ingin menghapus?')) {
        $.ajax({
            url: '/api/employees/' + id,
            type: 'DELETE'
          }).done(() => table.ajax.reload())
          .fail(err => alert('Gagal hapus: ' + err.responseText));
      }
    });

    // --- Tampilkan Detail Karyawan ---
    $('#myProjectTable').on('click', '.btn-detail', function() {
      const rowData = table.row($(this).closest('tr')).data();

      // Set data ke modal
      $('#detailId').text(rowData.id_karyawan);
      $('#detailNama').text(rowData.nama);
      $('#detailGender').text(rowData.gender);
      $('#detailEmail').text(rowData.user.email);
      $('#detailTelp').text(rowData.no_telp);
      $('#detailAlamat').text(rowData.alamat || '-');
      $('#detailDivisi').text(rowData.roles[0]?.division.nama_divisi || '-');
      $('#detailJabatan').text(rowData.roles[0]?.nama_jabatan || '-');
      $('#detailStatus').text('Aktif'); // Jika status belum ada di data
      $('#detailJoinDate').text(new Date(rowData.created_at).toLocaleDateString('id-ID'));

      // Optional: Jika ada foto
      $('#detailPhoto').attr('src', rowData.photo_url || 'https://via.placeholder.com/150');

      // Tampilkan modal
      $('#detailkaryawan').modal('show');
    });

  });
</script>
@endsection