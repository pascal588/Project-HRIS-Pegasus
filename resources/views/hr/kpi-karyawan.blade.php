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
                    <label class="form-label small mb-1">Bulan</label>
                    <select id="filterBulan" class="form-select form-select-sm">
                      <option value="all">Semua</option>
                    </select>
                  </div>
                  <div class="col-6 col-md-3">
                    <label class="form-label small mb-1">Tahun</label>
                    <select id="filterTahun" class="form-select form-select-sm">
                      <option value="all">Semua</option>
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

    // Inisialisasi DataTable
    var table = $('#kpiTable').DataTable({
      responsive: false,
      autoWidth: false,
      scrollX: true,
      order: [[6, 'desc']],
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
            return `<a href="/kpi/detail/${row.id_karyawan}" class="btn btn-outline-secondary btn-sm"><i class="icofont-eye-alt"></i> Detail</a>`;
          }
        }
      ]
    });

    // Ganti function loadKpiData() di kpi-karyawan.blade.php
async function loadKpiData() {
  try {
    console.log('🔄 Fetching KPI data...');
    const res = await fetch('/api/kpis/all-employee-scores');
    
    console.log('📊 Response status:', res.status);
    console.log('📊 Response ok:', res.ok);
    
    if (!res.ok) {
      const errorText = await res.text();
      console.error('❌ Error response:', errorText);
      throw new Error(`HTTP ${res.status}: ${errorText}`);
    }
    
    const response = await res.json();
    console.log('✅ API Response:', response);
    
    if (response.success && response.data) {
      kpiData = response.data;
      
      // Transform data untuk memastikan compatibility
      kpiData = kpiData.map(item => {
        return {
          id_karyawan: item.id_karyawan,
          nama: item.nama,
          division: item.division || '-',
          position: item.position || '-',
          status: item.status || 'Aktif',
          score: parseFloat(item.score) || 0,
          period: item.period || 'Unknown',
          period_month: item.period_month || 'Unknown',
          period_year: item.period_year || 'Unknown',
          photo: item.photo || 'assets/images/profile_av.png'
        };
      });
      
      console.log('📈 Processed KPI data:', kpiData);
      
      populateDropdowns(kpiData);
      
      // Load data ke DataTables
      table.clear();
      table.rows.add(kpiData).draw();
      
      populateTopEmployees(kpiData);
      updateStats(kpiData);
    } else {
      console.error('❌ API returned unsuccessful:', response);
      throw new Error(response.message || 'Format data tidak valid');
    }
  } catch (err) {
    console.error('💥 Error:', err);
    alert('Gagal memuat data KPI: ' + err.message);
  }
}

    // Populate dropdown filters
    function populateDropdowns(data) {
      const bulanSet = new Set(),
        tahunSet = new Set(),
        divisiSet = new Set(),
        jabatanSet = new Set();
      
      data.forEach(item => {
        if (item.period_month && item.period_month !== 'Unknown') {
          bulanSet.add(item.period_month);
        }
        if (item.period_year && item.period_year !== 'Unknown') {
          tahunSet.add(item.period_year);
        }
        if (item.division && item.division !== '-') {
          divisiSet.add(item.division);
        }
        if (item.position && item.position !== '-') {
          jabatanSet.add(item.position);
        }
      });
      
      fillSelect('#filterBulan', bulanSet);
      fillSelect('#filterTahun', tahunSet);
      fillSelect('#filterDivisi', divisiSet);
      fillSelect('#filterJabatan', jabatanSet);
    }

    function fillSelect(selector, items) {
      const select = $(selector);
      select.empty().append('<option value="all">Semua</option>');
      Array.from(items).sort().forEach(i => {
        if (i && i !== 'Unknown' && i !== '-') {
          select.append(`<option value="${i}">${i}</option>`);
        }
      });
    }

    // Apply filters
    function applyFilters() {
      const bulan = $('#filterBulan').val(),
        tahun = $('#filterTahun').val(),
        divisi = $('#filterDivisi').val(),
        jabatan = $('#filterJabatan').val();
      
      const filtered = kpiData.filter(item => {
        const bulanMatch = bulan === 'all' || item.period_month === bulan;
        const tahunMatch = tahun === 'all' || item.period_year === tahun;
        const divisiMatch = divisi === 'all' || item.division === divisi;
        const jabatanMatch = jabatan === 'all' || item.position === jabatan;
        
        return bulanMatch && tahunMatch && divisiMatch && jabatanMatch;
      });
      
      table.clear();
      table.rows.add(filtered).draw();
      
      populateTopEmployees(filtered);
      updateStats(filtered);
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

    // Event listeners untuk filter
    $('#filterBulan, #filterTahun, #filterDivisi, #filterJabatan').on('change', applyFilters);

    // Load initial data
    loadKpiData();
  });
</script>
@endsection