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
            <p class="text-muted">Template KPI ini akan otomatis digunakan untuk periode berikutnya setelah import absensi</p>
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

            <!-- Info -->
            <div class="alert alert-info">
              <i class="bi bi-info-circle"></i>
              <strong>Template Mode:</strong> KPI yang diedit ini adalah template yang akan digunakan untuk semua periode setelah absensi di-import.
            </div>

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
    $("#modeSelect").on("change", onModeChange);
    $("#divisionSelect").on("change", changeDivision);
    $("#addTopicBtn").on("click", addAspect);
    $("#saveKPIBtn").on("click", saveKPI);
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

    const aspect = {
      uid: uid("aspect"),
      id: null,
      nama: "",
      bobot: 0,
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

    const isReadOnly = currentMode === "division" && isGlobal;
    const contentHtml = `
<div class="tab-pane fade" id="tab-${aspectUid}" role="tabpanel">
  <input type="hidden" class="aspect-id" value="${aspectId}">
  <input type="hidden" class="aspect-is-global" value="${isGlobal}">
  <div class="mb-3">
    <label class="form-label">Nama aspek</label>
    <input type="text" class="form-control aspect-name" value="${escapeAttr(aspectName)}"
      oninput="updateAspectTabTitle('${aspectUid}', this.value)" ${isReadOnly ? 'readonly' : '' }>
  </div>
  <div class="mb-3">
    <label class="form-label">Bobot aspek (%)</label>
    <input type="number" class="form-control aspect-weight" value="${Number(aspectWeight)}"
      min="0" max="100" oninput="updateAspectWeight('${aspectUid}', this.value)"
      ${isReadOnly ? 'readonly' : '' }>
  </div>
  ${isReadOnly ? '<div class="alert alert-info">KPI Global - Hanya dapat diubah di mode Global</div>' : ''}
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

    if (points.length === 0 && !isReadOnly) addSubaspect(aspectUid);
    if (newlyCreated || $("#topicTabs .nav-link").length === 1) setActiveTab(aspectUid);
    updateInfo();
  }

  function subaspectTemplate(aspectUid, saObj = {}, isGlobalAspect = false) {
    const suid = saObj.uid || uid("sub");
    const sid = saObj.id || "";
    const sname = saObj.nama || "";
    const sweight = saObj.bobot || 0;
    const questions = saObj.questions || [];
    const isReadOnly = currentMode === "division" && isGlobalAspect;

    let qHtml = "";
    questions.forEach((q) => (qHtml += questionInputTemplate(suid, q, isReadOnly)));

    return `
<div class="card mb-2 p-2 subaspect-card" id="sub-${suid}">
  <input type="hidden" class="subaspect-id" value="${sid}">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <div style="flex:1">
      <label class="form-label">Nama Subaspek</label>
      <input type="text" class="form-control subaspect-name" value="${escapeAttr(sname)}"
        oninput="updateInfo()" ${isReadOnly ? 'readonly' : '' }>
    </div>
    <div style="width:140px; margin-left:10px">
      <label class="form-label">Bobot (%)</label>
      <input type="number" class="form-control subaspect-weight" value="${Number(sweight)}"
        min="0" max="100" oninput="updateSubaspectWeight('${suid}', this.value, '${aspectUid}')"
        ${isReadOnly ? 'readonly' : '' }>
    </div>
    ${!isReadOnly ? `
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
  ${!isReadOnly ? `
  <div class="mt-2">
    <button class="btn btn-outline-secondary btn-sm" type="button"
      onclick="addQuestionToSub('${suid}')">+ Tambah Pertanyaan</button>
  </div>
  ` : ''}
</div>
`;
  }

  function questionInputTemplate(suid, q = {}) {
    const qid = q.id || "";
    const qtext = q.pertanyaan || "";
    return `
<div class="input-group mb-2 question-row" data-question-id="${escapeAttr(qid)}">
  <input type="text" class="form-control question-text" value="${escapeAttr(qtext)}"
    placeholder="Masukkan pertanyaan">
  <button class="btn btn-outline-danger" type="button"
    onclick="confirmRemoveQuestionInSub(this, '${suid}')">Hapus</button>
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

    if (idPoint) {
      Swal.fire({
        title: 'Hapus Subaspek?',
        text: "Subaspek ini akan dihapus dari server",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: `/api/kpi-point/${idPoint}`,
            method: "DELETE",
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
              showAlert('error', 'Error', 'Gagal menghapus subaspek');
            }
          });
        }
      });
    } else {
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
      .map((_, el) => Number(el.value) || 0)
      .get();
    const totalWeight = weights.reduce((a, b) => a + b, 0);
    $("#infoTotalWeight").text(totalWeight + "%");
    $("#infoTopicCount").text($("#topicContents .tab-pane").length);
  }

  // ==================== SAVE KPI ====================
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
      const isGlobal = $pane.find(".aspect-is-global").val() === "true";
      const nama = $pane.find(".aspect-name").val().trim();
      const bobot = Number($pane.find(".aspect-weight").val()) || 0;

      if (currentMode === "division" && isGlobal) {
        return true;
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

        if (questions.length === 0) {
          showAlert('warning', 'Peringatan', 'Subaspek harus memiliki minimal 1 pertanyaan!');
          valid = false;
          return false;
        }

        points.push({
          id_point: idPoint,
          nama: subNama,
          bobot: subBobot,
          questions: questions,
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
        is_global: isGlobal,
        points: points,
      });
    });

    if (!valid) return;

    const payload = {
      is_global: currentMode === "global",
      division_id: currentMode === "division" ? currentDivisionId : null,
      kpis: aspects
    };

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
          loadKpiTemplates();
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
</script>
@endsection