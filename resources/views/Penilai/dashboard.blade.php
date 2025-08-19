@extends('penilai.template')

@section('title', 'Dashboard Penilai')

@section('content')
{{-- <link rel="stylesheet" href="{{ asset('css/my-task.style.min.css') }}"> --}}
     <div class="body d-flex py-3">
            <div class="container-xxl">
                <div class="row clearfix g-3">
                    <div class="col-xl-8 col-lg-12 col-md-12 flex-column">
                        <div class="row g-3">
                    <div class="col-md-12">
                        <div class="card bg-primary text-white p-3">
                        <h2>Hai Karyawan</h2>
                        <h6>Score Kamu Yang terbaru B+</h6>
                        </div>
                    </div> 
                    <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header py-3 d-flex justify-content-between bg-transparent border-bottom-0">
                                        <h6 class="mb-0 fw-bold ">KPI anda</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="ac-line-transparent" id="apex-emplyoeeAnalytics"></div>
                                    </div>
                                </div>
                            </div>
                         <!-- top karyawan -->
                            <div class="col-md-6 col-lg-6 col-xl-6">
                             <div class="card">
                                <div class="card-header">
                                    <h5>Statistik Karyawan</h5>
                                </div>
                                <div class="card-body">
                                    <!-- Chart akan muncul di sini -->
                                    <div id="apex-MainCategories"></div>
                                </div>
                            </div>
    
            </div>
                    <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header py-3 d-flex justify-content-between bg-transparent border-bottom-0">
                                        <h6 class="mb-0 fw-bold ">Absensi Anda</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-2 row-deck">
                                            <div class="col-md-6 col-sm-6">
                                                <div class="card">
                                                    <div class="card-body ">
                                                        <i class="icofont-checked fs-3"></i>
                                                        <h6 class="mt-3 mb-0 fw-bold small-14">Hadir</h6>
                                                        <span class="text-muted">400</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-sm-6">
                                                <div class="card">
                                                    <div class="card-body ">
                                                            <i class="icofont-stopwatch fs-3"></i>
                                                        <h6 class="mt-3 mb-0 fw-bold small-14">Terlambat</h6>
                                                        <span class="text-muted">17</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-sm-6">
                                                <div class="card">
                                                    <div class="card-body ">
                                                            <i class="icofont-ban fs-3"></i>
                                                        <h6 class="mt-3 mb-0 fw-bold small-14">Mangkir</h6>
                                                        <span class="text-muted">06</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-sm-6">
                                                <div class="card">
                                                    <div class="card-body ">
                                                        <i class="icofont-beach-bed fs-3"></i>
                                                        <h6 class="mt-3 mb-0 fw-bold small-14">Izin/Cuti</h6>
                                                        <span class="text-muted">14</span> 
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
         <div class="col-md-12">
                                <!-- Card / Container -->
<div class="card">
  <div class="card-body">
    <h5 class="fw-bold mb-3" id="judulDivisi">Rata-rata KPI Bulanan — Divisi IT</h5>
    <canvas id="kpiBarChart" height="120"></canvas>
  </div>
</div>

<!-- Chart.js CDN -->
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
  { bulan: "2025-01", kehadiran: 3.6, disiplin: 3.4, teknis: 3.2, umum: 3.8 },
  { bulan: "2025-02", kehadiran: 2.7, disiplin: 2.6, teknis: 3.3, umum: 2.9 },
  { bulan: "2025-03", kehadiran: 1.8, disiplin: 1.6, teknis: 1.5, umum: 1.8 },
  { bulan: "2025-04", kehadiran: 0.9, disiplin: 0.7, teknis: 0.6, umum: 0.9 },
  { bulan: "2025-05", kehadiran: 3.7, disiplin: 3.8, teknis: 3.4, umum: 3.9 }
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

                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-12 col-md-12">
                        <div class="row g-3 row-deck">
                             <div class="col-md-6 col-lg-6 col-xl-12">
                                <div class="card bg-primary">
                                    <div class="card-body row">
                                        <div class="col">
                                            <span class="avatar lg bg-white rounded-circle text-center d-flex align-items-center justify-content-center"><i class="icofont-file-text fs-5"></i></span>
                                            <h6 class="mt-3 mb-0 text-white fw-bold">Nilai KPI terbaru</h6>
                                            <h1 class="mb-0 fw-bold text-white">70,9</h1>
                                            <a href="#"><span class="text-white">Periksa Nilai</span></a>
                                        </div>
                                        <div class="col">
                                            <img class="img-fluid" src="assets/images/interview.svg" alt="interview">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- <div class="col-md-6 col-lg-6 col-xl-12  flex-column">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center flex-fill">
                                            <span class="avatar lg light-success-bg rounded-circle text-center d-flex align-items-center justify-content-center"><i class="icofont-users-alt-2 fs-5"></i></span>
                                            <div class="d-flex flex-column ps-3  flex-fill">
                                                <h6 class="mt-3 mb-0 fw-bold">Jumlah Karyawan yang belum anda nilai</h6>
                                                <span class="text-muted fw-bold">29</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> -->
                             <!-- nilai karyawan -->
             <div class="col-md-12 shadow">
                <div class="card">
                    <div class="card-body">
                        <div class="col">
                            <h6 class="mt-1 mb-1 fw-bold"><i class="icofont-users-alt-2 me-3"></i>Karyawan Yang belum dinilai</h6>
                            <!-- <h3 class="mb-0 fw-bold text-white mb-2">7</h3> -->
                        </div>  
                    </div>
                    <!-- Tambahkan style scroll di card-body -->
                    <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                        <div class="flex-grow-1">
                            <!-- List karyawan -->
                            <div class="py-2 d-flex align-items-center border-bottom flex-wrap">
                                            <div class="d-flex align-items-center flex-fill">
                                                <img class="avatar lg rounded-circle img-thumbnail" src="assets/images/lg/avatar2.jpg" alt="profile">
                                                <div class="d-flex flex-column ps-3">
                                                    <h6 class="fw-bold mb-0 small-14">Nama Karyawan</h6>
                                                    <span class="text-muted">ID Karyawan</span>
                                                </div>
                                            </div>
                                            <a class="btn btn-outline-warning" href="#" role="button">Nilai</a>
                                        </div>
                                        <div class="py-2 d-flex align-items-center border-bottom flex-wrap">
                                            <div class="d-flex align-items-center flex-fill">
                                                <img class="avatar lg rounded-circle img-thumbnail" src="assets/images/lg/avatar2.jpg" alt="profile">
                                                <div class="d-flex flex-column ps-3">
                                                    <h6 class="fw-bold mb-0 small-14">Nama Karyawan</h6>
                                                    <span class="text-muted">ID Karyawan</span>
                                                </div>
                                            </div>
                                            <a class="btn btn-outline-warning" href="#" role="button">Nilai</a>
                                        </div>
                                        <div class="py-2 d-flex align-items-center border-bottom flex-wrap">
                                            <div class="d-flex align-items-center flex-fill">
                                                <img class="avatar lg rounded-circle img-thumbnail" src="assets/images/lg/avatar2.jpg" alt="profile">
                                                <div class="d-flex flex-column ps-3">
                                                    <h6 class="fw-bold mb-0 small-14">Nama Karyawan</h6>
                                                    <span class="text-muted">ID Karyawan</span>
                                                </div>
                                            </div>
                                            <a class="btn btn-outline-warning" href="#" role="button">Nilai</a>
                                        </div>
                                        <div class="py-2 d-flex align-items-center border-bottom flex-wrap">
                                            <div class="d-flex align-items-center flex-fill">
                                                <img class="avatar lg rounded-circle img-thumbnail" src="assets/images/lg/avatar2.jpg" alt="profile">
                                                <div class="d-flex flex-column ps-3">
                                                    <h6 class="fw-bold mb-0 small-14">Nama Karyawan</h6>
                                                    <span class="text-muted">ID Karyawan</span>
                                                </div>
                                            </div>
                                            <a class="btn btn-outline-warning" href="#" role="button">Nilai</a>
                                        </div>                   
                                        </div>
                                    </div>
                                </div>
                            </div>

            <!-- tegur karyawan -->
            <div class="col-md-12 shadow">
                <div class="card">
                    <div class="card-body bg-primary">
                        <div class="col">
                            <h6 class="mt-1 mb-1 text-white fw-bold"><i class="icofont-users-alt-2 me-3"></i>Karyawan yang perlu anda tegur</h6>
                            <!-- <h3 class="mb-0 fw-bold text-white mb-2">7</h3> -->
                        </div>  
                    </div>
                    <!-- Tambahkan style scroll di card-body -->
                    <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                        <div class="flex-grow-1">
                            <!-- List karyawan -->
                            <div class="py-2 d-flex align-items-center border-bottom flex-wrap">
                                            <div class="d-flex align-items-center flex-fill">
                                                <img class="avatar lg rounded-circle img-thumbnail" src="assets/images/lg/avatar2.jpg" alt="profile">
                                                <div class="d-flex flex-column ps-3">
                                                    <h6 class="fw-bold mb-0 small-14">Nama Karyawan</h6>
                                                    <span class="text-muted">ID Karyawan</span>
                                                </div>
                                            </div>
                                            <a class="btn btn-outline-success" href="#" role="button"><i class="icofont-brand-whatsapp"></i></a>
                                        </div>
                            <div class="py-2 d-flex align-items-center border-bottom flex-wrap">
                                            <div class="d-flex align-items-center flex-fill">
                                                <img class="avatar lg rounded-circle img-thumbnail" src="assets/images/lg/avatar2.jpg" alt="profile">
                                                <div class="d-flex flex-column ps-3">
                                                    <h6 class="fw-bold mb-0 small-14">Nama Karyawan</h6>
                                                    <span class="text-muted">ID Karyawan</span>
                                                </div>
                                            </div>
                                            <a class="btn btn-outline-success" href="#" role="button"><i class="icofont-brand-whatsapp"></i></a>
                                        </div>
                                        <div class="py-2 d-flex align-items-center border-bottom flex-wrap">
                                            <div class="d-flex align-items-center flex-fill">
                                                <img class="avatar lg rounded-circle img-thumbnail" src="assets/images/lg/avatar2.jpg" alt="profile">
                                                <div class="d-flex flex-column ps-3">
                                                    <h6 class="fw-bold mb-0 small-14">Nama Karyawan</h6>
                                                    <span class="text-muted">ID Karyawan</span>
                                                </div>
                                            </div>
                                            <a class="btn btn-outline-success" href="#" role="button"><i class="icofont-brand-whatsapp"></i></a>
                                        </div>
                                        <div class="py-2 d-flex align-items-center border-bottom flex-wrap">
                                            <div class="d-flex align-items-center flex-fill">
                                                <img class="avatar lg rounded-circle img-thumbnail" src="assets/images/lg/avatar2.jpg" alt="profile">
                                                <div class="d-flex flex-column ps-3">
                                                    <h6 class="fw-bold mb-0 small-14">Nama Karyawan</h6>
                                                    <span class="text-muted">ID Karyawan</span>
                                                </div>
                                            </div>
                                            <a class="btn btn-outline-success" href="#" role="button"><i class="icofont-brand-whatsapp"></i></a>
                                        </div>
       
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </div>
                        </div>
                    </div>
                </div><!-- Row End -->
        
@endsection

@section('script')
<!-- Plugin Js-->
<script src="{{asset('assets/bundles/apexcharts.bundle.js')}}"></script>
<script>
// Data dummy rata-rata KPI tiap bulan
const dataKPI = [
    { bulan: "2025-01", kehadiran: 80, disiplin: 85, teknis: 78, umum: 90 },
    { bulan: "2025-02", kehadiran: 82, disiplin: 88, teknis: 80, umum: 92 },
    { bulan: "2025-03", kehadiran: 85, disiplin: 89, teknis: 84, umum: 91 },
    { bulan: "2025-04", kehadiran: 87, disiplin: 90, teknis: 86, umum: 93 },
    { bulan: "2025-05", kehadiran: 88, disiplin: 91, teknis: 85, umum: 94 }
];

// Pisahkan label dan dataset
const bulan = dataKPI.map(item => item.bulan);
const kehadiran = dataKPI.map(item => item.kehadiran);
const disiplin = dataKPI.map(item => item.disiplin);
const teknis = dataKPI.map(item => item.teknis);
const umum = dataKPI.map(item => item.umum);

// Buat chart
const ctx = document.getElementById('kpiChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: bulan,
        datasets: [
            {
                label: 'Kehadiran',
                data: kehadiran,
                borderColor: 'blue',
                backgroundColor: 'rgba(0,0,255,0.1)',
                fill: true,
                tension: 0.3
            },
            {
                label: 'Disiplin',
                data: disiplin,
                borderColor: 'green',
                backgroundColor: 'rgba(0,255,0,0.1)',
                fill: true,
                tension: 0.3
            },
            {
                label: 'Teknis',
                data: teknis,
                borderColor: 'orange',
                backgroundColor: 'rgba(255,165,0,0.1)',
                fill: true,
                tension: 0.3
            },
            {
                label: 'Umum',
                data: umum,
                borderColor: 'red',
                backgroundColor: 'rgba(255,0,0,0.1)',
                fill: true,
                tension: 0.3
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'top' },
            title: { display: true, text: 'Rata-rata KPI Tiap Bulan' }
        }
    }
});
</script>            
@endsection