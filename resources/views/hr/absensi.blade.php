@extends('template.template')

@section('title', 'Absensi Karyawan')

@section('content')
<style>
  .card {
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
  }

  .form-select.form-select-sm {
    height: 28px;
    padding: 2px 8px;
    font-size: 0.8rem;
  }

  .form-label.small {
    font-size: 0.75rem;
    margin-bottom: 2px;
  }

  .card.stat-card .card-body {
    padding: 1rem;
    min-height: 100px;
  }

  @media (max-width: 576px) {
    .card.stat-card {
      font-size: 0.75rem;
    }

    .card.stat-card .card-body {
      padding: 0.3rem 0.4rem;
      min-height: 60px;
    }

    .card.stat-card i {
      font-size: 1rem;
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
  
  /* biar label + select tetap sejajar */
.dataTables_length label {
    display: inline-flex;
    align-items: center;
    gap: 8px; /* jarak antara teks dan select */
    font-weight: 500; /* opsional, biar lebih rapih */
}

/* styling dropdown entries */
.dataTables_length select {
    display: inline-block;
    width: auto;       /* jangan full width */
    min-width: 70px;   /* kasih lebar minimal */
    margin: 0 4px;     /* kasih jarak kiri-kanan */
    padding: 4px 8px;  /* kasih padding biar ga gepeng */
    border-radius: 6px; /* biar lebih smooth */
}

.swal2-container {
    z-index: 99999 !important;
  }

</style>

<div class="body d-flex py-3">
  <div class="body d-flex py-lg-3 py-md-2">
    <div class="container-xxl">
      <div class="row align-items-center">
        <div class="border-0 mb-2">
          <div
            class="card-header py-3 no-bg bg-transparent align-items-center px-0 justify-content-between border-bottom flex-wrap">
            <h3 class="fw-bold mb-0">Absensi Karyawan</h3>

            <div class="d-flex align-items-center flex-wrap mt-3">
              <!-- card informasi -->
              <div class="container-fluid px-0">
                <div class="row g-3 mb-3" id="summary-cards">
                  <!-- Summary cards will be loaded by JavaScript -->
                </div>
              </div>
              
              {{-- button import --}}
              <div class="ms-auto">
                <button class="btn btn-primary btn-l d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#importModal">
                  <i class="icofont-upload me-1"></i> Import Absensi
                </button>
              </div>

              <!-- Card Filter Data -->
              <div class="container-xxl mt-2">
                <div class="card shadow-sm w-100">
                  <div class="card-header bg-primary text-white py-2">
                    <h5 class="mb-0">Filter Data</h5>
                  </div>
                  <div class="card-body p-2">
                    <form class="row g-2" id="filterForm">
                      <!-- Period -->
                      <div class="col-6 col-md-3">
                        <label class="form-label small mb-1">Periode</label>
                        <select class="form-select form-select-sm" id="periodFilter" name="period">
                          <option value="">Semua Periode</option>
                          <!-- Options will be populated by JavaScript -->
                        </select>
                      </div>

                      <!-- Tahun -->
                      <div class="col-6 col-md-3">
                        <label class="form-label small mb-1">Tahun</label>
                        <select class="form-select form-select-sm" id="yearFilter" name="year">
                          <option value="">Semua Tahun</option>
                          <!-- Options will be populated by JavaScript -->
                        </select>
                      </div>

                      <!-- Divisi -->
                      <div class="col-6 col-md-3">
                        <label class="form-label small mb-1">Divisi</label>
                        <select class="form-select form-select-sm" id="divisionFilter" name="division_id">
                          <option value="">Semua Divisi</option>
                          <!-- Options will be populated by JavaScript -->
                        </select>
                      </div>

                      <!-- Filter Button -->
                      <div class="col-6 col-md-3 d-flex align-items-end">
                        <button type="button" class="btn btn-primary btn-sm" id="applyFilter">Terapkan Filter</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Import Modal -->
        <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Import Data Absensi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <form id="importForm" enctype="multipart/form-data">
                  @csrf
                  <div class="mb-3">
                    <label for="file" class="form-label">Pilih File Excel</label>
                    <input class="form-control" type="file" id="file" name="files[]" accept=".xlsx,.xls" multiple required>
                  </div>
                </form>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="submitImport">Import</button>
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
                  <div class="table-responsive">
                    <table id="attendanceTable" class="table table-hover table-striped align-middle mb-0" style="width: 100%">
                      <thead>
                        <tr>
                          <th>Nama</th>
                          <th>Divisi</th>
                          <th>Hadir</th>
                          <th>Izin</th>
                          <th>Sakit</th>
                          <th>Mangkir</th>
                          <th>Terlambat</th>
                          <th style="width: 80px">Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        <!-- Data will be loaded by JavaScript -->
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
  </div>
</div>
@endsection


@section('script')
<script src="{{asset('assets/bundles/apexcharts.bundle.js')}}"></script>
<script src="{{asset('assets/bundles/dataTables.bundle.js')}}"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
  // ==================== UTILITY FUNCTIONS ====================
  function showAlert(icon, title, text) {
    return Swal.fire({
      icon: icon,
      title: title,
      text: text,
      confirmButtonColor: '#3085d6',
    });
  }

$(document).ready(function() {
    let attendanceTable;
    let periods = [];
    let divisions = [];
    
    // Initialize DataTable - Simple Approach
    function initDataTable() {
        attendanceTable = $('#attendanceTable').DataTable({
            processing: true,
            serverSide: false,
            searching: true,
            ordering: true,
            paging: true,
            data: [],
            columns: [
                { data: 'nama' },
                { data: 'division' },
                { data: 'hadir', className: 'text-center' },
                { data: 'izin', className: 'text-center' },
                { data: 'sakit', className: 'text-center' },
                { data: 'mangkir', className: 'text-center' },
                { data: 'jumlah_terlambat', className: 'text-center' },
                { 
                    data: 'actions',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                }
            ],
            language: {
                emptyTable: "Tidak ada data absensi",
                zeroRecords: "Tidak ada data yang cocok"
            },
            dom: '<"row"<"col-md-6"l><"col-md-6 text-end"f>>' + // length & search
                 'rt' + // table
                 '<"row mt-3"<"col-md-6"i><"col-md-6 d-flex justify-content-end"p>>' // info & pagination kanan
        });
        
        loadAttendanceData();
    }
    
    // Load attendance data
function loadAttendanceData() {
    console.log('Loading attendance data...');
    
    $.ajax({
        url: '/api/attendances/summary',
        data: {
            period: $('#periodFilter').val(),
            division_id: $('#divisionFilter').val(),
            year: $('#yearFilter').val()
        },
        success: function(response) {
            console.log('API Response:', response);
            
            if (response.success && response.data && Array.isArray(response.data)) {
                // Clear and add new data
                attendanceTable.clear();
                
                if (response.data.length > 0) {
                    attendanceTable.rows.add(response.data).draw();
                    console.log('Data loaded:', response.data.length, 'records');
                } else {
                    attendanceTable.draw();
                    console.log('No data available');
                }
            } else {
                console.error('Invalid response format:', response);
                attendanceTable.clear().draw();
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            showAlert('error', 'Error', 'Error loading data: ' + error);
        }
    });
}
    
    // Load summary cards
    function loadSummaryCards() {
        $.ajax({
            url: '/api/attendances/summary',
            data: {
                period: $('#periodFilter').val(),
                division_id: $('#divisionFilter').val(),
                year: $('#yearFilter').val()
            },
            success: function(response) {
                if (response.success && response.data) {
                    const data = response.data;
                    
                    const totals = {
                        hadir: 0,
                        izin: 0,
                        sakit: 0,
                        mangkir: 0,
                        jumlah_terlambat: 0
                    };
                    
                    data.forEach(item => {
                        totals.hadir += parseInt(item.hadir) || 0;
                        totals.izin += parseInt(item.izin) || 0;
                        totals.sakit += parseInt(item.sakit) || 0;
                        totals.mangkir += parseInt(item.mangkir) || 0;
                        totals.jumlah_terlambat += parseInt(item.jumlah_terlambat) || 0;
                    });
                    
                    console.log('Totals calculated:', totals);
                    
                    // Update summary cards
                    $('#summary-cards').html(`
                        <div class="col-6 col-md-3">
                            <div class="card bg-primary h-100 w-100 stat-card">
                                <div class="card-body text-white d-flex align-items-center">
                                    <i class="icofont-checked fs-3"></i>
                                    <div class="d-flex flex-column ms-3">
                                        <h6 class="mb-0">Jumlah Kehadiran</h6>
                                        <span>${totals.hadir}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card bg-primary h-100 w-100 stat-card">
                                <div class="card-body text-white d-flex align-items-center">
                                    <i class="icofont-beach-bed fs-3"></i>
                                    <div class="d-flex flex-column ms-3">
                                        <h6 class="mb-0">Jumlah Izin</h6>
                                        <span>${totals.izin}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card bg-primary h-100 w-100 stat-card">
                                <div class="card-body text-white d-flex align-items-center">
                                    <i class="icofont-medical-sign fs-3"></i>
                                    <div class="d-flex flex-column ms-3">
                                        <h6 class="mb-0">Jumlah Sakit</h6>
                                        <span>${totals.sakit}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card bg-primary h-100 w-100 stat-card">
                                <div class="card-body text-white d-flex align-items-center">
                                    <i class="icofont-ban fs-3"></i>
                                    <div class="d-flex flex-column ms-3">
                                        <h6 class="mb-0">Jumlah Mangkir</h6>
                                        <span>${totals.mangkir}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `);
                }
            },
            error: function(xhr, status, error) {
                console.error('Summary error:', error);
            }
        });
    }
    
    // Function untuk extract tahun dari periods
function loadYearsFromPeriods(periods) {
    const years = new Set();
    
    // Extract years from periods yang sudah berbentuk "5 Jul 2025 - 5 Aug 2025"
    periods.forEach(period => {
        // Ambil tahun dari kedua sisi periode
        const parts = period.split(' - ');
        if (parts.length === 2) {
            const startYear = parts[0].trim().split(' ').pop(); // Ambil tahun dari start date
            const endYear = parts[1].trim().split(' ').pop();   // Ambil tahun dari end date
            
            if (startYear && !isNaN(startYear)) years.add(parseInt(startYear));
            if (endYear && !isNaN(endYear)) years.add(parseInt(endYear));
        }
    });
    
    // Add current year if no periods found
    if (years.size === 0) {
        years.add(new Date().getFullYear());
    }
    
    // Convert to array and sort descending
    const sortedYears = Array.from(years).sort((a, b) => b - a);
    
    let yearOptions = '<option value="">Semua Tahun</option>';
    sortedYears.forEach(year => {
        yearOptions += `<option value="${year}">${year}</option>`;
    });
    $('#yearFilter').html(yearOptions);
}
    
    // Load filter options dengan refresh cache
    function loadFilterOptions() {
        // Clear cache dengan menambahkan parameter refresh
        const timestamp = new Date().getTime();
        
      
        $.ajax({
            url: '/api/divisions?' + timestamp,
            success: function(response) {
                if (response.success) {
                    divisions = response.data;
                    let options = '<option value="">Semua Divisi</option>';
                    divisions.forEach(division => {
                        options += `<option value="${division.id_divisi}">${division.nama_divisi}</option>`;
                    });
                    $('#divisionFilter').html(options);
                }
            }
        });
        
        // Load periods dengan parameter refresh untuk memastikan data terbaru
        // Load periods dengan parameter refresh untuk memastikan data terbaru
$.ajax({
    url: '/api/attendances/periods?refresh=true&' + timestamp,
    success: function(response) {
        if (response.success) {
            periods = response.data;
            let options = '<option value="">Semua Periode</option>';
            
            // Urutkan periods secara descending berdasarkan tahun
            periods.sort((a, b) => {
                const aYear = parseInt(a.split(' - ')[0].split(' ').pop());
                const bYear = parseInt(b.split(' - ')[0].split(' ').pop());
                return bYear - aYear || b.localeCompare(a);
            });
            
            periods.forEach(period => {
                options += `<option value="${period}">${period}</option>`;
            });
            
            $('#periodFilter').html(options);
            
            // Auto-select periode terbaru
            if (periods.length > 0) {
                $('#periodFilter').val(periods[0]);
                loadAttendanceData();
                loadSummaryCards();
            }
            
            // Load years dari data periods yang sebenarnya
            loadYearsFromPeriods(periods);
        }
    }
});
    }
    
    // Handle import
    $('#submitImport').click(function() {
        const file = $('#file')[0].files[0];
        if (!file) {
            showAlert('warning', 'Peringatan', 'Pilih file terlebih dahulu');
            return;
        }
        
        const formData = new FormData();
        formData.append('file', file);
        
        // Tampilkan loading
        Swal.fire({
            title: 'Mengimport...',
            text: 'Sedang memproses file absensi',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: '/api/attendances/import',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                Swal.close();
                if (response.success) {
                    showAlert('success', 'Berhasil', 'Import berhasil: ' + response.message);
                    $('#importModal').modal('hide');
                    $('#file').val('');
                    
                    // Force refresh cache dengan parameter tambahan
                    const timestamp = new Date().getTime();
                    
                    // Reload periods dengan force refresh
                    $.ajax({
                        url: '/api/attendances/periods?refresh=true&' + timestamp,
                        success: function(periodResponse) {
                            if (periodResponse.success) {
                                periods = periodResponse.data;
                                let options = '<option value="">Semua Periode</option>';
                                periods.forEach(period => {
                                    options += `<option value="${period}">${period}</option>`;
                                });
                                $('#periodFilter').html(options);
                                
                                // Update years filter juga
                                loadYearsFromPeriods(periods);
                                
                                // Show the imported period in filter
                                if (response.period) {
                                    $('#periodFilter').val(response.period);
                                }
                                
                                // Reload data
                                loadAttendanceData();
                                loadSummaryCards();
                            }
                        }
                    });
                } else {
                    showAlert('error', 'Gagal', 'Import gagal: ' + response.message);
                }
            },
            error: function(xhr) {
                Swal.close();
                let errorMessage = 'Error during import';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showAlert('error', 'Error', 'Error: ' + errorMessage);
            }
        });
    });
    
    // Apply filters
    $('#applyFilter').click(function() {
        loadAttendanceData();
        loadSummaryCards();
    });
    
    // Initialize
    initDataTable();
    loadFilterOptions();
    loadSummaryCards();
});
</script>
@endsection