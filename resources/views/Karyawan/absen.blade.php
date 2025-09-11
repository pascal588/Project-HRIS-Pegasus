@extends('template.template')

@section('title', 'Absensi karyawan')

@section('content')
<!-- CSS Tabel -->
<link rel="stylesheet" href="{{ asset('assets/plugin/datatables/responsive.dataTables.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugin/datatables/dataTables.bootstrap5.min.css') }}">

<!-- Body -->
<div class="body d-flex py-lg-3 py-md-2">
  <div class="container-xxl">
    <div class="row align-items-center">
      <div class="border-0 mb-4">
        <div
          class="card-header py-3 no-bg bg-transparent d-flex align-items-center px-0 justify-content-between border-bottom flex-wrap">
          <h3 class="fw-bold mb-0">Absensi Karyawan</h3>
        </div>

        <!-- Filter -->
        <div class="d-flex justify-content-between align-items-center mb-2 mt-2">
          <div class="d-flex gap-2 ms-auto">
            <select id="periodSelect" class="form-select">
              <option value="">Pilih Periode</option>
              <!-- Options akan diisi oleh JavaScript -->
            </select>
          </div>
        </div>

        <!-- Ringkasan Absen -->
        <div class="row g-3 mb-3 row-cols-2 row-cols-sm-2 row-cols-md-2 row-cols-lg-2 row-cols-xl-4" id="summaryCards">
          <!-- Data ringkasan akan diisi oleh JavaScript -->
        </div>

        <!-- /Ringkasan -->
      </div>
    </div>

    <!-- Tabel -->
    <div class="row clearfix g-3">
      <div class="col-sm-12">
        <div class="card mb-3">
          <div class="card-body">
            <!-- Tambahkan wrapper table-responsive untuk mobile -->
            <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
              <table id="myProjectTable" class="table table-hover align-middle mb-0" style="width:100%; min-width: 650px;">
                <thead class="table-secondary text-center">
                  <tr>
                    <th>Periode</th>
                    <th>Hadir</th>
                    <th>Izin</th>
                    <th>Sakit</th>
                    <th>Mangkir</th>
                    <th>Terlambat</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody class="text-center" id="attendanceTableBody">
                  <!-- Data akan diisi oleh JavaScript -->
                </tbody>
              </table>
            </div>
            
            <!-- Tabel Alternatif untuk Tampilan Mobile -->
            <div class="d-md-none" id="mobileAttendanceTable">
              <!-- Data akan diisi oleh JavaScript -->
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Grafik + Ringkasan -->
    <div class="card mt-3 shadow-sm">
      <div class="card-body">
        <div class="row gy-3">
          <!-- Grafik -->
          <div class="col-lg-9 col-12">
            <h6 class="mb-3 fw-bold">Tren Absensi Berdasarkan Periode</h6>
            <div id="chartAbsensi" style="min-height:260px;"></div>
          </div>

          <!-- Ringkasan -->
          <div class="col-lg-3 col-12 border-lg-start" id="attendanceInfo">
            <!-- Data ringkasan akan diisi oleh JavaScript -->
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal - Diperlebar untuk desktop -->
<div class="modal fade" id="showabsen" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Data Absensi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0">
        <div class="table-responsive">
          <table class="table table-bordered table-striped mb-0">
            <thead class="table-secondary text-center">
              <tr>
                <th style="min-width: 120px;">Tanggal</th>
                <th style="min-width: 100px;">Status</th>
                <th>Jam Masuk</th>
                <th>Jam Keluar</th>
                <th>Lama Kerja</th>
              </tr>
            </thead>
            <tbody class="text-center" id="modalAttendanceBody">
              <!-- Data akan diisi oleh JavaScript -->
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('script')
<!-- Plugin Js Tabel-->
<script src="{{ asset('assets/bundles/dataTables.bundle.js') }}"></script>
<script src="{{ asset('assets/bundles/apexcharts.bundle.js') }}"></script>
<script>
  
  let dataTable = null;

  $(document).ready(function () {
    // Ambil data absensi karyawan
    loadEmployeeAttendance();

    // Event listener untuk filter periode
    $('#periodSelect').change(function() {
      loadEmployeeAttendance($(this).val());
    });
  });

  // Fungsi untuk memuat data absensi karyawan
  function loadEmployeeAttendance(period = '') {
    // Ambil employee_id dari user yang sedang login
    const employeeId = '{{ Auth::user()->employee->id_karyawan ?? "" }}';
    
    if (!employeeId) {
      console.error('Employee ID tidak ditemukan');
      return;
    }

    // Tampilkan loading state
    $('#attendanceTableBody').html('<tr><td colspan="7" class="text-center">Memuat data...</td></tr>');
    $('#mobileAttendanceTable').html('<div class="text-center">Memuat data...</div>');

    // URL API untuk mendapatkan data absensi karyawan
    let apiUrl = `/api/attendances/employee/${employeeId}`;
    if (period) {
      apiUrl += `?period=${encodeURIComponent(period)}`;
    }

    // Ambil data dari API
    fetch(apiUrl)
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          updateUI(data);
        } else {
          console.error('Gagal memuat data absensi:', data.message);
          $('#attendanceTableBody').html('<tr><td colspan="7" class="text-center text-danger">Gagal memuat data</td></tr>');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        $('#attendanceTableBody').html('<tr><td colspan="7" class="text-center text-danger">Error memuat data</td></tr>');
      });
  }

  // Fungsi untuk memperbarui UI dengan data dari API
  function updateUI(data) {
    // Update ringkasan absensi
    updateSummaryCards(data.summary);
    
    // Update dropdown periode
    updatePeriodSelect(data.periods);
    
    // Update tabel absensi
    updateAttendanceTable(data.attendances, data.periods);
    
    // Update ringkasan informasi
    updateAttendanceInfo(data.summary, data.employee);
    
    // Update grafik berdasarkan periode
    updateAttendanceChartByPeriod(data.attendances);
  }

  // Fungsi untuk memperbarui kartu ringkasan
  function updateSummaryCards(summary) {
    const summaryCards = `
      <div class="col">
        <div class="card bg-primary">
          <div class="card-body text-white d-flex align-items-center">
            <i class="icofont-checked fs-3"></i>
            <div class="d-flex flex-column ms-3">
              <h6 class="mb-0">Hadir</h6>
              <span class="fw-bold text-white">${summary.hadir}</span>
            </div>
          </div>
        </div>
      </div>
      <div class="col">
        <div class="card bg-primary">
          <div class="card-body text-white d-flex align-items-center">
            <i class="icofont-beach-bed fs-3"></i>
            <div class="d-flex flex-column ms-3">
              <h6 class="mb-0">Izin</h6>
              <span class="fw-bold text-white">${summary.izin}</span>
            </div>
          </div>
        </div>
      </div>
      <div class="col">
        <div class="card bg-primary">
          <div class="card-body text-white d-flex align-items-center">
            <i class="icofont-medical-sign fs-3"></i>
            <div class="d-flex flex-column ms-3">
              <h6 class="mb-0">Sakit</h6>
              <span class="fw-bold text-white">${summary.sakit}</span>
            </div>
          </div>
        </div>
      </div>
      <div class="col">
        <div class="card bg-primary">
          <div class="card-body text-white d-flex align-items-center">
            <i class="icofont-ban fs-3"></i>
            <div class="d-flex flex-column ms-3">
              <h6 class="mb-0">Mangkir</h6>
              <span class="fw-bold text-white">${summary.mangkir}</span>
            </div>
          </div>
        </div>
      </div>
    `;
    
    $('#summaryCards').html(summaryCards);
  }

  // Fungsi untuk memperbarui dropdown periode
  function updatePeriodSelect(periods) {
    let options = '<option value="">Semua Periode</option>';
    
    // Batasi hanya 12 periode terbaru
    const limitedPeriods = periods.slice(0, 12);
    
    limitedPeriods.forEach(period => {
      const selected = $('#periodSelect').val() === period ? 'selected' : '';
      options += `<option value="${period}" ${selected}>${period}</option>`;
    });
    
    $('#periodSelect').html(options);
  }

  // Fungsi untuk memperbarui tabel absensi
  function updateAttendanceTable(attendances, periods) {
    let tableBody = '';
    let mobileTableBody = '';
    
    // Kelompokkan data berdasarkan periode
    const attendanceByPeriod = {};
    
    attendances.forEach(attendance => {
      if (!attendanceByPeriod[attendance.period]) {
        attendanceByPeriod[attendance.period] = {
          period: attendance.period,
          hadir: 0,
          izin: 0,
          sakit: 0,
          mangkir: 0,
          terlambat: 0
        };
      }
      
      // Hitung status kehadiran
      switch(attendance.status) {
        case 'Present at workday (PW)':
          attendanceByPeriod[attendance.period].hadir++;
          break;
        case 'Permission (I)':
          attendanceByPeriod[attendance.period].izin++;
          break;
        case 'Sick (S)':
          attendanceByPeriod[attendance.period].sakit++;
          break;
        case 'Absent (A)':
          attendanceByPeriod[attendance.period].mangkir++;
          break;
      }
      
      // Jumlahkan keterlambatan
      if (attendance.late) {
        attendanceByPeriod[attendance.period].terlambat += parseInt(attendance.late) || 0;
      }
    });
    
    // Buat baris tabel untuk setiap periode (desktop)
    Object.values(attendanceByPeriod).forEach(periodData => {
      tableBody += `
        <tr>
          <td><span class="fw-bold">${periodData.period}</span></td>
          <td><span class="fw-bold">${periodData.hadir}</span></td>
          <td><span class="fw-bold">${periodData.izin}</span></td>
          <td><span class="fw-bold">${periodData.sakit}</span></td>
          <td><span class="fw-bold">${periodData.mangkir}</span></td>
          <td><span class="fw-bold">${periodData.terlambat}m</span></td>
          <td>
            <button type="button" class="btn btn-outline-secondary btn-view-details" data-period="${periodData.period}">
              <i class="icofont-eye text-success"></i>
            </button>
          </td>
        </tr>
      `;
      
      // Buat tampilan mobile yang lebih sederhana
      mobileTableBody += `
        <div class="card mb-2">
          <div class="card-body">
            <h6 class="card-title">${periodData.period}</h6>
            <div class="row text-center">
              <div class="col-4">
                <small>Hadir</small>
                <div class="fw-bold">${periodData.hadir}</div>
              </div>
              <div class="col-4">
                <small>Izin</small>
                <div class="fw-bold">${periodData.izin}</div>
              </div>
              <div class="col-4">
                <small>Sakit</small>
                <div class="fw-bold">${periodData.sakit}</div>
              </div>
              <div class="col-4 mt-2">
                <small>Mangkir</small>
                <div class="fw-bold">${periodData.mangkir}</div>
              </div>
              <div class="col-4 mt-2">
                <small>Terlambat</small>
                <div class="fw-bold">${periodData.terlambat}m</div>
              </div>
              <div class="col-4 mt-2">
                <small>Aksi</small>
                <div>
                  <button type="button" class="btn btn-sm btn-outline-secondary btn-view-details" data-period="${periodData.period}">
                    <i class="icofont-eye text-success"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      `;
    });
    
    // Hancurkan DataTable jika sudah ada
    if (dataTable !== null) {
      dataTable.destroy();
      dataTable = null;
    }
    
    $('#attendanceTableBody').html(tableBody);
    $('#mobileAttendanceTable').html(mobileTableBody);
    
    // Inisialisasi DataTable
    dataTable = $('#myProjectTable').DataTable({
      responsive: {
        details: {
          display: $.fn.dataTable.Responsive.display.modal({
            header: function (row) {
              var data = row.data();
              return 'Detail Absensi Periode: ' + data[0];
            }
          }),
          renderer: $.fn.dataTable.Responsive.renderer.tableAll({
            tableClass: 'table'
          })
        }
      },
      columnDefs: [
        { targets: -1, orderable: false, searchable: false, className: 'dt-body-right all' }
      ],
      language: {
        search: "Cari:",
        lengthMenu: "Tampilkan _MENU_ entri",
        info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
        infoEmpty: "Menampilkan 0 sampai 0 dari 0 entri",
        infoFiltered: "(disaring dari _MAX_ total entri)",
        paginate: {
          first: "Pertama",
          last: "Terakhir",
          next: "Selanjutnya",
          previous: "Sebelumnya"
        }
      }
    });
    
    // Sembunyikan tabel desktop dan tampilkan mobile view pada layar kecil
    if ($(window).width() < 768) {
      $('#myProjectTable').closest('.table-responsive').hide();
      $('#mobileAttendanceTable').show();
    } else {
      $('#myProjectTable').closest('.table-responsive').show();
      $('#mobileAttendanceTable').hide();
    }
    
    // Tambahkan event listener untuk tombol lihat detail
    $('.btn-view-details').click(function() {
      const period = $(this).data('period');
      showAttendanceDetails(period, attendances);
    });
    
    // Handle window resize
    $(window).resize(function() {
      if ($(window).width() < 768) {
        $('#myProjectTable').closest('.table-responsive').hide();
        $('#mobileAttendanceTable').show();
      } else {
        $('#myProjectTable').closest('.table-responsive').show();
        $('#mobileAttendanceTable').hide();
      }
    });
  }

  // Fungsi untuk memperbarui informasi absensi
  function updateAttendanceInfo(summary, employee) {
    const totalDays = summary.hadir + summary.izin + summary.sakit + summary.mangkir;
    const attendanceRate = totalDays > 0 ? Math.round((summary.hadir / totalDays) * 100) : 0;
    
    const infoHtml = `
      <div class="row g-3">
        <div class="col-12">
          <div class="card shadow-sm text-center p-3 h-100">
            <h6 class="mb-1">Informasi Absensi</h6>
            <h5 class="fw-bold text-primary">${employee.nama}</h5>
          </div>
        </div>
        <div class="col-6">
          <div class="card shadow-sm text-center p-3 h-100">
            <h6 class="mb-1">Kehadiran</h6>
            <h3 class="fw-bold text-success">${attendanceRate}%</h3>
            <small class="text-muted">${summary.hadir} Hari Hadir</small>
          </div>
        </div>
        <div class="col-6">
          <div class="card shadow-sm text-center p-3 h-100">
            <h6 class="mb-1">Izin</h6>
            <h3 class="fw-bold text-warning">${summary.izin}</h3>
            <small class="text-muted">Total</small>
          </div>
        </div>
        <div class="col-6">
          <div class="card shadow-sm text-center p-3 h-100">
            <h6 class="mb-1">Sakit</h6>
            <h3 class="fw-bold text-info">${summary.sakit}</h3>
            <small class="text-muted">Total</small>
          </div>
        </div>
        <div class="col-6">
          <div class="card shadow-sm text-center p-3 h-100">
            <h6 class="mb-1">Mangkir</h6>
            <h3 class="fw-bold text-danger">${summary.mangkir}</h3>
            <small class="text-muted">Tanpa Keterangan</small>
          </div>
        </div>
      </div>
    `;
    
    $('#attendanceInfo').html(infoHtml);
  }

  // Fungsi untuk memperbarui grafik absensi berdasarkan periode
  function updateAttendanceChartByPeriod(attendances) {
    // Kelompokkan data per periode
    const periodData = {};
    
    attendances.forEach(attendance => {
      const period = attendance.period;
      
      if (!periodData[period]) {
        periodData[period] = {
          hadir: 0,
          izin: 0,
          sakit: 0,
          mangkir: 0
        };
      }
      
      // Hitung status kehadiran per periode
      switch(attendance.status) {
        case 'Present at workday (PW)':
          periodData[period].hadir++;
          break;
        case 'Permission (I)':
          periodData[period].izin++;
          break;
        case 'Sick (S)':
          periodData[period].sakit++;
          break;
        case 'Absent (A)':
          periodData[period].mangkir++;
          break;
      }
    });
    
    // Siapkan data untuk chart
    const categories = Object.keys(periodData);
    const hadirData = categories.map(period => periodData[period].hadir);
    const izinData = categories.map(period => periodData[period].izin);
    const sakitData = categories.map(period => periodData[period].sakit);
    const mangkirData = categories.map(period => periodData[period].mangkir);
    
    // Buat chart
    const absensiOpts = {
      chart: { 
        type: 'bar', 
        height: 260, 
        stacked: true,
        toolbar: { show: false }
      },
      series: [
        { name: 'Hadir', data: hadirData },
        { name: 'Izin', data: izinData },
        { name: 'Sakit', data: sakitData },
        { name: 'Mangkir', data: mangkirData }
      ],
      colors: ['#4CAF50', '#FFC107', '#2196F3', '#F44336'],
      xaxis: { 
        categories: categories,
        labels: {
          style: {
            fontSize: '12px'
          }
        }
      },
      yaxis: {
        title: {
          text: 'Jumlah Hari'
        }
      },
      dataLabels: { enabled: false },
      plotOptions: { 
        bar: { 
          columnWidth: '45%',
          borderRadius: 5
        } 
      },
      legend: {
        position: 'top',
        horizontalAlign: 'right'
      }
    };
    
    // Hapus chart lama jika ada dan buat yang baru
    const chartElement = document.querySelector('#chartAbsensi');
    chartElement.innerHTML = ''; // Clear previous chart
    
    new ApexCharts(chartElement, absensiOpts).render();
  }

  // Fungsi untuk menampilkan detail absensi dalam modal
  // Fungsi untuk menampilkan detail absensi dalam modal
function showAttendanceDetails(period, attendances) {
    // Filter absensi berdasarkan periode
    const periodAttendances = attendances.filter(a => a.period === period);
    
    let modalBody = '';
    
    periodAttendances.forEach(attendance => {
        // Format tanggal
        const date = new Date(attendance.date);
        const formattedDate = date.toLocaleDateString('id-ID', { 
            day: '2-digit', 
            month: 'short', 
            year: 'numeric' 
        });
        
        // Tentukan status dalam bahasa Indonesia
        let status = '';
        let statusClass = '';
        switch(attendance.status) {
            case 'Present at workday (PW)':
                status = 'Hadir';
                statusClass = 'text-success';
                break;
            case 'Permission (I)':
                status = 'Izin';
                statusClass = 'text-warning';
                break;
            case 'Sick (S)':
                status = 'Sakit';
                statusClass = 'text-info';
                break;
            case 'Absent (A)':
                status = 'Mangkir';
                statusClass = 'text-danger';
                break;
            default:
                status = attendance.status;
                statusClass = 'text-secondary';
        }
        
        // Format jam - coba beberapa field yang mungkin
        const clockIn = attendance.clock_in || 
                       attendance.daily_attendance_clock_in || 
                       (attendance.clock_in_time ? attendance.clock_in_time.substring(0, 5) : '-');
        
        const clockOut = attendance.clock_out || 
                        attendance.daily_attendance_clock_out || 
                        (attendance.clock_out_time ? attendance.clock_out_time.substring(0, 5) : '-');
        
        // Format durasi kerja
        let totalAttendance = attendance.total_attendance || '-';
        if (totalAttendance && totalAttendance !== '-') {
            // Jika format "HH:MM:SS", ambil hanya "HH:MM"
            if (totalAttendance.includes(':')) {
                const parts = totalAttendance.split(':');
                totalAttendance = `${parts[0]}:${parts[1]}`;
            }
        }
        
        modalBody += `
            <tr>
                <td>${formattedDate}</td>
                <td><span class="fw-bold ${statusClass}">${status}</span></td>
                <td>${clockIn}</td>
                <td>${clockOut}</td>
                <td>${totalAttendance}</td>
            </tr>
        `;
    });
    
    $('#modalAttendanceBody').html(modalBody);
    
    // Jika tidak ada data, tampilkan pesan
    if (periodAttendances.length === 0) {
        $('#modalAttendanceBody').html(`
            <tr>
                <td colspan="5" class="text-center text-muted py-3">
                    <i class="icofont-info-circle fs-4"></i>
                    <p class="mt-2 mb-0">Tidak ada data absensi untuk periode ini</p>
                </td>
            </tr>
        `);
    }
    
    $('#showabsen').modal('show');
}
</script>

<style>
  /* Responsivitas untuk tabel di mobile - PERBAIKAN */
  @media (max-width: 768px) {
    /* Perbaikan untuk memastikan semua header terlihat */
    .table-responsive {
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
      position: relative;
      display: block;
      width: 100%;
    }
    
    #myProjectTable {
      width: 100%;
      min-width: 650px; /* Lebar minimum agar semua kolom terlihat */
    }
    
    /* Pastikan header dan sel memiliki lebar yang sama */
    #myProjectTable th,
    #myProjectTable td {
      min-width: 80px;
      box-sizing: border-box;
      white-space: nowrap;
      padding: 8px 4px;
      font-size: 12px;
    }
    
    /* Atur lebar kolom spesifik agar sesuai dengan header */
    #myProjectTable th:nth-child(1),
    #myProjectTable td:nth-child(1) {
      min-width: 100px;
      width: 100px;
    }
    
    #myProjectTable th:nth-child(2),
    #myProjectTable td:nth-child(2) {
      min-width: 60px;
      width: 60px;
    }
    
    #myProjectTable th:nth-child(3),
    #myProjectTable td:nth-child(3) {
      min-width: 60px;
      width: 60px;
    }
    
    #myProjectTable th:nth-child(4),
    #myProjectTable td:nth-child(4) {
      min-width: 60px;
      width: 60px;
    }
    
    #myProjectTable th:nth-child(5),
    #myProjectTable td:nth-child(5) {
      min-width: 70px;
      width: 70px;
    }
    
    #myProjectTable th:nth-child(6),
    #myProjectTable td:nth-child(6) {
      min-width: 100px;
      width: 100px;
    }
    
    #myProjectTable th:nth-child(7),
    #myProjectTable td:nth-child(7) {
      min-width: 60px;
      width: 60px;
    }
    
    /* Perbaikan untuk header yang tetap terlihat saat discroll */
    #myProjectTable thead {
      position: sticky;
      top: 0;
      z-index: 10;
    }
    
    #myProjectTable thead th {
      background-color: #e9ecef;
      border-bottom: 2px solid #dee2e6;
      position: -webkit-sticky;
      position: sticky;
      top: 0;
    }
  }

  /* Untuk layar yang sangat kecil (kurang dari 400px) */
  @media (max-width: 400px) {
    #myProjectTable th,
    #myProjectTable td {
      font-size: 11px;
      padding: 6px 3px;
    }
    
    #myProjectTable th:nth-child(6),
    #myProjectTable td:nth-child(6) {
      min-width: 80px;
      width: 80px;
    }
  }
</style>
@endsection