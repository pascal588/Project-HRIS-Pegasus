@extends('template.template')

@section('title', 'Dashboard Penilai')

@section('content')
<div class="body d-flex">
  <div class="container-xxl">
    <div class="row g-3">

      <!-- HEADER SALAM -->
      <div class="col-12">
        <div class="card shadow-sm p-3 d-flex flex-row align-items-center">
          <img class="rounded-circle img-thumbnail me-3" 
               src="{{ Auth::user()->employee->foto ? asset('storage/' . Auth::user()->employee->foto) : asset('assets/images/profile_av.pngassets/images/xs/avatar2.jpg') }}" 
              alt="profile" style="width: 60px; height: 60px;"
              onerror="this.src='{{ asset('assets/images/profile_av.png') }}'">
          <div>
            <h4 class="mb-1">Hai, Ketua divisi 👋</h4>
            <small class="text-muted">Score terbaru kamu: <span class="fw-bold text-primary">B+</span></small>
          </div>
        </div>
      </div>

      <div class="row g-2">
  <!-- KIRI -->
  <div class="col-md-8">
    <div class="row g-3">
      <!-- KPI Terbaru -->
      <div class="col-md-6">
        <div class="card bg-primary text-white h-100 shadow-sm">
          <div class="card-body">
            <h6 class="fw-bold mb-3">Nilai KPI Terbaru</h6>
            <div class="d-flex justify-content-between align-items-center">
              <span class="avatar lg bg-white text-primary rounded-circle d-flex align-items-center justify-content-center">
                <i class="icofont-file-text fs-5"></i>
              </span>
              <h2 class="fw-bold mb-0">70,9</h2>
            </div>
            <span class="d-block text-end small">Nilai Sebelumnya: 20.20</span>
          </div>
        </div>
      </div>

      <!-- Performa -->
      <div class="col-md-6">
        <div class="card h-100 bg-primary text-white shadow-sm">
          <div class="card-body">
            <h6 class="fw-bold mb-3">Performa Anda</h6>
            <div class="d-flex justify-content-between align-items-center">
              <h4><span class="avatar lg rounded-circle text-primary bg-light d-flex align-items-center justify-content-center">A+</span></h4>
              <h3 class="fw-bold mb-0">Terbaik</h3>
            </div>
            <span class="d-block text-end small">Memuaskan</span>
          </div>
        </div>
      </div>

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
    </div>
  </div>

  <!-- KANAN -->
 <div class="col-md-4">
  <div class="card shadow-sm h-100 bg-light">
  <div class="card-body d-flex flex-column h-100">
  <h5 class="fw-bold mb-3">📢 Informasi</h5>
  <ul class="list-unstyled flex-grow-1 d-flex flex-column justify-content-between mb-0">
    <li class="d-flex flex-column justify-content-between p-3 bg-white rounded shadow-sm mb-3" style="height: 120px;">
  <div class="d-flex justify-content-between align-items-start mb-2">
    <div>
      <h6 class="fw-bold mb-1">Anda harus menilai</h6>
      <span class="text-muted small">Belum selesai</span>
    </div>
    <h4 class="fw-bold text-primary mb-0">20</h4>
  </div>
  <div class="d-flex justify-content-end mt-auto">
    <button class="btn btn-primary btn-sm" onclick="scrollToCard('belum-dinilai')">
      <i class="icofont-eye-alt me-1"></i> Lihat
    </button>
  </div>
</li>

<li class="d-flex flex-column justify-content-between p-3 bg-white rounded shadow-sm mb-3" style="height: 120px;">
  <div class="d-flex justify-content-between align-items-start mb-2">
    <div>
      <h6 class="fw-bold mb-1">Anda harus menegur</h6>
      <span class="text-muted small">Segera ditindak</span>
    </div>
    <h4 class="fw-bold text-danger mb-0">20</h4>
  </div>
  <div class="d-flex justify-content-end mt-auto">
    <button class="btn btn-primary btn-sm" onclick="scrollToCard('perlu-teguran')">
      <i class="icofont-eye-alt me-1"></i> Lihat
    </button>
  </div>
</li>

  </ul>
</div>

</div>

</div>



      <!-- GRAFIK & DETAIL KPI -->
      <div class="col-12">
        <div class="card shadow-sm p-3">
          <h6 class="mb-3 fw-bold">Grafik & Detail KPI</h6>
          <div class="row g-3">
            <div class="col-md-8 border-end">
              <div id="chartKPI" style="min-height:300px;"></div>
            </div>
            <div class="col-md-4">
              <div class="table-responsive">
                <div class="fw-bold">Detail nilai kpi terakhir</div>
                <table class="table table-sm table-striped mb-0">
                  <thead>
                    <tr>
                      <th>Aspek</th>
                      <th class="text-center">Skor</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr><td>Kedisiplinan</td><td class="text-center">90</td></tr>
                    <tr><td>Kompetensi Umum</td><td class="text-center">85</td></tr>
                    <tr><td>Teknikal</td><td class="text-center">88</td></tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- STATISTIK RATA2 DIVISI + LIST KARYAWAN -->
      <div class="col-12">
          <div class="row g-3">
            <div class="card shadow-sm bg-primary p-3 d-flex flex-row align-items-center">
          <div>
            <h5 class="mb-1 text-white fw-bold ">Kinerja Karyawan</h5>
          </div>
        </div>
             <!-- Card 1: Jumlah + Donut -->
    <div class="col-md-4">
    <div class="card shadow-sm mb-3">
        <div class="card-body text-center">
            <h6 class="fw-bold">Jumlah Karyawan Divisi</h6>
            <h3 class="fw-bold text-primary" id="employee-count">0</h3>
        </div>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <div id="gender-donut"></div>
        </div>
    </div>
</div>

    <!-- Card 2: Karyawan Belum Dinilai -->
    <div class="col-md-4">
      <div class="card shadow-sm" id="belum-dinilai">
        <div class="card-body">
          <h6 class="fw-bold mb-3"><i class="icofont-users-alt-2 me-2"></i>Karyawan Belum Dinilai</h6>
          <div style="max-height: 350px; overflow-y: auto; min-height: 350px;">
            <!-- list karyawan -->
            <div class="py-2 d-flex align-items-center border-bottom flex-wrap">
              <img class="avatar lg rounded-circle img-thumbnail" src="{{ asset('assets/images/xs/avatar2.jpg') }}" alt="profile">
              <div class="d-flex flex-column ps-3 flex-fill">
                <h6 class="fw-bold mb-0 small-14">Nama Karyawan</h6>
                <span class="text-muted">ID Karyawan</span>
              </div>
              <a class="btn btn-outline-warning btn-sm" href="#">Nilai</a>
            </div>
            <!-- duplikasi item sesuai kebutuhan -->
          </div>
        </div>
      </div>
    </div>

    <!-- Card 3: Karyawan Perlu Teguran -->
    <div class="col-md-4">
      <div class="card shadow-sm" id="perlu-teguran">
        <div class="card-body">
          <h6 class="fw-bold mb-3"><i class="icofont-users-alt-2 me-2"></i>Karyawan Perlu Teguran</h6>
        </div>
        <div class="card-body" style="max-height: 350px; overflow-y: auto; min-height: 350px;">
          <!-- list karyawan -->
          <div class="py-2 d-flex align-items-center border-bottom flex-wrap">
            <img class="avatar lg rounded-circle img-thumbnail" src="{{ asset('assets/images/xs/avatar2.jpg') }}" alt="profile">
            <div class="d-flex flex-column ps-3 flex-fill">
              <h6 class="fw-bold mb-0 small-14">Nama Karyawan</h6>
              <span class="text-muted">ID Karyawan</span>
            </div>
            <a class="btn btn-outline-success btn-sm" href="#"><i class="icofont-brand-whatsapp"></i></a>
          </div>
          <div class="py-2 d-flex align-items-center border-bottom flex-wrap">
            <img class="avatar lg rounded-circle img-thumbnail" src="{{ asset('assets/images/xs/avatar2.jpg') }}" alt="profile">
            <div class="d-flex flex-column ps-3 flex-fill">
              <h6 class="fw-bold mb-0 small-14">Nama Karyawan</h6>
              <span class="text-muted">ID Karyawan</span>
            </div>
            <a class="btn btn-outline-success btn-sm" href="#"><i class="icofont-brand-whatsapp"></i></a>
          </div>
          <div class="py-2 d-flex align-items-center border-bottom flex-wrap">
            <img class="avatar lg rounded-circle img-thumbnail" src="{{ asset('assets/images/xs/avatar2.jpg') }}" alt="profile">
            <div class="d-flex flex-column ps-3 flex-fill">
              <h6 class="fw-bold mb-0 small-14">Nama Karyawan</h6>
              <span class="text-muted">ID Karyawan</span>
            </div>
            <a class="btn btn-outline-success btn-sm" href="#"><i class="icofont-brand-whatsapp"></i></a>
          </div>
          <div class="py-2 d-flex align-items-center border-bottom flex-wrap">
            <img class="avatar lg rounded-circle img-thumbnail" src="{{ asset('assets/images/xs/avatar2.jpg') }}" alt="profile">
            <div class="d-flex flex-column ps-3 flex-fill">
              <h6 class="fw-bold mb-0 small-14">Nama Karyawan</h6>
              <span class="text-muted">ID Karyawan</span>
            </div>
            <a class="btn btn-outline-success btn-sm" href="#"><i class="icofont-brand-whatsapp"></i></a>
          </div>
          <div class="py-2 d-flex align-items-center border-bottom flex-wrap">
            <img class="avatar lg rounded-circle img-thumbnail" src="{{ asset('assets/images/xs/avatar2.jpg') }}" alt="profile">
            <div class="d-flex flex-column ps-3 flex-fill">
              <h6 class="fw-bold mb-0 small-14">Nama Karyawan</h6>
              <span class="text-muted">ID Karyawan</span>
            </div>
            <a class="btn btn-outline-success btn-sm" href="#"><i class="icofont-brand-whatsapp"></i></a>
          </div>
          <div class="py-2 d-flex align-items-center border-bottom flex-wrap">
            <img class="avatar lg rounded-circle img-thumbnail" src="{{ asset('assets/images/xs/avatar2.jpg') }}" alt="profile">
            <div class="d-flex flex-column ps-3 flex-fill">
              <h6 class="fw-bold mb-0 small-14">Nama Karyawan</h6>
              <span class="text-muted">ID Karyawan</span>
            </div>
            <a class="btn btn-outline-success btn-sm" href="#"><i class="icofont-brand-whatsapp"></i></a>
          </div>
          <div class="py-2 d-flex align-items-center border-bottom flex-wrap">
            <img class="avatar lg rounded-circle img-thumbnail" src="{{ asset('assets/images/xs/avatar2.jpg') }}" alt="profile">
            <div class="d-flex flex-column ps-3 flex-fill">
              <h6 class="fw-bold mb-0 small-14">Nama Karyawan</h6>
              <span class="text-muted">ID Karyawan</span>
            </div>
            <a class="btn btn-outline-success btn-sm" href="#"><i class="icofont-brand-whatsapp"></i></a>
          </div>
          <!-- duplikasi item sesuai kebutuhan -->
        </div>
      </div>
    </div>

          </div>

          <!-- KPI DIVISI -->
        <div class="col-12 mt-4">
          <div class="card shadow-sm">
            <div class="card-body">
              <h6 class="fw-bold mb-3" id="judulDivisi">Rata-rata KPI Bulanan — Divisi IT</h6>
              <canvas id="kpiBarChart" height="100"></canvas>
            </div>
          </div>
        </div>
        </div>
        
      </div>

    </div><!-- row end -->
  </div>
  </div>


@endsection

@section('script')
<!-- Plugin Js-->
<script src="{{asset('assets/bundles/apexcharts.bundle.js')}}"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// ======================
// KONFIG DATA & BOBOT
// ======================
const namaDivisi = "IT"; // ganti sesuai kebutuhan
document.getElementById("judulDivisi").textContent = `Rata-rata KPI Bulanan — Divisi ${namaDivisi}`;

const bobot = { kehadiran: 0.4, disiplin: 0.3, teknis: 0.2, umum: 0.1 };

// Data dummy skala 0–4 per bulan (ganti ke data real kamu)
const dataKPI = [
  { bulan: "Jan", kehadiran: 3.6, disiplin: 3.4, teknis: 3.2, umum: 3.8 },
  { bulan: "Feb", kehadiran: 2.7, disiplin: 2.6, teknis: 3.3, umum: 2.9 },
  { bulan: "Mar", kehadiran: 1.8, disiplin: 1.6, teknis: 1.5, umum: 1.8 },
  { bulan: "Apr", kehadiran: 0.9, disiplin: 0.7, teknis: 0.6, umum: 0.9 },
  { bulan: "May", kehadiran: 3.7, disiplin: 3.8, teknis: 3.4, umum: 3.9 },
  { bulan: "jun", kehadiran: 3.7, disiplin: 3.8, teknis: 3.4, umum: 3.9 },
  { bulan: "jul", kehadiran: 3.7, disiplin: 3.8, teknis: 3.4, umum: 3.9 },
  { bulan: "aug", kehadiran: 3.7, disiplin: 3.8, teknis: 3.4, umum: 3.9 },
  { bulan: "sept", kehadiran: 3.7, disiplin: 3.8, teknis: 3.4, umum: 3.9 },
];

// ======================
// HITUNG RATA-RATA BOBOT
// ======================
const labels = dataKPI.map(i => i.bulan);
const overall = dataKPI.map(i => (
  i.kehadiran * bobot.kehadiran +
  i.disiplin   * bobot.disiplin +
  i.teknis     * bobot.teknis +
  i.umum       * bobot.umum
).toFixed(2));

// ======================
// RENDER BAR CHART
// ======================
const ctx = document.getElementById('kpiBarChart').getContext('2d');
new Chart(ctx, {
  type: 'bar',
  data: {
    labels,
    datasets: [{
      label: 'Skor KPI (0–4)',
      data: overall,
      borderWidth: 1,
      // biarin Chart.js pilih warna default; bisa di-custom kalau mau
    }]
  },
  options: {
    responsive: true,
    scales: {
      y: {
        beginAtZero: true,
        max: 4,
        ticks: { stepSize: 0.5 },
        title: { display: true, text: 'Skor' }
      },
      x: {
        title: { display: true, text: 'Bulan' }
      }
    },
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          // tooltip juga tampilkan breakdown per topik
          afterBody: (items) => {
            const idx = items[0].dataIndex;
            const d = dataKPI[idx];
            return [
              `Kehadiran: ${d.kehadiran}`,
              `Disiplin: ${d.disiplin}`,
              `Teknis: ${d.teknis}`,
              `Umum: ${d.umum}`
            ];
          }
        }
      },
      title: { display: true, text: `KPI Bulanan Divisi ${namaDivisi}` }
    }
  }
});
</script>

<script>
// Fungsi untuk mengambil data divisi
async function loadDivisionData() {
    try {
        // Ganti dengan ID divisi user yang login
        const divisionId = {{ Auth::user()->employee->roles->first()->division_id ?? 0 }};
        
        if (divisionId === 0) {
            console.error('Divisi tidak ditemukan');
            return;
        }

        // Ambil jumlah karyawan
        const countResponse = await fetch(`/api/divisions/${divisionId}/employee-count`);
        const countData = await countResponse.json();
        
        if (countData.success) {
            document.getElementById('employee-count').textContent = countData.data;
        }

        // Ambil data gender untuk donut chart
        const genderResponse = await fetch(`/api/divisions/${divisionId}/gender-data`);
        const genderData = await genderResponse.json();
        
        if (genderData.success) {
            renderGenderDonutChart(genderData.data);
        }

    } catch (error) {
        console.error('Error loading division data:', error);
    }
}

// Fungsi untuk render donut chart gender
function renderGenderDonutChart(genderData) {
    // Siapkan data untuk chart
    const series = [];
    const labels = [];
    const colors = ['#007bff', '#ff4081']; // Warna untuk Pria dan Wanita

    // Process data dari API
    genderData.forEach(item => {
        labels.push(item.gender);
        series.push(item.count);
    });

    // Konfigurasi donut chart
    var options = {
        chart: {
            type: 'donut',
            height: 300
        },
        series: series,
        labels: labels,
        colors: colors,
        legend: {
            position: 'bottom'
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '65%',
                    labels: {
                        show: true,
                        total: {
                            show: true,
                            label: 'Total',
                            color: '#373d3f',
                            formatter: function (w) {
                                return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                            }
                        }
                    }
                }
            }
        },
        responsive: [{
            breakpoint: 768,
            options: {
                chart: {
                    height: 200
                },
                legend: {
                    position: 'bottom'
                }
            }
        }]
    };

    // Render chart
    var chart = new ApexCharts(document.querySelector("#gender-donut"), options);
    chart.render();
}

// Panggil fungsi saat halaman dimuat
document.addEventListener('DOMContentLoaded', loadDivisionData);

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
          document.getElementById('lateCount').textContent = data.summary.jumlah_terlambat || 0;
          
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

<script>
    var options = {
        chart: {
        type: 'line',
        height: 300
        },
        series: [{
        name: 'Score KPI',
        data: [78, 82, 85, 90, 87, 92, 88, 89, 90, 91, 90, 89] // contoh data tiap bulan
        }],
        xaxis: {
        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']
        }
    };

    var chart = new ApexCharts(document.querySelector("#chartKPI"), options);
    chart.render();
    </script>
@endsection