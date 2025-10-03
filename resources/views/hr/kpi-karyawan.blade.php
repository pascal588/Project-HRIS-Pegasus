@extends('template.template')

@section('title', 'KPI Karyawan')

@section('content')
<!-- Plugin DataTables -->
<link rel="stylesheet" href="{{ asset('assets/plugin/datatables/responsive.dataTables.min.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/plugin/datatables/dataTables.bootstrap5.min.css') }}" />

<style>
  /* --- Styling sama seperti sebelumnya --- */
  .card-info .col {
    padding-left: 10px;
    padding-right: 20px;
  }

  .filter-dropdowns .dropdown {
    margin-right: 0;
  }

  @media (min-width: 768px) {
    .filter-dropdowns .dropdown {
      margin-right: 0.5rem;
    }

    .filter-dropdowns .dropdown:last-child {
      margin-right: 0;
    }
  }

  .employee-card {
    border-radius: 10px;
    transition: all 0.3s ease;
    height: 100%;
  }

  .employee-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
  }

  .employee-score {
    font-size: 2rem;
    font-weight: bold;
  }

  .carousel-control-prev-icon,
  .carousel-control-next-icon {
    width: 2.5rem;
    height: 2.5rem;
    background-size: 1.5rem;
  }

  .carousel-indicators [data-bs-target] {
    background-color: #333;
  }

  .table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }

  #kpiTable {
    white-space: nowrap;
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

  /* Fix untuk dropdown pagination DataTables */
div.dataTables_wrapper div.dataTables_length select {
  margin: 0 0.5rem;
  padding: 0.375rem 2.25rem 0.375rem 0.75rem;
}

div.dataTables_wrapper div.dataTables_length {
  position: relative;
  z-index: 1;
}

.dataTables_wrapper .dataTables_length select {
  border: 1px solid #dee2e6;
  border-radius: 0.375rem;
  background-color: white;
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right 0.75rem center;
  background-size: 16px 12px;
  appearance: none;
  -webkit-appearance: none;
  -moz-appearance: none;
}

/* Pastikan dropdown tidak terpotong */
.dataTables_wrapper {
  position: relative;
  z-index: auto;
}

div.dataTables_wrapper div.dataTables_paginate {
  margin-top: 1rem;
}

/* Fix untuk overflow di table responsive */
.table-responsive {
  position: relative;
}

/* Pastikan dropdown di DataTables visible */
.dataTables_length .dropdown-menu {
  z-index: 1060 !important;
}

/* Fix khusus untuk select2 jika digunakan */
.select2-container--open {
  z-index: 1061 !important;
}
</style>

<div class="body d-flex py-lg-3 py-md-2">
  <div class="container-xxl">

    <!-- Header & Statistik -->
    <div class="row align-items-center mb-4">
      <div class="card-header py-3 no-bg bg-transparent d-flex align-items-center px-0 justify-content-between flex-wrap">
        <h3 class="fw-bold mb-0">Peringkat KPI Karyawan</h3>
        <div class="d-flex flex-wrap mt-3">
          <!-- Statistik Card -->
          <div class="container-fluid px-0">
            <div class="row g-3 mb-3">
              <div class="col-6 col-md-3">
                <div class="card bg-primary h-100 w-100 stat-card">
                  <div class="card-body text-white d-flex align-items-center">
                    <i class="icofont-star fs-3"></i>
                    <div class="d-flex flex-column ms-3">
                      <h6 class="mb-0">Skor Tertinggi</h6>
                      <span class="text-white" id="highestScore">0</span>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="card bg-primary h-100 w-100 stat-card">
                  <div class="card-body text-white d-flex align-items-center">
                    <i class="icofont-ui-rating fs-3"></i>
                    <div class="d-flex flex-column ms-3">
                      <h6 class="mb-0">Skor Rata-rata</h6>
                      <span class="text-white" id="avgScore">0</span>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="card bg-primary h-100 w-100 stat-card">
                  <div class="card-body text-white d-flex align-items-center">
                    <i class="icofont-warning fs-3"></i>
                    <div class="d-flex flex-column ms-3">
                      <h6 class="mb-0">Skor Terendah</h6>
                      <span class="text-white" id="lowestScore">0</span>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="card bg-primary h-100 w-100 stat-card">
                  <div class="card-body text-white d-flex align-items-center">
                    <i class="icofont-users fs-3"></i>
                    <div class="d-flex flex-column ms-3">
                      <h6 class="mb-0">Total Karyawan</h6>
                      <span class="text-white" id="totalEmployee">0</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Filter Dropdowns -->
<div class="container-xxl mt-2">
  <div class="card shadow-sm w-100">
    <div class="card-header bg-primary text-white py-2">
      <h5 class="mb-0">Filter Data</h5>
    </div>
    <div class="card-body p-2">
      <form class="row g-2">
        <div class="col-6 col-md-3">
          <label class="form-label small mb-1">Tahun</label>
          <select id="filterTahun" class="form-select form-select-sm">
            <option value="all">Semua</option>
          </select>
        </div>
        <div class="col-6 col-md-3">
          <label class="form-label small mb-1">Periode (Bulan)</label>
          <select id="filterPeriode" class="form-select form-select-sm">
            <option value="latest">Periode Terbaru</option>
            <!-- Options akan diisi oleh JavaScript -->
          </select>
        </div>
        <div class="col-6 col-md-3">
          <label class="form-label small mb-1">Divisi</label>
          <select id="filterDivisi" class="form-select form-select-sm">
            <option value="all">Semua</option>
          </select>
        </div>
        <div class="col-6 col-md-3">
          <label class="form-label small mb-1">Jabatan</label>
          <select id="filterJabatan" class="form-select form-select-sm">
            <option value="all">Semua</option>
          </select>
        </div>
      </form>
    </div>
  </div>
</div>

        </div>
      </div>
    </div>

    <!-- DataTable KPI -->
    <div class="row clearfix g-3">
      <div class="col-sm-12">
        <div class="card mb-3">
          <div class="card-body">
            <div class="table-responsive">
              <table id="kpiTable" class="table table-hover table-striped align-middle mb-0" style="width:100%">
                <thead class="table-light">
                  <tr>
                    <th>No</th>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Divisi</th>
                    <th>Jabatan</th>
                    <th>Status</th>
                    <th>Skor KPI</th>
                    <th>Periode</th>
                    <th style="width:80px">Aksi</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Karyawan Terbaik -->
    <div class="container-xxl">
      <div class="row mt-4">
        <div class="col-12">
          <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
              <h5 class="mb-0">🌟 Karyawan Terbaik</h5>
              <small class="text-light">Geser untuk melihat lainnya →</small>
            </div>
            <div class="card-body">
              <div class="best-employee-wrapper d-flex flex-nowrap overflow-auto px-2"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>
@endsection

@section('script')
<script src="{{ asset('assets/bundles/dataTables.bundle.js') }}"></script>
<script>
 $(document).ready(function() {
    let kpiData = [];
    let allPeriods = [];

    // Map bulan Inggris ke Indonesia
    const monthMap = {
        'January': 'Januari',
        'February': 'Februari',
        'March': 'Maret',
        'April': 'April',
        'May': 'Mei',
        'June': 'Juni',
        'July': 'Juli',
        'August': 'Agustus',
        'September': 'September',
        'October': 'Oktober',
        'November': 'November',
        'December': 'Desember'
    };

    // Function untuk translate bulan
    function translateMonth(englishMonth) {
        return monthMap[englishMonth] || englishMonth;
    }

    // Helper function untuk format date
    function formatDate(dateString, format) {
        const d = new Date(dateString);
        const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        
        if (format === 'F') return months[d.getMonth()];
        if (format === 'Y') return d.getFullYear();
        if (format === 'n') return d.getMonth() + 1;
        
        return d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
    }

    // Inisialisasi DataTable
    var table = $('#kpiTable').DataTable({
        responsive: false,
        autoWidth: false,
        scrollX: true,
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100],
        order: [[6, 'desc']],
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
            infoFiltered: "(disaring dari _MAX_ total data)",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Berikutnya",
                previous: "Sebelumnya"
            }
        },
        columns: [
            { data: null }, // No
            { data: 'id_karyawan' }, 
            { data: 'nama' }, 
            { data: 'division' }, 
            { data: 'position' }, 
            { data: 'status' }, 
            { data: 'score' }, 
            { data: 'period' }, 
            { data: null } 
        ],
        columnDefs: [
            {
                targets: 0,
                render: function(data, type, row, meta) {
                    return meta.row + 1;
                }
            },
            {
                targets: 5, // Kolom status
                render: function(data) {
                    return `<span class="badge ${data==='Aktif'?'bg-success':'bg-danger'}">${data}</span>`;
                }
            },
            {
                targets: 6, // Kolom score
                render: function(data) {
                    const score = parseFloat(data) || 0;
                    let badgeClass = 'bg-secondary';
                    if (score >= 80) badgeClass = 'bg-success';
                    else if (score >= 60) badgeClass = 'bg-primary';
                    else if (score >= 40) badgeClass = 'bg-warning';
                    else badgeClass = 'bg-danger';
                    
                    return `<span class="badge ${badgeClass}">${score.toFixed(2)}</span>`;
                }
            },
            {
                targets: 8, // Kolom aksi
                render: function(data, type, row) {
                    return `<a href="/kpi/detail/${row.id_karyawan}?period=${row.period_id || 'latest'}" class="btn btn-outline-secondary btn-sm"><i class="icofont-eye-alt"></i> Detail</a>`;
                }
            }
        ]
    });

    // Load available years from API
    async function loadAvailableYears() {
        try {
            const res = await fetch('/api/kpis/available-years');
            const response = await res.json();
            
            if (response.success) {
                const tahunSelect = $('#filterTahun');
                tahunSelect.empty().append('<option value="all">Semua Tahun</option>');
                
                response.data.forEach(year => {
                    tahunSelect.append(`<option value="${year}">${year}</option>`);
                });
            } else {
                // Fallback: generate years manually if API fails
                generateFallbackYears();
            }
        } catch (err) {
            console.error('Error loading years:', err);
            generateFallbackYears();
        }
    }

    // Fallback jika API tidak tersedia
    function generateFallbackYears() {
        const tahunSelect = $('#filterTahun');
        tahunSelect.empty().append('<option value="all">Semua Tahun</option>');
        
        // Generate tahun dari 2023 sampai tahun sekarang + 1
        const currentYear = new Date().getFullYear();
        for (let year = 2023; year <= currentYear + 1; year++) {
            tahunSelect.append(`<option value="${year}">${year}</option>`);
        }
    }

    // Load all periods
    async function loadAllPeriods() {
        try {
            const res = await fetch('/api/kpis/published-periods');
            const response = await res.json();
            
            if (response.success) {
                allPeriods = response.data;
                // Auto-select tahun terbaru dan periode terbaru
                autoSelectLatestPeriod();
            }
        } catch (err) {
            console.error('Error loading periods:', err);
        }
    }

    // Auto-select tahun dan periode terbaru
    function autoSelectLatestPeriod() {
        if (allPeriods.length === 0) return;
        
        // Urutkan periode dari yang terbaru
        const sortedPeriods = [...allPeriods].sort((a, b) => 
            new Date(b.tanggal_mulai) - new Date(a.tanggal_mulai)
        );
        
        const latestPeriod = sortedPeriods[0];
        const latestYear = new Date(latestPeriod.tanggal_mulai).getFullYear();
        
        // Set tahun ke tahun terbaru
        $('#filterTahun').val(latestYear);
        
        // Update periode dropdown untuk tahun terbaru
        updatePeriodFilter(latestYear);
    }

    // Update period filter based on selected year
    function updatePeriodFilter(selectedYear) {
        const periodeSelect = $('#filterPeriode');
        periodeSelect.empty(); // HAPUS OPSI "SEMUA PERIODE"
        
        let filteredPeriods = allPeriods;
        
        // Filter periods by selected year
        if (selectedYear !== 'all') {
            filteredPeriods = allPeriods.filter(period => {
                const periodYear = new Date(period.tanggal_mulai).getFullYear();
                return periodYear == selectedYear;
            });
        }
        
        // Populate period dropdown
        filteredPeriods.forEach(period => {
            const month = translateMonth(formatDate(period.tanggal_mulai, 'F'));
            const year = formatDate(period.tanggal_mulai, 'Y');
            const displayName = `${month} ${year}`;
            
            periodeSelect.append(`<option value="${period.id_periode}">${displayName}</option>`);
        });
        
        // AUTO-SELECT PERIODE PERTAMA jika ada periode
        if (filteredPeriods.length > 0) {
            // Urutkan dari terbaru ke terlama dan pilih yang terbaru
            const sorted = [...filteredPeriods].sort((a, b) => 
                new Date(b.tanggal_mulai) - new Date(a.tanggal_mulai)
            );
            periodeSelect.val(sorted[0].id_periode);
        }
    }

    // Load KPI data berdasarkan filter
    async function loadKpiData() {
        try {
            const tahun = $('#filterTahun').val();
            const periode = $('#filterPeriode').val();
            
        let url = '/api/kpis/all-employee-scores';
        
        // Prioritaskan filter by periode jika ada periode yang dipilih
        if (periode) {
            url = `/api/kpis/period/${periode}/scores`;
        }
        // Jika memilih tahun spesifik tapi tidak ada periode (seharusnya tidak terjadi)
        else if (tahun && tahun !== 'all') {
            // Fallback: ambil data semua employee scores
            console.warn('No period selected, using all employee scores');
        }

            console.log('Fetching KPI data from:', url);
            const res = await fetch(url);
            
            if (!res.ok) {
                const errorText = await res.text();
                throw new Error(`HTTP ${res.status}: ${errorText}`);
            }
            
            const response = await res.json();
            console.log('API Response:', response);
            
            if (response.success && response.data) {
                kpiData = response.data;
                
                // Transform data dengan bulan Indonesia
                kpiData = kpiData.map(item => {
                    return {
                        id_karyawan: item.id_karyawan,
                        nama: item.nama,
                        division: item.division || '-',
                        position: item.position || '-',
                        status: item.status || 'Aktif',
                        score: parseFloat(item.score) || 0,
                        period: item.period || 'Unknown',
                        period_month: translateMonth(item.period_month) || 'Unknown',
                        period_month_number: item.period_month_number || 0,
                        period_year: item.period_year || 'Unknown',
                        photo: item.photo || 'assets/images/profile_av.png',
                        period_id: item.period_id || 'latest'
                    };
                });
                
                console.log('Processed KPI data:', kpiData);
                
                // Apply divisi & jabatan filter client-side
                applyClientSideFilters();
                
            } else {
                console.error('API returned unsuccessful:', response);
                throw new Error(response.message || 'Format data tidak valid');
            }
        } catch (err) {
            console.error('Error:', err);
            alert('Gagal memuat data KPI: ' + err.message);
        }
    }

    // Apply client-side filters (divisi & jabatan)
    function applyClientSideFilters() {
        const divisi = $('#filterDivisi').val();
        const jabatan = $('#filterJabatan').val();
        
        const filtered = kpiData.filter(item => {
            const divisiMatch = divisi === 'all' || item.division === divisi;
            const jabatanMatch = jabatan === 'all' || item.position === jabatan;
            
            return divisiMatch && jabatanMatch;
        });
        
        // Update DataTable
        table.clear();
        table.rows.add(filtered).draw();
        
        // Update stats dan top employees
        populateTopEmployees(filtered);
        updateStats(filtered);
        
        // Update divisi & jabatan dropdowns
        updateDivisiJabatanDropdowns(filtered);
    }

    // Update divisi & jabatan dropdowns berdasarkan data yang difilter
    function updateDivisiJabatanDropdowns(data) {
        const divisiSet = new Set();
        const jabatanSet = new Set();
        
        data.forEach(item => {
            if (item.division && item.division !== '-') {
                divisiSet.add(item.division);
            }
            if (item.position && item.position !== '-') {
                jabatanSet.add(item.position);
            }
        });
        
        fillSelect('#filterDivisi', divisiSet);
        fillSelect('#filterJabatan', jabatanSet);
    }

    function fillSelect(selector, items) {
        const select = $(selector);
        const currentValue = select.val();
        select.empty().append('<option value="all">Semua</option>');
        
        Array.from(items).sort().forEach(i => {
            if (i && i !== 'Unknown' && i !== '-') {
                select.append(`<option value="${i}">${i}</option>`);
            }
        });
        
        // Restore previous selection if still available
        if (currentValue && currentValue !== 'all') {
            if (Array.from(items).includes(currentValue)) {
                select.val(currentValue);
            }
        }
    }

    // Populate top employees
    function populateTopEmployees(data) {
        // Filter hanya yang memiliki score > 0 dan urutkan descending
        const top = data
            .filter(emp => emp.score > 0)
            .sort((a, b) => b.score - a.score)
            .slice(0, 4);
        
        const wrapper = $('.best-employee-wrapper');
        wrapper.empty();
        
        if (top.length === 0) {
            wrapper.append(`
                <div class="col-12 text-center py-4">
                    <p class="text-muted">Tidak ada data karyawan dengan nilai KPI</p>
                </div>
            `);
            return;
        }
        
        top.forEach(emp => {
            // Determine badge color based on score
            let badgeClass = 'bg-secondary';
            let performanceText = 'Unknown';
            
            if (emp.score >= 80) {
                badgeClass = 'bg-success';
                performanceText = 'Excellent';
            } else if (emp.score >= 60) {
                badgeClass = 'bg-primary';
                performanceText = 'Good';
            } else if (emp.score >= 40) {
                badgeClass = 'bg-warning';
                performanceText = 'Average';
            } else {
                badgeClass = 'bg-danger';
                performanceText = 'Poor';
            }
            
            wrapper.append(`
                <div class="employee-card flex-shrink-0 me-3" style="width: 200px;">
                    <div class="card h-100 text-center p-3 border-0 shadow-sm">
                        <img src="${emp.photo}" class="rounded-circle mx-auto mb-3" width="80" height="80" alt="${emp.nama}" 
                             onerror="this.src='assets/images/profile_av.png'" />
                        <h6 class="fw-bold mb-1">${emp.nama}</h6>
                        <div class="mb-2">
                            <span class="badge ${badgeClass}">${emp.score.toFixed(2)}</span>
                            <small class="d-block text-muted mt-1">${performanceText}</small>
                        </div>
                        <small class="text-muted">${emp.position}</small>
                        <small class="text-muted d-block">${emp.division}</small>
                    </div>
                </div>
            `);
        });
    }

    // Update statistics
    function updateStats(data) {
        if (data.length === 0) {
            $('#highestScore').text('0');
            $('#avgScore').text('0');
            $('#lowestScore').text('0');
            $('#totalEmployee').text('0');
            return;
        }
        
        const scores = data.map(d => parseFloat(d.score) || 0).filter(score => score > 0);
        
        if (scores.length === 0) {
            $('#highestScore').text('0');
            $('#avgScore').text('0');
            $('#lowestScore').text('0');
            $('#totalEmployee').text(data.length);
            return;
        }
        
        const maxScore = Math.max(...scores);
        const minScore = Math.min(...scores);
        const avgScore = scores.reduce((a, b) => a + b, 0) / scores.length;
        
        $('#highestScore').text(maxScore.toFixed(2));
        $('#lowestScore').text(minScore.toFixed(2));
        $('#avgScore').text(avgScore.toFixed(2));
        $('#totalEmployee').text(data.length);
    }

    // Event listeners
    $('#filterTahun').on('change', function() {
        const selectedYear = $(this).val();
        updatePeriodFilter(selectedYear);
        
        // Load data dengan filter baru
        loadKpiData();
    });

    $('#filterPeriode').on('change', function() {
        loadKpiData();
    });

    $('#filterDivisi, #filterJabatan').on('change', function() {
        applyClientSideFilters();
    });

    // Initialize page
    async function initializePage() {
        await loadAvailableYears();
        await loadAllPeriods();
        await loadKpiData();
    }

    // Load initial data
    initializePage();
});
</script>
@endsection