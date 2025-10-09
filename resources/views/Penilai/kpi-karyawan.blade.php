@extends('template.template')

@section('title', 'KPI Karyawan')

@section('content')
<!-- CSS Tabel -->
<link rel="stylesheet" href="{{ asset('assets/plugin/datatables/responsive.dataTables.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugin/datatables/dataTables.bootstrap5.min.css') }}">

<!-- Main content-->
<div class="body d-flex py-lg-3 py-md-2">
    <div class="container-xxl">
        <div class="row align-items-center">
            <div class="border-0 mb-4">
                <div
                    class="card-header py-3 no-bg bg-transparent d-flex align-items-center px-0 justify-content-between border-bottom flex-wrap"
                >
                    <h3 class="fw-bold mb-0">Nilai Karyawan</h3>
                    <button class="btn btn-primary btn-sm" id="btnRefresh">
                        <i class="icofont-refresh"></i> Refresh Data
                    </button>
                </div>
            </div>
        </div>
        <div class="row clearfix g-3">
            <!-- Tabel Full Width -->
            <div class="col-12">
                <div class="card mb-3">
                    <div class="card-body">
                        <table
                            id="myProjectTable"
                            class="table table-hover align-middle mb-0"
                            style="width: 100%"
                        >
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Foto</th>
                                    <th>Periode</th>
                                    <th>Nama</th>
                                    <th>Divisi</th>
                                    <th>Jabatan</th>
                                    <th>Status KPI</th>
                                    <th>Total Score</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                <!-- Data akan di-load via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- Row End -->
    </div>
</div>

<!-- Modal Wizard -->
<div class="modal fade" id="modalWizard" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Form Penilaian KPI</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <!-- Header Info Karyawan -->
            <div class="modal-header bg-light">
                <div class="d-flex align-items-center w-100">
                    <img src="{{ asset('assets/images/xs/avatar2.jpg') }}" class="rounded-circle me-3" width="60" height="60" alt="Foto Karyawan" id="modalEmployeePhoto">
                    <div class="flex-grow-1">
                        <h6 class="mb-1" id="modalEmployeeName">Nama Karyawan</h6>
                        <p class="mb-1 text-muted" id="modalEmployeeDivision">Divisi</p>
                        <p class="mb-0 text-muted" id="modalEmployeePosition">Jabatan</p>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-warning" id="modalEmployeeStatus">Belum Dinilai</span>
                        <p class="mb-0 small text-muted" id="modalPeriodInfo">Periode: -</p>
                    </div>
                </div>
            </div>

            <div class="modal-body">
                <div id="wizardContent"></div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" id="prevStep">Kembali</button>
                <button class="btn btn-success" id="saveStep">Simpan Progress</button>
                <button class="btn btn-primary" id="nextStep">Lanjut</button>
                <button class="btn btn-success" id="finishWizard">Selesai & Simpan</button>
            </div>
        </div>
    </div>
</div>

<!-- Styles -->
<style>
    .step-container {
        display: flex;
        justify-content: center;
        align-items: center;
        margin: 30px 0;
        position: relative;
        gap: 20px;
    }

      .swal2-container {
    z-index: 99999 !important;
  }

    .step-container::before {
        content: "";
        position: absolute;
        top: 50%;
        left: 15%;
        right: 15%;
        height: 4px;
        background: linear-gradient(90deg, #6a11cb, #2575fc);
        z-index: 0;
    }

    .step-btn {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: white;
        border: 3px solid #ccc;
        font-weight: bold;
        color: #777;
        z-index: 1;
        transition: all 0.3s ease;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .step-btn.active {
        background: linear-gradient(135deg, #6a11cb, #2575fc);
        color: white;
        border-color: transparent;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        transform: scale(1.1);
    }

    .step-btn:hover:not(.active) {
        transform: scale(1.05);
        border-color: #6a11cb;
    }

    .kpi-section {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
    }

    .kpi-title {
        color: #2c3e50;
        border-bottom: 2px solid #3498db;
        padding-bottom: 8px;
        margin-bottom: 15px;
    }

    .point-section {
        background: white;
        border-radius: 6px;
        padding: 12px;
        margin-bottom: 12px;
        border-left: 4px solid #3498db;
    }

    .question-item {
        margin-bottom: 10px;
        padding: 8px;
        background: #f8f9fa;
        border-radius: 4px;
    }

    .wizard-step {
        min-height: 400px;
    }

    /* Styling untuk radio button options */
    .score-options {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .score-option {
        display: flex;
        align-items: center;
        padding: 10px;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .score-option:hover {
        background-color: #f8f9fa;
        border-color: #6c757d;
    }

    .score-option input[type="radio"] {
        margin-right: 10px;
        transform: scale(1.2);
    }

    .score-label {
        cursor: pointer;
        font-weight: 500;
        margin: 0;
        flex: 1;
    }

    .score-option input[type="radio"]:checked + .score-label {
        color: #198754;
        font-weight: 600;
    }

    .score-option input[type="radio"]:checked {
        accent-color: #198754;
    }

    /* Styling untuk subaspek card */
    .subaspek-card {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        margin-bottom: 20px;
        overflow: hidden;
    }

    .subaspek-header {
        background-color: #f8f9fa;
        padding: 12px 15px;
        border-bottom: 1px solid #e0e0e0;
    }

    .subaspek-body {
        padding: 15px;
    }

    .score-badge {
        font-size: 0.9em;
        padding: 4px 8px;
    }

    .progress {
        height: 8px;
    }

    .absence-calculation-step {
        max-height: 500px;
        overflow-y: auto;
    }

    .employee-info-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
</style>
@endsection

@section('script')
<script src="{{ asset('assets/bundles/dataTables.bundle.js') }}"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        /* ================================
           VARIABEL GLOBAL
        ================================= */
        let currentStep = 1;
        let totalSteps = 0;
        let currentEmployeeId = null;
        let currentPeriodId = null;
        let currentPeriodData = null;
        let answersMap = {};
        let stepsData = [];
        let currentEmployeeData = null;

        // DOM Elements
        const modalWizardEl = document.getElementById('modalWizard');
        const wizardContent = document.getElementById('wizardContent');
        const prevBtn = document.getElementById('prevStep');
        const nextBtn = document.getElementById('nextStep');
        const finishBtn = document.getElementById('finishWizard');
        const saveStepBtn = document.getElementById('saveStep');

        // Modal employee info elements
        const modalEmployeePhoto = document.getElementById('modalEmployeePhoto');
        const modalEmployeeName = document.getElementById('modalEmployeeName');
        const modalEmployeeDivision = document.getElementById('modalEmployeeDivision');
        const modalEmployeePosition = document.getElementById('modalEmployeePosition');
        const modalEmployeeStatus = document.getElementById('modalEmployeeStatus');
        const modalPeriodInfo = document.getElementById('modalPeriodInfo');

        /* ================================
           FUNGSI HELPER
        ================================ */
        function showAlert(icon, title, text) {
            return Swal.fire({
                icon: icon,
                title: title,
                text: text,
                confirmButtonColor: '#3085d6',
            });
        }

        function showConfirm(title, text, confirmText = 'Ya', cancelText = 'Batal') {
            return Swal.fire({
                title: title,
                text: text,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: confirmText,
                cancelButtonText: cancelText,
                reverseButtons: true
            });
        }

        function showLoading(show) {
            const spinner = document.getElementById('loadingSpinner');
            if (show) {
                spinner.classList.remove('d-none');
            } else {
                spinner.classList.add('d-none');
            }
        }

        function showError(message) {
            showAlert('error', 'Error!', message);
        }

        function showSuccess(message) {
            showAlert('success', 'Berhasil!', message);
        }

        function formatDate(dateString) {
            if (!dateString) return '-';
            const options = {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            };
            return new Date(dateString).toLocaleDateString('id-ID', options);
        }

        function initializeDataTable() {
    // Perbaikan destroy DataTable
    const table = $('#myProjectTable');
    if ($.fn.DataTable.isDataTable('#myProjectTable')) {
        try {
            table.DataTable().clear().destroy();
        } catch (e) {
            console.log('Error destroying table:', e);
        }
    }

    // Clear tbody
    $('#tableBody').empty();

    return table.DataTable({
        responsive: true,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        processing: true,
        serverSide: false,
        ajax: {
            url: "{{ url('/api/kpis/kpi-evaluation/employees/non-head') }}?division_id={{ auth()->user()->employee->roles->first()->division_id ?? 'null' }}",
            type: 'GET',
            dataSrc: function (json) {
                console.log('DataTable JSON Response:', json);
                
                if (!json || !json.success) {
                    console.error('API Error:', json?.message);
                    return [];
                }
                
                // Tampilkan pesan jika tidak ada data
                if (json.data.length === 0) {
                    const infoDiv = $('<div class="alert alert-success text-center">')
                        .html(`
                            <i class="icofont-check-circled"></i>
                            <strong>Semua karyawan sudah dinilai!</strong><br>
                            <small>Tidak ada karyawan yang belum dinilai untuk periode ini</small>
                        `);
                    $('.card-body').prepend(infoDiv);
                } else {
                    // Hapus pesan sebelumnya jika ada
                    $('.alert-success, .alert-info').remove();
                }
                
                return json.data || [];
            },
            error: function (xhr, error, thrown) {
                console.error('DataTable AJAX Error:', error, thrown);
                return [];
            }
        },
        columns: [
            {
                data: null,
                render: function(data, type, row, meta) {
                    return meta.row + 1;
                }
            },
{
    data: null,
    render: function(data) {
        // Handle foto dengan logic yang solid
        let fotoUrl = '{{ asset("assets/images/profile_av.png") }}';
        
        if (data.photo && data.photo !== '' && data.photo !== 'null') {
            // Jika foto sudah full URL
            if (data.photo.startsWith('http') || data.photo.includes('://')) {
                fotoUrl = data.photo;
            } 
            // Jika foto adalah path storage
            else if (data.photo.startsWith('storage/')) {
                fotoUrl = '{{ url("storage") }}/' + data.photo.replace('storage/', '');
            }
            // Jika hanya nama file, assume di storage
            else {
                fotoUrl = '{{ url("storage") }}/' + data.photo;
            }
        }
        
        // ✅ PERBAIKAN: Pastiin path asset bener
        return `<img src="${fotoUrl}" class="rounded-circle" width="40" height="40" style="object-fit: cover;" onerror="this.onerror=null; this.src='{{ asset("assets/images/profile_av.png") }}'">`;
    }
},
            {
                data: null,
                render: function(data) {
                    const periodName = data.period || '-';
                    const periodMonth = data.period_month || '';
                    const periodYear = data.period_year || '';
                    
                    let badgeText = periodName;
                    if (periodMonth && periodYear) {
                        badgeText = `${periodMonth} ${periodYear}`;
                    }
                    
                    return `<span class="badge bg-primary">${badgeText}</span>`;
                }
            },
            {
                data: 'nama'
            },
            {
                data: 'division'
            },
            {
                data: 'position'
            },
            {
                data: null,
                render: function(row) {
                    const score = parseFloat(row.score) || 0;
                    if (score > 0) {
                        return '<span class="badge bg-success">Sudah Dinilai</span>';
                    } else {
                        return '<span class="badge bg-warning">Belum Dinilai</span>';
                    }
                }
            },
            {
                data: null,
                render: function(row) {
                    const score = parseFloat(row.score) || 0;
                    if (score > 0) {
                        return `<span class="badge bg-primary">${score.toFixed(2)}</span>`;
                    } else {
                        return '<span class="badge bg-secondary">-</span>';
                    }
                }
            },
            {
    data: null,
    render: function(row) {
        const score = parseFloat(row.score) || 0;
        const periodId = row.period_id || null;
        const divisionId = row.division_id || null;
        
        if (!divisionId) {
            return '<span class="text-muted">Divisi tidak valid</span>';
        }

        // Handle foto untuk modal - ✅ PERBAIKI PATH
        let fotoForModal = '{{ asset("assets/images/profile_av.png") }}';
        if (row.photo && row.photo !== '' && row.photo !== 'null') {
            if (row.photo.startsWith('http') || row.photo.includes('://')) {
                fotoForModal = row.photo;
            } else if (row.photo.startsWith('storage/')) {
                fotoForModal = '{{ url("storage") }}/' + row.photo.replace('storage/', '');
            } else {
                fotoForModal = '{{ url("storage") }}/' + row.photo;
            }
        }

        if (score > 0) {
            return `
            <div class="btn-group">
                <button type="button" 
                    class="btn btn-outline-secondary btn-sm btn-nilai" 
                    data-id="${row.id_karyawan}" 
                    data-nama="${row.nama}" 
                    data-divisi="${row.division}"
                    data-divisi-id="${divisionId}"
                    data-position="${row.position}"
                    data-foto="${fotoForModal}"
                    data-period-id="${periodId}">
                    <i class="icofont-edit text-success"></i> Edit Nilai
                </button>
            </div>`;
        } else {
            return `
            <button type="button" 
                class="btn btn-outline-primary btn-sm btn-nilai" 
                data-id="${row.id_karyawan}" 
                data-nama="${row.nama}" 
                data-divisi="${row.division}"
                data-divisi-id="${divisionId}"
                data-position="${row.position}"
                data-foto="${fotoForModal}"
                data-period-id="${periodId}">
                <i class="icofont-edit text-success"></i> Nilai
            </button>`;
        }
    }
}
        ],
        language: {
            emptyTable: "Tidak ada Karyawan dengan absensi di periode yang aktif",
            loadingRecords: "Memuat data...",
            processing: "Memproses...",
            zeroRecords: "Tidak ada data yang cocok"
        },
        initComplete: function(settings, json) {
            console.log('📊 DataTable initialized successfully');
            
            // Hapus pesan loading jika ada
            $('.alert-info, .alert-danger').remove();
        },
        drawCallback: function(settings) {
            console.log('DataTable draw completed');
        }
    });
}

// GANTI SEMUA path jadi default avatar dulu
function updateModalEmployeeInfo() {
    if (!currentEmployeeData) return;

    modalEmployeeName.textContent = currentEmployeeData.nama;
    modalEmployeeDivision.textContent = currentEmployeeData.divisi;
    modalEmployeePosition.textContent = currentEmployeeData.position || '-';
    
    // ✅ PAKSA PAKE DEFAULT AVATAR DULU
    modalEmployeePhoto.src = '{{ asset("assets/images/profile_av.png") }}';

    if (currentPeriodData) {
        modalPeriodInfo.textContent = `Periode: ${currentPeriodData.nama} (${formatDate(currentPeriodData.tanggal_mulai)} - ${formatDate(currentPeriodData.tanggal_selesai)})`;
    }
}

        function openWizardModal(divisiId, periodId) {
            if (!periodId) {
                showAlert('warning', 'Peringatan', 'Periode tidak valid!');
                return;
            }

            // Set current period data
            currentPeriodId = periodId;
            
            // Show loading dalam modal
            wizardContent.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p>Memuat data KPI...</p>
                </div>
            `;

            const modal = new bootstrap.Modal(document.getElementById('modalWizard'));
            modal.show();

            let url = `/api/kpis/division/${divisiId}?periode_id=${periodId}`;
            console.log('Fetching KPI for Karyawan from:', url);

            fetch(url)
                .then(res => {
                    if (!res.ok) {
                        throw new Error(`HTTP error! status: ${res.status}`);
                    }
                    return res.json();
                })
                .then(response => {
                    console.log('KPI Response for Karyawan:', response);

                    let kpis = [];
                    if (response.success && response.data) {
                        kpis = response.data;
                    }

                    if (kpis.length === 0) {
                        wizardContent.innerHTML = `
                            <div class="alert alert-warning">
                                <i class="icofont-warning"></i>
                                Tidak ada KPI yang ditetapkan untuk Karyawan ini pada periode ini!
                            </div>
                        `;
                        return;
                    }

                    buildStepsFromKpis(kpis);
                })
                .catch(err => {
                    console.error("Error load KPI for Karyawan:", err);
                    wizardContent.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="icofont-warning"></i>
                            Gagal memuat data KPI: ${err.message}
                        </div>
                    `;
                });
        }

        function buildStepsFromKpis(kpis) {
            console.log('=== DEBUG KPI DATA ===');
            console.log('Raw KPI data:', kpis);

            stepsData = [];
            answersMap = {};

            // Load saved answers sebelum build steps
            loadSavedAnswers();

            // Deduplikasi KPI berdasarkan ID KPI
            const uniqueKpis = [];
            const seenKpiIds = new Set();

            kpis.forEach((kpi) => {
                const kpiId = kpi.id_kpi || kpi.id;
                
                if (seenKpiIds.has(kpiId)) {
                    console.log(`❌ SKIPPING DUPLICATE KPI: ${kpi.nama} (ID: ${kpiId})`);
                    return;
                }
                
                seenKpiIds.add(kpiId);
                uniqueKpis.push(kpi);
                
                console.log(`✅ ADDED KPI: ${kpi.nama} (ID: ${kpiId})`);
            });

            console.log('🔍 After deduplication:', {
                original: kpis.length,
                unique: uniqueKpis.length,
                duplicates: kpis.length - uniqueKpis.length,
                uniqueKpiIds: Array.from(seenKpiIds)
            });

            // Process KPI unik
            uniqueKpis.forEach((kpi, index) => {
                console.log(`🎯 Processing KPI ${index + 1}: ${kpi.nama} (ID: ${kpi.id_kpi})`);

                const kpiId = kpi.id_kpi || kpi.id || null;
                const kpiName = kpi.nama || kpi.name || 'Aspek ' + (index + 1);
                const kpiBobot = kpi.bobot || 0;
                const points = kpi.points || [];

                // Filter points yang valid
                const filteredPoints = points.filter(point => {
                    const questions = point.questions || [];
                    const isAbsensi = point.nama?.toLowerCase().includes('absensi') || 
                                    point.nama?.toLowerCase().includes('kehadiran');
                    
                    return point.nama && (questions.length > 0 || isAbsensi);
                });

                if (filteredPoints.length === 0) {
                    console.log(`⚠️ Skipping KPI ${kpiName} - no valid points`);
                    return;
                }

                const stepData = {
                    stepIndex: stepsData.length + 1,
                    kpiId,
                    kpiName,
                    kpiBobot,
                    points: []
                };

                filteredPoints.forEach((point, pointIndex) => {
                    const questions = point.questions || [];
                    console.log(`   📌 Point ${pointIndex + 1}: ${point.nama} (ID: ${point.id_point})`);

                    const isAbsensi = point.nama?.toLowerCase().includes('absensi') || 
                                    point.nama?.toLowerCase().includes('kehadiran');

                    const pointData = {
                        pointId: point.id_point || point.id || `point_${kpiId}_${pointIndex}`,
                        pointName: point.nama || point.name || `Sub-Aspek ${pointIndex + 1}`,
                        pointBobot: point.bobot || 0,
                        isAbsensi: isAbsensi,
                        questions: []
                    };

                    questions.forEach((q, qIndex) => {
                        const questionId = q.id_question || q.id || `q_${pointData.pointId}_${qIndex}`;
                        
                        // Apply saved answer jika ada
                        const savedAnswer = answersMap[questionId];
                        
                        pointData.questions.push({
                            id: questionId,
                            pertanyaan: q.pertanyaan || q.text || `Pertanyaan ${qIndex + 1}`,
                            answer: savedAnswer !== undefined ? savedAnswer : (q.answer || null)
                        });
                    });

                    stepData.points.push(pointData);
                });

                stepsData.push(stepData);
                console.log(`✅ Added KPI to steps: ${kpiName} with ${filteredPoints.length} points`);
            });

            console.log('🎯 Final stepsData:', stepsData);
            console.log('📊 Total steps:', stepsData.length);
            
            renderWizardSteps();
        }

        function renderWizardSteps() {
            console.log('🔄 Rendering wizard steps:', stepsData.length);
            
            totalSteps = stepsData.length;
            currentStep = 1;

            // Render step indicator
            let stepHtml = `<div class="step-container">`;

            for (let i = 1; i <= totalSteps; i++) {
                console.log(`📝 Creating step ${i}: ${stepsData[i-1]?.kpiName}`);
                stepHtml += `
                <button class="step-btn ${i === 1 ? 'active' : ''}" data-step="${i}">
                    ${i}
                </button>
                `;
            }

            stepHtml += `</div>`;

            // Render current step content
            stepHtml += renderStepContent(stepsData[0]);

            wizardContent.innerHTML = stepHtml;

            // Tambahkan event listener untuk step buttons
            document.querySelectorAll('.step-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const stepNum = parseInt(this.getAttribute('data-step'));
                    goToStep(stepNum);
                });
            });

            updateWizardButtons();
            
            console.log('✅ Wizard rendered with', totalSteps, 'steps');
        }

        function renderStepContent(step) {
            const scoreLabels = {
                1: 'Sangat Tidak Baik',
                2: 'Tidak Baik', 
                3: 'Baik',
                4: 'Sangat Baik'
            };
            
            // Cek jika ada point absensi di step ini
            const absencePoint = step.points.find(point => point.isAbsensi);
            
            if (absencePoint) {
                setTimeout(() => {
                    loadAbsenceCalculationData(absencePoint.pointId);
                }, 100);

                return renderAbsenceCalculationStep(step, absencePoint);
            }

            // Render normal untuk step tanpa absensi
            let html = `
                <h4 class="text-center mb-4">${step.kpiName}</h4>
                <p class="text-center text-muted mb-4">Bobot: ${step.kpiBobot}%</p>
                
                <div class="alert alert-info d-flex align-items-center">
                    <i class="icofont-info-circle me-2"></i>
                    <small>Jawaban yang sudah disimpan akan tetap tersimpan meskipun halaman di-refresh</small>
                </div>
            `;

            step.points.forEach(point => {
                html += `
                    <div class="subaspek-card">
                        <div class="subaspek-header">
                            <h6 class="mb-0">${point.pointName}</h6>
                            <small class="text-muted">Bobot: ${point.pointBobot}%</small>
                        </div>
                        <div class="subaspek-body">
                `;

                if (point.questions && point.questions.length > 0) {
                    point.questions.forEach((question, qIndex) => {
                        // Gunakan answersMap sebagai sumber utama
                        const currentAnswer = answersMap[question.id] !== undefined ? 
                            answersMap[question.id] : 
                            (question.answer || null);

                        html += `
                            <div class="question-item">
                                <p class="fw-semibold">${qIndex + 1}. ${question.pertanyaan}</p>
                                <div class="score-options">
                        `;

                        for (let score = 1; score <= 4; score++) {
                            const isChecked = currentAnswer == score;
                            
                            html += `
                                <div class="score-option">
                                    <input type="radio" 
                                        id="q_${question.id}_${score}" 
                                        name="q_${question.id}" 
                                        value="${score}"
                                        ${isChecked ? 'checked' : ''}
                                        onchange="updateAnswer('${question.id}', ${score})">
                                    <label for="q_${question.id}_${score}" class="score-label">
                                        ${score} - ${scoreLabels[score]}
                                        ${isChecked ? ' ✓' : ''}
                                    </label>
                                </div>
                            `;
                        }

                        // Tampilkan status saved
                        if (currentAnswer) {
                            html += `
                                <div class="mt-2">
                                    <small class="text-success">
                                        <i class="icofont-check-circled"></i>
                                        Jawaban tersimpan: ${currentAnswer} - ${scoreLabels[currentAnswer]}
                                    </small>
                                </div>
                            `;
                        }

                        html += `</div></div>`;
                    });
                } else {
                    html += `
                        <div class="alert alert-info">
                            <i class="icofont-info-circle"></i>
                            Tidak ada pertanyaan untuk sub-aspek ini.
                        </div>
                    `;
                }

                html += `</div></div>`;
            });

            return html;
        }

        function renderAbsenceCalculationStep(step, absencePoint) {
            const scoreLabels = {
                1: 'Sangat Tidak Baik',
                2: 'Tidak Baik',
                3: 'Baik', 
                4: 'Sangat Baik'
            };

            let html = `
                <div class="absence-calculation-step">
                    <h4 class="text-center mb-4">${step.kpiName} - ${absencePoint.pointName}</h4>
                    <p class="text-center text-muted mb-4">Penilaian Absensi Otomatis</p>
                    
                    <div class="alert alert-info">
                        <i class="icofont-info-circle"></i> 
                        Nilai absensi dihitung otomatis berdasarkan data kehadiran karyawan
                    </div>

                    <div id="absenceCalculationContainer-${absencePoint.pointId}" class="mt-4">
                        <div class="text-center">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p>Menghitung data absensi...</p>
                        </div>
                    </div>
                </div>
            `;

            const nonAbsencePoints = step.points.filter(point => !point.isAbsensi);
            
            if (nonAbsencePoints.length > 0) {
                html += `<hr class="my-4">`;
                html += `<h5 class="text-center mb-3">Sub-Aspek Lainnya</h5>`;
                
                nonAbsencePoints.forEach(point => {
                    html += `
                        <div class="subaspek-card mt-3">
                            <div class="subaspek-header">
                                <h6 class="mb-0">${point.pointName}</h6>
                                <small class="text-muted">Bobot: ${point.pointBobot}%</small>
                            </div>
                            <div class="subaspek-body">
                    `;

                    if (point.questions && point.questions.length > 0) {
                        point.questions.forEach((question, qIndex) => {
                            const currentAnswer = answersMap[question.id] || question.answer || null;

                            html += `
                                <div class="question-item">
                                    <p class="fw-semibold">${qIndex + 1}. ${question.pertanyaan}</p>
                                    <div class="score-options">
                            `;

                            for (let score = 1; score <= 4; score++) {
                                const isChecked = currentAnswer == score;

                                html += `
                                    <div class="score-option">
                                        <input type="radio" 
                                            id="q_${question.id}_${score}" 
                                            name="q_${question.id}" 
                                            value="${score}"
                                            ${isChecked ? 'checked' : ''}
                                            onchange="updateAnswer('${question.id}', ${score})">
                                        <label for="q_${question.id}_${score}" class="score-label">
                                            ${score} - ${scoreLabels[score]}
                                        </label>
                                    </div>
                                `;
                            }

                            html += `</div></div>`;
                        });
                    }

                    html += `</div></div>`;
                });
            }

            return html;
        }

        function loadAbsenceCalculationData(pointId) {
            const container = document.getElementById(`absenceCalculationContainer-${pointId}`);
            
            // Show loading
            container.innerHTML = `
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p>Menghitung data absensi...</p>
                </div>
            `;
            
            fetch(`/api/kpis/attendance-calculation/${currentEmployeeId}/${currentPeriodId}`)
                .then(res => res.json())
                .then(response => {
                    if (response.success) {
                        container.innerHTML = renderAbsenceCalculationTable(response.data);
                        
                        // Simpan nilai ABSOLUT (0-100) tanpa konversi
                        const finalScore = response.data.calculation.final_score;
                        
                        // Simpan dalam skala 0-100 (sesuai dengan backend)
                        const attendanceKey = `attendance_${pointId}`;
                        answersMap[attendanceKey] = finalScore;
                        
                        // Simpan juga di localStorage
                        localStorage.setItem(`attendance_score_${currentEmployeeId}_${pointId}`, finalScore);
                        
                        console.log(`Auto-saved attendance score for point ${pointId}:`, finalScore);
                        
                    } else {
                        container.innerHTML = `
                            <div class="alert alert-danger">
                                <i class="icofont-warning"></i>
                                Gagal memuat data absensi: ${response.message}
                            </div>
                        `;
                    }
                })
                .catch(err => {
                    console.error('Error loading absence data:', err);
                    container.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="icofont-warning"></i>
                            Error: Gagal memuat data absensi - ${err.message}
                        </div>
                    `;
                });
        }

        function renderAbsenceCalculationTable(data) {
    const calc = data.calculation;
    const attendance = data.attendance_data;
    const config = data.config;
    
    return `
        <div class="absence-calculation-card">
            <!-- HEADER INFO -->
            <div class="card border-primary mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="icofont-calculator me-2"></i>
                        Ringkasan Perhitungan Absensi
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="border rounded p-2 bg-light">
                                <small class="text-muted d-block">Total Hari Kerja</small>
                                <h5 class="mb-0 text-primary">${attendance.total_work_days}</h5>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-2 bg-light">
                                <small class="text-muted d-block">Persentase Kehadiran</small>
                                <h5 class="mb-0 ${calc.attendance_percent >= 80 ? 'text-success' : calc.attendance_percent >= 60 ? 'text-warning' : 'text-danger'}">
                                    ${calc.attendance_percent}%
                                </h5>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-2 bg-light">
                                <small class="text-muted d-block">Total Points</small>
                                <h5 class="mb-0 text-info">${calc.total_points_x}/${calc.max_points_y}</h5>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-2 bg-success text-white">
                                <small class="d-block">Nilai Final</small>
                                <h4 class="mb-0">${calc.final_score}/100</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PERHITUNGAN DETAIL -->
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">
                        <i class="icofont-calculator me-2"></i>
                        Detail Perhitungan
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Jenis</th>
                                    <th>Jumlah</th>
                                    <th>Multiplier</th>
                                    <th>Points</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="${attendance.hadir > 0 ? 'table-success' : ''}">
                                    <td>Hadir</td>
                                    <td>${attendance.hadir}</td>
                                    <td>× ${config.hadir_multiplier}</td>
                                    <td>${calc.kehadiran_points}</td>
                                </tr>
                                <tr class="${attendance.sakit > 0 ? 'table-warning' : ''}">
                                    <td>Sakit</td>
                                    <td>${attendance.sakit}</td>
                                    <td>× ${config.sakit_multiplier}</td>
                                    <td>${calc.sakit_points}</td>
                                </tr>
                                <tr class="${attendance.izin > 0 ? 'table-info' : ''}">
                                    <td>Izin</td>
                                    <td>${attendance.izin}</td>
                                    <td>× ${config.izin_multiplier}</td>
                                    <td>${calc.izin_points}</td>
                                </tr>
                                <tr class="${attendance.mangkir > 0 ? 'table-danger' : ''}">
                                    <td>Mangkir</td>
                                    <td>${attendance.mangkir}</td>
                                    <td>× ${config.mangkir_multiplier}</td>
                                    <td>${calc.mangkir_points}</td>
                                </tr>
                                <tr class="${attendance.terlambat > 0 ? 'table-danger' : ''}">
                                    <td>Terlambat</td>
                                    <td>${attendance.terlambat}</td>
                                    <td>× ${config.terlambat_multiplier}</td>
                                    <td>${calc.terlambat_points}</td>
                                </tr>
                            </tbody>
                            <tfoot class="table-secondary fw-bold">
                                <tr>
                                    <td colspan="3">Sub Total</td>
                                    <td>${calc.sub_total}</td>
                                </tr>
                                <tr>
                                    <td colspan="3">Total Points (X)</td>
                                    <td>${calc.total_points_x}</td>
                                </tr>
                                <tr>
                                    <td colspan="3">Max Points (Y) = ${attendance.total_work_days} × ${config.workday_multiplier}</td>
                                    <td>${calc.max_points_y}</td>
                                </tr>
                                <tr class="table-primary">
                                    <td colspan="3">Persentase = (${calc.total_points_x} ÷ ${calc.max_points_y}) × 100</td>
                                    <td>${calc.attendance_percent}%</td>
                                </tr>
                                <tr class="table-success">
                                    <td colspan="3"><strong>Nilai Final</strong></td>
                                    <td><strong>${calc.final_score}/100</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- KONVERSI NILAI -->
                    <div class="mt-3 p-3 bg-light rounded">
                        <h6 class="mb-2">Konversi Persentase ke Nilai:</h6>
                        <div class="small">
                            <span class="badge ${calc.attendance_percent >= 100 ? 'bg-success' : 'bg-light text-dark'}">≥100% = 100</span>
                            <span class="badge ${calc.attendance_percent >= 90 && calc.attendance_percent < 100 ? 'bg-success' : 'bg-light text-dark'}">90-99% = 80</span>
                            <span class="badge ${calc.attendance_percent >= 80 && calc.attendance_percent < 90 ? 'bg-warning' : 'bg-light text-dark'}">80-89% = 60</span>
                            <span class="badge ${calc.attendance_percent >= 65 && calc.attendance_percent < 80 ? 'bg-warning' : 'bg-light text-dark'}">65-79% = 40</span>
                            <span class="badge ${calc.attendance_percent >= 50 && calc.attendance_percent < 65 ? 'bg-danger' : 'bg-light text-dark'}">50-64% = 20</span>
                            <span class="badge ${calc.attendance_percent < 50 ? 'bg-danger' : 'bg-light text-dark'}">&lt;50% = 0</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="alert alert-success mt-3">
                <div class="d-flex align-items-center">
                    <i class="icofont-check-circled fs-4 me-3"></i>
                    <div>
                        <strong>Nilai absensi otomatis: ${calc.final_score}/100</strong><br>
                        <small class="text-muted">Nilai ini akan otomatis tersimpan saat Anda menyelesaikan penilaian</small>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function goToStep(stepNumber) {
    if (stepNumber < 1 || stepNumber > totalSteps) return;

    currentStep = stepNumber;

    // Update step indicator
    document.querySelectorAll('.step-btn').forEach((btn, index) => {
        if (index + 1 === stepNumber) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    // Update content
    const stepContent = renderStepContent(stepsData[stepNumber - 1]);
    const stepContainer = wizardContent.querySelector('.step-container');
    wizardContent.innerHTML = '';
    wizardContent.appendChild(stepContainer);
    wizardContent.innerHTML += stepContent;

    // 🔥 TAMBAHKAN: Scroll ke atas modal body
    const modalBody = document.querySelector('.modal-body');
    if (modalBody) {
        modalBody.scrollTop = 0;
    }

    updateWizardButtons();
}

        function updateWizardButtons() {
            prevBtn.style.display = currentStep > 1 ? 'inline-block' : 'none';
            nextBtn.style.display = currentStep < totalSteps ? 'inline-block' : 'none';
            finishBtn.style.display = currentStep === totalSteps ? 'inline-block' : 'none';
        }

        function prevStep() {
            if (currentStep > 1) {
                goToStep(currentStep - 1);
            }
        }

        function nextStep() {
            if (currentStep < totalSteps) {
                goToStep(currentStep + 1);
            }
        }
        
                function loadSavedAnswers() {
            if (!currentEmployeeId || !currentPeriodId) return;

            // Load dari localStorage
            const savedAnswersKey = `kpi_answers_${currentEmployeeId}_${currentPeriodId}`;
            const savedAnswers = localStorage.getItem(savedAnswersKey);
            
            if (savedAnswers) {
                const parsedAnswers = JSON.parse(savedAnswers);
                answersMap = { ...answersMap, ...parsedAnswers };
                console.log('📥 Loaded saved answers:', parsedAnswers);
            }

            // Load attendance scores dari localStorage
            stepsData.forEach(step => {
                step.points.forEach(point => {
                    if (point.isAbsensi) {
                        const attendanceKey = `attendance_score_${currentEmployeeId}_${point.pointId}`;
                        const savedAttendanceScore = localStorage.getItem(attendanceKey);
                        if (savedAttendanceScore) {
                            answersMap[`attendance_${point.pointId}`] = parseFloat(savedAttendanceScore);
                        }
                    }
                });
            });
        }

        // Fungsi global untuk update answer
        window.updateAnswer = function(questionId, score) {
            answersMap[questionId] = score;
            console.log('Updated answer:', questionId, score);
            
            // Auto-save ke localStorage setiap kali jawaban diubah
            const savedAnswersKey = `kpi_answers_${currentEmployeeId}_${currentPeriodId}`;
            const existingAnswers = localStorage.getItem(savedAnswersKey);
            let allAnswers = existingAnswers ? JSON.parse(existingAnswers) : {};
            
            allAnswers[questionId] = score;
            localStorage.setItem(savedAnswersKey, JSON.stringify(allAnswers));
            
            console.log('Auto-saved to localStorage');
        };

        function saveStepProgress() {
            const stepData = stepsData[currentStep - 1];
            const answersToSave = {};

            console.log(`💾 Saving progress for step ${currentStep}: ${stepData.kpiName}`);

            // Kumpulkan jawaban untuk step ini
            stepData.points.forEach(point => {
                if (!point.isAbsensi) {
                    point.questions.forEach(question => {
                        if (answersMap[question.id] !== undefined) {
                            answersToSave[question.id] = answersMap[question.id];
                        }
                    });
                } else {
                    // Simpan juga nilai absensi
                    const attendanceKey = `attendance_${point.pointId}`;
                    if (answersMap[attendanceKey] !== undefined) {
                        answersToSave[attendanceKey] = answersMap[attendanceKey];
                    }
                }
            });

            if (Object.keys(answersToSave).length === 0) {
                showAlert('info', 'Info', 'Belum ada jawaban yang diisi pada step ini.');
                return;
            }

            // Simpan ke localStorage sebagai backup
            const savedAnswersKey = `kpi_answers_${currentEmployeeId}_${currentPeriodId}`;
            const existingAnswers = localStorage.getItem(savedAnswersKey);
            let allAnswers = existingAnswers ? JSON.parse(existingAnswers) : {};
            
            // Gabungkan dengan jawaban yang sudah ada
            allAnswers = { ...allAnswers, ...answersToSave };
            localStorage.setItem(savedAnswersKey, JSON.stringify(allAnswers));

            showAlert('success', 'Berhasil', 'Progress step ini telah disimpan!');
        }

        function finishWizard() {
            // Kumpulkan data absensi untuk disimpan
            const attendanceScores = [];

            // CARI SEMUA POINT ABSENSI DAN KUMPULKAN NILAINYA
            stepsData.forEach(step => {
                step.points.forEach(point => {
                    if (point.isAbsensi) {
                        const attendanceKey = `attendance_${point.pointId}`;
                        const finalScore = answersMap[attendanceKey] || 0;
                        
                        console.log(`Attendance score for point ${point.pointId}:`, finalScore);
                        
                        if (finalScore > 0) {
                            attendanceScores.push({
                                point_id: point.pointId,
                                score: finalScore // Kirim dalam skala 0-100
                            });
                        }
                    }
                });
            });

            console.log('Final attendance scores to save:', attendanceScores);

            // Validasi pertanyaan normal (HANYA untuk yang bukan absensi)
            let totalQuestions = 0;
            let answeredQuestions = 0;

            stepsData.forEach(step => {
                step.points.forEach(point => {
                    if (!point.isAbsensi) { // Hanya hitung yang bukan absensi
                        point.questions.forEach(question => {
                            totalQuestions++;
                            if (answersMap[question.id] !== undefined && answersMap[question.id] !== null) {
                                answeredQuestions++;
                            }
                        });
                    }
                });
            });

            // ⚠️ PERUBAHAN: Validasi HARUS mengisi semua pertanyaan
            if (answeredQuestions < totalQuestions) {
                showAlert(
                    'warning', 
                    'Peringatan', 
                    `Anda harus mengisi semua ${totalQuestions} pertanyaan sebelum menyimpan! \n\nAnda baru mengisi ${answeredQuestions} dari ${totalQuestions} pertanyaan.`
                );
                return; // Hentikan proses
            }

            // Validasi minimal harus ada jawaban atau nilai absensi
            if (answeredQuestions === 0 && attendanceScores.length === 0) {
                showAlert('warning', 'Peringatan', 'Anda belum mengisi jawaban apapun!');
                return;
            }

            // ⚠️ PERUBAHAN: Langsung submit tanpa konfirmasi jika semua sudah terisi
            submitAnswers(attendanceScores);
        }

        function submitAnswers(attendanceScores = []) {
            console.log('=== DEBUG SUBMIT ANSWERS ===');

            const aspekMap = {};

            // Kumpulkan jawaban pertanyaan normal
            stepsData.forEach(step => {
                const kpiId = step.kpiId;
                if (!kpiId) return;

                aspekMap[kpiId] = [];

                step.points.forEach(point => {
                    if (!point.isAbsensi) {
                        point.questions.forEach(question => {
                            const answerScore = answersMap[question.id] !== undefined ? answersMap[question.id] : question.answer;

                            if (answerScore !== undefined && answerScore !== null && answerScore >= 1 && answerScore <= 4) {
                                aspekMap[kpiId].push({
                                    id: question.id,
                                    jawaban: answerScore
                                });
                            }
                        });
                    }
                });
            });

            console.log('Formatted hasil:', aspekMap);
            console.log('Attendance scores:', attendanceScores);

            const formattedHasil = [];
            for (const kpiId in aspekMap) {
                if (aspekMap[kpiId].length > 0) {
                    formattedHasil.push({
                        id_aspek: parseInt(kpiId),
                        jawaban: aspekMap[kpiId]
                    });
                }
            }

            // FIX: Pastikan hasil tidak kosong
            if (formattedHasil.length === 0 && attendanceScores.length === 0) {
                showError('Tidak ada data yang akan disimpan!');
                return;
            }

            // PAYLOAD DITAMBAH ATTENDANCE_SCORES
            const finalPayload = {
                id_karyawan: currentEmployeeId,
                periode_id: currentPeriodId,
                hasil: formattedHasil,
                attendance_scores: attendanceScores
            };

            console.log('Final payload:', finalPayload);

            // Show loading
            Swal.fire({
                title: 'Menyimpan Jawaban...',
                text: 'Harap tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // KIRIM dengan attendance_scores
            fetch('/api/kpis/submit-answers', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(finalPayload)
                })
                .then(async response => {
                    const result = await response.json();

                    if (!response.ok) {
                        console.error("Backend error:", result);
                        let errorMessage = result.message || 'Terjadi kesalahan';
                        if (result.errors) {
                            errorMessage += ": " + Object.values(result.errors).flat().join(', ');
                        }
                        throw new Error(errorMessage);
                    }

                    return result;
                })
                .then(result => {
                    console.log('Success response:', result);

                    // Clear saved answers setelah berhasil submit
                    clearSavedAnswers();
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        html: `
                            <p>Jawaban KPI berhasil disimpan!</p>
                            <small>
                                ${result.saved_count} jawaban disimpan<br>
                                ${result.attendance_scores_saved || 0} nilai absensi disimpan
                            </small>
                        `,
                        confirmButtonColor: '#3085d6',
                    }).then(() => {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('modalWizard'));
                        modal.hide();

                        // Refresh tabel
                        refreshTableAndStatus();
                    });
                })
                .catch(error => {
                    console.error('Error submitting answers:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: error.message || 'Gagal menyimpan jawaban',
                        confirmButtonColor: '#3085d6',
                    });
                });
        }

        function clearSavedAnswers() {
            if (!currentEmployeeId || !currentPeriodId) return;
            
            const savedAnswersKey = `kpi_answers_${currentEmployeeId}_${currentPeriodId}`;
            localStorage.removeItem(savedAnswersKey);
            
            // Hapus saved answers per step
            for (let i = 1; i <= totalSteps; i++) {
                localStorage.removeItem(`kpi_answers_${currentEmployeeId}_${currentPeriodId}_step${i}`);
            }
            
            // Hapus attendance scores
            stepsData.forEach(step => {
                step.points.forEach(point => {
                    if (point.isAbsensi) {
                        localStorage.removeItem(`attendance_score_${currentEmployeeId}_${point.pointId}`);
                    }
                });
            });
            
            console.log('Cleared all saved answers');
        }

        function refreshTableAndStatus() {
            // Refresh tabel DataTable
            $('#myProjectTable').DataTable().ajax.reload(null, false);
            
            // Refresh status karyawan yang sedang dilihat
            if (currentEmployeeId) {
                setTimeout(() => {
                    checkEmployeeKPIStatus(currentEmployeeId);
                }, 1000);
            }
        }

        function resetWizard() {
            currentStep = 1;
            totalSteps = 0;
            stepsData = [];
            answersMap = {};
            wizardContent.innerHTML = '';
        }

        prevBtn.addEventListener('click', prevStep);
        nextBtn.addEventListener('click', nextStep);
        finishBtn.addEventListener('click', finishWizard);

        // Event listener untuk tombol save step
        saveStepBtn.addEventListener('click', saveStepProgress);

        // Event listener untuk tombol refresh
        document.getElementById('btnRefresh').addEventListener('click', function() {
            $('#myProjectTable').DataTable().ajax.reload(null, false);
            loadActivePeriods();
        });

        // Reset wizard saat modal tertutup
        modalWizardEl.addEventListener('hidden.bs.modal', function() {
            resetWizard();
        });

        function updateModalEmployeeInfo() {
            if (!currentEmployeeData) return;

            modalEmployeeName.textContent = currentEmployeeData.nama;
            modalEmployeeDivision.textContent = currentEmployeeData.divisi;
            modalEmployeePosition.textContent = currentEmployeeData.position || '-';
            modalEmployeePhoto.src = currentEmployeeData.foto;

            if (currentPeriodData) {
                modalPeriodInfo.textContent = `Periode: ${currentPeriodData.nama} (${formatDate(currentPeriodData.tanggal_mulai)} - ${formatDate(currentPeriodData.tanggal_selesai)})`;
            }
        }

        function loadAllPeriods() {
            fetch('/api/periods?attendance_uploaded=1&status=active')
                .then(res => res.json())
                .then(response => {
                    if (response.success) {
                        allPeriods = response.data;
                        console.log('Loaded periods:', allPeriods);
                        initializeDataTable();
                    } else {
                        console.error('Error loading periods:', response.message);
                        initializeEmptyDataTable();
                    }
                })
                .catch(err => {
                    console.error('Error loading periods:', err);
                    initializeEmptyDataTable();
                });
        }

        function initializeEmptyDataTable() {
            const table = $('#myProjectTable');
            
            if ($.fn.DataTable.isDataTable('#myProjectTable')) {
                try {
                    table.DataTable().clear().destroy();
                } catch (e) {
                    console.log('Error destroying table:', e);
                }
            }

            $('#tableBody').empty();

            return table.DataTable({
                responsive: true,
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                data: [],
                columns: [
                    { 
                        data: null, 
                        render: function(data, type, row, meta) {
                            return meta.row + 1;
                        } 
                    },
                    { 
                        data: null, 
                        render: function() {
                            return '<img src="{{ asset("assets/images/profile_av.png") }}" class="rounded-circle" width="40" height="40" style="object-fit: cover;">';
                        } 
                    },
                    { 
                        data: null, 
                        render: function() {
                            return '<span class="badge bg-secondary">-</span>';
                        } 
                    },
                    { 
                        data: null, 
                        render: function() {
                            return '-';
                        } 
                    },
                    { 
                        data: null, 
                        render: function() {
                            return '-';
                        } 
                    },
                    { 
                        data: null, 
                        render: function() {
                            return '-';
                        } 
                    },
                    { 
                        data: null, 
                        render: function() {
                            return '<span class="badge bg-secondary">-</span>';
                        } 
                    },
                    { 
                        data: null, 
                        render: function() {
                            return '<span class="badge bg-secondary">-</span>';
                        } 
                    },
                    { 
                        data: null, 
                        render: function() {
                            return '<button class="btn btn-outline-secondary btn-sm" disabled><i class="icofont-edit text-muted"></i> Nilai</button>';
                        } 
                    }
                ],
                language: {
                    emptyTable: "Tidak ada data periode dengan absensi"
                }
            });
        }

        function initialize() {
            console.log('🚀 Initializing KPI Evaluation System...');
            
            // Initialize table
            initializeDataTable();
            
            // Event handler untuk tombol nilai
            $(document).off('click', '.btn-nilai').on('click', '.btn-nilai', function() {
                const employeeData = {
                    id: $(this).data('id'),
                    nama: $(this).data('nama'),
                    divisi: $(this).data('divisi'),
                    divisiId: $(this).data('divisi-id'),
                    position: $(this).data('position'),
                    foto: $(this).data('foto')
                };
                
                const periodId = $(this).data('period-id');
                
                currentEmployeeId = employeeData.id;
                currentEmployeeData = employeeData;
                
                updateModalEmployeeInfo();
                openWizardModal(employeeData.divisiId, periodId);
            });

            // Event handler untuk tombol detail
            $(document).off('click', '.btn-detail').on('click', '.btn-detail', function() {
                const employeeId = $(this).data('id');
                const periodId = $(this).data('period-id');
                
                // Redirect ke halaman detail KPI
                window.location.href = `/hr/kpi/detail/${employeeId}?period_id=${periodId}`;
            });
        }

        // Start the application
        initialize();
    });
</script>
@endsection