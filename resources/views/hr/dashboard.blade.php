@extends('template.template')

@section('title', 'Dashboard HR')

@section('content')
<style>
  /* Compact layout with smaller cards */
  body {
    font-size: 0.875rem;
  }

  .card {
    margin-bottom: 10px;
    border-radius: 10px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    height: 100%;
  }

  .card-header {
    padding: 10px 15px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  }

  .card-header h6 {
    font-size: 0.9rem;
    font-weight: 600;
    margin: 0;
  }

  .card-body {
    padding: 15px;
  }

  /* Compact chart containers */
  #apex-emplyoeeAnalytics,
  #apex-MainCategories,
  #hiringsources {
    min-height: 200px;
  }

  /* Employee list cards */
  .employee-list-card .card-body {
    padding: 0;
    max-height: 200px;
    overflow-y: auto;
  }

  /* Compact stat cards */
  .stat-card {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 15px 5px;
  }

  .stat-card img {
    width: 40px;
    height: 40px;
    margin-bottom: 10px;
    object-fit: contain;
  }

  .stat-card h4 {
    font-weight: 700;
    margin: 5px 0;
    font-size: 1.25rem;
  }

  .stat-card span {
    font-size: 0.75rem;
  }

  /* Compact employee list items */
  .employee-item {
    display: flex;
    align-items: center;
    padding: 8px 12px;
    border-bottom: 1px solid #eee;
    transition: background-color 0.2s;
  }

  .employee-item:last-child {
    border-bottom: none;
  }

  .employee-item:hover {
    background-color: #f8f9fa;
  }

  .employee-item .avatar {
    width: 32px;
    height: 32px;
    margin-right: 10px;
  }

  .employee-item .employee-info h6 {
    font-size: 0.8rem;
    margin-bottom: 2px;
  }

  .employee-item .employee-info span {
    font-size: 0.7rem;
  }

  .employee-item .employee-time {
    color: #6c757d;
    font-size: 0.7rem;
  }

  /* Default (desktop & tablet) */
  .card-header h6 {
    font-size: 1rem;
  }

  .stat-card h4 {
    font-size: 1.25rem;
  }

  .stat-card img {
    width: 40px;
    height: 40px;
  }

  /* Untuk HP agar lebih kecil */
  @media (max-width: 576px) {
    .card-header h6 {
      font-size: 0.85rem;
    }

    .stat-card h4 {
      font-size: 1rem;
    }

    .stat-card img {
      width: 28px;
      height: 28px;
    }

    .employee-item {
      padding: 6px 8px;
    }

    .best-card {
      order: 1;
    }

    .worst-card {
      order: 2;
    }
  }
</style>

<div class="container-fluid px-2">
  <div class="container-fluid px-2">
    <div class="row g-2">
      <!-- KPI Divisi -->
      <div class="col-12 col-lg-8">
        <div class="card h-100">
          <div class="card-header py-2 bg-transparent border-bottom-0">
            <h6 class="mb-0 fw-bold">KPI Divisi</h6>
          </div>
          <div class="card-body p-2">
            <div
              class="ac-line-transparent"
              id="apex-stacked-area"></div>
          </div>
        </div>
      </div>

      <!-- Karyawan Terbaik -->
      <div class="col-12 col-lg-4 best-card">
        <div class="card h-100 employee-list-card">
          <div class="card-header py-2 bg-transparent border-bottom-0">
            <h6 class="mb-0 fw-bold">Karyawan Terbaik</h6>
          </div>
          <div class="card-body p-0">
            <div class="flex-grow-1" id="best-employees-list">
              <!-- Data akan diisi oleh JavaScript -->
            </div>
          </div>
        </div>
      </div>

      <!-- Stat Cards -->
      <div class="col-6 col-md-3 col-lg-2">
        <div class="card h-100">
          <div class="card-header py-2 bg-transparent border-bottom-0">
            <h6 class="mb-0 fw-bold text-center">Karyawan</h6>
          </div>
          <div class="card-body stat-card">
            <img src="user.png" alt="User Icon" />
            <h4 class="fw-bold" id="total-employees">0</h4>
            <span class="text-muted">Total</span>
          </div>
        </div>
      </div>

      <!-- divisi -->
      <div class="col-6 col-md-3 col-lg-2">
        <div class="card h-100">
          <div class="card-header py-2 bg-transparent border-bottom-0">
            <h6 class="mb-0 fw-bold text-center">Divisi</h6>
          </div>
          <div class="card-body stat-card">
            <img src="employment.png" alt="Divisi Icon" />
            <h4 class="fw-bold" id="total-divisions">0</h4>
            <span class="text-muted">Total</span>
          </div>
        </div>
      </div>

      <!-- Gender Chart -->
      <div class="col-12 col-md-6 col-lg-4">
        <div class="card h-100">
          <div class="card-header py-2 bg-transparent border-bottom-0">
            <h6 class="mb-0 fw-bold text-center">Gender Karyawan</h6>
          </div>
          <div class="card-body p-2">
            <div id="apex-MainCategories"></div>
          </div>
        </div>
      </div>

      <!-- Karyawan Bottom -->
      <div class="col-12 col-lg-4 worst-card">
        <div class="card h-100 employee-list-card">
          <div class="card-header py-2 bg-transparent border-bottom-0">
            <h6 class="mb-0 fw-bold">Perlu Perhatian</h6>
          </div>
          <div class="card-body p-0">
            <div class="flex-grow-1" id="worst-employees-list">
              <!-- Data akan diisi oleh JavaScript -->
            </div>
          </div>
        </div>
      </div>

      <!-- Distribusi Karyawan -->
      <div class="col-12">
        <div class="card">
          <div class="card-header py-2 bg-transparent border-bottom-0">
            <h6 class="mb-0 fw-bold">Distribusi Karyawan per Divisi</h6>
          </div>
          <div class="card-body p-2">
            <div id="hiringsources"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
$(document).ready(function() {
    // Fungsi untuk mengambil data karyawan
    function fetchEmployees() {
        $.ajax({
            url: '/api/employees',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    processEmployeeData(response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching employees:', error);
            }
        });
    }

    // Fungsi untuk mengambil data divisi
    function fetchDivisions() {
        $.ajax({
            url: '/api/divisions',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    $('#total-divisions').text(response.data.length);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching divisions:', error);
            }
        });
    }

    // Fungsi untuk memproses data karyawan
    function processEmployeeData(employees) {
        // Update total karyawan
        $('#total-employees').text(employees.length);
        
        // Hitung distribusi gender berdasarkan nilai 'Pria' dan 'Wanita'
        let priaCount = 0;
        let wanitaCount = 0;
        
        employees.forEach(employee => {
            if (employee.gender === 'Pria') {
                priaCount++;
            } else if (employee.gender === 'Wanita') {
                wanitaCount++;
            }
        });
        
        // Render chart gender
        renderGenderChart(priaCount, wanitaCount);
        
        // Tampilkan karyawan terbaik (contoh: 3 karyawan pertama)
        const bestEmployees = employees.slice(0, 3);
        renderEmployeeList('#best-employees-list', bestEmployees, 'Karyawan Terbaik');
        
        // Tampilkan karyawan yang perlu perhatian (contoh: 3 karyawan terakhir)
        const worstEmployees = employees.slice(-3);
        renderEmployeeList('#worst-employees-list', worstEmployees, 'Perlu Perhatian');
    }

    // Fungsi untuk merender chart gender
    function renderGenderChart(priaCount, wanitaCount) {
        const options = {
            align: 'center',
            chart: {
                height: 250,
                type: 'donut',
                align: 'center',
            },
            labels: ['Pria', 'Wanita'],
            dataLabels: {
                enabled: false,
            },
            legend: {
                position: 'bottom',
                horizontalAlign: 'center',
                show: true,
            },
            colors: ['#4361ee', '#f72585'],
            series: [priaCount, wanitaCount],
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: 200
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }]
        };
        
        const chart = new ApexCharts(document.querySelector("#apex-MainCategories"), options);
        chart.render();
    }

    // Fungsi untuk merender daftar karyawan
    function renderEmployeeList(selector, employees, title) {
        const listElement = $(selector);
        listElement.empty();
        
        if (employees.length === 0) {
            listElement.append('<div class="employee-item text-center text-muted">Tidak ada data</div>');
            return;
        }
        
        employees.forEach(employee => {
            const genderIcon = employee.gender === 'Pria' ? '♂' : '♀';
            const roleName = employee.roles && employee.roles.length > 0 
                ? employee.roles[0].nama_jabatan 
                : 'Tidak ada jabatan';
                
            const employeeItem = `
                <div class="employee-item">
                    <div class="d-flex align-items-center flex-fill">
                        <div class="avatar rounded-circle img-thumbnail d-flex align-items-center justify-content-center bg-light">
                            ${genderIcon}
                        </div>
                        <div class="employee-info">
                            <h6 class="fw-bold mb-0 small-14">${employee.nama}</h6>
                            <span class="text-muted">${roleName}</span>
                        </div>
                    </div>
                    <div class="employee-time">
                        <i class="icofont-clock-time"></i> ${employee.no_telp || '-'}
                    </div>
                </div>
            `;
            
            listElement.append(employeeItem);
        });
    }

    // Jalankan fungsi untuk mengambil data
    fetchEmployees();
    fetchDivisions();
});
</script>
@endsection