@extends('template.template')

@section('title', 'Dashboard Karyawan')

@section('content')
<div class="body d-flex">
  <div class="container-xxl">
    <div class="row g-3">

      <!-- Header Salam -->
      <div class="col-12">
        <div class="card shadow-sm p-3 d-flex flex-row align-items-center">
          <img class="rounded-circle img-thumbnail me-3" 
               src="{{ Auth::user()->employee->foto ? asset('storage/' . Auth::user()->employee->foto) : asset('assets/images/profile_av.pngassets/images/xs/avatar2.jpg') }}" 
              alt="profile" style="width: 60px; height: 60px;"
              onerror="this.src='{{ asset('assets/images/profile_av.png') }}'">
          <div>
            <h4 class="mb-1">Hai, {{ Auth::user()->Employee->nama }} 👋</h4>
            <small class="text-muted">Score terbaru kamu: <span class="fw-bold text-primary">B+</span></small>
          </div>
        </div>
      </div>

      <!-- Ringkasan KPI -->
      <div class="col-md-4">
        <div class="card bg-primary text-white h-100 shadow-sm">
          <div class="card-body">
            <h6 class="fw-bold mb-3">Nilai KPI Terbaru</h6>
            <div class="d-flex justify-content-between align-items-center">
              <span class="avatar lg bg-white text-primary rounded-circle d-flex align-items-center justify-content-center">
                <i class="icofont-file-text fs-5"></i>
              </span>
              <h2 class="fw-bold">70,9</h2>
            </div>
            <span class="d-block text-end small">Nilai Sebelumnya: 20.20</span>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card h-100 shadow-sm">
          <div class="card-body">
            <h6 class="fw-bold mb-3">Performa Anda</h6>
            <div class="d-flex justify-content-between align-items-center">
              <h4><span class="avatar lg rounded-circle bg-primary text-white d-flex align-items-center justify-content-center">A+</span></h4>
              <h3 class="fw-bold mb-0">Terbaik</h3>
            </div>
            <span class="d-block text-end small text-muted">Memuaskan</span>
          </div>
        </div>
      </div>

      <div class="col-md-4 ">
        <div class="card bg-primary text-white h-100 shadow-sm">
          <div class="card-body">
            <h6 class="fw-bold mb-3">Ranking Anda</h6>
            <div class="d-flex justify-content-between align-items-center">
              <span class="avatar lg bg-white text-primary rounded-circle d-flex align-items-center justify-content-center">
                <i class="icofont-chart-line fs-4"></i>
              </span>
              <h2 class="fw-bold mb-0">5/20</h2>
            </div>
            <span class="d-block text-end small">Meningkat 20</span>
          </div>
        </div>
      </div>

      <!-- Kiri (8 kolom) -->
      <div class="col-xl-8 col-lg-12">
        <div class="row g-3">

        {{-- absensi --}}
        <div class="col-12">
          <div class="card mb-3">
            <div class="card-header bg-transparent border-bottom-0 d-flex justify-content-between align-items-center">
              <h6 class="mb-0 fw-bold">Histori Absensi</h6>
              <!-- Hapus dropdown pemilihan periode -->
            </div>
            <div class="card-body">
              <div id="attendanceLoading" class="text-center py-3">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Memuat data absensi...</p>
              </div>
              <div id="attendanceError" class="alert alert-danger d-none" role="alert"></div>
              <div id="attendanceContent" class="d-none">
                <div class="row g-2">
                  <div class="col-3">
                    <div class="card text-center p-2">
                      <i class="icofont-checked fs-3 text-success"></i>
                      <h6 class="fw-bold small mt-2 mb-0">Hadir</h6>
                      <span class="text-muted" id="presentCount">0</span>
                    </div>
                  </div>
                  <div class="col-3">
                    <div class="card text-center p-2">
                      <i class="icofont-ban fs-3 text-danger"></i>
                      <h6 class="fw-bold small mt-2 mb-0">Mangkir</h6>
                      <span class="text-muted" id="absentCount">0</span>
                    </div>
                  </div>
                  <div class="col-3">
                    <div class="card text-center p-2">
                      <i class="icofont-beach-bed fs-3 text-warning"></i>
                      <h6 class="fw-bold small mt-2 mb-0">Izin/Cuti</h6>
                      <span class="text-muted" id="permissionCount">0</span>
                    </div>
                  </div>
                  <div class="col-3">
                    <div class="card text-center p-2">
                      <i class="icofont-stopwatch fs-3 text-primary"></i>
                      <h6 class="fw-bold small mt-2 mb-0">Terlambat</h6>
                      <span class="text-muted" id="lateCount">0</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

          <!-- Grafik KPI -->
          <div class="col-12">
            <div class="card shadow-sm p-3">
              <h6 class="mb-3">Grafik Tren KPI</h6>
              <div id="chartKpi" style="min-height:300px;"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Kanan (4 kolom) -->
      <div class="col-xl-4 col-lg-12">

        <!-- Top Karyawan -->
        <div class="card p-3">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="fw-bold mb-0">Top Karyawan</h6>
            <span class="text-muted small">Agustus</span>
          </div>

          <div class="list-group list-group-flush" style="max-height: 250px; overflow-y: auto;">
            
            <div class="list-group-item d-flex align-items-center">
              <img class="avatar rounded-circle me-3" 
                  src="{{ asset('assets/images/lg/avatar9.jpg') }}" 
                  alt="profile" width="40" height="40">
              
              <!-- Nama dibuat fleksibel -->
              <div class="flex-fill" style="min-width: 0;">
                <h6 class="mb-0 small-14 fw-bold text-truncate">
                  Paul Rees Nughroho Sangat Panjang adjoijawoijwa oijwadoawjijdiwao wiadjiowajojwo
                </h6>
              </div>

              <div class="ms-2" style="flex-shrink: 0; min-width: 50px; text-align: right;">
                <span class="fw-bold text-primary">77%</span>
              </div>
            </div>

            <div class="list-group-item d-flex align-items-center">
              <img class="avatar rounded-circle me-3" 
                  src="{{ asset('assets/images/lg/avatar9.jpg') }}" 
                  alt="profile" width="40" height="40">
              
              <div class="flex-fill" style="min-width: 0;">
                <h6 class="mb-0 small-14 fw-bold text-truncate">
                  John Doe Nur Insani
                </h6>
              </div>

              <div class="ms-2" style="flex-shrink: 0; min-width: 50px; text-align: right;">
                <span class="fw-bold text-primary">75%</span>
              </div>
            </div>

            <div class="list-group-item d-flex align-items-center">
              <img class="avatar rounded-circle me-3" 
                  src="{{ asset('assets/images/lg/avatar9.jpg') }}" 
                  alt="profile" width="40" height="40">
              
              <div class="flex-fill" style="min-width: 0;">
                <h6 class="mb-0 small-14 fw-bold text-truncate">
                  John Doe Nur Insani
                </h6>
              </div>

              <div class="ms-2" style="flex-shrink: 0; min-width: 50px; text-align: right;">
                <span class="fw-bold text-primary">75%</span>
              </div>
            </div>

          </div>
        </div>

        
        <!-- Detail KPI -->
        <div class="card mb-3 mt-3">
          <div class="card-body">
            <h6 class="mb-3">Detail KPI</h6>
            <table class="table table-sm table-striped mb-0">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Aspek</th>
                  <th>Skor</th>
                  <th>Keterangan</th>
                </tr>
              </thead>
              <tbody>
                <tr><td>1</td><td>Kedisiplinan</td><td>90</td><td>Sangat Baik</td></tr>
                <tr><td>2</td><td>Kompetensi Umum</td><td>85</td><td>Baik</td></tr>
                <tr><td>3</td><td>Teknikal</td><td>88</td><td>Baik</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div><!-- Row End -->
  </div>
</div>
@endsection

@section('script')
    <script src="{{ asset('assets/bundles/apexcharts.bundle.js') }}"></script>
    <script>
  var options = {
    chart: { type: 'line', height: 300 },
    series: [{
      name: 'Skor KPI',
      data: [78, 82, 80, 85, 87, 90, 88, 87, 85, 89, 92, 87]
    }],
    xaxis: {
      categories: ['Sep', 'Okt', 'Nov', 'Des', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu']
    },
    colors: ['#28a745']
  };
  var chart = new ApexCharts(document.querySelector("#chartKpi"), options);
  chart.render();

  // Fungsi untuk mengambil data absensi
  function fetchAttendanceData() {
    const employeeId = '{{ Auth::user()->employee->id_karyawan }}';
    let url = `/api/attendances/employee/${employeeId}`;
    
    // Tampilkan loading, sembunyikan error dan content
    document.getElementById('attendanceLoading').classList.remove('d-none');
    document.getElementById('attendanceError').classList.add('d-none');
    document.getElementById('attendanceContent').classList.add('d-none');
    
    fetch(url)
      .then(response => {
        if (!response.ok) {
          throw new Error('Network response was not ok');
        }
        return response.json();
      })
      .then(data => {
        if (data.success) {
          // Update data absensi
          document.getElementById('presentCount').textContent = data.summary.hadir || 0;
          document.getElementById('absentCount').textContent = data.summary.mangkir || 0;
          document.getElementById('permissionCount').textContent = data.summary.izin || 0;
          document.getElementById('lateCount').textContent = data.summary.terlambat || 0;
          
          // Sembunyikan loading, tampilkan content
          document.getElementById('attendanceLoading').classList.add('d-none');
          document.getElementById('attendanceContent').classList.remove('d-none');
        } else {
          throw new Error(data.message || 'Failed to fetch attendance data');
        }
      })
      .catch(error => {
        console.error('Error fetching attendance data:', error);
        document.getElementById('attendanceLoading').classList.add('d-none');
        document.getElementById('attendanceError').classList.remove('d-none');
        document.getElementById('attendanceError').textContent = 'Gagal memuat data absensi: ' + error.message;
      });
  }

  // Ambil data absensi saat halaman dimuat
  document.addEventListener('DOMContentLoaded', function() {
    fetchAttendanceData();
  });
</script>
@endsection