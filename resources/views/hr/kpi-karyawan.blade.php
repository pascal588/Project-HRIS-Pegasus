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
      order: [
        [6, 'desc']
      ],
      columns: [{
          data: null
        },
        {
          data: 'id_karyawan'
        },
        {
          data: 'nama'
        },
        {
          data: 'nama divisi'
        },
        {
          data: 'position'
        },
        {
          data: 'status'
        },
        {
          data: 'score'
        },
        {
          data: 'period'
        },
        {
          data: null
        }
      ],
      columnDefs: [{
          targets: 0,
          render: function(data, type, row, meta) {
            return meta.row + 1;
          }
        },
        {
          targets: 5,
          render: function(data) {
            return `<span class="badge ${data==='Aktif'?'bg-success':'bg-danger'}">${data}</span>`;
          }
        },
        {
          targets: 8,
          render: function(data, type, row) {
            return `<a href="/kpi/detail/${row.id}" class="btn btn-outline-secondary btn-sm"><i class="icofont-eye-alt"></i></a>`;
          }
        }
      ]
    });


    async function loadKpiData() {
      try {
        const res = await fetch('/api/kpis'); // Pastikan endpoint ini benar
        if (!res.ok) throw new Error('Gagal fetch data');
        const response = await res.json();
        
        // Pastikan struktur data sesuai dengan yang diharapkan
        if (response.success && response.data) {
          kpiData = response.data;
          populateDropdowns(kpiData);
          applyFilters();
        } else {
          throw new Error('Format data tidak valid');
        }
      } catch (err) {
        console.error('Error:', err);
        alert('Gagal memuat data KPI: ' + err.message);
      }
    }

    function populateDropdowns(data) {
    const bulanSet = new Set(),
      tahunSet = new Set(),
      divisiSet = new Set(),
      jabatanSet = new Set();
    
    data.forEach(item => {
      // Pastikan property yang diakses sesuai dengan response API
      const [month, year] = (item.period || 'Unknown Unknown').split(' ');
      bulanSet.add(month);
      tahunSet.add(year);
      divisiSet.add(item.division || item.divisi); // Coba kedua kemungkinan
      jabatanSet.add(item.position || item.jabatan); // Coba kedua kemungkinan
    });
    
    fillSelect('#filterBulan', bulanSet);
    fillSelect('#filterTahun', tahunSet);
    fillSelect('#filterDivisi', divisiSet);
    fillSelect('#filterJabatan', jabatanSet);
  }

    function fillSelect(selector, items) {
      const select = $(selector);
      select.empty().append('<option value="all">Semua</option>');
      Array.from(items).forEach(i => select.append(`<option value="${i}">${i}</option>`));
    }

    function applyFilters() {
      const bulan = $('#filterBulan').val(),
        tahun = $('#filterTahun').val(),
        divisi = $('#filterDivisi').val(),
        jabatan = $('#filterJabatan').val();
      const filtered = kpiData.filter(item => {
        const [month, year] = (item.period ?? 'Unknown Unknown').split(' ');
        return (bulan === 'all' || month === bulan) && (tahun === 'all' || year === tahun) && (divisi === 'all' || item.division === divisi) && (jabatan === 'all' || item.position === jabatan);
      });
      populateTable(filtered);
      populateTopEmployees(filtered);
      updateStats(filtered);
    }

    function populateTable(data) {
      table.clear();
      data.forEach((item, idx) => {
        table.row.add([
          idx + 1,
          item.id,
          item.name,
          item.division,
          item.position,
          item.status,
          item.score,
          item.period,
          `<a href="/kpi/detail/${item.id}" class="btn btn-outline-secondary btn-sm"><i class="icofont-eye-alt"></i></a>`
        ]);
      });
      table.draw();
    }

    function populateTopEmployees(data) {
      const top = data.sort((a, b) => b.score - a.score).slice(0, 4);
      const wrapper = $('.best-employee-wrapper');
      wrapper.empty();
      top.forEach(emp => {
        wrapper.append(`
        <div class="employee-card flex-shrink-0 me-3">
          <div class="card h-100 text-center p-3 border-0 shadow-sm">
            <img src="${emp.photo??'assets/images/profile_av.png'}" class="rounded-circle mx-auto mb-3" width="80" height="80" alt="${emp.name}" />
            <h6 class="fw-bold mb-1">${emp.name}</h6>
            <small class="text-muted">${emp.score}</small>
          </div>
        </div>
      `);
      });
    }

    function updateStats(data) {
      if (data.length === 0) {
        $('#highestScore,#avgScore,#lowestScore,#totalEmployee').text(0);
        return;
      }
      const scores = data.map(d => d.score);
      $('#highestScore').text(Math.max(...scores));
      $('#lowestScore').text(Math.min(...scores));
      $('#avgScore').text((scores.reduce((a, b) => a + b, 0) / scores.length).toFixed(2));
      $('#totalEmployee').text(data.length);
    }

    $('#filterBulan,#filterTahun,#filterDivisi,#filterJabatan').on('change', applyFilters);

    loadKpiData();
  });
</script>
@endsection