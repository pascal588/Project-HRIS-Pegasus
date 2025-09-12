@extends('template.template')

@section('title', 'KPI Karyawan')

@section('content')
<!-- CSS Tabel -->
<link rel="stylesheet" href="{{asset('assets/plugin/datatables/responsive.dataTables.min.css')}}">
<link rel="stylesheet" href="{{asset('assets/plugin/datatables/dataTables.bootstrap5.min.css')}}">

<!-- Body: Body -->
<div class="body d-flex py-lg-3 py-md-2">
    <div class="container-xxl">
        <div class="row align-items-center">
            <div class="border-0 mb-4">
                <div class="card-header py-3 no-bg bg-transparent d-flex align-items-center px-0 justify-content-between border-bottom flex-wrap">
                    <h3 class="fw-bold mb-0">Nilai Karyawan</h3>
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
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Kolom kanan: form -->
            <div class="col-12 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="fw-bold">Form Nilai</h5>
                        <div class="text-center mb-3">
                            <img src="{{ asset('assets/images/xs/avatar2.jpg') }}" class="rounded-circle mb-2" alt="Foto Karyawan">
                            <h6 class="mb-0">Nama Karyawan</h6>
                            <small class="text-muted">ID Karyawan</small>
                        </div>
                        <hr>

                        <!-- Dinamis isi aspek KPI -->
                        <div id="aspekKpiList"></div>

                        <button id="btnNilai" class="btn btn-primary w-100 mt-3">Nilai</button>
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

            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title">Form Penilaian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <!-- Step Indicator -->
            <div class="d-flex justify-content-center my-3">
                <div class="step-container">
                    <button class="step-btn active" data-step="1">1</button>
                    <button class="step-btn mx-4" data-step="2">2</button>
                    <button class="step-btn" data-step="3">3</button>
                </div>
            </div>

            <!-- Style Step -->
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
            </style>

            <!-- Body -->
            <div class="modal-body">

                <div id="wizardBody"></div>

            </div>

            <!-- Footer -->
            <div class="modal-footer">
                <button class="btn btn-secondary" id="prevStep">Kembali</button>
                <button class="btn btn-success" id="saveStep">Simpan</button>
                <button class="btn btn-primary" id="nextStep">Lanjut</button>
                <button class="btn btn-success d-none" id="finishWizard">Selesai</button>
            </div>

        </div>
    </div>
</div>
@endsection

@section('script')
<script src="{{asset ('assets/bundles/dataTables.bundle.js')}}"></script>
<script>
    // ===================== DataTable =====================
    $('#myProjectTable').DataTable({
        responsive: true,
        pageLength: 5,
        lengthMenu: [5, 10, 25, 50],
        ajax: {
            url: "{{ url('api/employees/kepala-divisi') }}",
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
                        let kepala = data.find(r => r.nama_jabatan.toLowerCase().includes("kepala divisi"));
                        if (kepala) {
                            return kepala.division?.nama_divisi ?? '-';
                        }
                        return data[0].division?.nama_divisi ?? '-';
                    }
                    return '-';
                }
            },
            {
                data: 'status_kpi',
                defaultContent: 'Belum Dinilai'
            },
            {
                data: null,
                render: function(row) {
                    let divisi = "-";
                    if (row.roles && row.roles.length > 0) {
                        divisi = row.roles[0].division?.nama_divisi ?? "-";
                    }
                    return `
                        <button type="button" 
                            class="btn btn-outline-secondary btn-nilai" 
                            data-id="${row.id_karyawan}" 
                            data-nama="${row.nama}" 
                            data-divisi="${divisi}" 
                            data-divisi-id="${row.roles[0]?.division?.id_divisi ?? ''}">
                            <i class="icofont-edit text-success"></i>
                        </button>`;
                }
            }
        ]
    });

    // ===================== Event Tombol Nilai =====================
    $('#myProjectTable').on('click', '.btn-nilai', function() {
        let nama = $(this).data('nama');
        let divisi = $(this).data('divisi');
        let divisiId = $(this).data('divisi-id');

        $(".card-body h6").text(nama);
        $(".card-body small").text(divisi);

        $("#btnNilai").data('nama', nama)
            .data('divisi', divisi)
            .data('divisi-id', divisiId);
    });

    // ===================== Wizard KPI Dinamis =====================
    $("#btnNilai").on("click", function() {
        if (!$(this).data('nama')) {
            alert("Pilih karyawan dulu!");
            return;
        }

        let divisionId = $(this).data('divisi-id');
        let url = divisionId ?
            `/kpi-by-division/${divisionId}` :
            `/kpi-global`;

        $.get(url, function(response) {
        buildWizard(response);   // untuk modal wizard
        buildFormNilai(response); // untuk form kanan
        let modal = new bootstrap.Modal(document.getElementById("modalWizard"));
        modal.show();
    });
    });

    function buildWizard(kpis) {
        let wizardBody = $("#wizardBody");
        let stepNav = $(".step-container");
        wizardBody.empty();
        stepNav.empty();

        kpis.forEach((kpi, index) => {
            stepNav.append(`
                <button class="step-btn ${index===0 ? 'active' : ''}" data-step="${index+1}">${index+1}</button>
            `);

            let rows = kpi.pertanyaan.map((q, idx) => `
                <tr>
                    <td>${idx+1}</td>
                    <td class="text-start">${q.teks}</td>
                    ${[1,2,3,4].map(val => 
                        `<td><input type="radio" name="kpi_${kpi.id_kpi}_q${q.id}" value="${val}"></td>`
                    ).join("")}
                </tr>
            `).join("");

            wizardBody.append(`
                <div class="wizard-step ${index!==0 ? 'd-none' : ''}" id="step${index+1}">
                    <h6 class="mb-3">Topik: ${kpi.nama} <small class="text-muted">(Bobot: ${kpi.bobot}%)</small></h6>
                    <table class="table table-bordered">
                        <thead class="text-center">
                            <tr>
                                <th>No</th>
                                <th>Pertanyaan</th>
                                <th>1</th>
                                <th>2</th>
                                <th>3</th>
                                <th>4</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            ${rows}
                        </tbody>
                    </table>
                </div>
            `);
        });

        initWizardNav(kpis.length);
    }

    function initWizardNav(totalSteps) {
        let currentStep = 1;
        const steps = document.querySelectorAll(".wizard-step");
        const stepButtons = document.querySelectorAll(".step-btn");
        const prevBtn = document.getElementById("prevStep");
        const nextBtn = document.getElementById("nextStep");
        const finishBtn = document.getElementById("finishWizard");

        function showStep(step) {
            steps.forEach((el, index) => {
                el.classList.toggle("d-none", index + 1 !== step);
            });
            stepButtons.forEach((btn, index) => {
                btn.classList.toggle("active", index + 1 === step);
            });
            prevBtn.style.display = step === 1 ? "none" : "inline-block";
            nextBtn.style.display = step === totalSteps ? "none" : "inline-block";
            finishBtn.classList.toggle("d-none", step !== totalSteps);
        }

        stepButtons.forEach(btn => {
            btn.addEventListener("click", () => {
                currentStep = parseInt(btn.dataset.step);
                showStep(currentStep);
            });
        });
        nextBtn.onclick = () => {
            if (currentStep < totalSteps) {
                currentStep++;
                showStep(currentStep);
            }
        };
        prevBtn.onclick = () => {
            if (currentStep > 1) {
                currentStep--;
                showStep(currentStep);
            }
        };

        showStep(currentStep);
    }
</script>
@endsection
@section('script')
<script src="{{asset ('assets/bundles/dataTables.bundle.js')}}"></script>
<script>
    // ===================== DataTable =====================
    $('#myProjectTable').DataTable({
        responsive: true,
        pageLength: 5,
        lengthMenu: [5, 10, 25, 50],
        ajax: {
            url: "{{ url('api/employees/kepala-divisi') }}",
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
                        let kepala = data.find(r => r.nama_jabatan.toLowerCase().includes("kepala divisi"));
                        if (kepala) {
                            return kepala.division?.nama_divisi ?? '-';
                        }
                        return data[0].division?.nama_divisi ?? '-';
                    }
                    return '-';
                }
            },
            {
                data: 'status_kpi',
                defaultContent: 'Belum Dinilai'
            },
            {
                data: null,
                render: function(row) {
                    let divisi = "-";
                    if (row.roles && row.roles.length > 0) {
                        divisi = row.roles[0].division?.nama_divisi ?? "-";
                    }
                    return `
                        <button type="button" 
                            class="btn btn-outline-secondary btn-nilai" 
                            data-id="${row.id_karyawan}" 
                            data-nama="${row.nama}" 
                            data-divisi="${divisi}" 
                            data-divisi-id="${row.roles[0]?.division?.id_divisi ?? ''}">
                            <i class="icofont-edit text-success"></i>
                        </button>`;
                }
            }
        ]
    });

    // ===================== Event Tombol Nilai =====================
    $('#myProjectTable').on('click', '.btn-nilai', function() {
        let nama = $(this).data('nama');
        let divisi = $(this).data('divisi');
        let divisiId = $(this).data('divisi-id');

        $(".card-body h6").text(nama);
        $(".card-body small").text(divisi);

        $("#btnNilai").data('nama', nama)
            .data('divisi', divisi)
            .data('divisi-id', divisiId);
    });

    // ===================== Wizard KPI Dinamis =====================
    $("#btnNilai").on("click", function() {
        if (!$(this).data('nama')) {
            alert("Pilih karyawan dulu!");
            return;
        }

        let divisionId = $(this).data('divisi-id');
        let url = divisionId ?
            `/kpi-by-division/${divisionId}` :
            `/kpi-global`;

        $.get(url, function(response) {
            buildWizard(response);
            let modal = new bootstrap.Modal(document.getElementById("modalWizard"));
            modal.show();
        });
    });

    function buildWizard(kpis) {
        let wizardBody = $("#wizardBody");
        let stepNav = $(".step-container");
        wizardBody.empty();
        stepNav.empty();

        kpis.forEach((kpi, index) => {
            stepNav.append(`
                <button class="step-btn ${index===0 ? 'active' : ''}" data-step="${index+1}">${index+1}</button>
            `);

            let rows = kpi.pertanyaan.map((q, idx) => `
                <tr>
                    <td>${idx+1}</td>
                    <td class="text-start">${q.teks}</td>
                    ${[1,2,3,4].map(val => 
                        `<td><input type="radio" name="kpi_${kpi.id_kpi}_q${q.id}" value="${val}"></td>`
                    ).join("")}
                </tr>
            `).join("");

            wizardBody.append(`
                <div class="wizard-step ${index!==0 ? 'd-none' : ''}" id="step${index+1}">
                    <h6 class="mb-3">Topik: ${kpi.nama} <small class="text-muted">(Bobot: ${kpi.bobot}%)</small></h6>
                    <table class="table table-bordered">
                        <thead class="text-center">
                            <tr>
                                <th>No</th>
                                <th>Pertanyaan</th>
                                <th>1</th>
                                <th>2</th>
                                <th>3</th>
                                <th>4</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            ${rows}
                        </tbody>
                    </table>
                </div>
            `);
        });

        initWizardNav(kpis.length);
    }

    function initWizardNav(totalSteps) {
        let currentStep = 1;
        const steps = document.querySelectorAll(".wizard-step");
        const stepButtons = document.querySelectorAll(".step-btn");
        const prevBtn = document.getElementById("prevStep");
        const nextBtn = document.getElementById("nextStep");
        const finishBtn = document.getElementById("finishWizard");

        function showStep(step) {
            steps.forEach((el, index) => {
                el.classList.toggle("d-none", index + 1 !== step);
            });
            stepButtons.forEach((btn, index) => {
                btn.classList.toggle("active", index + 1 === step);
            });
            prevBtn.style.display = step === 1 ? "none" : "inline-block";
            nextBtn.style.display = step === totalSteps ? "none" : "inline-block";
            finishBtn.classList.toggle("d-none", step !== totalSteps);
        }

        stepButtons.forEach(btn => {
            btn.addEventListener("click", () => {
                currentStep = parseInt(btn.dataset.step);
                showStep(currentStep);
            });
        });
        nextBtn.onclick = () => {
            if (currentStep < totalSteps) {
                currentStep++;
                showStep(currentStep);
            }
        };
        prevBtn.onclick = () => {
            if (currentStep > 1) {
                currentStep--;
                showStep(currentStep);
            }
        };

        showStep(currentStep);
    }
</script>

@endsection