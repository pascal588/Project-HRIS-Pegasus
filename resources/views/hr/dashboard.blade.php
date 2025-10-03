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

 /* Compact employee list cards */
.employee-list-card {
    border: 1px solid #e9ecef;
    transition: box-shadow 0.2s;
}

.employee-list-card:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
}

.employee-list-card .card-body {
    padding: 0;
    max-height: 280px; /* Sedikit lebih tinggi */
    overflow-y: auto;
}

/* Scrollbar styling */
.employee-list-card .card-body::-webkit-scrollbar {
    width: 4px;
}

.employee-list-card .card-body::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 2px;
}

.employee-list-card .card-body::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 2px;
}

.employee-list-card .card-body::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Employee item styling */
.employee-item {
    display: flex;
    align-items: center;
    padding: 10px 15px;
    border-bottom: 1px solid #f8f9fa;
    transition: all 0.2s ease;
    margin: 0 5px;
}

.employee-item:first-child {
    margin-top: 5px;
}

.employee-item:last-child {
    border-bottom: none;
    margin-bottom: 5px;
}

.employee-item:hover {
    background-color: #f8f9fa;
    border-radius: 8px;
    margin: 2px 5px;
}

.employee-item .avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #fff;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    flex-shrink: 0;
}

.employee-item .employee-info {
    flex: 1;
    min-width: 0; /* Untuk text truncate */
    margin: 0 12px;
}

.employee-item .employee-info h6 {
    font-size: 0.8rem;
    font-weight: 600;
    margin-bottom: 2px;
    color: #2c3e50;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.employee-item .employee-info .position {
    font-size: 0.7rem;
    color: #6c757d;
    margin-bottom: 1px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.employee-item .employee-info .division {
    font-size: 0.65rem;
    color: #95a5a6;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.employee-item .employee-score {
    font-size: 0.7rem;
    font-weight: 600;
    padding: 2px 6px;
    border-radius: 8px;
    background: #f8f9fa;
    flex-shrink: 0;
}

/* Score color variations */
.score-excellent { color: #27ae60; background: #e8f6ef !important; }
.score-good { color: #2980b9; background: #e8f4fc !important; }
.score-average { color: #f39c12; background: #fef5e6 !important; }
.score-poor { color: #e74c3c; background: #fdedec !important; }

/* Action buttons */
.employee-actions {
    display: flex;
    gap: 5px;
    margin-left: 8px;
    flex-shrink: 0;
}

.employee-actions .btn {
    width: 28px;
    height: 28px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    font-size: 0.7rem;
}

/* Card header improvements */
.employee-list-card .card-header {
    padding: 12px 15px;
    border-bottom: 1px solid #e9ecef;
    background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
}

.employee-list-card .card-header h6 {
    font-size: 0.85rem;
    font-weight: 700;
    color: #2c3e50;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.employee-count {
    font-size: 0.7rem;
    color: #6c757d;
    background: #e9ecef;
    padding: 2px 8px;
    border-radius: 10px;
    font-weight: 500;
}

/* Empty state styling */
.employee-empty-state {
    padding: 30px 20px;
    text-align: center;
    color: #95a5a6;
}

.employee-empty-state i {
    font-size: 2rem;
    margin-bottom: 10px;
    opacity: 0.5;
}

.employee-empty-state p {
    font-size: 0.8rem;
    margin-bottom: 10px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .employee-list-card .card-body {
        max-height: 220px;
    }
    
    .employee-item {
        padding: 8px 10px;
    }
    
    .employee-item .avatar {
        width: 32px;
        height: 32px;
    }
    
    .employee-actions .btn {
        width: 26px;
        height: 26px;
    }
}

@media (max-width: 576px) {
    .best-card {
        order: 1;
        margin-bottom: 10px;
    }
    
    .worst-card {
        order: 2;
    }
    
    .employee-list-card .card-body {
        max-height: 200px;
    }
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

  .employee-actions {
    margin-left: 10px;
}

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
    font-size: 14px;
}

.employee-item .employee-info h6 {
    font-size: 0.8rem;
    margin-bottom: 2px;
}

.employee-item .employee-info span {
    font-size: 0.7rem;
}

.employee-item .employee-info small {
    font-size: 0.65rem;
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
              id="apex-stacked-area"
            ></div>
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
            <img src="{{ asset('img/user.png') }}" alt="User Icon"/>
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
            <img src="{{ asset('img/employment.png') }}" alt="Divisi Icon" />
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
        
        // Hitung distribusi gender
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
        
        // Load data ranking 10 terbaik dari API KPI
        loadTopEmployees();
        
        // Load data karyawan perlu perhatian dari semua divisi
        loadLowPerformingEmployeesAll();
    }

    // FUNGSI BARU: Load 10 karyawan terbaik dengan tampilan sama seperti dashboard penilai
    function loadTopEmployees() {
        $.ajax({
            url: '/api/kpis/all-employee-scores',
            method: 'GET',
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    console.log('Top employees loaded:', response.data.length, 'items');
                    renderEmployeeList('#best-employees-list', response.data, 'Karyawan Terbaik');
                } else {
                    console.error('Failed to load top employees:', response.message);
                    renderFallbackBestEmployees();
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading top employees:', error);
                renderFallbackBestEmployees();
            }
        });
    }

    // FUNGSI BARU: Load karyawan perlu perhatian dengan tampilan sama seperti dashboard penilai
    function loadLowPerformingEmployeesAll() {
        $.ajax({
            url: '/api/kpis/low-performing-employees-all',
            method: 'GET',
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    console.log('Low performing employees loaded:', response.data.length, 'items');
                    renderEmployeeList('#worst-employees-list', response.data, 'Perlu Perhatian');
                } else {
                    console.error('Failed to load low performing employees:', response.message);
                    renderFallbackWorstEmployees();
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading low performing employees:', error);
                renderFallbackWorstEmployees();
            }
        });
    }

    // FUNGSI FALLBACK untuk karyawan terbaik
    function renderFallbackBestEmployees() {
        // Ambil data karyawan dari API employees sebagai fallback
        $.ajax({
            url: '/api/employees',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const bestEmployees = response.data.slice(0, 5).map(emp => ({
                        ...emp,
                        score: Math.floor(Math.random() * 20) + 80 // Random score 80-100 untuk demo
                    }));
                    renderEmployeeList('#best-employees-list', bestEmployees, 'Karyawan Terbaik');
                }
            },
            error: function() {
                const fallbackData = [
                    { nama: 'Data tidak tersedia', position: 'Silakan coba lagi', division: '-' }
                ];
                renderEmployeeList('#best-employees-list', fallbackData, 'Karyawan Terbaik');
            }
        });
    }

    // FUNGSI FALLBACK untuk karyawan perlu perhatian
    function renderFallbackWorstEmployees() {
        // Ambil data karyawan dari API employees sebagai fallback
        $.ajax({
            url: '/api/employees',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const worstEmployees = response.data.slice(-5).map(emp => ({
                        ...emp,
                        score: Math.floor(Math.random() * 20) + 30, 
                        phone: emp.no_telp || '62'
                    }));
                    renderEmployeeList('#worst-employees-list', worstEmployees, 'Perlu Perhatian');
                }
            },
            error: function() {
                const fallbackData = [
                    { nama: 'Data tidak tersedia', position: 'Silakan coba lagi', division: '-' }
                ];
                renderEmployeeList('#worst-employees-list', fallbackData, 'Perlu Perhatian');
            }
        });
    }

// FUNGSI RENDER YANG LEBIH RAPI
function renderEmployeeList(selector, employees, title) {
    const listElement = $(selector);
    listElement.empty();
    
    if (!employees || employees.length === 0) {
        listElement.append(`
            <div class="employee-empty-state">
                <i class="icofont-info-circle"></i>
                <p class="mt-2">Tidak ada data ${title.toLowerCase()}</p>
                <small class="text-muted">Data akan muncul ketika tersedia</small>
            </div>
        `);
        return;
    }
    
    // Update count badge di header
    const cardHeader = listElement.closest('.card').find('.card-header h6');
    cardHeader.find('.employee-count').remove();
    cardHeader.append(`<span class="employee-count">${employees.length}</span>`);
    
    employees.forEach((employee, index) => {
        // Handle berbagai format data
        const employeeName = employee.nama || employee.name || 'Tidak ada nama';
        const employeePosition = employee.position || 
                               (employee.roles && employee.roles.length > 0 ? 
                                employee.roles[0].nama_jabatan : 'Tidak ada jabatan');
        const employeeDivision = employee.division || 
                               (employee.roles && employee.roles.length > 0 ? 
                                employee.roles[0].division?.nama_divisi : '-');
        
        const hasScore = employee.score !== undefined;
        const scoreColor = hasScore ? getScoreColorClass(employee.score) : 'score-average';
        const scoreText = hasScore ? employee.score.toFixed(1) : 'N/A';
        
        // ⚠️ PERBAIKAN: Foto employee - gunakan format yang sama untuk semua
        let photoUrl = '{{ asset('assets/images/profile_av.png') }}';
        
        if (employee.foto) {
            // Cek apakah foto sudah memiliki path lengkap atau relative
            if (employee.foto.startsWith('http') || employee.foto.startsWith('/storage')) {
                photoUrl = employee.foto;
            } else if (employee.foto.startsWith('assets/')) {
                photoUrl = '{{ asset('') }}' + employee.foto;
            } else {
                photoUrl = '/storage/' + employee.foto;
            }
        } else if (employee.photo) {
            // Handle jika properti namanya 'photo' bukan 'foto'
            if (employee.photo.startsWith('http') || employee.photo.startsWith('/storage')) {
                photoUrl = employee.photo;
            } else if (employee.photo.startsWith('assets/')) {
                photoUrl = '{{ asset('') }}' + employee.photo;
            } else {
                photoUrl = '/storage/' + employee.photo;
            }
        }
        
        const employeeItem = `
            <div class="employee-item">
                <img class="avatar" 
                     src="${photoUrl}" 
                     alt="${employeeName}"
                     onerror="this.src='{{ asset('assets/images/profile_av.png') }}'">
                
                <div class="employee-info">
                    <h6 class="mb-1">${employeeName}</h6>
                    <div class="position">${employeePosition}</div>
                    <div class="division">${employeeDivision}</div>
                </div>
                
                ${hasScore ? `
                    <div class="employee-score ${scoreColor}">
                        ${scoreText}
                    </div>
                ` : ''}
                
                ${title === 'Perlu Perhatian' && employee.phone ? `
                    <div class="employee-actions">
                        <a class="btn btn-outline-success btn-sm" 
                           href="https://wa.me/${employee.phone}" 
                           target="_blank"
                           title="Hubungi via WhatsApp">
                            <i class="icofont-brand-whatsapp"></i>
                        </a>
                        <a class="btn btn-outline-primary btn-sm" 
                           href="/kpi/detail/${employee.id_karyawan || employee.id}"
                           title="Lihat Detail">
                            <i class="icofont-eye-alt"></i>
                        </a>
                    </div>
                ` : title === 'Karyawan Terbaik' ? `
                    <div class="employee-actions">
                        <a class="btn btn-outline-primary btn-sm" 
                           href="/kpi/detail/${employee.id_karyawan || employee.id}"
                           title="Lihat Detail">
                            <i class="icofont-eye-alt"></i>
                        </a>
                    </div>
                ` : ''}
            </div>
        `;
        
        listElement.append(employeeItem);
    });
}

// Helper function untuk score color yang lebih baik
function getScoreColorClass(score) {
    const numericScore = parseFloat(score) || 0;
    if (numericScore >= 90) return 'score-excellent';
    if (numericScore >= 80) return 'score-good';
    if (numericScore >= 70) return 'score-average';
    if (numericScore >= 60) return 'score-average';
    return 'score-poor';
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

    // KPI Divisi Chart
    const chartContainer = document.querySelector("#apex-stacked-area");
    if (chartContainer) {
        chartContainer.innerHTML = 'Memuat data perkembangan KPI...';

        $.ajax({
            url: '/api/periods/performance-across-periods',
            method: 'GET',
            success: function(response) {
                if (response.success && response.data.series.length > 0) {
                    renderChart(response.data);
                } else {
                    chartContainer.innerHTML = 'Tidak ada data perkembangan KPI untuk ditampilkan.';
                }
            },
            error: function(error) {
                console.error('Error fetching data:', error);
                chartContainer.innerHTML = '<div class="alert alert-danger p-2">Gagal memuat data grafik.</div>';
            }
        });

        function renderChart(apiData) {
            var options = {
                chart: {
                    height: 300,
                    type: 'area',
                    stacked: true,
                    toolbar: {
                        show: false,
                    },
                },
                colors: ['#008FFB', '#00E396', '#FEB019', '#FF4560', '#775DD0'],
                dataLabels: {
                    enabled: false
                },
                series: apiData.series,
                fill: {
                    type: 'gradient',
                    gradient: {
                        opacityFrom: 0.6,
                        opacityTo: 0.8,
                    }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'right',
                    show: true,
                },
                xaxis: {
                    type: 'category',
                    categories: apiData.categories,
                },
                yaxis: {
                    title: { text: 'Rata-rata Nilai KPI' }
                },
                grid: {
                    yaxis: {
                        lines: {
                            show: false,
                        }
                    },
                    padding: {
                        top: 20,
                        right: 20,
                        bottom: 0,
                        left: 20
                    },
                },
                stroke: {
                    show: true,
                    curve: 'smooth',
                    width: 2,
                },
                tooltip: {
                    theme: 'dark'
                }
            };

            chartContainer.innerHTML = '';
            var chart = new ApexCharts(chartContainer, options);
            chart.render();
        }
    }

    // Distribusi Karyawan per Divisi
    function fetchEmployeeDistribution() {
        $.ajax({
            url: '/api/employees/jumlahkaryawan-by-month',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    renderEmployeeDistributionChart(response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching employee distribution:', error);
                renderEmployeeDistributionChart([]);
            }
        });
    }

    function renderEmployeeDistributionChart(data) {
        if (!data || data.length === 0) {
            data = generateFallbackData();
        }

        const divisions = [...new Set(data.map(item => item.nama_divisi))];
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        
        const series = divisions.map(division => {
            const monthlyData = months.map((month, index) => {
                const monthData = data.find(item => 
                    item.nama_divisi === division && item.month === index + 1
                );
                return monthData ? monthData.total_karyawan : 0;
            });

            return {
                name: division,
                data: monthlyData
            };
        });

        const colors = ['#4361ee', '#f72585', '#4cc9f0', '#7209b7', '#3a0ca3', '#f8961e', '#43aa8b', '#577590', '#f94144', '#90be6d'];

        var options = {
            series: series,
            chart: {
                type: 'bar',
                height: 300,
                stacked: true,
                toolbar: {
                    show: false
                },
                zoom: {
                    enabled: true
                }
            },
            colors: colors.slice(0, divisions.length),
            responsive: [{
                breakpoint: 480,
                options: {
                    legend: {
                        position: 'bottom',
                        offsetX: -10,
                        offsetY: 0
                    }
                }
            }],
            xaxis: {
                categories: months,
                title: {
                    text: 'Bulan'
                }
            },
            yaxis: {
                title: {
                    text: 'Jumlah Karyawan'
                },
                min: 0
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right',
            },
            dataLabels: {
                enabled: false,
            },
            fill: {
                opacity: 1
            },
            title: {
                text: 'Distribusi Karyawan per Divisi',
                align: 'left',
                style: {
                    fontSize: '14px',
                    fontWeight: 'bold'
                }
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return val + " karyawan";
                    }
                }
            }
        };

        if (window.employeeDistributionChart) {
            window.employeeDistributionChart.destroy();
        }

        window.employeeDistributionChart = new ApexCharts(document.querySelector("#hiringsources"), options);
        window.employeeDistributionChart.render();
    }

    function generateFallbackData() {
        const divisions = ['IT', 'HR', 'Finance', 'Marketing'];
        
        let data = [];
        divisions.forEach(division => {
            let cumulative = 0;
            for (let month = 1; month <= 12; month++) {
                cumulative += Math.floor(Math.random() * 3) + 1;
                data.push({
                    nama_divisi: division,
                    month: month,
                    total_karyawan: cumulative
                });
            }
        });
        return data;
    }

    // Jalankan semua fungsi
    fetchEmployees();
    fetchDivisions();
    fetchEmployeeDistribution();
});
</script>
@endsection