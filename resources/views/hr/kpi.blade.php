@extends('template.template')

@section('title', 'Management KPI Template')

@section('content')
<style>
  .nav-tabs-wrapper {
    display: flex;
    align-items: center;
    justify-content: space-between;
    overflow-x: auto;
    flex-wrap: wrap;
  }

  .nav-link:not(.active) {
    color: white !important;
  }

  .global-kpi-badge {
    background-color: #6c757d;
    color: white;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 0.7rem;
    margin-left: 6px;
    white-space: nowrap;
  }

  .subaspect-card {
    border-left: 4px solid #0d6efd;
    border-radius: 8px;
    margin-bottom: 1rem;
  }

  .question-row {
    border-left: 2px solid #6c757d;
    padding-left: 10px;
    margin-bottom: 0.5rem;
  }

  .swal2-container {
    z-index: 99999 !important;
  }

  .nav-tabs .nav-item {
    position: relative;
    min-width: 120px;
  }

  .nav-tabs .nav-link {
    padding-right: 35px !important;
    position: relative;
    display: flex;
    align-items: center;
    min-height: 40px;
    font-size: 0.875rem;
    white-space: nowrap;
    overflow: hidden;
  }

  .tab-delete-btn {
    position: absolute;
    top: 50%;
    right: 8px;
    transform: translateY(-50%);
    background: rgba(220, 53, 69, 0.9);
    color: white;
    border: none;
    border-radius: 4px;
    width: 22px;
    height: 22px;
    padding: 0;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: all 0.2s ease;
    font-size: 11px;
  }

  .nav-tabs .nav-link.active .tab-delete-btn {
    opacity: 1;
  }

  .nav-tabs .nav-link:hover .tab-delete-btn {
    opacity: 0.7;
  }

  .tab-delete-btn:hover {
    background: #dc3545;
    transform: translateY(-50%) scale(1.1);
  }

  .tab-delete-btn svg {
    width: 12px;
    height: 12px;
    fill: currentColor;
  }

  /* Style untuk tab content */
  .nav-tabs .nav-link .tab-content-wrapper {
    display: flex;
    align-items: center;
    flex: 1;
    min-width: 0;
    margin-right: 8px;
  }

  .nav-tabs .nav-link .tab-text {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    flex: 1;
    min-width: 0;
    font-size: 0.8rem;
  }

  /* Responsive Design */
  @media (max-width: 768px) {
    .container {
      padding: 0 0.5rem;
    }

    .card {
      margin-bottom: 1rem;
      border-radius: 8px;
    }

    .card-header {
      padding: 0.75rem 1rem;
    }

    .card-body {
      padding: 1rem;
    }

    .card-title {
      font-size: 1.1rem;
    }

    /* Form controls mobile */
    .form-control,
    .form-select {
      font-size: 0.875rem;
      padding: 0.5rem 0.75rem;
    }

    /* Info cards mobile */
    .p-3.bg-light.rounded {
      padding: 0.75rem !important;
      margin-bottom: 0.5rem;
    }

    .p-3.bg-light.rounded i {
      font-size: 1.25rem !important;
      margin-right: 0.5rem !important;
    }

    /* Button group mobile */
    .d-flex.justify-content-between {
      flex-direction: column;
      gap: 0.75rem;
    }

    .d-flex.justify-content-between .btn {
      width: 100%;
      font-size: 0.875rem;
      padding: 0.5rem 1rem;
    }

    /* Nav tabs mobile */
    .nav-tabs-wrapper {
      overflow-x: auto;
      padding-bottom: 0.25rem;
    }

    .nav-tabs {
      flex-wrap: nowrap;
      min-width: max-content;
    }

    .nav-tabs .nav-item {
      min-width: 100px;
    }

    .nav-tabs .nav-link {
      padding: 0.5rem 0.75rem;
      font-size: 0.8rem;
    }

    /* Subaspect card mobile */
    .subaspect-card {
      padding: 0.75rem;
    }

    .subaspect-card .d-flex {
      flex-direction: column;
      gap: 0.5rem;
    }

    .subaspect-card .d-flex>div {
      width: 100% !important;
      margin-left: 0 !important;
    }

    .subaspect-card .btn {
      width: 100%;
      margin-top: 0.5rem;
    }

    /* Attendance configuration mobile */
    .card-body.p-3 .row.g-2 {
      margin: -0.25rem;
    }

    .card-body.p-3 .row.g-2>[class*="col-"] {
      padding: 0.25rem;
    }

    .attendance-multiplier {
      font-size: 0.8rem;
    }

    /* Questions mobile */
    .input-group.mb-2 {
      flex-direction: column;
      gap: 0.5rem;
    }

    .input-group.mb-2 .form-control {
      width: 100%;
    }

    .input-group.mb-2 .btn {
      width: 100%;
    }

    /* Alert mobile */
    .alert {
      padding: 0.75rem;
      font-size: 0.875rem;
    }
  }

  @media (max-width: 576px) {
    .container {
      padding: 0 0.25rem;
    }

    .body.d-flex.py-3 {
      padding: 0.5rem 0 !important;
    }

    .card {
      margin-bottom: 0.75rem;
      border-radius: 6px;
    }

    .card-header {
      padding: 0.5rem 0.75rem;
    }

    .card-body {
      padding: 0.75rem;
    }

    /* Grid system mobile */
    .row.g-3 {
      margin: -0.5rem;
    }

    .row.g-3>[class*="col-"] {
      padding: 0.5rem;
    }

    /* Info section mobile */
    .row.g-3 .col-md-6,
    .row.g-3 .col-md-4 {
      margin-bottom: 0.5rem;
    }

    /* Form labels mobile */
    .form-label {
      font-size: 0.875rem;
      margin-bottom: 0.25rem;
    }

    .form-label.fw-bold {
      font-size: 0.9rem;
    }

    /* Button sizes mobile */
    .btn {
      font-size: 0.8rem;
      padding: 0.4rem 0.75rem;
    }

    .btn-sm {
      font-size: 0.75rem;
      padding: 0.3rem 0.6rem;
    }

    /* Tab navigation mobile */
    .nav-tabs .nav-item {
      min-width: 90px;
    }

    .nav-tabs .nav-link {
      padding: 0.4rem 0.6rem;
      font-size: 0.75rem;
    }

    .tab-delete-btn {
      width: 18px;
      height: 18px;
      font-size: 10px;
    }

    .global-kpi-badge {
      font-size: 0.65rem;
      padding: 1px 4px;
      margin-left: 4px;
    }

    /* Subaspect mobile optimization */
    .subaspects-wrapper h6 {
      font-size: 0.9rem;
    }

    .questions-list {
      font-size: 0.875rem;
    }

    /* Attendance multipliers grid mobile */
    .card-body.p-3 .row.g-2>[class*="col-"] {
      flex: 0 0 50%;
      max-width: 50%;
    }

    /* Modal adjustments for mobile */
    .modal-dialog {
      margin: 0.5rem;
    }

    .modal-content {
      border-radius: 8px;
    }

    .modal-header,
    .modal-footer {
      padding: 0.75rem 1rem;
    }

    .modal-body {
      padding: 1rem;
    }
  }

  @media (max-width: 400px) {

    /* Extra small devices */
    .nav-tabs .nav-item {
      min-width: 80px;
    }

    .nav-tabs .nav-link {
      padding: 0.35rem 0.5rem;
      font-size: 0.7rem;
    }

    .tab-text {
      font-size: 0.7rem;
    }

    /* Info cards extra small */
    .p-3.bg-light.rounded {
      padding: 0.5rem !important;
    }

    .p-3.bg-light.rounded i {
      font-size: 1rem !important;
      margin-right: 0.25rem !important;
    }

    .p-3.bg-light.rounded div:first-child {
      font-size: 0.75rem;
    }

    .p-3.bg-light.rounded .fw-bold.fs-6 {
      font-size: 0.8rem !important;
    }

    /* Attendance multipliers single column */
    .card-body.p-3 .row.g-2>[class*="col-"] {
      flex: 0 0 100%;
      max-width: 100%;
    }

    /* Button text adjustment */
    .btn .bi {
      margin-right: 0.25rem;
    }

    .btn-text {
      font-size: 0.75rem;
    }
  }

  /* Tablet specific adjustments */
  @media (min-width: 769px) and (max-width: 1024px) {
    .container {
      max-width: 100%;
      padding: 0 1rem;
    }

    .nav-tabs .nav-item {
      min-width: 140px;
    }

    .nav-tabs .nav-link {
      padding: 0.6rem 1rem;
    }

    /* Subaspect card tablet */
    .subaspect-card .d-flex {
      flex-wrap: wrap;
      gap: 0.75rem;
    }

    .subaspect-card .d-flex>div {
      flex: 1;
      min-width: 200px;
    }

    .subaspect-card .d-flex>div:last-child {
      flex: 0 0 auto;
    }

    /* Attendance configuration tablet */
    .card-body.p-3 .row.g-2>[class*="col-"] {
      flex: 0 0 33.333%;
      max-width: 33.333%;
    }
  }

  /* Desktop optimizations */
  @media (min-width: 1025px) {
    .container {
      max-width: 1200px;
    }

    .nav-tabs .nav-item {
      min-width: 160px;
    }

    .nav-tabs .nav-link {
      padding: 0.75rem 1.25rem;
    }
  }

  /* Common improvements for all devices */
  .card {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    border: 1px solid #eef2f7;
  }

  .form-control:focus,
  .form-select:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  }

  .btn {
    border-radius: 6px;
    transition: all 0.2s ease;
  }

  .btn:hover {
    transform: translateY(-1px);
  }

  /* Loading state improvements */
  .swal2-popup {
    border-radius: 12px;
  }

  /* Scrollbar styling for mobile */
  .nav-tabs-wrapper::-webkit-scrollbar {
    height: 4px;
  }

  .nav-tabs-wrapper::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 2px;
  }

  .nav-tabs-wrapper::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 2px;
  }

  .nav-tabs-wrapper::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
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
                        <div>Divisi</div>
                        <div id="infoDivision" class="fw-bold fs-6">-</div>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="p-3 bg-light rounded d-flex align-items-center">
                      <i class="bi bi-percent text-warning fs-4 me-3"></i>
                      <div>
                        <div>Total Bobot</div>
                        <div id="infoTotalWeight" class="fw-bold fs-6">0%</div>
                        <!-- Tambahan untuk detail perhitungan -->
                        <div id="weightCalculation" class="small text-muted mt-1" style="display: none;">
                          <span id="globalWeight">0%</span> + <span id="divisionWeight">0%</span> = <span id="totalWeight">0%</span>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="p-3 bg-light rounded d-flex align-items-center">
                      <i class="bi bi-graph-up text-danger fs-4 me-3"></i>
                      <div>
                        <div>Maksimal Bobot</div>
                        <div class="fw-bold fs-6">100%</div>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="p-3 bg-light rounded d-flex align-items-center">
                      <i class="bi bi-list-ol text-primary fs-4 me-3"></i>
                      <div>
                        <div>Jumlah Aspek</div>
                        <div id="infoTopicCount" class="fw-bold fs-6">0</div>
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
    $("#modeSelect").off('change').on("change", onModeChange);
    $("#divisionSelect").off('change').on("change", changeDivision);
    $("#addTopicBtn").off('click').on("click", addAspect);
    $("#saveKPIBtn").off('click').on("click", saveKPI);

    // ✅ EVENT UNTUK TAB ACTIVATION
    $('#topicTabs').on('shown.bs.tab', function(e) {
      updateTabDeleteButtons();
    });

    // ✅ NEW: Event listener untuk tombol delete di tab (delegated)
    $(document).on('click', '.tab-delete-btn', function(e) {
      e.stopPropagation();
      e.preventDefault();

      const aspectUid = $(this).data('aspect-uid');
      console.log('🎯 Delete button clicked for:', aspectUid);

      if (aspectUid) {
        confirmRemoveAspect(aspectUid);
      }
    });

    // ✅ Initial update untuk tombol delete
    setTimeout(updateTabDeleteButtons, 500);
  }

  // Fungsi baru untuk mengatur visibilitas tombol hapus
  function updateDeleteButtonsVisibility() {
    // Sembunyikan semua tombol hapus
    $('.delete-tab-btn').css('opacity', '0');

    // Tampilkan hanya pada tab aktif
    const activeTab = $('#topicTabs .nav-link.active');
    if (activeTab.length) {
      activeTab.find('.delete-tab-btn').css('opacity', '1');
    }
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

  function loadActivePeriods() {
    // ✅ AMBIL periode yang sudah ada absensi DAN sudah auto-published KPI
    fetch('/api/periods?attendance_uploaded=1&kpi_published=1&status=active')
      .then(res => res.json())
      .then(response => {
        if (response.success && response.data.length > 0) {
          const activePeriod = response.data[0];
          currentPeriodId = activePeriod.id_periode;
          currentPeriodData = activePeriod;

          $('#periodInfo').html(`
                    <div class="alert alert-success">
                        <strong>Periode Aktif:</strong> ${activePeriod.nama}<br>
                        <small>${formatDate(activePeriod.tanggal_mulai)} - ${formatDate(activePeriod.tanggal_selesai)}</small>
                        <br><small><em>KPI tersedia otomatis setelah import absensi</em></small>
                    </div>
                `);

          // HAPUS deadline info
          $('#deadlineInfo').hide();

        } else {
          $('#periodInfo').html(`
                    <div class="alert alert-info">
                        <strong>Menunggu Data Absensi</strong><br>
                        <small>KPI akan tersedia otomatis setelah absensi di-import</small>
                    </div>
                `);

          currentPeriodId = null;
          currentPeriodData = null;
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
    let url = '';
    const params = [];

    if (currentMode === "global") {
      url = '/api/kpis/templates';
      params.push(`is_global=1`);
    } else if (currentMode === "division" && currentDivisionId) {
      // ⚠️ PERBAIKAN: Load SEMUA KPI (Global + Divisi) HANYA jika divisi dipilih
      url = `/api/kpis/division/${currentDivisionId}`;
    } else if (currentMode === "division" && !currentDivisionId) {
      // ⚠️ FIX: Jika mode divisi tapi belum pilih divisi, clear form dan keluar
      console.log('🔴 Mode divisi tapi belum pilih divisi, clear form');
      clearKPIForm();
      return;
    } else {
      clearKPIForm();
      return;
    }

    if (params.length) url += '?' + params.join('&');

    console.log('🔍 Loading KPI from:', url);

    $.ajax({
      url: url,
      method: "GET",
      success: function(response) {
        if (response.success) {
          clearKPIForm();

          if (response.data && response.data.length > 0) {
            // ⚠️ PERBAIKAN: Simpan SEMUA data KPI untuk perhitungan bobot
            window.allKpiData = response.data;

            // Debug: lihat data yang diterima
            console.log('📦 ALL KPI DATA:', response.data);
            console.log('🌍 GLOBAL KPIs:', response.data.filter(kpi => kpi.is_global));
            console.log('🏢 DIVISION KPIs:', response.data.filter(kpi => !kpi.is_global));

            // Hanya render KPI Divisi saja (non-global)
            const kpisToRender = currentMode === "division" ?
              response.data.filter(kpi => !kpi.is_global) :
              response.data;

            console.log('🎯 KPI to RENDER:', kpisToRender);

            if (kpisToRender.length > 0) {
              kpisToRender.forEach((kpi) => renderAspect(normalizeKpiFromServer(kpi), false));
            } else {
              showNoDataMessage();
            }
          } else {
            showNoDataMessage();
          }
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

    // ⚠️ FIX: Hanya buat aspek "Disiplin" otomatis di mode Global
    if (currentMode === "global") {
      // Cek apakah sudah ada aspek "Disiplin" di mode Global
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
      } else {
        // Jika belum ada Disiplin di Global, buat Disiplin dengan sub-aspek absensi
        const aspect = {
          uid: uid("aspect"),
          id: null,
          nama: "Disiplin",
          bobot: 30, // Default bobot untuk Disiplin
          is_global: true,
          points: [],
        };

        renderAspect(aspect, true);
        setActiveTab(aspect.uid);
        updateInfo();
        return;
      }
    }

    // ⚠️ FIX: Untuk mode divisi, selalu buat aspek biasa (bukan Disiplin)
    if (currentMode === "division") {
      const aspect = {
        uid: uid("aspect"),
        id: null,
        nama: "",
        bobot: 0,
        is_global: false,
        points: [],
      };
      renderAspect(aspect, true);
      setActiveTab(aspect.uid);
      updateInfo();
      return;
    }

    // Fallback untuk mode lainnya
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


    // ✅ PERBAIKAN: Gunakan span bukan button untuk menghindari nested buttons
    const tabHtml = `
<li class="nav-item" id="tab-btn-${aspectUid}">
  <button class="nav-link position-relative" id="tab-${aspectUid}-tab" data-bs-toggle="tab"
    data-bs-target="#tab-${aspectUid}" type="button" role="tab" data-aspect-uid="${aspectUid}">
    <div class="tab-content-wrapper">
      <span class="tab-text">${escapeAttr(aspectName) || "Aspek Baru"}</span>
      ${isGlobal ? '<span class="global-kpi-badge">Global</span>' : ''}
    </div>
    <span class="tab-delete-btn" title="Hapus Aspek" data-aspect-uid="${aspectUid}">
      <i class="icofont-ui-delete"></i>
    </span>
  </button>
</li>
`;

    $("#topicTabs").append(tabHtml);
    // Create content
    let subHtml = "";
    points.forEach((sa) => (subHtml += subaspectTemplate(aspectUid, sa, isGlobal)));

    // KPI Global menjadi read-only ketika di mode Divisi
    const isReadOnly = currentMode === "division" && isGlobal;

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
  
  <div class="subaspects-wrapper" id="subaspects-${aspectUid}">
    <h6>Subaspek</h6>
    ${subHtml}
  </div>
  ${!isReadOnly ? `
  <div class="mt-2">
    <button class="btn btn-outline-primary btn-sm" type="button"
      onclick="addSubaspect('${aspectUid}')">+ Tambah Subaspek</button>
  </div>
  ` : ''}
</div>
`;
    $("#topicContents").append(contentHtml);

    // ✅ AUTO-CREATE SUB-ASPEK ABSENSI JIKA DISIPLIN GLOBAL BARU
    const isDisiplinGlobal = isGlobal && aspectName.toLowerCase().includes('disiplin');
    if (isDisiplinGlobal && newlyCreated && points.length === 0) {
      addAbsensiSubaspect(aspectUid);
    }

    if (points.length === 0 && !isReadOnly && !isDisiplinGlobal) addSubaspect(aspectUid);
    if (newlyCreated || $("#topicTabs .nav-link").length === 1) setActiveTab(aspectUid);
    updateInfo();
  }

  function addAbsensiSubaspect(aspectUid, defaultBobot = 10) {
    const sub = {
      uid: uid("sub"),
      id: "",
      nama: "Penilaian Absensi",
      bobot: defaultBobot,
      questions: [],
    };

    const html = subaspectTemplate(aspectUid, sub);
    $(`#subaspects-${aspectUid}`).append(html);

    // ✅ TAMBAH INFO BAHWA INI SUB-ASPEK ABSENSI
    const subCard = $(`#sub-${sub.uid}`);
    subCard.addClass('border-warning');
    subCard.find('.subaspect-name').attr('readonly', true).addClass('fw-bold text-warning');
    subCard.find('.subaspect-weight').removeAttr('readonly');

    // ✅ HAPUS TOMBOL HAPUS UNTUK ABSENSI
    subCard.find('button[onclick*="confirmRemoveSubaspect"]').remove();

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

    // ✅ JIKA ABSENSI, TAMPILKAN KONFIGURASI DAN INFORMASI KHUSUS
    if (isAbsensi) {
      qHtml = `
        <!-- ✅ KONFIGURASI ABSENSI DINAMIS -->
        <div class="card border-warning mt-3">
            <div class="card-header bg-warning text-dark py-2">
                <i class="bi bi-gear-fill me-2"></i>
                Konfigurasi Penilaian Absensi
            </div>
            <div class="card-body p-3">
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label small">Hadir ×</label>
                        <input type="number" class="form-control form-control-sm attendance-multiplier" 
                               data-type="hadir_multiplier" value="3" ${isReadOnly ? 'readonly' : ''}>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Sakit ×</label>
                        <input type="number" class="form-control form-control-sm attendance-multiplier" 
                               data-type="sakit_multiplier" value="0" ${isReadOnly ? 'readonly' : ''}>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Izin ×</label>
                        <input type="number" class="form-control form-control-sm attendance-multiplier" 
                               data-type="izin_multiplier" value="0" ${isReadOnly ? 'readonly' : ''}>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Mangkir ×</label>
                        <input type="number" class="form-control form-control-sm attendance-multiplier" 
                               data-type="mangkir_multiplier" value="-3" ${isReadOnly ? 'readonly' : ''}>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Terlambat ×</label>
                        <input type="number" class="form-control form-control-sm attendance-multiplier" 
                               data-type="terlambat_multiplier" value="-2" ${isReadOnly ? 'readonly' : ''}>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Hari Kerja ×</label>
                        <input type="number" class="form-control form-control-sm attendance-multiplier" 
                               data-type="workday_multiplier" value="2" min="1" ${isReadOnly ? 'readonly' : ''}>
                    </div>
                </div>
                <small class="text-muted mt-2 d-block">
                    <i class="bi bi-info-circle"></i> Konfigurasi ini menentukan rumus perhitungan nilai absensi otomatis
                </small>
            </div>
        </div>
        
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
        ${isReadOnly ? 'readonly' : ''}>
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

    console.log('Deleting subaspect:', {
      subUid,
      idPoint
    }); // Debug log

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

    // Update tombol hapus setelah penghapusan
    setTimeout(updateDeleteButtonsVisibility, 100);

    updateInfo();
  }

  // ✅ FUNGSI UNTUK MENGATUR TAMPIL TOMBOL DELETE PADA TAB AKTIF
  function updateTabDeleteButtons() {
    console.log('🔄 Updating tab delete buttons...');

    $('.tab-delete-btn').css('opacity', '0');

    const activeTab = $('#topicTabs .nav-link.active');
    console.log('🔍 Active tab found:', activeTab.length);

    if (activeTab.length) {
      const deleteBtn = activeTab.find('.tab-delete-btn');
      console.log('🔍 Delete button found:', deleteBtn.length);
      deleteBtn.css('opacity', '1');
    }
  }

  function updateAspectTabTitle(uid, text) {

    const el = $(`#tab-btn-${uid} .nav-link`);
    if (el.length) {
      const isGlobal = el.find('.global-kpi-badge').length > 0;

      // Update dengan struktur baru
      el.html(`
      <div class="tab-content-wrapper">
        <span class="tab-text">${escapeAttr(text) || "Aspek Baru"}</span>
        ${isGlobal ? '<span class="global-kpi-badge">Global</span>' : ''}
      </div>
      <span class="tab-delete-btn" title="Hapus Aspek" data-aspect-uid="${uid}">
        <i class="icofont-ui-delete"></i>
      </span>
    `);
    }
  }

  function setActiveTab(uid) {
    const btn = $(`#tab-${uid}-tab`);
    if (btn.length) {
      // Aktifkan tab
      new bootstrap.Tab(btn[0]).show();

      // ✅ UPDATE TOMBOL DELETE - beri timeout untuk memastikan tab sudah aktif
      setTimeout(() => {
        updateTabDeleteButtons();
        debugCheckTabButtons(); // DEBUG
      }, 200);
    }
  }

  function updateInfo() {
    let totalWeight = 0;
    let globalWeight = 0;
    let divisionWeight = 0;

    // ⚠️ PERBAIKAN: Hitung bobot Global dan Divisi secara terpisah
    if (currentMode === "division" && currentDivisionId && window.allKpiData) {
      // Hitung bobot Global
      globalWeight = window.allKpiData
        .filter(kpi => kpi.is_global)
        .reduce((sum, kpi) => sum + (Number(kpi.bobot) || 0), 0);

      // Hitung bobot Divisi
      divisionWeight = window.allKpiData
        .filter(kpi => !kpi.is_global)
        .reduce((sum, kpi) => sum + (Number(kpi.bobot) || 0), 0);

      totalWeight = globalWeight + divisionWeight;

      console.log('📊 Detailed weight calculation:', {
        globalWeight: globalWeight,
        divisionWeight: divisionWeight,
        totalWeight: totalWeight
      });

      // Tampilkan detail perhitungan
      $("#weightCalculation").show();
      $("#globalWeight").text(globalWeight + "%");
      $("#divisionWeight").text(divisionWeight + "%");
      $("#totalWeight").text(totalWeight + "%");

    } else if (currentMode === "division" && !currentDivisionId) {
      // ⚠️ FIX: Jika mode divisi tapi belum pilih divisi
      totalWeight = 0;
      $("#weightCalculation").hide();
      console.log('🔴 Mode divisi dipilih tapi belum pilih divisi, bobot = 0%');
    } else if (currentMode === "global") {
      // Mode Global: hitung seperti biasa
      totalWeight = $(".aspect-weight")
        .map((_, el) => Number($(el).val()) || 0)
        .get()
        .reduce((a, b) => a + b, 0);

      // Sembunyikan detail perhitungan di mode Global
      $("#weightCalculation").hide();
    } else {
      // Fallback untuk kondisi lainnya
      totalWeight = 0;
      $("#weightCalculation").hide();
    }

    $("#infoTotalWeight").text(totalWeight + "%");
    $("#infoTopicCount").text($("#topicContents .tab-pane").length);

    // Highlight jika total bobot tidak 100%
    if (totalWeight > 100) {
      $("#infoTotalWeight").addClass("text-danger");
      $("#totalWeight").addClass("text-danger");
    } else {
      $("#infoTotalWeight").removeClass("text-danger");
      $("#totalWeight").removeClass("text-danger");
    }

    // Highlight jika ada masalah di perhitungan detail
    if (globalWeight > 100 || divisionWeight > 100) {
      $("#globalWeight, #divisionWeight").addClass("text-danger");
    } else {
      $("#globalWeight, #divisionWeight").removeClass("text-danger");
    }
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

        // ⚠️ PERBAIKAN: Validasi pertanyaan hanya untuk non-absensi
        if (questions.length === 0 && !isAbsensiSubaspect) {
          showAlert('warning', 'Peringatan', `Subaspek "${subNama}" harus memiliki minimal 1 pertanyaan!`);
          valid = false;
          return false;
        }

        // ✅ AMBIL KONFIGURASI MULTIPLIER JIKA INI SUB-ASPEK ABSENSI
        let attendanceMultipliers = null;
        if (isAbsensiSubaspect) {
          attendanceMultipliers = {};
          $sub.find('.attendance-multiplier').each(function() {
            const type = $(this).data('type');
            const value = Number($(this).val()) || 0;
            attendanceMultipliers[type] = value;
          });
        }

        points.push({
          id_point: idPoint,
          nama: subNama,
          bobot: subBobot,
          questions: questions,
          is_absensi: isAbsensiSubaspect,
          attendance_multipliers: attendanceMultipliers
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

    console.log('Payload to save:', payload);

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

  });
</script>
@endsection