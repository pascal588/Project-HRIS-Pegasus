@extends('template.template')

@section('title', 'Management KPI Template')

@section('content')
<style>
  .nav-tabs-wrapper {
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .nav-link:not(.active) {
    color: white !important;
  }

  .global-kpi-badge {
    background-color: #6c757d;
    color: white;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 0.75rem;
    margin-left: 8px;
  }

  .subaspect-card {
    border-left: 4px solid #0d6efd;
  }

  .question-row {
    border-left: 2px solid #6c757d;
    padding-left: 10px;
  }

  .swal2-container {
    z-index: 99999 !important;
  }
</style>

<div class="body d-flex py-3">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-12">
        <div class="card mb-3 shadow-sm">
          <div class="card-header bg-transparent border-0">
            <h4 class="card-title">
              <i class="bi bi-bar-chart-fill text-primary me-2"></i>
              Management KPI Template
            </h4>
            <button id="publishKpiModalBtn" class="btn btn-warning">
              <i class="bi bi-send-check"></i> Publish semua KPI
            </button>
          </div>

          <div class="card-body">
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label fw-bold">Pilih Mode</label>
                <select id="modeSelect" class="form-select">
                  <option value="">Pilih Mode</option>
                  <option value="global">Global</option>
                  <option value="division">Divisi</option>
                </select>
              </div>

              <div class="col-md-6" id="divisionWrapper" style="display:none;">
                <label class="form-label fw-bold">Pilih Divisi</label>
                <select id="divisionSelect" class="form-select">
                  <option value="">Pilih Divisi</option>
                </select>
              </div>
            </div>

            {{-- <!-- Info -->
            <div class="alert alert-info">
              <i class="bi bi-info-circle"></i>
              <strong>Template Mode:</strong> KPI yang diedit ini adalah template yang akan digunakan untuk semua periode setelah absensi di-import.
            </div> --}}

            <!-- Card Informasi -->
            <div class="card border-0 mb-4 shadow-sm">
              <div class="card-body">
                <h5 class="card-title fw-bold mb-3">
                  <i class="bi bi-info-circle text-primary me-2"></i>
                  Informasi KPI Template
                </h5>
                <div class="row g-3">
                  <div class="col-md-6">
                    <div class="p-3 bg-light rounded d-flex align-items-center">
                      <i class="bi bi-diagram-3-fill text-success fs-4 me-3"></i>
                      <div>
                        <small class="text-muted">Divisi</small>
                        <div id="infoDivision" class="fw-semibold">-</div>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="p-3 bg-light rounded d-flex align-items-center">
                      <i class="bi bi-percent text-warning fs-4 me-3"></i>
                      <div>
                        <small class="text-muted">Total Bobot</small>
                        <div id="infoTotalWeight" class="fw-semibold">0%</div>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="p-3 bg-light rounded d-flex align-items-center">
                      <i class="bi bi-graph-up text-danger fs-4 me-3"></i>
                      <div>
                        <small class="text-muted">Maksimal Bobot</small>
                        <div class="fw-semibold">100%</div>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="p-3 bg-light rounded d-flex align-items-center">
                      <i class="bi bi-list-ol text-primary fs-4 me-3"></i>
                      <div>
                        <small class="text-muted">Jumlah Aspek</small>
                        <div id="infoTopicCount" class="fw-semibold">0</div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Tombol Action -->
            <div class="d-flex justify-content-between mb-4">
              <button id="addTopicBtn" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Tambah Aspek KPI
              </button>
              <button id="saveKPIBtn" class="btn btn-success">
                <i class="bi bi-check-circle"></i> Simpan Template KPI
              </button>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="publishKpiModal" tabindex="-1" aria-labelledby="publishKpiModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">

                  <!-- Header -->
                  <div class="modal-header">
                    <h5 class="modal-title" id="publishKpiModalLabel">Publish KPI ke Periode</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>

                  <!-- Body -->
                  <div class="modal-body">
                    <!-- Daftar Periode yang Tersedia -->
                    <div class="mb-3">
                      <label class="form-label fw-bold">Pilih Periode</label>
                      <div id="periodeListContainer">
                        <div class="text-center">
                          <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                          </div>
                          <p>Memuat data periode...</p>
                        </div>
                      </div>
                    </div>

                    <!-- Atur Deadline -->
                    <div class="mb-3">
                      <label for="deadline" class="form-label fw-bold">Atur Deadline Evaluasi (hari)</label>
                      <input type="number" class="form-control" id="deadline" placeholder="Masukkan jumlah hari" min="1" max="60" value="7">
                      <small class="text-muted">Jumlah hari untuk periode evaluasi KPI</small>
                    </div>
                  </div>

                  <!-- Footer -->
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="publishBtn">Publish KPI</button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Content KPI -->
            <div class="card mb-5 shadow-sm">
              <div class="card-header bg-primary text-white">
                <div class="nav-tabs-wrapper mb-2">
                  <ul class="nav nav-tabs" id="topicTabs"></ul>
                </div>
              </div>
              <div class="card-body">
                <div class="tab-content" id="topicContents"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('script')
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
  // ==================== GLOBAL VARIABLES ====================
  let _uidCounter = 0;
  let currentDivisionId = "";
  let currentMode = "";

  // ==================== UTILITY FUNCTIONS ====================
  function uid(prefix = "id") {
    _uidCounter++;
    return `${prefix}_${Date.now()}_${_uidCounter}`;
  }

  function showAlert(icon, title, text) {
    return Swal.fire({
      icon: icon,
      title: title,
      text: text,
      confirmButtonColor: '#3085d6',
    });
  }

  function escapeAttr(text) {
    if (text === null || text === undefined) return "";
    return String(text)
      .replace(/&/g, "&amp;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#39;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;");
  }

  // ==================== INITIALIZATION ====================
  $(document).ready(function() {
    initializeEventListeners();
    loadDivisions();
    loadKpiTemplates();
  });

  function initializeEventListeners() {
    // Gunakan off() untuk menghapus listener sebelumnya
    $("#modeSelect").off('change').on("change", onModeChange);
    $("#divisionSelect").off('change').on("change", changeDivision);
    $("#addTopicBtn").off('click').on("click", addAspect);
    $("#saveKPIBtn").off('click').on("click", saveKPI);

    // Modal publish - inisialisasi sekali saja
    $('#publishKpiModalBtn').off('click').on('click', function() {
      loadAvailablePeriods();
      $('#publishKpiModal').modal('show');
    });

    $('#publishBtn').off('click').on('click', publishKpiToPeriod);
  }

  // ==================== MODE & DIVISION HANDLING ====================
  function onModeChange() {
    currentMode = $("#modeSelect").val();
    if (currentMode === "division") {
      $("#divisionWrapper").show();
    } else {
      $("#divisionWrapper").hide();
      $("#divisionSelect").val("");
      currentDivisionId = "";
    }
    loadKpiTemplates();
  }

  function loadDivisions() {
    $.ajax({
      url: "/api/divisions",
      method: "GET",
      success: function(response) {
        if (response.success) {
          $("#divisionSelect").empty().append('<option value="">Pilih Divisi</option>');
          response.data.forEach((division) => {
            $("#divisionSelect").append(new Option(division.nama_divisi, division.id_divisi));
          });
        }
      },
      error: function(xhr) {
        console.error("Error loading divisions:", xhr);
        showAlert('error', 'Error', 'Gagal memuat data divisi');
      }
    });
  }

  function changeDivision() {
    currentDivisionId = $("#divisionSelect").val();
    if (currentDivisionId) {
      $("#infoDivision").text($("#divisionSelect option:selected").text());
      loadKpiTemplates();
    } else {
      $("#infoDivision").text("-");
      clearKPIForm();
    }
  }

  // ==================== KPI DATA LOADING ====================
  function loadKpiTemplates() {
    let url = '/api/kpis/templates';
    const params = [];

    if (currentMode) params.push(`is_global=${currentMode === 'global' ? 1 : 0}`);
    if (currentMode === 'division' && currentDivisionId) params.push(`division_id=${currentDivisionId}`);

    if (params.length) url += '?' + params.join('&');

    $.ajax({
      url: url,
      method: "GET",
      success: function(response) {
        if (response.success) {
          clearKPIForm();
          response.data.forEach((kpi) => renderAspect(normalizeKpiFromServer(kpi), false));
          updateInfo();
        }
      },
      error: function(xhr) {
        console.error("Error loading KPI templates:", xhr);
        showAlert('error', 'Error', 'Gagal memuat template KPI');
      }
    });
  }

  function normalizeKpiFromServer(kpi) {
    console.log('Normalizing KPI:', kpi); // Debug
    return {
      uid: uid("kpi"),
      id: kpi.id_kpi || null,
      nama: kpi.nama || "",
      bobot: kpi.bobot || 0,
      is_global: kpi.is_global || false,
      points: (kpi.points || []).map((pt) => ({
        uid: uid("sub"),
        id: pt.id_point || null,
        nama: pt.nama || "",
        bobot: pt.bobot || 0,
        questions: (pt.questions || []).map((q) => ({
          id: q.id_question || null,
          pertanyaan: q.pertanyaan || "",
        })),
      })),
    };
  }

  // ==================== UI RENDERING ====================
  function clearKPIForm() {
    $("#topicTabs").empty();
    $("#topicContents").empty();
    updateInfo();
  }

  function addAspect() {
    if (currentMode === "division" && !currentDivisionId) {
        showAlert('warning', 'Peringatan', 'Pilih divisi terlebih dahulu!');
        return;
    }

    // ⚠️ FIX: Cek apakah sudah ada aspek "Disiplin" di mode Global
    if (currentMode === "global") {
        const existingDisiplin = $("#topicContents .tab-pane").find('.aspect-name')
            .filter((_, el) => el.value.toLowerCase().includes('disiplin'));
        
        if (existingDisiplin.length > 0) {
            // Jika sudah ada Disiplin, buat aspek biasa
            const aspect = {
                uid: uid("aspect"),
                id: null,
                nama: "",
                bobot: 0,
                is_global: true,
                points: [],
            };
            renderAspect(aspect, true);
            setActiveTab(aspect.uid);
            updateInfo();
            return;
        }
    }

    // Jika belum ada Disiplin di Global, buat Disiplin dengan sub-aspek absensi
    const aspect = {
        uid: uid("aspect"),
        id: null,
        nama: "Disiplin",
        bobot: 30, // Default bobot untuk Disiplin
        is_global: currentMode === "global",
        points: [],
    };

    renderAspect(aspect, true);
    setActiveTab(aspect.uid);
    updateInfo();
}

  function renderAspect(aspectObj, newlyCreated = false) {
    const aspectUid = aspectObj.uid;
    const aspectId = aspectObj.id || "";
    const aspectName = aspectObj.nama || "";
    const aspectWeight = aspectObj.bobot || 0;
    const isGlobal = aspectObj.is_global || false;
    const points = aspectObj.points || [];

    // Create tab
    const tabHtml = `
<li class="nav-item" id="tab-btn-${aspectUid}">
  <button class="nav-link" id="tab-${aspectUid}-tab" data-bs-toggle="tab"
    data-bs-target="#tab-${aspectUid}" type="button" role="tab">
    ${escapeAttr(aspectName) || "Aspek Baru"}
    ${isGlobal ? '<span class="global-kpi-badge">Global</span>' : ''}
  </button>
</li>
`;
    $("#topicTabs").append(tabHtml);

    // Create content
    let subHtml = "";
    points.forEach((sa) => (subHtml += subaspectTemplate(aspectUid, sa, isGlobal)));

    // KPI Global menjadi read-only ketika di mode Divisi
    const isReadOnly = currentMode === "division" && isGlobal;

    // ✅ DETEKSI JIKA INI ASPEK DISIPLIN GLOBAL
    const isDisiplinGlobal = isGlobal && aspectName.toLowerCase().includes('disiplin');

    const contentHtml = `
<div class="tab-pane fade" id="tab-${aspectUid}" role="tabpanel">
  <input type="hidden" class="aspect-id" value="${aspectId}">
  <input type="hidden" class="aspect-is-global" value="${isGlobal}">
  <div class="mb-3">
    <label class="form-label">Nama aspek</label>
    <input type="text" class="form-control aspect-name" value="${escapeAttr(aspectName)}"
      oninput="updateAspectTabTitle('${aspectUid}', this.value)" ${isReadOnly ? 'readonly' : ''}>
  </div>
  <div class="mb-3">
    <label class="form-label">Bobot aspek (%)</label>
    <input type="number" class="form-control aspect-weight" value="${Number(aspectWeight)}"
      min="0" max="100" oninput="updateAspectWeight('${aspectUid}', this.value)"
      ${isReadOnly ? 'readonly' : ''}>
  </div>
  ${isReadOnly ? '<div class="alert alert-info">KPI Global - Hanya dapat diubah di mode Global</div>' : ''}
  
  ${isDisiplinGlobal ? `
<!-- ✅ KONFIGURASI ABSENSI DINAMIS -->
<div class="card border-warning mb-3">
    <div class="card-header bg-warning text-dark">
        <i class="bi bi-gear-fill me-2"></i>
        Konfigurasi Penilaian Absensi (Dinamis)
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label small">Hadir ×</label>
                <input type="number" class="form-control form-control-sm attendance-multiplier" 
                       data-type="hadir_multiplier" value="3">
            </div>
            <div class="col-md-4">
                <label class="form-label small">Sakit ×</label>
                <input type="number" class="form-control form-control-sm attendance-multiplier" 
                       data-type="sakit_multiplier" value="0">
            </div>
            <div class="col-md-4">
                <label class="form-label small">Izin ×</label>
                <input type="number" class="form-control form-control-sm attendance-multiplier" 
                       data-type="izin_multiplier" value="0">
            </div>
            <div class="col-md-4">
                <label class="form-label small">Mangkir ×</label>
                <input type="number" class="form-control form-control-sm attendance-multiplier" 
                       data-type="mangkir_multiplier" value="-3">
            </div>
            <div class="col-md-4">
                <label class="form-label small">Terlambat ×</label>
                <input type="number" class="form-control form-control-sm attendance-multiplier" 
                       data-type="terlambat_multiplier" value="-2">
            </div>
            <div class="col-md-4">
                <label class="form-label small">Hari Kerja ×</label>
                <input type="number" class="form-control form-control-sm attendance-multiplier" 
                       data-type="workday_multiplier" value="2" min="1">
            </div>
        </div>
        <small class="text-muted mt-2 d-block">
            <i class="bi bi-info-circle"></i> Konfigurasi ini menentukan rumus perhitungan nilai absensi otomatis
        </small>
    </div>
</div>
` : ''}

  <div class="subaspects-wrapper" id="subaspects-${aspectUid}">
    <h6>Subaspek</h6>
    ${subHtml}
  </div>
  ${!isReadOnly ? `
  <div class="mt-2">
    <button class="btn btn-outline-primary btn-sm" type="button"
      onclick="addSubaspect('${aspectUid}')">+ Tambah Subaspek</button>
    <button class="btn btn-danger btn-sm" type="button"
      onclick="confirmRemoveAspect('${aspectUid}')">Hapus aspek</button>
  </div>
  ` : ''}
</div>
`;
    $("#topicContents").append(contentHtml);

    // ✅ AUTO-CREATE SUB-ASPEK ABSENSI JIKA DISIPLIN GLOBAL BARU
    if (isDisiplinGlobal && newlyCreated && points.length === 0) {
        addAbsensiSubaspect(aspectUid);
    }

    if (points.length === 0 && !isReadOnly && !isDisiplinGlobal) addSubaspect(aspectUid);
    if (newlyCreated || $("#topicTabs .nav-link").length === 1) setActiveTab(aspectUid);
    updateInfo();
}

function addAbsensiSubaspect(aspectUid) {
    const sub = {
        uid: uid("sub"),
        id: "",
        nama: "Penilaian Absensi", // ✅ NAMA KHUSUS UNTUK ABSENSI
        bobot: 10, // ✅ BOBOT DEFAULT 10%
        questions: [], // ✅ ABSENSI TIDAK PERLU PERTANYAAN
    };
    
    const html = subaspectTemplate(aspectUid, sub);
    $(`#subaspects-${aspectUid}`).append(html);
    
    // ✅ TAMBAH INFO BAHWA INI SUB-ASPEK ABSENSI
    const subCard = $(`#sub-${sub.uid}`);
    subCard.addClass('border-warning');
    subCard.find('.subaspect-name').attr('readonly', true).addClass('fw-bold text-warning');
    subCard.find('.subaspect-weight').attr('readonly', true);
    
    // ✅ HAPUS TOMBOL HAPUS UNTUK ABSENSI
    subCard.find('button[onclick*="confirmRemoveSubaspect"]').remove();
    
    // ✅ TAMBAH BADGE ABSENSI
    subCard.find('.form-label').append('<span class="badge bg-warning ms-2">Auto-calculate</span>');
    
    updateInfo();
}

  function subaspectTemplate(aspectUid, saObj = {}, isGlobalAspect = false) {
    const suid = saObj.uid || uid("sub");
    const sid = saObj.id || "";
    const sname = saObj.nama || "";
    const sweight = saObj.bobot || 0;
    const questions = saObj.questions || [];

    // ✅ DETEKSI JIKA INI SUB-ASPEK ABSENSI
    const isAbsensi = sname.toLowerCase().includes('absensi') || 
                     sname.toLowerCase().includes('kehadiran') ||
                     sname.toLowerCase().includes('penilaian absensi');
    
    // Subaspek menjadi read-only jika termasuk dalam KPI Global di mode Divisi
    const isReadOnly = currentMode === "division" && isGlobalAspect;

    let qHtml = "";
    
    // ✅ JIKA ABSENSI, TAMPILKAN INFORMASI KHUSUS
    if (isAbsensi) {
        qHtml = `
        <div class="alert alert-info mt-2">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Sub-aspek Absensi</strong><br>
            Nilai akan dihitung otomatis berdasarkan data kehadiran karyawan dengan konfigurasi multiplier yang ditentukan.
        </div>
        `;
    } else {
        // Tampilkan pertanyaan normal untuk sub-aspek lainnya
        questions.forEach((q) => (qHtml += questionInputTemplate(suid, q, isReadOnly)));
    }

    return `
<div class="card mb-2 p-2 subaspect-card ${isAbsensi ? 'border-warning' : ''}" id="sub-${suid}">
  <input type="hidden" class="subaspect-id" value="${sid}">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <div style="flex:1">
      <label class="form-label">
        Nama Subaspek 
        ${isAbsensi ? '<span class="badge bg-warning ms-2">Auto-calculate</span>' : ''}
      </label>
      <input type="text" class="form-control subaspect-name ${isAbsensi ? 'fw-bold text-warning' : ''}" 
             value="${escapeAttr(sname)}" oninput="updateInfo()" 
             ${isReadOnly || isAbsensi ? 'readonly' : ''}>
    </div>
    <div style="width:140px; margin-left:10px">
      <label class="form-label">Bobot (%)</label>
      <input type="number" class="form-control subaspect-weight" value="${Number(sweight)}"
        min="0" max="100" oninput="updateSubaspectWeight('${suid}', this.value, '${aspectUid}')"
        ${isReadOnly || isAbsensi ? 'readonly' : ''}>
    </div>
    ${!isReadOnly && !isAbsensi ? `
    <div style="margin-left:10px">
      <label class="form-label">&nbsp;</label>
      <button class="btn btn-sm btn-outline-danger d-block" type="button"
        onclick="confirmRemoveSubaspect('${suid}')">Hapus</button>
    </div>
    ` : ''}
  </div>
  <div class="questions-list" id="questions-${suid}">
    ${qHtml}
  </div>
  ${!isReadOnly && !isAbsensi ? `
  <div class="mt-2">
    <button class="btn btn-outline-secondary btn-sm" type="button"
      onclick="addQuestionToSub('${suid}')">+ Tambah Pertanyaan</button>
  </div>
  ` : ''}
</div>
`;
}

  function questionInputTemplate(suid, q = {}, isReadOnly = false) {
    const qid = q.id || "";
    const qtext = q.pertanyaan || "";
    return `
<div class="input-group mb-2 question-row" data-question-id="${escapeAttr(qid)}">
  <input type="text" class="form-control question-text" value="${escapeAttr(qtext)}"
    placeholder="Masukkan pertanyaan" ${isReadOnly ? 'readonly' : ''}>
  ${!isReadOnly ? `
  <button class="btn btn-outline-danger" type="button"
    onclick="confirmRemoveQuestionInSub(this, '${suid}')">Hapus</button>
  ` : ''}
</div>
`;
  }

  // ==================== SUBASPECT & QUESTION MANAGEMENT ====================
  function addSubaspect(aspectUid) {
    const sub = {
      uid: uid("sub"),
      id: "",
      nama: "",
      bobot: 0,
      questions: [],
    };
    const html = subaspectTemplate(aspectUid, sub);
    $(`#subaspects-${aspectUid}`).append(html);
    updateInfo();
  }

  function addQuestionToSub(subUid) {
    const html = questionInputTemplate(subUid, {});
    $(`#questions-${subUid}`).append(html);
    updateInfo();
  }

  function updateAspectWeight(aspectUid, weight) {
    const totalSubWeight = $(`#subaspects-${aspectUid} .subaspect-weight`)
      .map((_, el) => Number(el.value) || 0)
      .get()
      .reduce((a, b) => a + b, 0);

    if (totalSubWeight > weight) {
      $(`#subaspects-${aspectUid} .subaspect-weight`).each(function() {
        const subWeight = Number(this.value) || 0;
        const newSubWeight = Math.floor((subWeight / totalSubWeight) * weight);
        $(this).val(newSubWeight);
      });
    }

    updateInfo();
  }

  function updateSubaspectWeight(subUid, weight, aspectUid) {
    const aspectWeight = Number($(`#tab-${aspectUid} .aspect-weight`).val()) || 0;
    const currentSubWeights = $(`#subaspects-${aspectUid} .subaspect-weight`)
      .map((_, el) => Number(el.value) || 0)
      .get();

    const totalSubWeight = currentSubWeights.reduce((a, b) => a + b, 0);

    if (totalSubWeight > aspectWeight) {
      $(`#subaspects-${aspectUid} .subaspect-weight`).each(function() {
        const subWeight = Number(this.value) || 0;
        const newSubWeight = Math.floor((subWeight / totalSubWeight) * aspectWeight);
        $(this).val(newSubWeight);
      });
    }

    updateInfo();
  }

  // ==================== DELETE CONFIRMATIONS ====================
  function confirmRemoveQuestionInSub(btn, subUid) {
    const row = $(btn).closest(".question-row");
    const qid = row.data("question-id") || null;

    if (qid) {
      Swal.fire({
        title: 'Hapus Pertanyaan?',
        text: "Pertanyaan ini akan dihapus dari server",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: `/api/kpi-question/${qid}`,
            method: "DELETE",
            success: function(resp) {
              if (resp.success) {
                row.remove();
                showAlert('success', 'Berhasil', resp.message || 'Pertanyaan dihapus');
                updateInfo();
              } else {
                showAlert('error', 'Error', resp.message || 'Gagal menghapus pertanyaan');
              }
            },
            error: function(xhr) {
              console.error("Error deleting question:", xhr);
              showAlert('error', 'Error', 'Gagal menghapus pertanyaan');
            }
          });
        }
      });
    } else {
      Swal.fire({
        title: 'Hapus Pertanyaan?',
        text: "Pertanyaan ini akan dihapus dari form",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          row.remove();
          updateInfo();
        }
      });
    }
  }

  function confirmRemoveAspect(aspectUid) {
    const pane = $(`#tab-${aspectUid}`);
    const aspectId = pane.find(".aspect-id").val();
    const isGlobal = pane.find(".aspect-is-global").val() === "true";

    // Cegah penghapusan KPI Global di mode Divisi
    if (currentMode === "division" && isGlobal) {
      showAlert('warning', 'Peringatan', 'KPI Global tidak dapat dihapus dari mode Divisi. Gunakan mode Global untuk mengelola KPI Global.');
      return;
    }

    if (aspectId) {
      Swal.fire({
        title: 'Hapus Aspek?',
        text: "Aspek ini akan dihapus dari server",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          const url = `/api/kpis/${aspectId}`;

          $.ajax({
            url: url,
            method: "DELETE",
            success: function(resp) {
              if (resp.success) {
                removeAspectFromUI(aspectUid);
                showAlert('success', 'Berhasil', resp.message || 'Aspek dihapus');
                loadKpiTemplates();
              } else {
                showAlert('error', 'Error', resp.message || 'Gagal menghapus aspek');
              }
            },
            error: function(xhr) {
              console.error("Error deleting aspect:", xhr);
              showAlert('error', 'Error', 'Gagal menghapus aspek');
            }
          });
        }
      });
    } else {
      Swal.fire({
        title: 'Hapus Aspek?',
        text: "Aspek ini akan dihapus dari form",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          removeAspectFromUI(aspectUid);
          updateInfo();
        }
      });
    }
  }

  function confirmRemoveSubaspect(subUid) {
    const subCard = $(`#sub-${subUid}`);
    const idPoint = subCard.find(".subaspect-id").val();

    console.log('Deleting subaspect:', { subUid, idPoint }); // Debug log

    if (idPoint) {
        Swal.fire({
            title: 'Hapus Subaspek?',
            text: "Subaspek dan semua pertanyaan di dalamnya akan dihapus dari server",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // ✅ PERBAIKAN: Gunakan endpoint yang benar dengan headers
                $.ajax({
                    url: `/api/kpis/point/${idPoint}`,
                    method: "DELETE",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    success: function(resp) {
                        if (resp.success) {
                            subCard.remove();
                            showAlert('success', 'Berhasil', resp.message || 'Subaspek dihapus');
                            updateInfo();
                        } else {
                            showAlert('error', 'Error', resp.message || 'Gagal menghapus subaspek');
                        }
                    },
                    error: function(xhr) {
                        console.error("Error deleting subaspect:", xhr);
                        
                        let errorMsg = 'Gagal menghapus subaspek';
                        try {
                            const err = xhr.responseJSON;
                            if (err && err.message) {
                                errorMsg = err.message;
                            }
                            if (err && err.errors) {
                                errorMsg = Object.values(err.errors).flat().join(', ');
                            }
                        } catch (e) {
                            errorMsg = `HTTP ${xhr.status}: ${xhr.statusText}`;
                        }
                        
                        showAlert('error', 'Error', errorMsg);
                    }
                });
            }
        });
    } else {
        // Untuk subaspek yang belum disimpan (baru dibuat di frontend)
        Swal.fire({
            title: 'Hapus Subaspek?',
            text: "Subaspek ini akan dihapus dari form",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                subCard.remove();
                updateInfo();
            }
        });
    }
}

function confirmRemoveQuestionInSub(btn, subUid) {
    const row = $(btn).closest(".question-row");
    const qid = row.data("question-id") || null;

    if (qid) {
        Swal.fire({
            title: 'Hapus Pertanyaan?',
            text: "Pertanyaan ini akan dihapus dari server",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // ✅ PERBAIKAN: Gunakan endpoint yang benar
                $.ajax({
                    url: `/api/kpis/question/${qid}`, // ✅ Route yang sudah diperbaiki
                    method: "DELETE",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(resp) {
                        if (resp.success) {
                            row.remove();
                            showAlert('success', 'Berhasil', resp.message || 'Pertanyaan dihapus');
                            updateInfo();
                        } else {
                            showAlert('error', 'Error', resp.message || 'Gagal menghapus pertanyaan');
                        }
                    },
                    error: function(xhr) {
                        console.error("Error deleting question:", xhr);
                        let errorMsg = 'Gagal menghapus pertanyaan';
                        try {
                            const err = xhr.responseJSON;
                            if (err && err.message) errorMsg = err.message;
                        } catch (e) {}
                        showAlert('error', 'Error', errorMsg);
                    }
                });
            }
        });
    } else {
        // Untuk pertanyaan yang belum disimpan (baru dibuat di frontend)
        Swal.fire({
            title: 'Hapus Pertanyaan?',
            text: "Pertanyaan ini akan dihapus dari form",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                row.remove();
                updateInfo();
            }
        });
    }
}

  // ==================== UI UTILITIES ====================
  function removeAspectFromUI(aspectUid) {
    $(`#tab-btn-${aspectUid}`).remove();
    $(`#tab-${aspectUid}`).remove();
    const first = $("#topicTabs .nav-link").first();
    if (first.length) new bootstrap.Tab(first[0]).show();
    updateInfo();
  }

  function updateAspectTabTitle(uid, text) {
    const el = $(`#tab-btn-${uid} button`);
    if (el.length) {
      const badge = el.find('.global-kpi-badge');
      el.html(escapeAttr(text) || "Aspek Baru");
      if (badge.length) el.append(badge);
    }
  }

  function setActiveTab(uid) {
    const btn = $(`#tab-${uid}-tab`);
    if (btn.length) {
      new bootstrap.Tab(btn[0]).show();
    }
  }

  function updateInfo() {
    const weights = $(".aspect-weight")
      .map((_, el) => {
        const $el = $(el);
        // Hanya hitung bobot untuk KPI divisi (bukan global)
        if (currentMode === "division" && $el.closest('.tab-pane').find('.aspect-is-global').val() === "true") {
          return 0;
        }
        return Number($el.val()) || 0;
      })
      .get();

    const totalWeight = weights.reduce((a, b) => a + b, 0);
    $("#infoTotalWeight").text(totalWeight + "%");
    $("#infoTopicCount").text($("#topicContents .tab-pane").length);
  }

  function saveKPI() {
    if (!currentMode) {
      showAlert('warning', 'Peringatan', 'Pilih mode (Global/Divisi) terlebih dahulu.');
      return;
    }
    if (currentMode === "division" && !currentDivisionId) {
      showAlert('warning', 'Peringatan', 'Pilih divisi terlebih dahulu!');
      return;
    }

    const kpiPanes = $("#topicContents .tab-pane");
    if (kpiPanes.length === 0) {
      showAlert('warning', 'Peringatan', 'Tambahkan minimal 1 aspek KPI!');
      return;
    }

    const aspects = [];
    let valid = true;

    kpiPanes.each(function() {
      const $pane = $(this);
      const idKpi = $pane.find(".aspect-id").val() || null;

      // ⚠️ FIX: Ambil nilai is_global dari data yang sudah ada
      const isGlobalExisting = $pane.find(".aspect-is-global").val() === "true";

      // ⚠️ FIX: Untuk KPI baru, gunakan currentMode. Untuk KPI existing, pertahankan nilai aslinya
      const isGlobal = idKpi ? isGlobalExisting : (currentMode === "global");

      const nama = $pane.find(".aspect-name").val().trim();
      const bobot = Number($pane.find(".aspect-weight").val()) || 0;

      // ⚠️ FILTER PENTING: Di mode divisi, hanya kirim KPI divisi (bukan global)
      if (currentMode === "division" && isGlobal) {
        console.log('Skipping global KPI in division mode:', nama);
        return true; // Skip KPI Global
      }

      if (!nama) {
        showAlert('warning', 'Peringatan', 'Nama KPI tidak boleh kosong!');
        valid = false;
        return false;
      }

      const points = [];
      let totalPointWeight = 0;

      $pane.find(".subaspect-card").each(function() {
        const $sub = $(this);
        const idPoint = $sub.find(".subaspect-id").val() || null;
        const subNama = $sub.find(".subaspect-name").val().trim();
        const subBobot = Number($sub.find(".subaspect-weight").val()) || 0;

        if (!subNama) {
          showAlert('warning', 'Peringatan', 'Nama subaspek tidak boleh kosong!');
          valid = false;
          return false;
        }

        totalPointWeight += subBobot;

        const questions = [];
        $sub.find(".question-row").each(function() {
          const qid = $(this).data("question-id") || null;
          const qText = $(this).find(".question-text").val().trim();
          if (qText) {
            questions.push({
              id_question: qid,
              pertanyaan: qText,
            });
          }
        });

        const isAbsensiSubaspect = subNama.toLowerCase().includes('absensi');

        if (questions.length === 0 && !isAbsensiSubaspect) {
          showAlert('warning', 'Peringatan', 'Subaspek harus memiliki minimal 1 pertanyaan!');
          valid = false;
          return false;
        }

        points.push({
          id_point: idPoint,
          nama: subNama,
          bobot: subBobot,
          questions: questions,
          is_absensi: isAbsensiSubaspect
        });
      });

      if (!valid) return false;

      if (totalPointWeight > 100) {
        showAlert('warning', 'Peringatan', 'Total bobot subaspek tidak boleh lebih dari 100%');
        valid = false;
        return false;
      }

      aspects.push({
        id_kpi: idKpi,
        nama: nama,
        bobot: bobot,
        // ⚠️ FIX: Pertahankan status is_global yang original untuk KPI existing
        is_global: isGlobal,
        points: points,
      });
    });

    if (!valid) return;

    // Validasi: Di mode divisi, pastikan ada KPI divisi yang akan disimpan
    if (currentMode === "division" && aspects.length === 0) {
      showAlert('info', 'Informasi', 'Tidak ada KPI Divisi yang akan disimpan. KPI Global tidak dapat diedit di mode Divisi.');
      return;
    }

    const payload = {
      is_global: currentMode === "global",
      division_id: currentMode === "division" ? currentDivisionId : null,
      kpis: aspects
    };

    console.log('Payload to save:', payload); // Debug

    Swal.fire({
      title: 'Menyimpan KPI',
      text: 'Sedang menyimpan template KPI...',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading()
      }
    });

    $.ajax({
      url: "/api/kpis",
      method: "POST",
      contentType: "application/json",
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        'Accept': 'application/json'
      },
      data: JSON.stringify(payload),
      success: function(response) {
        Swal.close();
        if (response.success) {
          showAlert('success', 'Berhasil', response.message || 'Template KPI berhasil disimpan!');
          loadKpiTemplates(); // Reload data
        } else {
          showAlert('error', 'Error', response.message || 'Gagal menyimpan template KPI');
        }
      },
      error: function(xhr) {
        Swal.close();
        console.error("Error saving KPI:", xhr);
        let msg = "Gagal menyimpan template KPI";
        try {
          const errResp = xhr.responseJSON;
          if (errResp && errResp.message) msg = errResp.message;
          if (errResp && errResp.errors) {
            msg += "\n" + Object.values(errResp.errors).flat().join("\n");
          }
        } catch (e) {}
        showAlert('error', 'Error', msg);
      }
    });
  }

  // ==================== LOAD TOTAL WEIGHT ====================
  function loadTotalWeight() {
    if (currentMode === "division" && currentDivisionId) {
      $.ajax({
        url: `/api/kpis/division/${currentDivisionId}/total-weight`,
        method: "GET",
        success: function(response) {
          if (response.success) {
            const totalWeight = response.data.total_weight;
            $("#infoTotalDivisionWeight").text(totalWeight + "%");

            // Highlight if total weight exceeds 100%
            if (totalWeight > 100) {
              $("#infoTotalDivisionWeight").addClass("text-danger");
            } else {
              $("#infoTotalDivisionWeight").removeClass("text-danger");
            }
          }
        },
        error: function(xhr) {
          console.error("Error loading total weight:", xhr);
        }
      });
    } else {
      $("#infoTotalDivisionWeight").text("0%").removeClass("text-danger");
    }
  }

  // Panggil fungsi ini setelah loadKpiTemplates()
  function loadKpiTemplates() {
    let url = '';
    const params = [];

    // Tentukan endpoint berdasarkan mode
    if (currentMode === "global") {
      url = '/api/kpis/templates';
      params.push(`is_global=1`);
    } else if (currentMode === "division" && currentDivisionId) {
      // Gunakan endpoint yang mengembalikan KPI Global + Divisi
      url = `/api/kpis/division/${currentDivisionId}`;
    } else {
      // Mode divisi tapi belum pilih divisi
      clearKPIForm();
      return;
    }

    if (params.length) url += '?' + params.join('&');

    console.log('Loading KPI from:', url); // Debug

    $.ajax({
      url: url,
      method: "GET",
      success: function(response) {
        if (response.success) {
          clearKPIForm();
          if (response.data && response.data.length > 0) {
            response.data.forEach((kpi) => renderAspect(normalizeKpiFromServer(kpi), false));
          } else {
            showNoDataMessage();
          }
          updateInfo();

          // Load total weight untuk mode divisi
          if (currentMode === "division" && currentDivisionId) {
            loadTotalWeight();
          }
        }
      },
      error: function(xhr) {
        console.error("Error loading KPI templates:", xhr);
        showAlert('error', 'Error', 'Gagal memuat template KPI');
      }
    });
  }

  function showNoDataMessage() {
    const html = `
        <div class="alert alert-info text-center">
            <i class="bi bi-info-circle me-2"></i>
            Tidak ada template KPI untuk mode ${currentMode === 'global' ? 'Global' : 'Divisi'} ini.
            ${currentMode === 'division' ? 'Pilih divisi lain atau buat template baru.' : 'Buat template KPI Global baru.'}
        </div>
    `;
    $("#topicContents").html(html);
  }

  function changeDivision() {
    currentDivisionId = $("#divisionSelect").val();
    if (currentDivisionId) {
      $("#infoDivision").text($("#divisionSelect option:selected").text());
      loadKpiTemplates();
      loadTotalWeight(); // Load total weight
    } else {
      $("#infoDivision").text("-");
      clearKPIForm();
    }
  }

  // ==================== INITIALIZATION ====================
  $(document).ready(function() {
    initializeEventListeners();
    loadDivisions();
    loadKpiTemplates();

    // // Inisialisasi modal publish
    initializePublishModal();
  });

  function initializePublishModal() {
    // Event listener untuk tombol publish
    $('#publishKpiModalBtn').on('click', function() {
      loadAvailablePeriods();
      $('#publishKpiModal').modal('show');
    });

    $('#publishBtn').on('click', publishKpiToPeriod);
  }

  function loadAvailablePeriods() {
    $('#periodeListContainer').html(`
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p>Memuat data periode...</p>
        </div>
    `);

    $.ajax({
      url: '/api/kpis/available-periods-publishing',
      method: 'GET',
      success: function(response) {
        if (response.success) {
          const periods = response.data;
          let periodHtml = '';

          if (periods.length > 0) {
            periods.forEach(period => {
              const startDate = new Date(period.tanggal_mulai).toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
              });
              const endDate = new Date(period.tanggal_selesai).toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
              });

              periodHtml += `
                            <div class="form-check mb-3 p-3 border rounded">
                                <input class="form-check-input period-radio" type="radio" 
                                    name="selectedPeriod" value="${period.id_periode}" 
                                    id="period${period.id_periode}">
                                <label class="form-check-label w-100" for="period${period.id_periode}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong class="d-block">${period.nama}</strong>
                                            <small class="text-muted">${startDate} - ${endDate}</small>
                                        </div>
                                        <span class="badge bg-${period.status === 'active' ? 'success' : 'warning'}">
                                            ${period.status === 'active' ? 'Aktif' : 'Draft'}
                                        </span>
                                    </div>
                                </label>
                            </div>
                        `;
            });
          } else {
            periodHtml = `
                        <div class="alert alert-warning text-center">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Tidak ada periode yang tersedia untuk publish KPI.
                            <br>
                            <small>Pastikan sudah mengimport absensi dan periode memiliki status aktif/draft.</small>
                        </div>
                    `;
          }

          $('#periodeListContainer').html(periodHtml);
        } else {
          $('#periodeListContainer').html(`
                    <div class="alert alert-danger">
                        Gagal memuat data periode: ${response.message || 'Unknown error'}
                    </div>
                `);
        }
      },
      error: function(xhr) {
        console.error('Error loading periods:', xhr);
        $('#periodeListContainer').html(`
                <div class="alert alert-danger">
                    <i class="bi bi-x-circle me-2"></i>
                    Gagal memuat data periode. Silakan refresh halaman dan coba lagi.
                </div>
            `);
      }
    });
  }

  function publishKpiToPeriod() {
    const selectedPeriod = $('input[name="selectedPeriod"]:checked').val();
    const deadlineDays = $('#deadline').val();

    // Step 1: Validasi input terlebih dahulu
    if (!selectedPeriod) {
      showAlert('warning', 'Peringatan', 'Pilih periode terlebih dahulu!');
      return;
    }

    if (!deadlineDays || deadlineDays < 1 || deadlineDays > 60) {
      showAlert('warning', 'Peringatan', 'Masukkan deadline yang valid (1-60 hari)!');
      return;
    }

    // Step 2: Tampilkan konfirmasi
    showPublishConfirmation(selectedPeriod, deadlineDays);
  }

  // **Tambahkan ini di sini**
  $('#publishKpiModal').modal('hide'); // modal hilang sebelum alert

  // Fungsi untuk menampilkan konfirmasi sebelum AJAX
  function showPublishConfirmation(periodId, deadlineDays) {
    Swal.fire({
      title: 'Publish KPI?',
      html: `KPI akan dipublish ke periode terpilih dengan deadline evaluasi <strong>${deadlineDays} hari</strong>.`,
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Ya, Publish Sekarang!',
      cancelButtonText: 'Batal',
      reverseButtons: true
    }).then((result) => {
      if (result.isConfirmed) {
        // Step 3: Jalankan publish
        executePublish(periodId, deadlineDays);
      }
    });
  }

  // Fungsi untuk AJAX publish
  function executePublish(periodId, deadlineDays) {
    Swal.fire({
      title: 'Memproses...',
      text: 'Sedang mempublish KPI ke periode',
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading()
    });

    $.ajax({
      url: '/api/kpis/publish-to-period',
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      data: JSON.stringify({
        period_id: periodId,
        deadline_days: parseInt(deadlineDays)
      }),
      success: function(response) {
        Swal.close();
        if (response.success) {
          Swal.fire({
            title: 'Berhasil!',
            html: `KPI berhasil dipublish ke periode <strong>${response.data.period.nama}</strong>!`,
            icon: 'success',
            confirmButtonText: 'OK'
          }).then(() => {
            $('#publishKpiModal').modal('hide');
            setTimeout(() => window.location.href = '/penilaian', 2000);
          });
        } else {
          showAlert('error', 'Gagal', response.message || 'Terjadi kesalahan saat mempublish KPI');
        }
      },
      error: function(xhr) {
        Swal.close();
        let errorMessage = 'Terjadi kesalahan jaringan. Silakan coba lagi.';
        try {
          const err = JSON.parse(xhr.responseText);
          if (err.message) errorMessage = err.message;
          if (err.errors) errorMessage += '<br>' + Object.values(err.errors).join('<br>');
        } catch (e) {}
        Swal.fire('Gagal!', errorMessage, 'error');
      }
    });
  }
</script>
@endsection