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
                                <tbody>
                                    <tr>
                                        <td><span class="fw-bold">1</span></td>
                                        <td><span class="fw-bold ms-1">ui</span></td>
                                        <td><span class="fw-bold ms-1">Divisi 1</span></td>
                                        <td><span class="fw-bold ms-1">Belum Dinilai</span></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#formnilai">
                                                    <i class="icofont-edit text-success"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><span class="fw-bold">1</span></td>
                                        <td><span class="fw-bold ms-1">ui</span></td>
                                        <td><span class="fw-bold ms-1">Divisi 1</span></td>
                                        <td><span class="fw-bold ms-1">Belum Dinilai</span></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#formnilai">
                                                    <i class="icofont-edit text-success"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><span class="fw-bold">1</span></td>
                                        <td><span class="fw-bold ms-1">ui</span></td>
                                        <td><span class="fw-bold ms-1">Divisi 1</span></td>
                                        <td><span class="fw-bold ms-1">Belum Dinilai</span></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#formnilai">
                                                    <i class="icofont-edit text-success"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><span class="fw-bold">1</span></td>
                                        <td><span class="fw-bold ms-1">ui</span></td>
                                        <td><span class="fw-bold ms-1">Divisi 1</span></td>
                                        <td><span class="fw-bold ms-1">Belum Dinilai</span></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#formnilai">
                                                    <i class="icofont-edit text-success"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><span class="fw-bold">1</span></td>
                                        <td><span class="fw-bold ms-1">ui</span></td>
                                        <td><span class="fw-bold ms-1">Divisi 1</span></td>
                                        <td><span class="fw-bold ms-1">Belum Dinilai</span></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#formnilai">
                                                    <i class="icofont-edit text-success"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><span class="fw-bold">1</span></td>
                                        <td><span class="fw-bold ms-1">ui</span></td>
                                        <td><span class="fw-bold ms-1">Divisi 1</span></td>
                                        <td><span class="fw-bold ms-1">Belum Dinilai</span></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#formnilai">
                                                    <i class="icofont-edit text-success"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><span class="fw-bold">1</span></td>
                                        <td><span class="fw-bold ms-1">ui</span></td>
                                        <td><span class="fw-bold ms-1">Divisi 1</span></td>
                                        <td><span class="fw-bold ms-1">Belum Dinilai</span></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#formnilai">
                                                    <i class="icofont-edit text-success"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><span class="fw-bold">1</span></td>
                                        <td><span class="fw-bold ms-1">ui</span></td>
                                        <td><span class="fw-bold ms-1">Divisi 1</span></td>
                                        <td><span class="fw-bold ms-1">Belum Dinilai</span></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#formnilai">
                                                    <i class="icofont-edit text-success"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><span class="fw-bold">1</span></td>
                                        <td><span class="fw-bold ms-1">ui</span></td>
                                        <td><span class="fw-bold ms-1">Divisi 1</span></td>
                                        <td><span class="fw-bold ms-1">Belum Dinilai</span></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#formnilai">
                                                    <i class="icofont-edit text-success"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><span class="fw-bold">1</span></td>
                                        <td><span class="fw-bold ms-1">ui</span></td>
                                        <td><span class="fw-bold ms-1">Divisi 1</span></td>
                                        <td><span class="fw-bold ms-1">Belum Dinilai</span></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#formnilai">
                                                    <i class="icofont-edit text-success"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><span class="fw-bold">1</span></td>
                                        <td><span class="fw-bold ms-1">ui</span></td>
                                        <td><span class="fw-bold ms-1">Divisi 1</span></td>
                                        <td><span class="fw-bold ms-1">Belum Dinilai</span></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#formnilai">
                                                    <i class="icofont-edit text-success"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><span class="fw-bold">1</span></td>
                                        <td><span class="fw-bold ms-1">ui</span></td>
                                        <td><span class="fw-bold ms-1">Divisi 1</span></td>
                                        <td><span class="fw-bold ms-1">Belum Dinilai</span></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#formnilai">
                                                    <i class="icofont-edit text-success"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><span class="fw-bold">1</span></td>
                                        <td><span class="fw-bold ms-1">ui</span></td>
                                        <td><span class="fw-bold ms-1">Divisi 1</span></td>
                                        <td><span class="fw-bold ms-1">Belum Dinilai</span></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#formnilai">
                                                    <i class="icofont-edit text-success"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
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
                            <div class="form-group mb-2">
                                <label class="fw-bold">Poin Kehadiran</label>
                                <div class="form-control">-</div>
                            </div>
                            <div class="form-group mb-2">
                                <label class="fw-bold">Poin Disiplin</label>
                                <div class="form-control">-</div>
                            </div>
                            <div class="form-group mb-2">
                                <label class="fw-bold">Poin Kompetensi Teknis</label>
                                <div class="form-control">-</div>
                            </div>
                            <div class="form-group mb-2">
                                <label class="fw-bold">Poin Kompetensi Umum</label>
                                <div class="form-control">-</div>
                            </div>
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
          box-shadow: 0 4px 10px rgba(0,0,0,0.2);
          transform: scale(1.1);
        }
        .step-btn:hover {
          transform: scale(1.15);
          cursor: pointer;
        }
      </style>

      <!-- Body -->
      <div class="modal-body">

        <!-- Step 1 -->
        <div class="wizard-step" id="step1">
        <h6 class="mb-3">Topik: Disiplin Kerja <small class="text-muted">(Bobot: 20%)</small></h6>
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
            <tr>
            <td>1</td>
            <td class="text-start">Kehadiran tepat waktu</td>
            <td><input type="radio" name="disiplin_q1" value="1"></td>
            <td><input type="radio" name="disiplin_q1" value="2"></td>
            <td><input type="radio" name="disiplin_q1" value="3"></td>
            <td><input type="radio" name="disiplin_q1" value="4"></td>
            </tr>
            <tr>
            <td>2</td>
            <td class="text-start">Mematuhi aturan perusahaan</td>
            <td><input type="radio" name="disiplin_q2" value="1"></td>
            <td><input type="radio" name="disiplin_q2" value="2"></td>
            <td><input type="radio" name="disiplin_q2" value="3"></td>
            <td><input type="radio" name="disiplin_q2" value="4"></td>
            </tr>
        </tbody>
        </table>
        </div>


        <!-- Step 2 -->
        <div class="wizard-step" id="step2">
        <h6 class="mb-3">Topik: Disiplin Kerja <small class="text-muted">(Bobot: 20%)</small></h6>
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
            <tr>
            <td>1</td>
            <td class="text-start">Kehadiran tepat waktu</td>
            <td><input type="radio" name="disiplin_q1" value="1"></td>
            <td><input type="radio" name="disiplin_q1" value="2"></td>
            <td><input type="radio" name="disiplin_q1" value="3"></td>
            <td><input type="radio" name="disiplin_q1" value="4"></td>
            </tr>
            <tr>
            <td>2</td>
            <td class="text-start">Mematuhi aturan perusahaan</td>
            <td><input type="radio" name="disiplin_q2" value="1"></td>
            <td><input type="radio" name="disiplin_q2" value="2"></td>
            <td><input type="radio" name="disiplin_q2" value="3"></td>
            <td><input type="radio" name="disiplin_q2" value="4"></td>
            </tr>
        </tbody>
        </table>
        </div>

        <!-- Step 3 -->
        <div class="wizard-step" id="step3">
        <h6 class="mb-3">Topik: Disiplin Kerja <small class="text-muted">(Bobot: 20%)</small></h6>
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
            <tr>
            <td>1</td>
            <td class="text-start">Kehadiran tepat waktu</td>
            <td><input type="radio" name="disiplin_q1" value="1"></td>
            <td><input type="radio" name="disiplin_q1" value="2"></td>
            <td><input type="radio" name="disiplin_q1" value="3"></td>
            <td><input type="radio" name="disiplin_q1" value="4"></td>
            </tr>
            <tr>
            <td>2</td>
            <td class="text-start">Mematuhi aturan perusahaan</td>
            <td><input type="radio" name="disiplin_q2" value="1"></td>
            <td><input type="radio" name="disiplin_q2" value="2"></td>
            <td><input type="radio" name="disiplin_q2" value="3"></td>
            <td><input type="radio" name="disiplin_q2" value="4"></td>
            </tr>
        </tbody>
        </table>
        </div>

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
$(document).ready(function() {
    $('#myProjectTable')
    .addClass('nowrap')
    .DataTable({
        responsive: true,
        pageLength: 5,
        lengthMenu: [5, 10, 25, 50],
        columnDefs: [
            { targets: -1, searchable: false, className: 'dt-body-right all' }, 
            { responsivePriority: 1, targets: 0 }, // # (selalu tampil)
            { responsivePriority: 2, targets: 1 }, // Nama (prioritas tinggi)
            { responsivePriority: 3, targets: 3 }, // Status KPI
            { responsivePriority: 10001, targets: 2 } // Divisi (paling rendah → ilang di HP)
        ]
    });
});
</script>



<script>
document.addEventListener("DOMContentLoaded", function () {
    let selectedEmployee = {}; // simpan data karyawan yang dipilih

    // Klik tombol di tabel → isi form kanan
    document.querySelectorAll("#myProjectTable .btn-outline-secondary").forEach(function (button) {
        button.addEventListener("click", function () {
            let row = this.closest("tr");
            selectedEmployee.nama = row.cells[1].innerText.trim();
            selectedEmployee.divisi = row.cells[2].innerText.trim();
            selectedEmployee.status = row.cells[3].innerText.trim();

            // Isi form kanan
            document.querySelector(".card-body h6").innerText = selectedEmployee.nama;
            document.querySelector(".card-body small").innerText = selectedEmployee.divisi;
        });
    });

    // Klik tombol Nilai → buka modal wizard
    document.getElementById("btnNilai").addEventListener("click", function () {
        if (!selectedEmployee.nama) {
            alert("Pilih karyawan dulu!");
            return;
        }
        let modal = new bootstrap.Modal(document.getElementById("modalWizard"));
        modal.show();
    }); 

    // Wizard stepper
    let currentStep = 1;
    const totalSteps = 3;

    const stepButtons = document.querySelectorAll(".step-btn");
    const steps = document.querySelectorAll(".wizard-step");

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

    // Pertama kali buka modal → tampilkan step 1
    showStep(currentStep);
});
</script>
@endsection

