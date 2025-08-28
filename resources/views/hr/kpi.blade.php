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
              <div class="col-md-6">
                <label class="form-label fw-bold">Pilih Divisi</label>
                <select id="divisionSelect" class="form-select">
                  <option value="">Pilih Divisi</option>
                  <option value="IT">IT</option>
                  <option value="Finance">Finance</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold">Pilih Jabatan</label>
                <select id="positionSelect" class="form-select">
                  <option value="">Pilih Jabatan</option>
                  <option value="Ketua Divisi">Ketua</option>
                  <option value="Karyawan">Karyawan</option>
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
                    <div
                      class="p-3 bg-light rounded d-flex align-items-center">
                      <i
                        class="bi bi-diagram-3-fill text-success fs-4 me-3"></i>
                      <div>
                        <small class="text-muted">Divisi</small>
                        <div id="infoDivision" class="fw-semibold">
                          -
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div
                      class="p-3 bg-light rounded d-flex align-items-center">
                      <i
                        class="bi bi-person-badge-fill text-info fs-4 me-3"></i>
                      <div>
                        <small class="text-muted">Jabatan</small>
                        <div id="infoPosition" class="fw-semibold">
                          -
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div
                      class="p-3 bg-light rounded d-flex align-items-center">
                      <i
                        class="bi bi-percent text-warning fs-4 me-3"></i>
                      <div>
                        <small class="text-muted">Total Bobot</small>
                        <div id="infoTotalWeight" class="fw-semibold">
                          0%
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div
                      class="p-3 bg-light rounded d-flex align-items-center">
                      <i
                        class="bi bi-graph-up text-danger fs-4 me-3"></i>
                      <div>
                        <small class="text-muted">Maksimal Bobot</small>
                        <div class="fw-semibold">100%</div>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div
                      class="p-3 bg-light rounded d-flex align-items-center">
                      <i
                        class="bi bi-list-ol text-primary fs-4 me-3"></i>
                      <div>
                        <small class="text-muted">Jumlah aspek</small>
                        <div id="infoTopicCount" class="fw-semibold">
                          0
                        </div>
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
                    <button
                      id="addTopicBtn"
                      class="btn btn-light btn-sm">
                      + Tambah aspek
                    </button>
                    <button
                      id="saveKPIBtn"
                      class="btn btn-success btn-sm ms-2">
                      Simpan KPI
                    </button>
                  </div>
                </div>
              </div>
              <div class="card-body">
                <div
                  class="p-3 tab-content mb-2"
                  id="topicContents"></div>
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
<!-- plugin chart -->
<script src="assets/bundles/apexcharts.bundle.js"></script>
<!-- Plugin Js tabel-->
<script src="assets/bundles/dataTables.bundle.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  let kpiData = JSON.parse(localStorage.getItem("kpiData")) || {};
  let topicCount = 0;
  let currentKey = "";

  const divisionSelect = document.getElementById("divisionSelect");
  const positionSelect = document.getElementById("positionSelect");

  divisionSelect.addEventListener("change", changeSelection);
  positionSelect.addEventListener("change", changeSelection);
  document
    .getElementById("addTopicBtn")
    .addEventListener("click", addTopic);
  document.getElementById("saveKPIBtn").addEventListener("click", saveKPI);

  function changeSelection() {
    const division = divisionSelect.value;
    const position = positionSelect.value;
    document.getElementById("infoDivision").textContent = division || "-";
    document.getElementById("infoPosition").textContent = position || "-";

    currentKey = division && position ? `${division}|${position}` : "";
    loadData();
  }

  function loadData() {
    document.getElementById("topicTabs").innerHTML = "";
    document.getElementById("topicContents").innerHTML = "";
    topicCount = 0;

    if (!currentKey) return;
    if (!kpiData[currentKey]) kpiData[currentKey] = [];

    kpiData[currentKey].forEach((topic) => {
      topicCount++;
      renderTopic(
        topicCount,
        topic.topicName,
        topic.topicWeight,
        topic.questions
      );
    });

    updateInfo();
  }

  function addTopic() {
    if (!currentKey) {
      alert("Pilih divisi dan jabatan dulu!");
      return;
    }
    topicCount++;
    renderTopic(topicCount, "", "", []);
    setActiveTab(topicCount);
    updateInfo();
  }

  function renderTopic(index, topicName, topicWeight, questions) {
    const tabId = `tab-${index}`;
    const tabHTML = `
          <li class="nav-item" role="presentation" id="tab-btn-${index}">
            <button class="nav-link ${index === 1 ? "active" : ""}" 
              id="${tabId}-tab" data-bs-toggle="tab" data-bs-target="#${tabId}" 
              type="button" role="tab">
              ${topicName || `aspek ${index}`}
            </button>
          </li>`;
    document
      .getElementById("topicTabs")
      .insertAdjacentHTML("beforeend", tabHTML);

    let questionsHTML = "";
    questions.forEach((q) => {
      questionsHTML += questionTemplate(q);
    });

    const contentHTML = `
          <div class="tab-pane fade ${
            index === 1 ? "show active" : ""
          }" id="${tabId}" role="tabpanel">
            <div class="mb-3">
              <label class="form-label">Nama aspek</label>
              <input type="text" class="form-control topic-name" value="${topicName}" 
                oninput="updateTabTitle(${index}, this.value)">
            </div>
            <div class="mb-3">
              <label class="form-label">Bobot aspek (%)</label>
              <input type="number" class="form-control topic-weight" value="${topicWeight}" min="0" max="100" 
                oninput="updateInfo()">
            </div>
            <h6>Pertanyaan</h6>
            <div class="questions-container">
              ${questionsHTML}
            </div>
            <button class="btn btn-outline-primary btn-sm mt-3" onclick="addQuestion('${tabId}')">+ Tambah Pertanyaan</button>
            <button class="btn btn-danger btn-sm mt-3" onclick="confirmRemoveTopic(${index})">Hapus aspek</button>
          </div>`;
    document
      .getElementById("topicContents")
      .insertAdjacentHTML("beforeend", contentHTML);
  }

  function questionTemplate(value = "") {
    return `
          <div class="input-group mb-2">
            <input type="text" class="form-control question-text" value="${value}" placeholder="Masukkan pertanyaan">
            <button class="btn btn-outline-danger" type="button" onclick="confirmRemoveQuestion(this)">Hapus</button>
          </div>`;
  }

  function updateTabTitle(index, value) {
    const btn = document.querySelector(`#tab-btn-${index} button`);
    btn.textContent = value || `aspek ${index}`;
  }

  function addQuestion(tabId) {
    const container = document.querySelector(
      `#${tabId} .questions-container`
    );
    container.insertAdjacentHTML("beforeend", questionTemplate());
  }

  function confirmRemoveQuestion(btn) {
    if (confirm("Hapus pertanyaan ini?")) {
      btn.parentElement.remove();
    }
  }

  function confirmRemoveTopic(index) {
    if (confirm("Yakin hapus aspek ini?")) {
      removeTopic(index);
    }
  }

  function removeTopic(index) {
    document.getElementById(`tab-btn-${index}`)?.remove();
    document.getElementById(`tab-${index}`)?.remove();
    updateInfo();
  }

  function setActiveTab(index) {
    const tabTrigger = new bootstrap.Tab(
      document.querySelector(`#tab-btn-${index} button`)
    );
    tabTrigger.show();
  }

  function updateInfo() {
    const weights = Array.from(
      document.querySelectorAll(".topic-weight")
    ).map((input) => Number(input.value) || 0);
    const totalWeight = weights.reduce((a, b) => a + b, 0);
    document.getElementById("infoTotalWeight").textContent =
      totalWeight + "%";
    const topicCountNow = document.querySelectorAll(
      "#topicContents .tab-pane"
    ).length;
    document.getElementById("infoTopicCount").textContent = topicCountNow;
  }

  function saveKPI() {
    if (!currentKey) {
      alert("Pilih divisi dan jabatan dulu!");
      return;
    }
    const topics = [];
    let totalWeight = 0;
    let valid = true;

    document
      .querySelectorAll("#topicContents .tab-pane")
      .forEach((tabPane) => {
        const topicName = tabPane.querySelector(".topic-name").value.trim();
        const topicWeight =
          Number(tabPane.querySelector(".topic-weight").value) || 0;
        const questions = [];
        tabPane.querySelectorAll(".question-text").forEach((q) => {
          if (q.value.trim()) questions.push(q.value.trim());
        });

        if (!topicName) {
          alert("Nama aspek tidak boleh kosong!");
          valid = false;
        }
        totalWeight += topicWeight;
        topics.push({
          topicName,
          topicWeight,
          questions
        });
      });

    if (!valid) return;
    if (totalWeight > 100) {
      alert("Total bobot tidak boleh lebih dari 100%");
      return;
    }

    kpiData[currentKey] = topics;
    localStorage.setItem("kpiData", JSON.stringify(kpiData));
    alert(`Data KPI untuk ${currentKey} tersimpan!`);
    updateInfo();
  }
</script>
@endsection