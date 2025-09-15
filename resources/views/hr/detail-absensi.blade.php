@extends('template.template')

@section('title', 'Detail Absen')

@section('content')
<style>
    .stat-card {
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 20px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        text-align: center;
        height: 100%;
    }
    .stat-card .icon {
        font-size: 2rem;
        margin-bottom: 10px;
    }
    .stat-card .title {
        font-size: 0.9rem;
        color: #6c757d;
    }
    .stat-card .value {
        font-size: 1.5rem;
        font-weight: bold;
    }
    .detail-table th {
        background-color: #f8f9fa;
        font-weight: 600;
        white-space: nowrap;
    }
    @media (max-width: 992px) {
        .stat-card .value { font-size: 1.3rem; }
        .stat-card .icon { font-size: 1.7rem; }
    }
    @media (max-width: 768px) {
        .stat-card .value { font-size: 1.2rem; }
        .stat-card .icon { font-size: 1.5rem; }
        .card-body h4 { font-size: 1.2rem; }
        .card-body span { display: block; margin-bottom: 4px; }
    }
    @media (max-width: 576px) {
        .stat-card { padding: 10px; }
        .stat-card .icon { font-size: 1.2rem; margin-bottom: 4px; }
        .stat-card .title { font-size: 0.7rem; }
        .stat-card .value { font-size: 1rem; }
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
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>

<div class="body d-flex py-3">
    <div class="container-xxl">
        <!-- Header Info -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex flex-wrap align-items-start justify-content-between">
                            <div class="mb-3 mb-md-0">
                                <h4 class="fw-bold mb-2">Detail Absensi Karyawan</h4>
                                <div class="d-flex flex-wrap" id="employee-info">
                                    <span class="me-3"><strong>Nama:</strong> <span id="emp-name">Loading...</span></span>
                                    <span class="me-3"><strong>ID Karyawan:</strong> <span id="emp-id">Loading...</span></span>
                                    <span class="me-3"><strong>Divisi:</strong> <span id="emp-division">Loading...</span></span>
                                    <span><strong>Jabatan:</strong> <span id="emp-position">Loading...</span></span>
                                </div>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-primary dropdown-toggle" type="button" id="periodeDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span id="selected-period-text">Pilih Periode</span>
                                    <span id="loading-spinner" class="loading-spinner ms-2 d-none"></span>
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="periodeDropdown" id="period-list">
                                    <li><h6 class="dropdown-header">Pilih Periode</h6></li>
                                    <li><a class="dropdown-item period-option" href="#" data-period="">Semua Periode</a></li>
                                    <!-- Period options will be loaded by JavaScript -->
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistik Ringkasan - Diperbaiki untuk 5 kartu sejajar -->
      <div class="row row-cols-2 row-cols-md-5 mb-4 text-center">
    <!-- Hadir full di HP -->
    <div class="col-12 mb-3">
        <div class="stat-card bg-primary text-white">
            <div class="icon"><i class="icofont-checked"></i></div>
            <div class="title">Hadir</div>
            <div class="value" id="summary-hadir">0 Hari</div>
        </div>
    </div>

    <!-- Sisanya otomatis 2 kolom di HP, 5 kolom di laptop -->
    <div class="col mb-3">
        <div class="stat-card bg-primary text-white">
            <div class="icon"><i class="icofont-beach-bed"></i></div>
            <div class="title">Izin</div>
            <div class="value" id="summary-izin">0 Hari</div>
        </div>
    </div>

    <div class="col mb-3">
        <div class="stat-card bg-primary text-white">
            <div class="icon"><i class="icofont-medical-sign"></i></div>
            <div class="title">Sakit</div>
            <div class="value" id="summary-sakit">0 Hari</div>
        </div>
    </div>

    <div class="col mb-3">
        <div class="stat-card bg-primary text-white">
            <div class="icon"><i class="icofont-ban"></i></div>
            <div class="title">Mangkir</div>
            <div class="value" id="summary-mangkir">0 Hari</div>
        </div>
    </div>

    <div class="col mb-3">
        <div class="stat-card bg-primary text-white">
            <div class="icon"><i class="icofont-stopwatch"></i></div>
            <div class="title">Terlambat</div>
            <div class="value" id="summary-terlambat">0 Kali</div>
        </div>
    </div>
</div>

        <!-- Tabel Detail Absensi -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                        <h5 class="card-title mb-2 mb-md-0">
                            Rincian Absensi Harian 
                            <span id="period-display" class="text-muted fs-6 ms-2"></span>
                        </h5>
                        <div>
                            <button class="btn btn-sm btn-outline-secondary me-2" id="refresh-btn">
                                <i class="icofont-refresh"></i> Refresh
                            </button>
                            {{-- <button class="btn btn-sm btn-primary" id="export-btn">Export Excel</button> --}}
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table detail-table table-bordered table-hover" id="attendance-detail-table">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Tanggal</th>
                                        <th>Hari</th>
                                        <th>Status</th>
                                        <th>Jam Masuk</th>
                                        <th>Jam Keluar</th>
                                        <th>Lama Kerja</th>
                                        <th>Terlambat</th>
                                        <th>Pulang Cepat</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="10" class="text-center">Memuat data...</td>
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
@endsection

@section('script')
<script>
$(document).ready(function() {
    const employeeId = "{{ $employee_id }}"; // Get employee_id from route parameter
    let currentPeriod = "";
    
    // Show/hide loading spinner
    function setLoading(isLoading) {
        if (isLoading) {
            $('#loading-spinner').removeClass('d-none');
            $('#periodeDropdown').prop('disabled', true);
        } else {
            $('#loading-spinner').addClass('d-none');
            $('#periodeDropdown').prop('disabled', false);
        }
    }
    
    // Format date to Indonesian format
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    }
    
    // Load employee attendance data
    function loadEmployeeAttendance(period = "") {
        setLoading(true);
        
        $.ajax({
            url: `/api/attendances/employee/${employeeId}`,
            data: { period: period },
            success: function(response) {
                if (response.success) {
                    
                    // Update employee info
                    $('#emp-name').text(response.employee.nama);
                    $('#emp-id').text(response.employee.id_karyawan);
                    $('#emp-division').text(response.employee.roles[0]?.division?.nama_divisi || '-');
                    // Tampilkan semua jabatan (jika ada lebih dari satu)
                    const positions = response.employee.roles.map(role => role.nama_jabatan).join(', ');
                    $('#emp-position').text(positions || '-');
                    
                    // Update summary
                    $('#summary-hadir').text(response.summary.hadir + ' Hari');
                    $('#summary-izin').text(response.summary.izin + ' Hari');
                    $('#summary-sakit').text(response.summary.sakit + ' Hari');
                    $('#summary-mangkir').text(response.summary.mangkir + ' Hari');
                    $('#summary-terlambat').text(response.summary.jumlah_terlambat + ' Kali');
                    
                    // Update period dropdown
                    $('#period-list').html(`
                        <li><h6 class="dropdown-header">Pilih Periode</h6></li>
                        <li><a class="dropdown-item period-option" href="#" data-period="">Semua Periode</a></li>
                    `);
                    
                    response.periods.forEach(period => {
                        $('#period-list').append(`
                            <li><a class="dropdown-item period-option" href="#" data-period="${period}">${period}</a></li>
                        `);
                    });
                    
                    // Update selected period text
                    if (period) {
                        $('#selected-period-text').text(period);
                        $('#period-display').text(`(Periode: ${period})`);
                    } else {
                        $('#selected-period-text').text('Semua Periode');
                        $('#period-display').text('(Semua Periode)');
                    }
                    
                    // Update attendance table
                    const tbody = $('#attendance-detail-table tbody');
                    tbody.empty();
                    
                    if (response.attendances.length > 0) {
                        let counter = 1; // Counter untuk nomor urut
                        response.attendances.forEach(attendance => {
                            const date = new Date(attendance.date);
                            const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                            const dayName = dayNames[date.getDay()];
                            
                            // Determine status badge
                            let statusBadge = '';
                            if (attendance.status === 'Present at workday (PW)') {
                                statusBadge = '<span class="badge bg-success">Hadir</span>';
                            } else if (attendance.status === 'Permission (I)') {
                                statusBadge = '<span class="badge bg-warning text-dark">Izin</span>';
                            } else if (attendance.status === 'Sick (S)') {
                                statusBadge = '<span class="badge bg-info">Sakit</span>';
                            } else if (attendance.status === 'Absent (A)') {
                                statusBadge = '<span class="badge bg-danger">Mangkir</span>';
                            } else {
                                statusBadge = `<span class="badge bg-secondary">${attendance.status}</span>`;
                            }
                           
                            tbody.append(`
                                <tr>
                                    <td>${counter}</td>
                                    <td>${formatDate(attendance.date)}</td>
                                    <td>${dayName}</td>
                                    <td>${statusBadge}</td>
                                    <td>${attendance.clock_in || attendance.daily_attendance_clock_in || '-'}</td>
                                    <td>${attendance.clock_out || attendance.daily_attendance_clock_out || '-'}</td>
                                    <td>${attendance.total_attendance || '-'}</td>
                                    <td>${attendance.late ? attendance.late + ' menit' : '-'}</td>
                                    <td>${attendance.early_leave ? attendance.early_leave + ' menit' : '-'}</td>
                                    <td>
                                        ${attendance.status === 'Non-working day (NW)' ? 'Hari Libur' : ''}
                                        ${attendance.status === 'Permission (I)' ? 'Izin' : ''}
                                        ${attendance.status === 'Sick (S)' ? 'Sakit' : ''}
                                    </td>
                                </tr>
                            `);
                            counter++; // Increment counter
                        });
                    } else {
                        tbody.append(`
                            <tr>
                                <td colspan="10" class="text-center">Tidak ada data absensi untuk periode yang dipilih</td>
                            </tr>
                        `);
                    }
                }
                setLoading(false);
            },
            error: function(xhr, status, error) {
                console.error('Error details:', xhr.responseJSON);
                alert('Error loading attendance data: ' + (xhr.responseJSON?.message || error));
                setLoading(false);
                
                // Show error in table
                const tbody = $('#attendance-detail-table tbody');
                tbody.html(`
                    <tr>
                        <td colspan="10" class="text-center text-danger">
                            Gagal memuat data. Silakan refresh halaman.
                        </td>
                    </tr>
                `);
            }
        });
    }
    
    // Handle period selection
    $(document).on('click', '.period-option', function(e) {
        e.preventDefault();
        const period = $(this).data('period');
        currentPeriod = period;
        loadEmployeeAttendance(period);
    });
    
    // refresh button
    $('#refresh-btn').click(function() {
        loadEmployeeAttendance(currentPeriod);
    });
    
    // Initial load
    loadEmployeeAttendance();
});
</script>
@endsection