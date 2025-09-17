@extends('template.template')

@section('title', 'KPI')

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
</style>

<div class="body d-flex py-3">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-12">
        <div class="card mb-3 shadow-sm">
          <div class="card-header bg-transparent border-0 row">
            <div class="row mb-3">
              <!-- Dropdown mode -->
              <div class="col-md-6">
                <label class="form-label fw-bold">Pilih Mode</label>
                <select id="modeSelect" class="form-select">
                  <option value="">Pilih Mode</option>
                  <option value="global">Global</option>
                  <option value="division">Divisi</option>
                </select>
              </div>

              <!-- Dropdown divisi (default disembunyikan) -->
              <div class="col-md-6" id="divisionWrapper" style="display:none;">
                <label class="form-label fw-bold">Pilih Divisi</label>
                <select id="divisionSelect" class="form-select">
                  <option value="">Pilih Divisi</option>
                </select>
              </div>
            </div>

            <!-- Card KPI -->
            <div class="card border-0 mb-4 shadow-sm">
              <div class="card-body">
                <h5 class="card-title fw-bold mb-3">
                  <i class="bi bi-bar-chart-fill text-primary me-2"></i>
                  Informasi KPI
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
                        <small class="text-muted">Jumlah aspek divisi</small>
                        <div id="infoTopicCount" class="fw-semibold">0</div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Modal Edit KPI -->
            <div class="card mb-5 shadow-sm">
              <div class="card-header bg-primary">
                <div class="nav-tabs-wrapper mb-2">
                  <ul class="nav nav-tabs" id="topicTabs"></ul>
                  <div class="ms-auto">
                    <button id="addTopicBtn" class="btn btn-light btn-sm">+ Tambah aspek</button>
                    <button id="saveKPIBtn" class="btn btn-success btn-sm ms-2">Simpan KPI</button>
                  </div>
                </div>
              </div>
              <div class="card-body">
                <div class="p-3 tab-content mb-2" id="topicContents"></div>
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
<script>
  let _uidCounter = 0;

  function uid(prefix = "id") {
    _uidCounter++;
    return `${prefix}_${Date.now()}_${_uidCounter}`;
  }

  let currentDivisionId = "";
  let currentMode = ""; // "global" atau "division"
  const divisionSelect = document.getElementById("divisionSelect");

  $(document).ready(function() {
    $("#modeSelect").on("change", onModeChange);
    loadDivisions();
    $(divisionSelect).on("change", changeDivision);
    $("#addTopicBtn").on("click", addAspect);
    $("#saveKPIBtn").on("click", saveKPI);
  });

  function onModeChange() {
    currentMode = $("#modeSelect").val();
    if (currentMode === "division") {
      $("#divisionWrapper").show();
    } else {
      $("#divisionWrapper").hide();
      $("#divisionSelect").val("");
      currentDivisionId = "";
    }

    if (currentMode === "global") {
      $("#infoDivision").text("Global");
      loadKpiData();
    } else {
      $("#infoDivision").text("-");
      clearKPIForm();
    }
  }

  function loadDivisions() {
    $.getJSON("/api/divisions")
      .done(function(response) {
        divisionSelect.innerHTML = '<option value="">Pilih Divisi</option>';
        if (response && response.success && response.data) {
          response.data.forEach((division) => {
            const option = document.createElement("option");
            option.value = division.id_divisi;
            option.textContent = division.nama_divisi;
            divisionSelect.appendChild(option);
          });
        }
      })
      .fail(function(xhr) {
        console.error("Error loading divisions:", xhr.responseText || xhr.statusText);
      });
  }

  function changeDivision() {
    currentDivisionId = divisionSelect.value;
    if (currentDivisionId) {
      $("#infoDivision").text($("#divisionSelect option:selected").text());
      if (currentMode === "division") loadKpiData();
    } else {
      $("#infoDivision").text("-");
      clearKPIForm();
    }
  }

  function clearKPIForm() {
    $("#topicTabs").empty();
    $("#topicContents").empty();
    updateInfo();
  }

  function loadKpiData() {
    clearKPIForm();
    let url = '';
    
    if (currentMode === "division" && currentDivisionId) {
        url = `/api/kpi-by-division/${currentDivisionId}`;
    } else if (currentMode === "global") {
        url = `/api/kpi-global`;
    } else {
        return;
    }

    $.getJSON(url)
        .done(function(response) {
            // Perhatikan struktur response yang berbeda
            let kpis = [];
            
            if (currentMode === "division") {
                // Response dari /api/kpi-by-division/{id} langsung array KPI
                kpis = response || [];
            } else if (currentMode === "global") {
                // Response dari /api/kpi-global adalah {kpis: array}
                kpis = response.kpis || [];
            }

            if (kpis.length) {
                kpis.forEach((kpi) => renderAspect(normalizeKpiFromServer(kpi), false));
                updateInfo();
            }
        })
        .fail(function(xhr) {
            console.error("Error loading KPI data:", xhr.responseText || xhr.statusText);
            alert("Gagal memuat data KPI");
        });
}
        function normalizeKpiFromServer(kpi) {
          return {
            uid: uid("kpi"),
            id: kpi.id || kpi.id_kpi || null,
            nama: kpi.nama || "",
            bobot: kpi.bobot !== undefined ? kpi.bobot : 0,
            is_global: kpi.is_global || false, // Pastikan ini ada
            subaspects: (kpi.points || kpi.kpi_points || []).map((pt) => ({
            uid: uid("sub"),
            id: pt.id || pt.id_point || null,
            nama: pt.nama || "",
            bobot: pt.bobot !== undefined ? pt.bobot : 0,
            questions: (pt.questions || pt.kpi_questions || []).map((q) => ({
                id: q.id_question || q.id || null,
                pertanyaan: q.pertanyaan || "",
            })),
        })),
    };
}

  function addAspect() {
    if (currentMode === "division" && !currentDivisionId) {
      alert("Pilih divisi dulu!");
      return;
    }
    const aspect = {
      uid: uid("aspect"),
      id: null,
      nama: "",
      bobot: 0,
      subaspects: [],
    };

    renderAspect(aspect, true);
    setActiveTab(aspect.uid);
    updateInfo();
  }

  function renderAspect(aspectObj, newlyCreated = false) {
    const aspectUid = aspectObj.uid || uid("aspect");
    const aspectId = aspectObj.id || "";
    const aspectName = aspectObj.nama || "";
    const aspectWeight = aspectObj.bobot || 0;
    const subaspects = aspectObj.subaspects || [];
    const isGlobal = aspectObj.is_global || false;

    // Tab - tambahkan class khusus untuk KPI global
    const li = document.createElement("li");
    li.className = "nav-item";
    li.id = `tab-btn-${aspectUid}`;
    li.setAttribute("role", "presentation");
    
    if (currentMode === "division" && isGlobal) {
        li.classList.add("text-muted"); // Tampilkan berbeda untuk KPI global
    }

    const btn = document.createElement("button");
    btn.type = "button";
    btn.className = "nav-link";
    btn.id = `tab-${aspectUid}-tab`;
    btn.setAttribute("data-bs-toggle", "tab");
    btn.setAttribute("data-bs-target", `#tab-${aspectUid}`);
    btn.setAttribute("role", "tab");
    btn.textContent = aspectName || "Aspek Baru";
    li.appendChild(btn);
    document.getElementById("topicTabs").appendChild(li);

    // Content - tambahkan kondisi readonly untuk KPI global
    let subHtml = "";
    subaspects.forEach((sa) => (subHtml += subaspectTemplate(aspectUid, sa, isGlobal)));

    const contentPane = document.createElement("div");
    contentPane.className = "tab-pane fade";
    if (document.querySelectorAll("#topicContents .tab-pane").length === 0) {
        contentPane.classList.add("show", "active");
    }
    const isReadOnly = currentMode === "division" && isGlobal;
    contentPane.id = `tab-${aspectUid}`;
    contentPane.setAttribute("role", "tabpanel");
    contentPane.dataset.aspectUid = aspectUid;
    contentPane.innerHTML = `
        <input type="hidden" class="aspect-id" value="${aspectId}">
        <input type="hidden" class="aspect-is-global" value="${isGlobal}">
        <div class="mb-3">
            <label class="form-label">Nama aspek</label>
            <input type="text" class="form-control aspect-name" value="${escapeAttr(aspectName)}"
                oninput="updateAspectTabTitle('${aspectUid}', this.value)" ${isReadOnly ? 'readonly' : ''}>
        </div>
        <div class="mb-3">
            <label class="form-label">Bobot aspek (%)</label>
            <input type="number" class="form-control aspect-weight" value="${Number(aspectWeight)}" min="0" max="100" 
                oninput="updateInfo()" ${isReadOnly ? 'readonly' : ''}>
        </div>
        ${isReadOnly ? '<div class="alert alert-info">KPI Global - Hanya dapat diubah di mode Global</div>' : ''}
        <div class="subaspects-wrapper" id="subaspects-${aspectUid}">
            <h6>Subaspek</h6>
            ${subHtml}
        </div>
        ${!isReadOnly ? `
        <div class="mt-2">
            <button class="btn btn-outline-primary btn-sm" type="button" onclick="addSubaspect('${aspectUid}')">+ Tambah Subaspek</button>
            <button class="btn btn-danger btn-sm" type="button" onclick="confirmRemoveAspect('${aspectUid}')">Hapus aspek</button>
        </div>
        ` : ''}
    `;
    document.getElementById("topicContents").appendChild(contentPane);

    if (subaspects.length === 0 && !isReadOnly) addSubaspect(aspectUid);
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
                oninput="updateInfo()" ${isReadOnly ? 'readonly' : ''}>
          </div>
          <div style="width:140px; margin-left:10px">
            <label class="form-label">Bobot (%)</label>
            <input type="number" class="form-control subaspect-weight" value="${Number(sweight)}" min="0" max="100" 
                oninput="updateInfo()" ${isReadOnly ? 'readonly' : ''}>
          </div>
          ${!isReadOnly ? `
          <div style="margin-left:10px">
            <label class="form-label">&nbsp;</label>
            <button class="btn btn-sm btn-outline-danger d-block" type="button" onclick="confirmRemoveSubaspect('${suid}')">Hapus</button>
          </div>
          ` : ''}
        </div>
        <div class="questions-list" id="questions-${suid}">
          ${qHtml}
        </div>
        ${!isReadOnly ? `
        <div class="mt-2">
          <button class="btn btn-outline-secondary btn-sm" type="button" onclick="addQuestionToSub('${suid}')">+ Tambah Pertanyaan</button>
        </div>
        ` : ''}
      </div>
    `;
}

  function questionInputTemplate(suid, q = {}) {
    const qid = q.id_question || q.id || "";
    const qtext = q.pertanyaan || "";
    return `
      <div class="input-group mb-2 question-row" data-question-id="${escapeAttr(qid)}">
        <input type="text" class="form-control question-text" value="${escapeAttr(qtext)}" placeholder="Masukkan pertanyaan">
        <button class="btn btn-outline-danger" type="button" onclick="confirmRemoveQuestionInSub(this, '${suid}')">Hapus</button>
      </div>
    `;
  }

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

  function confirmRemoveQuestionInSub(btn, subUid) {
    const row = btn.closest(".question-row");
    const qid = row ? row.dataset.questionId : null;
    if (qid) {
      if (!confirm("Yakin hapus pertanyaan ini dari server?")) return;
      $.ajax({
        url: `/api/kpi-question/${qid}`,
        method: "DELETE",
        success: function(resp) {
          if (row) row.remove();
          alert(resp.message || "Pertanyaan dihapus");
          updateInfo();
        },
        error: function(xhr) {
          console.error("Error deleting question:", xhr.responseText || xhr.statusText);
          alert("Gagal menghapus pertanyaan");
        },
      });
    } else {
      if (confirm("Hapus pertanyaan ini?") && row) {
        row.remove();
        updateInfo();
      }
    }
  }

  function confirmRemoveAspect(aspectUid) {
    const pane = $(`#tab-${aspectUid}`);
    const aspectId = pane.find(".aspect-id").val();
    const isGlobal = pane.find(".aspect-is-global").val() === "true";
    
    // Jika KPI global di mode divisi, jangan izinkan hapus
    if (currentMode === "division" && isGlobal) {
        alert("KPI Global tidak dapat dihapus dari mode Divisi. Gunakan mode Global untuk mengelola KPI Global.");
        return;
    }

    if (aspectId) {
        if (!confirm("Yakin hapus aspek ini?")) return;
        $.ajax({
            url: currentMode === "global" ?
                `/api/kpi-global/${aspectId}` :
                `/api/division/${currentDivisionId}/kpi/${aspectId}`,
            method: "DELETE",
            success: function(resp) {
                removeAspectFromUI(aspectUid);
                alert(resp.message || "Aspek dihapus");
                loadKpiData();
            },
            error: function(xhr) {
                console.error("Error deleting aspect:", xhr.responseText || xhr.statusText);
                alert("Gagal menghapus aspek");
            },
        });
    } else {
        if (confirm("Hapus aspek ini?")) {
            removeAspectFromUI(aspectUid);
            updateInfo();
        }
    }
}

  function confirmRemoveSubaspect(subUid) {
    const subCard = $(`#sub-${subUid}`);
    const idPoint = subCard.find(".subaspect-id").val();
    if (idPoint) {
      if (!confirm("Yakin hapus subaspek ini dari server?")) return;
      $.ajax({
        url: `/api/kpi-point/${idPoint}`,
        method: "DELETE",
        success: function(resp) {
          subCard.remove();
          alert(resp.message || "Subaspek dihapus");
          updateInfo();
        },
        error: function(xhr) {
          console.error("Error deleting subaspect:", xhr.responseText || xhr.statusText);
          alert("Gagal menghapus subaspek");
        },
      });
    } else {
      if (confirm("Hapus subaspek ini?")) {
        subCard.remove();
        updateInfo();
      }
    }
  }

  function removeAspectFromUI(aspectUid) {
    $(`#tab-btn-${aspectUid}`).remove();
    $(`#tab-${aspectUid}`).remove();
    const first = $("#topicTabs .nav-link").first();
    if (first.length) new bootstrap.Tab(first.get(0)).show();
    updateInfo();
  }

  function updateAspectTabTitle(uid, text) {
    const el = document.querySelector(`#tab-btn-${uid} button`);
    if (el) el.textContent = text || "Aspek Baru";
  }

  function setActiveTab(uid) {
    const btn = document.querySelector(`#tab-${uid}-tab`);
    if (!btn) return;
    new bootstrap.Tab(btn).show();
  }

  function saveKPI() {
    if (!currentMode) {
        alert("Pilih mode (Global/Divisi) terlebih dahulu.");
        return;
    }
    if (currentMode === "division" && !currentDivisionId) {
        alert("Pilih divisi dulu!");
        return;
    }

    const kpiPanes = $("#topicContents .tab-pane");
    if (kpiPanes.length === 0) {
        alert("Tambahkan minimal 1 aspek KPI!");
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

        // Jika di mode divisi dan KPI global, skip karena tidak boleh diubah
        if (currentMode === "division" && isGlobal) {
            return true; // continue ke next iteration
        }

        if (!nama) {
            alert("Nama KPI tidak boleh kosong!");
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
          alert("Nama subaspek tidak boleh kosong!");
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
          alert("Subaspek harus memiliki minimal 1 pertanyaan!");
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
        alert("Total bobot subaspek tidak boleh lebih dari 100%");
        valid = false;
        return false;
      }

      aspects.push({
        id_kpi: idKpi,
        nama: nama,
        bobot: bobot,
        points: points,
      });
    });

    if (!valid) return;

    // Di function saveKPI(), perbaiki payload yang dikirim:
    const payload = {
      is_global: currentMode === "global" ? 1 : 0,
      division_id: currentMode === "division" ? currentDivisionId : null,
      kpis: aspects.map(aspect => {
        // Untuk KPI yang sudah ada, pertahankan status global aslinya
        if (aspect.id_kpi) {
          return {
            ...aspect,
            // Kirim status global asli untuk KPI yang sudah ada
            is_global: aspect.is_global || (currentMode === "global" ? 1 : 0)
          };
        }
        // Untuk KPI baru, gunakan mode saat ini
        return {
          ...aspect,
          is_global: currentMode === "global" ? 1 : 0
        };
      }),
    };

    console.log("Payload dikirim:", payload);

    $.ajax({
      url: "/api/kpi/save",
      method: "POST",
      contentType: "application/json",
      processData: false,
      headers: {
        Accept: "application/json"
      },
      data: JSON.stringify(payload),
      success: function(response) {
        alert(response.message || "KPI berhasil disimpan!");
        loadKpiData();
      },
      error: function(xhr) {
        console.error("Error saving KPI:", xhr.responseText || xhr.statusText);
        let msg = "Gagal menyimpan KPI";
        try {
          const errResp = JSON.parse(xhr.responseText);
          if (errResp && errResp.errors)
            msg += "\n" + Object.values(errResp.errors).flat().join("\n");
        } catch (e) {}
        alert(msg);
      },
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

  function updateInfo() {
    const weights = $(".aspect-weight")
      .map((_, el) => Number(el.value) || 0)
      .get();
    const totalWeight = weights.reduce((a, b) => a + b, 0);
    $("#infoTotalWeight").text(totalWeight + "%");
    $("#infoTopicCount").text($("#topicContents .tab-pane").length);
  }
</script> 
@endsection
