@extends('template.template')

@section('title', 'KPI Karyawan')

@section('content')
<!-- CSS Tabel -->
<link rel="stylesheet" href="{{ asset('assets/plugin/datatables/responsive.dataTables.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugin/datatables/dataTables.bootstrap5.min.css') }}">

<!-- Body -->
<div class="body d-flex py-lg-3 py-md-2">
    <div class="container-xxl">
        <div class="row align-items-center">
            <div class="border-0 mb-4">
                <div class="card-header py-3 no-bg bg-transparent d-flex align-items-center px-0 justify-content-between border-bottom flex-wrap">
                    <h3 class="fw-bold mb-0">Nilai Karyawan</h3>
                    <div id="periodInfo"></div>
                </div>
            </div>
        </div>

        <div class="row clearfix g-3">
            <!-- Kolom kiri: tabel -->
            <div class="col-12 col-lg-8">
                <div class="card mb-3">
                    <div class="card-body">
                        <table id="myProjectTable" class="table table-hover align-middle mb-0" style="width:100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nama</th>
                                    <th>Divisi</th>
                                    <th>Status KPI</th>
                                    <th>Total Score</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Kolom kanan: form & hasil -->
            <div class="col-12 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="fw-bold">Form Nilai</h5>

                        <div class="text-center mb-3">
                            <img src="{{ asset('assets/images/xs/avatar2.jpg') }}" class="rounded-circle mb-2" alt="Foto Karyawan">
                            <h6 class="mb-0" id="previewNama">Nama Karyawan</h6>
                            <small class="text-muted" id="previewDivisi">ID Karyawan</small>
                            <div class="mt-1">
                                <span class="badge bg-warning" id="previewStatus">Belum Dinilai</span>
                            </div>
                            <div class="mt-2" id="deadlineInfo" style="display: none;">
                                <small class="text-muted">
                                    <i class="icofont-calendar"></i>
                                    Deadline: <span id="deadlineDate">-</span>
                                </small>
                            </div>
                        </div>

                        <hr>

                        <!-- Container untuk form penilaian -->
                        <div id="formAspekContainer" class="d-none">
                            <div class="alert alert-info">
                                <i class="icofont-info-circle"></i> Klik tombol "Nilai" untuk memulai penilaian
                            </div>
                        </div>

                        <!-- Container untuk hasil penilaian -->
                        <div id="hasilPenilaianContainer" class="d-none">
                            <h6 class="fw-bold mb-3">Hasil Penilaian</h6>
                            <div id="hasilPerAspek"><!-- Hasil per aspek akan diisi via JS --></div>
                            <div class="mt-3 p-3 bg-light rounded">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong>Total Score KPI:</strong>
                                    <span class="badge bg-primary fs-6" id="totalScoreKPI">0</span>
                                </div>
                                <small class="text-muted">Skala 0-100</small>
                            </div>
                        </div>

                        <button id="btnNilai" class="btn btn-primary w-100 mt-3">Nilai</button>
                        <button id="btnLihatHasil" class="btn btn-outline-success w-100 mt-2 d-none">Lihat Hasil Detail</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Wizard -->
<div class="modal fade" id="modalWizard" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Form Penilaian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <style>
                .step-container {
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    margin: 30px 0;
                    position: relative;
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
                    width: 35px;
                    height: 35px;
                    border-radius: 50%;
                    background: white;
                    border: 3px solid #ccc;
                    font-weight: bold;
                    color: #777;
                    z-index: 1;
                    transition: all 0.3s ease;
                }

                .step-btn.active {
                    background: linear-gradient(135deg, #6a11cb, #2575fc);
                    color: white;
                    border-color: transparent;
                    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
                    transform: scale(1.1);
                }

                .step-btn:hover {
                    transform: scale(1.15);
                    cursor: pointer;
                }

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

                .question-item {
                    margin-bottom: 15px;
                    padding-bottom: 15px;
                    border-bottom: 1px solid #f0f0f0;
                }

                .question-item:last-child {
                    border-bottom: none;
                    margin-bottom: 0;
                    padding-bottom: 0;
                }

                .score-option {
                    display: flex;
                    align-items: center;
                    margin-bottom: 5px;
                    cursor: pointer;
                }

                .score-option input {
                    margin-right: 8px;
                }

                .score-label {
                    font-size: 0.9em;
                }

                .step-container {
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    margin: 30px 0;
                    position: relative;
                    gap: 20px;
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
            </style>

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

<!-- Modal Hasil Detail -->
<div class="modal fade" id="modalHasilDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Hasil KPI</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="detailHasilContent"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="{{ asset('assets/bundles/dataTables.bundle.js') }}"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    /* Konfigurasi / Variabel global */
    const tableSelector = '#myProjectTable';
    const modalWizardEl = document.getElementById('modalWizard');
    const modalHasilDetailEl = document.getElementById('modalHasilDetail');
    const wizardContent = document.getElementById('wizardContent');
    const detailHasilContent = document.getElementById('detailHasilContent');
    const prevBtn = document.getElementById('prevStep');
    const nextBtn = document.getElementById('nextStep');
    const finishBtn = document.getElementById('finishWizard');

    let currentStep = 1;
    let totalSteps = 0;
    let stepsData = [];
    let answersMap = {};
    let currentEmployeeId = null;
    let currentPeriodId = null;
    let currentPeriodData = null;

    /* Fungsi SweetAlert2 */
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

    /* Inisialisasi DataTable */
    $(document).ready(function() {
        initializeDataTable();
        loadActivePeriods();

        // Event listener untuk tombol nilai
        $("#btnNilai").on("click", handleNilaiClick);
        $("#btnLihatHasil").on("click", showDetailHasil);

        // Event listener untuk wizard
        prevBtn.addEventListener("click", prevStep);
        nextBtn.addEventListener("click", nextStep);
        finishBtn.addEventListener("click", finishWizard);

        // Reset wizard saat modal tertutup
        modalWizardEl.addEventListener("hidden.bs.modal", function() {
            resetWizard();
        });
    });

    function initializeDataTable() {
        $(tableSelector).DataTable({
            responsive: true,
            pageLength: 5,
            lengthMenu: [5, 10, 25, 50],
            ajax: {
                url: "{{ url('api/employees/Kepala Divisi') }}",
                dataSrc: 'data'
            },
            columns: [{
                    data: null,
                    render: (data, type, row, meta) => meta.row + 1
                },
                {
                    data: 'nama'
                },
                {
                    data: 'roles',
                    render: function(data) {
                        if (data && data.length > 0) {
                            return data[0].division?.nama_divisi ?? '-';
                        }
                        return '-';
                    }
                },
                {
                    data: null,
                    render: function(row) {
                        // Status akan diupdate setelah pengecekan
                        return '<span class="badge bg-warning">Belum Dicek</span>';
                    }
                },
                {
                    data: null,
                    render: function(row) {
                        // Total score akan di-update setelah penilaian
                        return '<span class="badge bg-secondary">-</span>';
                    }
                },
                {
                    data: null,
                    render: function(row) {
                        let divisi = "-";
                        let divisiId = null;
                        if (row.roles && row.roles.length > 0) {
                            divisi = row.roles[0].division?.nama_divisi ?? "-";
                            divisiId = row.roles[0].division?.id_divisi ?? null;
                        }
                        return `
                        <button type="button" 
                        class="btn btn-outline-secondary btn-nilai" 
                        data-id="${row.id_karyawan}" 
                        data-nama="${row.nama}" 
                        data-divisi="${divisi}"
                        data-divisi-id="${divisiId}">
                        <i class="icofont-edit text-success"></i> Nilai
                        </button>`;
                    }
                }
            ]
        });

        /* Preview karyawan saat klik tombol edit */
        $(tableSelector).on('click', '.btn-nilai', function() {
            let id = $(this).data('id');
            let nama = $(this).data('nama');
            let divisi = $(this).data('divisi');
            let divisiId = $(this).data('divisi-id');

            currentEmployeeId = id;

            $("#previewNama").text(nama);
            $("#previewDivisi").text(divisi);
            $("#btnNilai").data({
                id,
                nama,
                divisi,
                divisiId
            });

            // Cek apakah karyawan sudah dinilai
            checkEmployeeKPIStatus(id);
        });
    }

    function loadActivePeriods() {
        fetch('/api/periods?status=active')
            .then(res => res.json())
            .then(response => {
                if (response.success && response.data.length > 0) {
                    // Ambil period terbaru yang memiliki KPI published
                    const activePeriod = response.data.find(p => p.kpi_published) || response.data[0];
                    currentPeriodId = activePeriod.id_periode;
                    currentPeriodData = activePeriod;

                    // Update UI dengan period info
                    $('#periodInfo').html(`
                        <div class="alert alert-info">
                            <strong>Periode Aktif:</strong> ${activePeriod.nama}<br>
                            <small>${formatDate(activePeriod.tanggal_mulai)} - ${formatDate(activePeriod.tanggal_selesai)}</small>
                        </div>
                    `);

                    // Load info deadline
                    loadPeriodInfo(activePeriod.id_periode);

                    // Refresh tabel untuk update status KPI
                    $(tableSelector).DataTable().ajax.reload(null, false);
                } else {
                    $('#periodInfo').html(`
                        <div class="alert alert-warning">
                            <strong>Tidak ada periode aktif!</strong><br>
                            <small>Pastikan sudah mempublish KPI ke periode.</small>
                        </div>
                    `);
                }
            })
            .catch(err => {
                console.error('Error loading periods:', err);
                $('#periodInfo').html(`
                    <div class="alert alert-danger">
                        <strong>Error memuat data periode!</strong>
                    </div>
                `);
            });
    }

    function loadPeriodInfo(periodId) {
        fetch(`/api/periods/${periodId}`)
            .then(res => res.json())
            .then(response => {
                if (response.success) {
                    const period = response.data;
                    const now = new Date();
                    const evalEnd = new Date(period.evaluation_end_date);
                    const daysLeft = Math.ceil((evalEnd - now) / (1000 * 60 * 60 * 24));

                    if (period.evaluation_end_date) {
                        $('#deadlineInfo').show();
                        $('#deadlineDate').html(`
                            <strong>${formatDate(period.evaluation_end_date)}</strong>
                            (${daysLeft > 0 ? `${daysLeft} hari lagi` : 'Telah berakhir'})
                        `);

                        if (daysLeft <= 0) {
                            $('#deadlineInfo').addClass('text-danger');
                            $('#deadlineInfo small').html('<i class="icofont-warning"></i> Periode evaluasi telah berakhir!');
                        } else if (daysLeft <= 3) {
                            $('#deadlineInfo').addClass('text-danger');
                        } else if (daysLeft <= 7) {
                            $('#deadlineInfo').addClass('text-warning');
                        }
                    }
                }
            })
            .catch(err => console.error('Error loading period info:', err));
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

    function checkEmployeeKPIStatus(employeeId) {
        if (!currentPeriodId) {
            showEmptyForm();
            return;
        }

        fetch(`/api/kpis/employee/${employeeId}/period/${currentPeriodId}`)
            .then(res => res.json())
            .then(data => {
                console.log('KPI Status Response:', data);

                if (data.success && data.data && data.data.length > 0) {
                    // Pastikan ada data yang valid
                    const validData = data.data.filter(aspek =>
                        aspek.points && aspek.points.length > 0
                    );

                    console.log('Valid Data Count:', validData.length);

                    if (validData.length > 0) {
                        // Cek apakah sudah ada jawaban
                        let hasAnswers = false;
                        let totalAnswers = 0;

                        validData.forEach(aspek => {
                            aspek.points.forEach(point => {
                                if (point.questions) {
                                    point.questions.forEach(q => {
                                        if (q.answer !== null && q.answer !== undefined) {
                                            hasAnswers = true;
                                            totalAnswers++;
                                        }
                                    });
                                }
                            });
                        });

                        console.log('Has Answers:', hasAnswers, 'Total Answers:', totalAnswers);

                        if (hasAnswers) {
                            showHasilPenilaian(validData);
                            $("#previewStatus").removeClass('bg-warning').addClass('bg-success').text('Sudah Dinilai');
                            $("#btnNilai").addClass('d-none');
                            $("#btnLihatHasil").removeClass('d-none');

                            // Update status di tabel
                            const totalScore = calculateTotalScore(validData);
                            console.log('Calculated Total Score:', totalScore);
                            updateTableStatus(employeeId, 'Sudah Dinilai', totalScore);
                        } else {
                            console.log('No answers found');
                            showEmptyForm();
                            updateTableStatus(employeeId, 'Belum Dinilai', 0);
                        }
                    } else {
                        console.log('No valid data found');
                        showEmptyForm();
                        updateTableStatus(employeeId, 'Belum Dinilai', 0);
                    }
                } else {
                    console.log('No data from API');
                    showEmptyForm();
                    updateTableStatus(employeeId, 'Belum Dinilai', 0);
                }
            })
            .catch(err => {
                console.error("Error checking KPI status:", err);
                showEmptyForm();
                updateTableStatus(employeeId, 'Error', 0);
            });
    }

    function updateTableStatus(employeeId, status, score) {
        const table = $(tableSelector).DataTable();
        const data = table.data();

        // Pastikan score adalah number
        const validScore = parseFloat(score) || 0;

        for (let i = 0; i < data.length; i++) {
            if (data[i].id_karyawan == employeeId) {
                // Update baris yang sesuai
                const row = table.row(i);
                const node = row.node();

                if (node) {
                    // Update status
                    const statusBadge = status === 'Sudah Dinilai' ?
                        '<span class="badge bg-success">Sudah Dinilai</span>' :
                        '<span class="badge bg-warning">Belum Dinilai</span>';

                    $(node).find('td:eq(3)').html(statusBadge);

                    // Update score
                    const scoreBadge = status === 'Sudah Dinilai' ?
                        `<span class="badge bg-primary">${validScore.toFixed(2)}</span>` :
                        '<span class="badge bg-secondary">-</span>';

                    $(node).find('td:eq(4)').html(scoreBadge);
                }
                break;
            }
        }
    }

    function showEmptyForm() {
        $("#formAspekContainer").removeClass('d-none');
        $("#hasilPenilaianContainer").addClass('d-none');
        $("#previewStatus").removeClass('bg-success').addClass('bg-warning').text('Belum Dinilai');
        $("#btnNilai").removeClass('d-none');
        $("#btnLihatHasil").addClass('d-none');
    }

    function showHasilPenilaian(kpiData) {
        console.log('=== DEBUG showHasilPenilaian ===');
        console.log('KPI Data:', kpiData);

        $("#formAspekContainer").addClass('d-none');
        $("#hasilPenilaianContainer").removeClass('d-none');

        let html = '';
        const totalScore = calculateTotalScore(kpiData);

        console.log('Total Score:', totalScore);

        kpiData.forEach((aspek, index) => {
            const scoreAspek = calculateAspekScore(aspek);

            // Hitung total bobot sub-aspek untuk validasi (dengan default value 0)
            const totalBobotSubAspek = aspek.points ?
                aspek.points.reduce((total, point) => total + (parseFloat(point.bobot) || 0), 0) : 0;

            console.log(`Aspek ${index + 1}:`, aspek.nama, 'Score:', scoreAspek, 'Bobot:', totalBobotSubAspek);

            html += `
            <div class="mb-3 p-3 border rounded">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong>${aspek.aspek || aspek.nama || 'Aspek ' + (index + 1)}</strong>
                    <div>
                        <span class="badge bg-primary score-badge">${scoreAspek.toFixed(2)}</span>
                        <small class="text-muted">/${totalBobotSubAspek.toFixed(1)}</small>
                    </div>
                </div>
        `;

            // Progress bar hanya ditampilkan jika totalBobotSubAspek > 0
            if (totalBobotSubAspek > 0) {
                html += `
                <div class="progress mb-2">
                    <div class="progress-bar" style="width: ${(scoreAspek / totalBobotSubAspek) * 100}%"></div>
                </div>
            `;
            }

            html += `
                <small class="text-muted d-block">Total Bobot Sub-Aspek: ${totalBobotSubAspek}%</small>
        `;

            // Cek jika ada points
            if (aspek.points && aspek.points.length > 0) {
                html += `<div class="mt-2">`;

                aspek.points.forEach((point, pointIndex) => {
                    let pointScore = 0;
                    let answeredQuestions = 0;

                    console.log(`  Point ${pointIndex + 1}:`, point.nama, 'Bobot:', point.bobot);

                    if (point.questions && point.questions.length > 0) {
                        point.questions.forEach((question, qIndex) => {
                            if (question.answer !== null && question.answer !== undefined) {
                                pointScore += parseFloat(question.answer) || 0;
                                answeredQuestions++;
                                console.log(`    Q ${qIndex + 1}:`, question.answer);
                            }
                        });

                        const pointBobot = parseFloat(point.bobot) || 0;

                        if (answeredQuestions > 0) {
                            const avgPointScore = pointScore / answeredQuestions;
                            const pointContribution = (avgPointScore * 2.5) * (pointBobot / 100);
                            const nilaiMaksimalPoint = 4 * 2.5 * (pointBobot / 100);

                            html += `
                            <div class="sub-point-detail mb-2 p-2 bg-light rounded">
                                <strong>${point.nama || 'Sub-Aspek ' + (pointIndex + 1)}</strong>
                                <div class="d-flex justify-content-between">
                                    <small>Rata-rata: ${avgPointScore.toFixed(2)}/4</small>
                                    <small>× 2.5 = ${(avgPointScore * 2.5).toFixed(2)}/10</small>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <small>Bobot: ${pointBobot}%</small>
                                    <small>Kontribusi: ${pointContribution.toFixed(2)}/${nilaiMaksimalPoint.toFixed(2)}</small>
                                </div>
                            </div>
                        `;
                        } else {
                            html += `
                            <div class="sub-point-detail mb-2 p-2 bg-light rounded">
                                <strong>${point.nama || 'Sub-Aspek ' + (pointIndex + 1)}</strong>
                                <small class="text-muted">Belum ada jawaban</small>
                            </div>
                        `;
                        }
                    }
                });

                html += `</div>`;
            } else {
                html += `<small class="text-muted">Tidak ada sub-aspek</small>`;
            }

            html += `</div>`;
        });

        $("#hasilPerAspek").html(html);
        $("#totalScoreKPI").text(totalScore.toFixed(2));

        // Simpan data untuk modal detail
        window.kpiResultData = kpiData;
        window.kpiTotalScore = totalScore;

        console.log('=== END DEBUG ===');
    }

    function calculateAspekScore(aspek) {
        let totalAspek = 0;

        if (!aspek.points || aspek.points.length === 0) return 0;

        aspek.points.forEach(point => {
            let totalPoint = 0;
            let answeredQuestions = 0;

            if (point.questions && point.questions.length > 0) {
                point.questions.forEach(question => {
                    if (question.answer !== null && question.answer !== undefined) {
                        totalPoint += parseFloat(question.answer) || 0; // Nilai 1-4
                        answeredQuestions++;
                    }
                });

                if (answeredQuestions > 0) {
                    const averagePointScore = totalPoint / answeredQuestions;
                    const pointBobot = parseFloat(point.bobot) || 0;
                    // Konversi 1-4 ke skala 0-10: (rata-rata × 2.5) × bobot%
                    const pointContribution = (averagePointScore * 2.5) * (pointBobot / 100);
                    totalAspek += pointContribution;
                }
            }
        });

        return totalAspek;
    }

    function calculateTotalScore(kpiData) {
        let totalScore = 0;

        if (!kpiData || !Array.isArray(kpiData)) return 0;

        kpiData.forEach(aspek => {
            const aspekScore = calculateAspekScore(aspek);
            totalScore += aspekScore;
        });

        return totalScore;
    }

    function handleNilaiClick() {
        if (!$(this).data('nama')) {
            showAlert('warning', 'Peringatan', 'Pilih karyawan dulu!');
            return;
        }

        if (!currentPeriodId) {
            showAlert('warning', 'Peringatan', 'Tidak ada periode aktif! Pastikan KPI sudah dipublish ke periode.');
            return;
        }

        let divisiId = $(this).data("divisi-id");

        if (!divisiId) {
            showAlert('warning', 'Peringatan', 'Divisi karyawan tidak valid!');
            return;
        }

        // Hanya mengambil KPI global dan divisi karyawan tersebut
        let url = `/api/kpis/division/${divisiId}?periode_id=${currentPeriodId}`;
        console.log('Fetching KPI from:', url);

        fetch(url)
            .then(res => res.json())
            .then(response => {
                console.log('KPI Response:', response);

                let kpis = [];
                if (response.success && response.data) {
                    kpis = response.data;
                }

                if (kpis.length === 0) {
                    showAlert('warning', 'Peringatan', `Tidak ada KPI yang ditetapkan untuk divisi ini pada periode aktif!`);
                    return;
                }

                buildStepsFromKpis(kpis);

                const modal = new bootstrap.Modal(document.getElementById('modalWizard'));
                modal.show();
            })
            .catch(err => {
                console.error("Error load KPI:", err);
                showAlert('error', 'Error', "Gagal memuat data KPI: " + err.message);
            });
    }

    function buildStepsFromKpis(kpis) {
        console.log('=== DEBUG KPI DATA ===');
        console.log('Raw KPI data:', kpis);

        stepsData = [];
        answersMap = {};

        kpis.forEach((kpi, index) => {
            console.log(`KPI ${index + 1}:`, kpi);

            const kpiId = kpi.id_kpi || kpi.id || null;
            const kpiName = kpi.nama || kpi.name || 'Aspek ' + (index + 1);
            const kpiBobot = kpi.bobot || 0;
            const points = kpi.points || [];

            const stepData = {
                stepIndex: stepsData.length + 1,
                kpiId,
                kpiName,
                kpiBobot,
                points: []
            };

            points.forEach((point, pointIndex) => {
                const questions = point.questions || [];
                console.log(`  Point ${pointIndex + 1}:`, point);

                const pointData = {
                    pointId: point.id_point || point.id || null,
                    pointName: point.nama || point.name || `Sub-Aspek ${pointIndex + 1}`,
                    pointBobot: point.bobot || 0,
                    questions: []
                };

                questions.forEach((q, qIndex) => {
                    pointData.questions.push({
                        id: q.id_question || q.id || `temp_${index}_${pointIndex}_${qIndex}`,
                        pertanyaan: q.pertanyaan || q.text || `Pertanyaan ${qIndex + 1}`,
                        answer: q.answer || null
                    });
                });

                stepData.points.push(pointData);
            });

            stepsData.push(stepData);
        });

        console.log('Processed stepsData:', stepsData);
        renderWizardSteps(stepsData);
    }

    function renderWizardSteps(steps) {
        totalSteps = steps.length;
        currentStep = 1;

        // Render step indicator
        let stepHtml = `
        <div class="step-container">
    `;

        for (let i = 1; i <= totalSteps; i++) {
            stepHtml += `
            <button class="step-btn ${i === 1 ? 'active' : ''}" data-step="${i}">
                ${i}
            </button>
        `;
        }

        stepHtml += `</div>`;

        // Render current step content
        stepHtml += renderStepContent(steps[0]);

        wizardContent.innerHTML = stepHtml;

        // Tambahkan event listener untuk step buttons
        document.querySelectorAll('.step-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const stepNum = parseInt(this.getAttribute('data-step'));
                goToStep(stepNum);
            });
        });

        updateWizardButtons();
    }

    function renderStepContent(step) {
        let html = `
            <h4 class="text-center mb-4">${step.kpiName}</h4>
            <p class="text-center text-muted mb-4">Bobot: ${step.kpiBobot}%</p>
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

            point.questions.forEach((question, qIndex) => {
                const currentAnswer = answersMap[question.id] || question.answer || null;

                html += `
                    <div class="question-item">
                        <p class="fw-semibold">${qIndex + 1}. ${question.pertanyaan}</p>
                        <div class="score-options">
                `;

                // Opsi nilai 1-4
                for (let score = 1; score <= 4; score++) {
                    const scoreLabels = {
                        1: 'Sangat Tidak Baik',
                        2: 'Tidak Baik',
                        3: 'Baik',
                        4: 'Sangat Baik'
                    };

                    html += `
                        <div class="score-option">
                            <input type="radio" 
                                id="q_${question.id}_${score}" 
                                name="q_${question.id}" 
                                value="${score}"
                                ${currentAnswer == score ? 'checked' : ''}
                                onchange="updateAnswer('${question.id}', ${score})">
                            <label for="q_${question.id}_${score}" class="score-label">
                                ${score} - ${scoreLabels[score]}
                            </label>
                        </div>
                    `;
                }

                html += `</div></div>`;
            });

            html += `</div></div>`;
        });

        return html;
    }

    function updateAnswer(questionId, score) {
        answersMap[questionId] = score;
        console.log('Updated answer:', questionId, score);
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

        updateWizardButtons();
    }

    function updateWizardButtons() {
        prevBtn.style.display = currentStep > 1 ? 'inline-block' : 'none';
        nextBtn.style.display = currentStep < totalSteps ? 'inline-block' : 'none';
        finishBtn.style.display = currentStep === totalSteps ? 'inline-block' : 'none';

        // Sembunyikan tombol Simpan dan Lanjut di step terakhir
        if (currentStep === totalSteps) {
            nextBtn.style.display = 'none';
            saveStep.style.display = 'none';
        } else {
            saveStep.style.display = 'inline-block';
        }
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

    function saveStepProgress() {
        const stepData = stepsData[currentStep - 1];
        const answersToSave = {};

        // Kumpulkan jawaban untuk step ini
        stepData.points.forEach(point => {
            point.questions.forEach(question => {
                if (answersMap[question.id] !== undefined) {
                    answersToSave[question.id] = answersMap[question.id];
                }
            });
        });

        if (Object.keys(answersToSave).length === 0) {
            showAlert('info', 'Info', 'Belum ada jawaban yang diisi pada step ini.');
            return;
        }

        // Simpan ke localStorage sebagai backup
        localStorage.setItem(`kpi_answers_${currentEmployeeId}_${currentPeriodId}_step${currentStep}`,
            JSON.stringify(answersToSave));

        showAlert('success', 'Berhasil', 'Progress step ini telah disimpan sementara.');
    }

    function finishWizard() {
        // Validasi apakah semua pertanyaan sudah dijawab
        let totalQuestions = 0;
        let answeredQuestions = 0;

        stepsData.forEach(step => {
            step.points.forEach(point => {
                point.questions.forEach(question => {
                    totalQuestions++;
                    if (answersMap[question.id] !== undefined && answersMap[question.id] !== null) {
                        answeredQuestions++;
                    }
                });
            });
        });

        if (answeredQuestions === 0) {
            showAlert('warning', 'Peringatan', 'Anda belum mengisi jawaban apapun!');
            return;
        }

        if (answeredQuestions < totalQuestions) {
            showConfirm(
                'Konfirmasi',
                `Anda telah mengisi ${answeredQuestions} dari ${totalQuestions} pertanyaan. Yakin ingin menyimpan?`,
                'Ya, Simpan',
                'Lanjutkan Mengisi'
            ).then(result => {
                if (result.isConfirmed) {
                    submitAnswers();
                }
            });
        } else {
            submitAnswers();
        }
    }

    function submitAnswers() {
        // 1. Kumpulkan semua jawaban dari answersMap dan kelompokkan
        // Kita perlu tahu ID Aspek (kpiId) untuk setiap pertanyaan, diambil dari stepsData.

        const aspekMap = {}; // Key: kpiId, Value: Array of { id: questionId, jawaban: score }

        stepsData.forEach(step => {
            const kpiId = step.kpiId;
            if (!kpiId) return; // Lewati jika tidak ada ID KPI/Aspek

            // Inisialisasi array jawaban untuk aspek ini
            aspekMap[kpiId] = [];

            step.points.forEach(point => {
                point.questions.forEach(question => {
                    // Prioritas ambil dari answersMap (jawaban baru), fallback ke question.answer (jawaban lama/default)
                    const answerScore = answersMap[question.id] !== undefined ? answersMap[question.id] : question.answer;

                    // Hanya kirim pertanyaan yang sudah dijawab (skor 1-4)
                    if (answerScore !== undefined && answerScore !== null && answerScore >= 1 && answerScore <= 4) {
                        aspekMap[kpiId].push({
                            // Mapping: question.id -> id (ID KpiQuestion)
                            id: question.id,
                            // Mapping: answerScore -> jawaban (Nilai 1-4)
                            jawaban: answerScore
                        });
                    }
                });
            });
        });

        // 2. Format menjadi array 'hasil'
        const formattedHasil = [];
        for (const kpiId in aspekMap) {
            // Hanya masukkan aspek yang memiliki minimal satu jawaban yang diisi
            if (aspekMap[kpiId].length > 0) {
                formattedHasil.push({
                    // Mapping: kpiId (key) -> id_aspek
                    id_aspek: parseInt(kpiId),
                    // Mapping: array jawaban per aspek -> jawaban
                    jawaban: aspekMap[kpiId]
                });
            }
        }

        // 3. Buat Payload Akhir dengan nama field yang benar
        const finalPayload = {
            // Mapping: currentEmployeeId -> id_karyawan
            id_karyawan: currentEmployeeId,
            // Mapping: currentPeriodId -> periode_id
            periode_id: currentPeriodId,
            // Mapping: formattedHasil -> hasil
            hasil: formattedHasil
        };

        console.log('Submitting finalPayload:', finalPayload);

        // Show loading
        Swal.fire({
            title: 'Menyimpan Jawaban...',
            text: 'Harap tunggu sebentar',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // KIRIM finalPayload (Payload yang sudah di-mapping)
        fetch('/api/kpis/submit-answers', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    // Pastikan Anda menggunakan X-CSRF-TOKEN
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(finalPayload) // Menggunakan finalPayload yang benar
            })
            .then(async response => {
                const result = await response.json();

                if (!response.ok) {
                    // Log detail error dari backend jika ada
                    console.error("Backend validation/error response:", result);

                    // Ambil pesan error dari backend
                    let errorMessage = result.message || 'Terjadi kesalahan';
                    if (result.errors) {
                        // Coba tampilkan error validasi yang lebih spesifik jika ada
                        errorMessage += ": " + Object.values(result.errors).flat().join(', ');
                    }
                    throw new Error(errorMessage);
                }

                return result;
            })
            .then(result => {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Jawaban KPI berhasil disimpan',
                    confirmButtonColor: '#3085d6',
                }).then(() => {
                    // Tutup modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalWizard'));
                    modal.hide();

                    // Refresh status karyawan
                    checkEmployeeKPIStatus(currentEmployeeId);

                    // Refresh tabel
                    $(tableSelector).DataTable().ajax.reload(null, false);
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

    function resetWizard() {
        currentStep = 1;
        totalSteps = 0;
        stepsData = [];
        answersMap = {};
        wizardContent.innerHTML = '';
    }

    function showDetailHasil() {
        if (!window.kpiResultData) {
            showAlert('warning', 'Peringatan', 'Data hasil tidak tersedia!');
            return;
        }

        let html = `
        <div class="text-center mb-4">
            <h4>Detail Hasil KPI</h4>
            <p class="text-muted">Periode: ${currentPeriodData?.nama || '-'}</p>
            <div class="total-score-display mb-3">
                <h2 class="text-primary">${window.kpiTotalScore?.toFixed(2) || '0.00'}</h2>
                <p class="text-muted">Total Skor KPI</p>
            </div>
        </div>
    `;

        window.kpiResultData.forEach((aspek, index) => {
            const aspekScore = calculateAspekScore(aspek);

            html += `
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0">${aspek.aspek || aspek.nama || 'Aspek ' + (index + 1)}</h6>
                    <small class="text-muted">Skor: ${aspekScore.toFixed(2)}</small>
                </div>
                <div class="card-body">
        `;

            if (aspek.points && aspek.points.length > 0) {
                aspek.points.forEach((point, pointIndex) => {
                    let pointScore = 0;
                    let answeredQuestions = 0;

                    if (point.questions && point.questions.length > 0) {
                        point.questions.forEach(question => {
                            if (question.answer !== null && question.answer !== undefined) {
                                pointScore += parseFloat(question.answer) || 0;
                                answeredQuestions++;
                            }
                        });

                        const avgPointScore = answeredQuestions > 0 ? pointScore / answeredQuestions : 0;
                        const pointBobot = parseFloat(point.bobot) || 0;
                        const pointContribution = (avgPointScore * 2.5) * (pointBobot / 100);

                        html += `
                        <div class="mb-3 p-3 border rounded">
                            <h6>${point.nama || 'Sub-Aspek ' + (pointIndex + 1)}</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <small><strong>Rata-rata Nilai:</strong> ${avgPointScore.toFixed(2)}/4</small><br>
                                    <small><strong>Konversi:</strong> ${(avgPointScore * 2.5).toFixed(2)}/10</small>
                                </div>
                                <div class="col-md-6">
                                    <small><strong>Bobot:</strong> ${pointBobot}%</small><br>
                                    <small><strong>Kontribusi:</strong> ${pointContribution.toFixed(2)}</small>
                                </div>
                            </div>
                            
                            <div class="mt-2">
                                <strong>Detail Jawaban:</strong>
                                <div class="mt-2">
                    `;

                        point.questions.forEach((question, qIndex) => {
                            const answer = question.answer !== null && question.answer !== undefined ?
                                parseFloat(question.answer) : null;
                            const answerLabels = {
                                1: 'Sangat Tidak Baik',
                                2: 'Tidak Baik',
                                3: 'Baik',
                                4: 'Sangat Baik'
                            };

                            html += `
                            <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                                <small>${qIndex + 1}. ${question.pertanyaan || question.text}</small>
                                <span class="badge ${answer ? 'bg-primary' : 'bg-secondary'}">
                                    ${answer ? answer + ' - ' + answerLabels[answer] : 'Belum diisi'}
                                </span>
                            </div>
                        `;
                        });

                        html += `</div></div></div>`;
                    }
                });
            } else {
                html += `<p class="text-muted">Tidak ada sub-aspek</p>`;
            }

            html += `</div></div>`;
        });

        detailHasilContent.innerHTML = html;

        const modal = new bootstrap.Modal(document.getElementById('modalHasilDetail'));
        modal.show();
    }

    // Tambahkan event listener untuk tombol saveStep
    document.getElementById('saveStep').addEventListener('click', saveStepProgress);
</script>
@endsection