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
                            <h6 class="mb-0" id="previewNama">Nama Karyawan</h6>
                            <small class="text-muted" id="previewDivisi">ID Karyawan</small>
                        </div>

                        <hr>

                        <div id="formAspekContainer"><!-- Render aspek via JS --></div>

                        <button id="btnNilai" class="btn btn-primary w-100 mt-3">Nilai</button>
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

            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title">Form Penilaian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
            </style>

            <!-- Body -->
            <div class="modal-body">
                <div id="wizardContent"></div>
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
<script src="{{ asset('assets/bundles/dataTables.bundle.js') }}"></script>
<script>
    /* Konfigurasi / Variabel global */
    const tableSelector = '#myProjectTable';
    const modalWizardEl = document.getElementById('modalWizard');
    const wizardContent = document.getElementById('wizardContent');
    const prevBtn = document.getElementById('prevStep');
    const nextBtn = document.getElementById('nextStep');
    const finishBtn = document.getElementById('finishWizard');

    let currentStep = 1;
    let totalSteps = 0;
    // stepsData: array of { stepIndex, kpiId, kpiName, kpiBobot, points: [{pointId, pointName, questions: [{id, pertanyaan}] }] }
    let stepsData = [];
    // answersMap: questionId -> numeric(1..4)
    let answersMap = {};

    /* Inisialisasi DataTable (tidak diubah banyak) */
    $(tableSelector).DataTable({
        responsive: true,
        pageLength: 5,
        lengthMenu: [5, 10, 25, 50],
        ajax: {
            url: "{{ url('api/employees-by-division-except-head') }}/{{ auth()->user()->employee->roles->first()->division_id ?? 'null' }}",
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
                        // Ambil divisi dari role pertama (atau sesuai struktur data Anda)
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
                    <i class="icofont-edit text-success"></i>
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
    let divisiId = $(this).data('divisi-id'); // Ambil ID divisi

    $("#previewNama").text(nama);
    $("#previewDivisi").text(divisi);
    $("#btnNilai").data({
        id,
        nama,
        divisi,
            divisiId // Simpan ID divisi
        });
    });

    $("#btnNilai").on("click", function() {
    if (!$(this).data('nama')) {
        alert("Pilih karyawan dulu!");
        return;
    }

    let divisiId = $(this).data("divisi-id");
    
    if (!divisiId) {
        alert("Divisi karyawan tidak valid!");
        return;
    }

    let url = `/api/kpi-by-division/${divisiId}`;
    
    fetch(url)
        .then(res => {
            if (!res.ok) throw new Error('Network response was not ok');
            return res.json();
        })
        .then(kpis => {
            if (!Array.isArray(kpis)) {
                throw new Error('Invalid data format from server');
            }
            
            if (kpis.length === 0) {
                alert("Tidak ada KPI yang ditetapkan untuk divisi ini!");
                return;
            }
            
            buildStepsFromKpis(kpis);
            
            const modal = new bootstrap.Modal(document.getElementById('modalWizard'));
            modal.show();
        })
        .catch(err => {
            console.error("Error load KPI:", err);
            alert("Gagal memuat data KPI: " + err.message);
        });
});

    /* Build steps: 1 step per aspek utama */
    function buildStepsFromKpis(kpis) {
        stepsData = [];
        answersMap = {};

        // iterate kpis (aspek utama)
        kpis.forEach(kpi => {
            const kpiId = kpi.id_kpi ?? kpi.id ?? null;
            const kpiName = kpi.nama ?? kpi.name ?? 'Aspek';
            const kpiBobot = kpi.bobot ?? 0;

            const points = kpi.points || kpi.subaspek || [];

            stepsData.push({
                stepIndex: stepsData.length + 1,
                kpiId,
                kpiName,
                kpiBobot,
                points: points.map(point => ({
                    pointId: point.id_point ?? point.id ?? null,
                    pointName: point.nama ?? point.name ?? null,
                    pointBobot: point.bobot ?? 0,
                    questions: (point.questions || []).map(q => ({
                        id: q.id_question ?? q.id,
                        pertanyaan: q.pertanyaan ?? q.text ?? ''
                    }))
                }))
            });
        });

        renderWizardSteps(stepsData);
    }

    /* Render wizard steps (each step = satu aspek utama) */
    function renderWizardSteps(steps) {
        wizardContent.innerHTML = "";

        // step buttons container
        const stepButtonsContainer = document.createElement("div");
        stepButtonsContainer.className = "d-flex justify-content-center my-3 step-container flex-wrap gap-1";

        steps.forEach((stepObj, idx) => {
            const btn = document.createElement("button");
            btn.type = 'button';
            btn.className = "step-btn btn btn-sm btn-outline-secondary";
            btn.dataset.step = idx + 1;
            btn.innerText = `${idx + 1} • ${truncate(stepObj.kpiName, 18)}`;
            btn.addEventListener("click", () => {
                currentStep = idx + 1;
                showStep(currentStep);
            });
            stepButtonsContainer.appendChild(btn);
        });

        wizardContent.appendChild(stepButtonsContainer);

        // Render each step content (satu aspek utama per step)
        steps.forEach((stepObj, idx) => {
            const stepDiv = document.createElement("div");
            stepDiv.className = "wizard-step d-none";
            stepDiv.id = `step${idx + 1}`;
            stepDiv.dataset.kpiId = stepObj.kpiId;

            // Header
            const header = document.createElement("div");
            header.className = "mb-3";
            header.innerHTML = `<h5 class="mb-1">Aspek: <strong>${escapeHtml(stepObj.kpiName)}</strong></h5>
                            <div class="mb-2"><small class="text-muted">Bobot: ${stepObj.kpiBobot}%</small></div>`;

            stepDiv.appendChild(header);

            // Render semua sub-aspek (points) dalam aspek ini
            if (stepObj.points && stepObj.points.length > 0) {
                stepObj.points.forEach((point, pointIdx) => {
                    const pointCard = document.createElement("div");
                    pointCard.className = "subaspek-card";
                    
                    // Header sub-aspek
                    const pointHeader = document.createElement("div");
                    pointHeader.className = "subaspek-header";
                    pointHeader.innerHTML = `<h6 class="mb-0">${pointIdx + 1}. ${escapeHtml(point.pointName)}</h6>
                                            <small class="text-muted">Bobot: ${point.pointBobot}%</small>`;
                    
                    // Body sub-aspek (tabel pertanyaan)
                    const pointBody = document.createElement("div");
                    pointBody.className = "subaspek-body";
                    
                    const table = document.createElement("table");
                    table.className = "table table-bordered mb-0";
                    table.innerHTML = `
                        <thead class="text-center">
                            <tr><th style="width:48px">No</th><th class="text-start">Pertanyaan</th><th>1</th><th>2</th><th>3</th><th>4</th></tr>
                        </thead>
                        <tbody class="text-center"></tbody>
                    `;

                    const tbody = table.querySelector('tbody');

                    point.questions.forEach((q, i) => {
                        const tr = document.createElement("tr");
                        tr.innerHTML = `
                            <td>${i + 1}</td>
                            <td class="text-start">${escapeHtml(q.pertanyaan)}</td>
                            <td><input type="radio" name="q_${q.id}" data-kpi="${stepObj.kpiId}" data-question="${q.id}" value="1"></td>
                            <td><input type="radio" name="q_${q.id}" data-kpi="${stepObj.kpiId}" data-question="${q.id}" value="2"></td>
                            <td><input type="radio" name="q_${q.id}" data-kpi="${stepObj.kpiId}" data-question="${q.id}" value="3"></td>
                            <td><input type="radio" name="q_${q.id}" data-kpi="${stepObj.kpiId}" data-question="${q.id}" value="4"></td>
                        `;
                        tbody.appendChild(tr);
                    });

                    pointBody.appendChild(table);
                    pointCard.appendChild(pointHeader);
                    pointCard.appendChild(pointBody);
                    stepDiv.appendChild(pointCard);
                });
            } else {
                // Jika tidak ada sub-aspek, tampilkan pesan
                const noDataMsg = document.createElement("div");
                noDataMsg.className = "alert alert-info";
                noDataMsg.textContent = "Tidak ada sub-aspek untuk aspek ini.";
                stepDiv.appendChild(noDataMsg);
            }

            // Score display untuk ASPEK
            const scoreDisplay = document.createElement("div");
            scoreDisplay.className = "mt-3 fw-bold text-end";
            scoreDisplay.innerHTML = `Score Aspek: <span id="score_aspek_${stepObj.kpiId}">Belum dinilai</span>`;

            stepDiv.appendChild(scoreDisplay);
            wizardContent.appendChild(stepDiv);
        });

        totalSteps = steps.length;
        // Attaching event listener via delegation for radio change
        wizardContent.addEventListener('change', onRadioChangeDelegated);

        // store stepsData
        // clone to avoid mutation
        stepsData = JSON.parse(JSON.stringify(steps));

        // show first step
        showStep(1);
    }

    /* Event delegation handler untuk radio inputs */
    function onRadioChangeDelegated(e) {
        const target = e.target;
        if (target && target.matches('input[type="radio"][data-question]')) {
            const qId = target.dataset.question;
            const val = parseInt(target.value);
            // simpan di answersMap
            answersMap[qId] = val;

            // update skor aspek parent
            const kpiId = target.dataset.kpi;
            updateAspekScoreDisplay(kpiId);
        }
    }

    /* Hitung dan update skor aspek (rata-rata semua question di aspek / 4) */
    function updateAspekScoreDisplay(kpiId) {
        // collect all question ids that belong to this kpi from stepsData
        let questionIds = [];
        stepsData.forEach(s => {
            if (String(s.kpiId) === String(kpiId)) {
                s.points.forEach(point => {
                    point.questions.forEach(q => questionIds.push(q.id));
                });
            }
        });

        if (!questionIds.length) {
            // nothing to score
            document.getElementById(`score_aspek_${kpiId}`).innerText = "Belum dinilai";
            return;
        }

        let sum = 0;
        let countAnswered = 0;
        questionIds.forEach(qId => {
            const v = answersMap[qId];
            if (v !== undefined && v !== null) {
                sum += Number(v);
                countAnswered++;
            }
        });

        if (countAnswered === 0) {
            document.getElementById(`score_aspek_${kpiId}`).innerText = "Belum dinilai";
            return;
        }

        const avg = sum / countAnswered; // 1..4
        const norm = (avg / 4); // 0..1
        // tampilkan 2 desimal
        document.getElementById(`score_aspek_${kpiId}`).innerText = norm.toFixed(2);
    }

    /* Navigasi wizard (prev/next/finish visibility) */
    function showStep(step) {
        document.querySelectorAll(".wizard-step").forEach((el, index) => {
            el.classList.toggle("d-none", index + 1 !== step);
        });
        document.querySelectorAll(".step-btn").forEach((btn, index) => {
            btn.classList.toggle("active", index + 1 === step);
        });

        prevBtn.style.display = step === 1 ? "none" : "inline-block";
        nextBtn.style.display = step === totalSteps ? "none" : "inline-block";
        finishBtn.classList.toggle("d-none", step !== totalSteps);
    }

    nextBtn.addEventListener("click", () => {
        if (currentStep < totalSteps) {
            currentStep++;
            showStep(currentStep);
        }
    });
    prevBtn.addEventListener("click", () => {
        if (currentStep > 1) {
            currentStep--;
            showStep(currentStep);
        }
    });

    /* Kumpulkan jawaban: group per aspek (kpi) sesuai format backend */
    function kumpulkanJawaban() {
        // kelompokkan questions per kpi
        const aspekMap = {}; // kpiId -> { id_aspek, nama_aspek, bobot, jawaban: [{id, jawaban}], skor }
        // build baseline dari stepsData
        stepsData.forEach(s => {
            const kpiId = s.kpiId;
            if (!aspekMap[kpiId]) {
                aspekMap[kpiId] = {
                    id_aspek: kpiId,
                    nama_aspek: s.kpiName,
                    bobot: s.kpiBobot ?? 0,
                    jawaban: [],
                    skor: null
                };
            }
            
            s.points.forEach(point => {
                point.questions.forEach(q => {
                    const stored = answersMap[q.id];
                    aspekMap[kpiId].jawaban.push({
                        id: q.id,
                        jawaban: stored !== undefined ? Number(stored) : null
                    });
                });
            });
        });

        //
        Object.keys(aspekMap).forEach(k => {
            const obj = aspekMap[k];
            const answers = obj.jawaban;
            let sum = 0;
            let cnt = 0;
            answers.forEach(a => {
                if (a.jawaban !== null) {
                    sum += Number(a.jawaban);
                    cnt++;
                }
            });
            if (cnt > 0) {
                obj.skor = parseFloat((sum / cnt / 4).toFixed(2)); // 0..1
            } else {
                obj.skor = null;
            }
        });

        // convert to array
        return Object.values(aspekMap);
    }

    // Simpan jawaban ke backend
    finishBtn.addEventListener("click", () => {
        const hasil = kumpulkanJawaban();
        const karyawanId = $("#btnNilai").data("id");

        // validate minimally on client: minimal satu jawaban terisi
        const anyAnswered = hasil.some(a => a.jawaban.some(q => q.jawaban !== null));
        if (!anyAnswered) {
            if (!confirm("Belum ada jawaban yang diisi. Tetap kirim?")) return;
        }

        fetch("/api/kpi/score", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    id_karyawan: karyawanId,
                    hasil: hasil
                })
            })
            .then(res => {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(res => {
                alert(res.message ?? "Nilai berhasil disimpan!");
                // close modal
                const modalInstance = bootstrap.Modal.getInstance(modalWizardEl);
                if (modalInstance) modalInstance.hide();
                // reload datatable
                $(tableSelector).DataTable().ajax.reload(null, false);
            })
            .catch(err => {
                console.error(err);
                alert("Gagal menyimpan nilai!");
            });
    });

    /* Reset saat modal tertutup */
    modalWizardEl.addEventListener("hidden.bs.modal", function() {
        wizardContent.innerHTML = "";
        stepsData = [];
        answersMap = {};
        currentStep = 1;
        totalSteps = 0;
    });

    
    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function truncate(s, n) {
        if (!s) return '';
        return s.length > n ? s.slice(0, n - 1) + '…' : s;
    }
</script>
@endsection