@extends('template.template')

@section('title', 'KPI Saya')

@section('content')
   <link rel="stylesheet" href="{{ asset('assets/plugin/datatables/responsive.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugin/datatables/dataTables.bootstrap5.min.css') }}">

    <!-- Body: Body -->       
        <div class="body d-flex">
            <div class="container-xxl">
                <div class="row align-items-center">
                    <div class="border-0 mb-2">
                        <div class="card-header py-3 no-bg bg-transparent d-flex align-items-center px-0 justify-content-between border-bottom flex-wrap">
                            <h3 class="fw-bold mb-0">Nilai KPI</h3>
                        </div>
                    </div>
                </div>
                <div class="container mt-1">
                <!-- Ringkasan KPI -->
                <h5 class="fw-bold">Penilaian KPI Terakhir</h5>
                <div class="row mb-3 mt-3">
                    <div class="col-md-3">
                    <div class="card shadow-sm text-center p-3">
                        <h6 class="mb-1">Total Score</h6>
                        <h3 class="fw-bold text-primary" id="totalScore">0</h3>
                        <small class="text-muted">dari <span id="maxScore">100</span></small>
                    </div>
                    </div>
                    <div class="col-md-3">
                    <div class="card shadow-sm text-center p-3">
                        <h6 class="mb-1">Grade</h6>
                        <h3 class="fw-bold text-success" id="grade">-</h3>
                        <small class="text-muted" id="gradeText">-</small>
                    </div>
                    </div>
                    <div class="col-md-3">
                    <div class="card shadow-sm text-center p-3">
                        <h6 class="mb-1">Ranking Divisi</h6>
                        <h3 class="fw-bold text-warning" id="ranking">-</h3>
                        <small class="text-muted" id="rankingText">-</small>
                    </div>
                    </div>
                    <div class="col-md-3">
                    <div class="card shadow-sm text-center p-3">
                        <h6 class="mb-1">Periode</h6>
                        <h3 class="fw-bold text-info" id="periodMonth">-</h3>
                        <small class="text-muted" id="periodYear">-</small>
                    </div>
                    </div>
                </div>

                 
                <!-- Tabel KPI Detail -->
                <div class="card shadow-sm mb-3">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold">Detail Score KPI</h6>
        <div class="dropdown">
            <button class="btn btn-sm btn-primary dropdown-toggle" type="button" id="periodDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                Periode: <span id="currentPeriod">Pilih Periode</span>
            </button>
            <ul class="dropdown-menu" aria-labelledby="periodDropdown" id="periodList">
                <li><h6 class="dropdown-header">Memuat periode...</h6></li>
            </ul>
        </div>
    </div>
    <div class="card-body">
        <div class="row clearfix g-3">
            <div class="col-sm-12">
                <div class="card mb-3">
                    <div class="card-body">
                        <table id="myProjectTable" class="table table-hover align-middle mb-0" style="width:100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Aspek KPI</th>
                                    <th>Bobot</th>
                                    <th>Nilai</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="kpiTableBody">
                                <tr>
                                    <td colspan="5" class="text-center">Pilih periode untuk melihat data KPI</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="table-light">
                                    <td colspan="2" class="fw-bold text-end">Total</td>
                                    <td id="totalBobot">0%</td>
                                    <td id="totalNilai">0</td>
                                    <td id="totalStatus">-</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

                <!-- Grafik Perkembangan -->
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-transparent">
                    <h6 class="mb-0 fw-bold">Grafik Perkembangan KPI</h6>
                    </div>
                    <div class="card-body">
                    <div id="chartKPI" style="min-height:300px;"></div>
                    </div>
                </div>
                </div>
            </div>
        </div>
@endsection

@section('script')
<script src="{{ asset('assets/bundles/dataTables.bundle.js') }}"></script>
<script src="{{asset('assets/bundles/apexcharts.bundle.js')}}"></script>

<script>
let currentEmployeeId = {{ Auth::user()->employee->id_karyawan ?? 'null' }};
let currentPeriodId = null;
let availablePeriods = [];
let kpiChart = null;
let allMonthlyData = {}; // Menyimpan semua data bulanan

// Load available periods
async function loadAvailablePeriods() {
    try {
        const response = await fetch('/api/periods?kpi_published=true');
        const data = await response.json();

        if (data.success) {
            availablePeriods = data.data;
            populatePeriodDropdown(availablePeriods);
            
            // Auto-load periode terbaru
            if (availablePeriods.length > 0) {
                const latestPeriod = availablePeriods[0];
                selectPeriod(latestPeriod);
                
                // Load data bulanan untuk grafik
                await loadAllMonthlyData(currentEmployeeId);
            }
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        console.error('Error loading periods:', error);
        document.getElementById('periodList').innerHTML = '<li><h6 class="dropdown-header text-danger">Gagal memuat data bulan</h6></li>';
    }
}

// Load semua data bulanan untuk grafik
async function loadAllMonthlyData(employeeId) {
    try {
        allMonthlyData = {};
        
        // Load data untuk setiap periode
        for (const period of availablePeriods) {
            try {
                const response = await fetch(`/api/kpis/employee/${employeeId}/detail/${period.id_periode}`);
                const data = await response.json();
                
                if (data.success) {
                    const periodData = data.data;
                    const startDate = new Date(period.tanggal_mulai);
                    const monthKey = startDate.toLocaleDateString('id-ID', { 
                        month: 'short', 
                        year: 'numeric' 
                    });
                    
                    // Simpan data lengkap termasuk detail aspek
                    allMonthlyData[monthKey] = {
                        month: monthKey,
                        totalScore: periodData.kpi_summary.total_score,
                        averageScore: periodData.kpi_summary.average_score,
                        periodName: period.nama,
                        fullDate: startDate,
                        kpiDetails: periodData.kpi_details // Simpan detail aspek
                    };
                }
            } catch (error) {
                console.error(`Error loading data for period ${period.id_periode}:`, error);
            }
        }
        
        // Update chart dengan data bulanan
        updateMonthlyChart();
        
    } catch (error) {
        console.error('Error loading monthly data:', error);
    }
}

// Update chart dengan data bulanan
function updateMonthlyChart() {
    // Convert object ke array dan urutkan berdasarkan tanggal
    const monthlyArray = Object.values(allMonthlyData).sort((a, b) => a.fullDate - b.fullDate);
    
    if (monthlyArray.length === 0) {
        // Tampilkan pesan jika tidak ada data
        document.getElementById('chartKPI').innerHTML = `
            <div class="text-center p-5">
                <i class="icofont-chart-line-alt fs-1 text-muted"></i>
                <p class="text-muted mt-2">Tidak ada data KPI bulanan yang tersedia</p>
            </div>
        `;
        return;
    }

    const categories = monthlyArray.map(item => item.month);
    const totalScores = monthlyArray.map(item => parseFloat(item.totalScore) || 0);

    if (kpiChart) {
        kpiChart.destroy();
    }

    const options = {
        chart: {
            type: 'line',
            height: 350,
            zoom: {
                enabled: false
            },
            toolbar: {
                show: true
            }
        },
        series: [
            {
                name: 'Total Nilai KPI',
                data: totalScores
            }
        ],
        stroke: {
            width: 3,
            curve: 'smooth'
        },
        markers: {
            size: 5,
            hover: {
                size: 7
            }
        },
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
                text: 'Total Nilai KPI',
                style: {
                    fontSize: '14px',
                    fontWeight: 'bold'
                }
            },
            min: 0,
            max: Math.max(...totalScores) * 1.1, // Beri sedikit ruang di atas
            labels: {
                formatter: function(val) {
                    return val.toFixed(0);
                }
            }
        },
        colors: ['#0d6efd'],
        grid: {
            borderColor: '#f1f1f1',
            strokeDashArray: 4
        },
        legend: {
            position: 'top',
            horizontalAlign: 'center'
        },
        tooltip: {
            custom: function({ series, seriesIndex, dataPointIndex, w }) {
                const monthData = monthlyArray[dataPointIndex];
                const totalScore = series[seriesIndex][dataPointIndex];
                
                // Buat tooltip custom dengan detail aspek
                let tooltipHTML = `
                    <div class="apexcharts-tooltip-title" style="font-weight: bold; margin-bottom: 8px;">
                        ${monthData.month}
                    </div>
                    <div style="padding: 4px 0;">
                        <strong>Total Nilai: ${totalScore.toFixed(2)}</strong>
                    </div>
                `;
                
                // Tambahkan detail aspek jika ada
                if (monthData.kpiDetails && monthData.kpiDetails.length > 0) {
                    tooltipHTML += `<div style="border-top: 1px solid #e0e0e0; margin-top: 6px; padding-top: 6px;">`;
                    tooltipHTML += `<div style="font-weight: 600; margin-bottom: 4px;">Detail Aspek:</div>`;
                    
                    monthData.kpiDetails.forEach(aspek => {
                        const nilai = parseFloat(aspek.score) || 0;
                        tooltipHTML += `
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 2px 0; font-size: 12px;">
                                <span>${aspek.aspek_kpi}:</span>
                                <strong style="margin-left: 8px;">${nilai.toFixed(1)}</strong>
                            </div>
                        `;
                    });
                    
                    tooltipHTML += `</div>`;
                }
                
                return tooltipHTML;
            }
        },
        dataLabels: {
            enabled: false
        },
        responsive: [{
            breakpoint: 768,
            options: {
                chart: {
                    height: 300
                },
                xaxis: {
                    labels: {
                        rotate: -45
                    }
                }
            }
        }]
    };

    kpiChart = new ApexCharts(document.querySelector("#chartKPI"), options);
    kpiChart.render();
}

// Populate dropdown periode
function populatePeriodDropdown(periods) {
    const periodList = document.getElementById('periodList');
    
    if (periods.length === 0) {
        periodList.innerHTML = '<li><h6 class="dropdown-header">Tidak ada data KPI</h6></li>';
        return;
    }

    let dropdownHTML = '';
    
    // Urutkan periode dari yang terbaru
    const sortedPeriods = [...periods].sort((a, b) => new Date(b.tanggal_mulai) - new Date(a.tanggal_mulai));
    
    sortedPeriods.forEach(period => {
        const startDate = new Date(period.tanggal_mulai);
        const monthYear = startDate.toLocaleDateString('id-ID', { 
            month: 'long', 
            year: 'numeric' 
        });
        
        dropdownHTML += `
            <li>
                <a class="dropdown-item period-item" href="#" data-period-id="${period.id_periode}">
                    ${monthYear} (${period.nama})
                </a>
            </li>
        `;
    });
    
    periodList.innerHTML = dropdownHTML;

    // Add event listeners
    document.querySelectorAll('.period-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const periodId = this.getAttribute('data-period-id');
            const selectedPeriod = availablePeriods.find(p => p.id_periode == periodId);
            
            if (selectedPeriod) {
                selectPeriod(selectedPeriod);
            }
        });
    });
}

// Function ketika periode dipilih
function selectPeriod(period) {
    currentPeriodId = period.id_periode;
    
    // Update tampilan periode
    const startDate = new Date(period.tanggal_mulai);
    const monthName = startDate.toLocaleDateString('id-ID', { month: 'long' });
    const year = startDate.getFullYear();
    
    document.getElementById('currentPeriod').textContent = `${monthName} ${year}`;
    document.getElementById('periodMonth').textContent = monthName;
    document.getElementById('periodYear').textContent = year;
    
    // Load data KPI untuk periode yang dipilih
    loadKpiData(currentEmployeeId, currentPeriodId);
}

// Load data KPI untuk periode tertentu
async function loadKpiData(employeeId, periodId) {
    try {
        showLoading();
        const response = await fetch(`/api/kpis/employee/${employeeId}/detail/${periodId}`);
        const data = await response.json();

        if (data.success) {
            updateKpiSummary(data.data.kpi_summary);
            updateKpiTable(data.data.kpi_details);
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        console.error('Error loading KPI data:', error);
        showError('Gagal memuat data KPI: ' + error.message);
    } finally {
        hideLoading();
    }
}

// Update summary KPI - STANDARDIZED WITH CONTROLLER
function updateKpiSummary(summary) {
    document.getElementById('totalScore').textContent = summary.total_score.toFixed(2);
    document.getElementById('ranking').textContent = summary.ranking;
    document.getElementById('rankingText').textContent = `dari ${summary.total_employees} orang`;
    
    // ⚠️ PERBAIKAN: Calculate grade berdasarkan TOTAL SCORE bukan average score
    const totalScore = parseFloat(summary.total_score) || 0;
    let grade, gradeText, gradeColor;
    
    if (totalScore >= 90) {
        grade = 'A'; gradeText = 'Sangat Baik'; gradeColor = 'text-success';
    } else if (totalScore >= 80) {
        grade = 'B'; gradeText = 'Baik'; gradeColor = 'text-success';
    } else if (totalScore >= 70) {
        grade = 'C'; gradeText = 'Cukup'; gradeColor = 'text-warning';
    } else if (totalScore >= 50) {
        grade = 'D'; gradeText = 'Kurang'; gradeColor = 'text-warning';
    } else {
        grade = 'E'; gradeText = 'Sangat Kurang'; gradeColor = 'text-danger';
    }
    
    document.getElementById('grade').textContent = grade;
    document.getElementById('grade').className = `fw-bold ${gradeColor}`;
    document.getElementById('gradeText').textContent = gradeText;
    
    console.log("KPI Summary Updated:", {
        totalScore: totalScore,
        averageScore: summary.average_score,
        grade: grade,
        status: gradeText
    });
}

function updateKpiTable(details) {
    const tbody = document.getElementById('kpiTableBody');
    tbody.innerHTML = '';
    
    let totalBobot = 0;
    let totalNilai = 0;
    let rowCount = 0;

    details.forEach((item, index) => {
        const nilai = parseFloat(item.score) || 0;
        const bobot = parseFloat(item.bobot) || 0;
        const status = item.status;
        const statusClass = getStatusClass(status);
        
        const row = `
            <tr>
                <td><span class="fw-bold">${index + 1}</span></td>
                <td><span class="fw-bold ms-1">${item.aspek_kpi}</span></td>
                <td><span class="fw-bold ms-1">${bobot.toFixed(1)}%</span></td>
                <td><span class="fw-bold ms-1">${nilai.toFixed(2)}</span></td>
                <td><span class="kpi-badge ${statusClass}">${status}</span></td>
            </tr>
        `;
        
        tbody.innerHTML += row;
        
        totalBobot += bobot;
        totalNilai += nilai;
        rowCount++;
    });

    // Update footer
    document.getElementById('totalBobot').textContent = totalBobot.toFixed(1) + '%';
    document.getElementById('totalNilai').textContent = totalNilai.toFixed(2);
    
    // ⚠️ PERBAIKAN: Overall status berdasarkan TOTAL SCORE bukan rata-rata
    const totalScore = rowCount > 0 ? totalNilai : 0;
    const overallStatus = getOverallStatus(totalScore);
    document.getElementById('totalStatus').innerHTML = `<span class="kpi-badge ${getStatusClass(overallStatus)}">${overallStatus}</span>`;

    console.log("KPI Table Updated:", {
        totalBobot: totalBobot,
        totalNilai: totalNilai,
        totalScore: totalScore,
        overallStatus: overallStatus
    });
}

// Helper functions
function getStatusClass(status) {
    const statusMap = {
        'Sangat Baik': 'badge-excellent',
        'Baik': 'badge-good',
        'Cukup': 'badge-average',
        'Kurang': 'badge-poor',
        'Sangat Kurang': 'badge-poor'
    };
    return statusMap[status] || 'badge-average';
}


function getOverallStatus(score) {
    const numericScore = parseFloat(score) || 0;
    if (numericScore >= 90) return 'Sangat Baik';
    if (numericScore >= 80) return 'Baik';
    if (numericScore >= 70) return 'Cukup';
    if (numericScore >= 50) return 'Kurang';
    return 'Sangat Kurang';
}

// Loading functions
function showLoading() {
    document.getElementById('kpiTableBody').innerHTML = `
        <tr>
            <td colspan="5" class="text-center">
                <div class="spinner-border spinner-border-sm" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                Memuat data...
            </td>
        </tr>
    `;
}

function hideLoading() {
    // Hide loading indicator
}

function showError(message) {
    document.getElementById('kpiTableBody').innerHTML = `
        <tr>
            <td colspan="5" class="text-center text-danger">
                <i class="icofont-close-circled"></i> ${message}
            </td>
        </tr>
    `;
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    if (currentEmployeeId) {
        loadAvailablePeriods();
    }
    
    // Initialize DataTable
    $('#myProjectTable').DataTable({
        responsive: true,
        searching: false,
        paging: false,
        info: false,
        ordering: false
    });
});

// Add CSS untuk badge
const style = document.createElement('style');
style.textContent = `
    .kpi-badge {
        padding: 5px 10px;
        border-radius: 20px;
        font-weight: 500;
        font-size: 0.8rem;
    }
    .badge-excellent {
        background-color: rgba(40, 167, 69, 0.2);
        color: #28a745;
    }
    .badge-good {
        background-color: rgba(23, 162, 184, 0.2);
        color: #17a2b8;
    }
    .badge-average {
        background-color: rgba(255, 193, 7, 0.2);
        color: #ffc107;
    }
    .badge-poor {
        background-color: rgba(220, 53, 69, 0.2);
        color: #dc3545;
    }
    
    /* Custom tooltip styling */
    .apexcharts-tooltip {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border: 1px solid #e0e0e0;
    }
`;
document.head.appendChild(style);
</script>
@endsection