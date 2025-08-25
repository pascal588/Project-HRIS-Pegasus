@extends('template.template')

@section('title', 'Dashboard HR')

@section('content')
<style>
  /* Compact layout with smaller cards */
  body {
    font-size: 0.875rem;
  }

  .card {
    margin-bottom: 10px;
    border-radius: 10px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    height: 100%;
  }

  .card-header {
    padding: 10px 15px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  }

  .card-header h6 {
    font-size: 0.9rem;
    font-weight: 600;
    margin: 0;
  }

  .card-body {
    padding: 15px;
  }

  /* Compact chart containers */
  #apex-emplyoeeAnalytics,
  #apex-MainCategories,
  #hiringsources {
    min-height: 200px;
  }

  /* Employee list cards */
  .employee-list-card .card-body {
    padding: 0;
    max-height: 200px;
    overflow-y: auto;
  }

  /* Compact stat cards */
  .stat-card {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 15px 5px;
  }

  .stat-card img {
    width: 40px;
    height: 40px;
    margin-bottom: 10px;
    object-fit: contain;
  }

  .stat-card h4 {
    font-weight: 700;
    margin: 5px 0;
    font-size: 1.25rem;
  }

  .stat-card span {
    font-size: 0.75rem;
  }

  /* Compact employee list items */
  .employee-item {
    display: flex;
    align-items: center;
    padding: 8px 12px;
    border-bottom: 1px solid #eee;
    transition: background-color 0.2s;
  }

  .employee-item:last-child {
    border-bottom: none;
  }

  .employee-item:hover {
    background-color: #f8f9fa;
  }

  .employee-item .avatar {
    width: 32px;
    height: 32px;
    margin-right: 10px;
  }

  .employee-item .employee-info h6 {
    font-size: 0.8rem;
    margin-bottom: 2px;
  }

  .employee-item .employee-info span {
    font-size: 0.7rem;
  }

  .employee-item .employee-time {
    color: #6c757d;
    font-size: 0.7rem;
  }

  /* Default (desktop & tablet) */
  .card-header h6 {
    font-size: 1rem;
  }

  .stat-card h4 {
    font-size: 1.25rem;
  }

  .stat-card img {
    width: 40px;
    height: 40px;
  }

  /* Untuk HP agar lebih kecil */
  @media (max-width: 576px) {
    .card-header h6 {
      font-size: 0.85rem;
    }

    .stat-card h4 {
      font-size: 1rem;
    }

    .stat-card img {
      width: 28px;
      height: 28px;
    }

    .employee-item {
      padding: 6px 8px;
    }

    .best-card {
      order: 1;
    }

    .worst-card {
      order: 2;
    }
  }
</style>

<div class="container-fluid px-2">
  <div class="container-fluid px-2">
    <div class="row g-2">
      <!-- KPI Divisi -->
      <div class="col-12 col-lg-8">
        <div class="card h-100">
          <div class="card-header py-2 bg-transparent border-bottom-0">
            <h6 class="mb-0 fw-bold">KPI Divisi</h6>
          </div>
          <div class="card-body p-2">
            <div
              class="ac-line-transparent"
              id="apex-stacked-area"></div>
          </div>
        </div>
      </div>

      <!-- Karyawan Terbaik -->
      <div class="col-12 col-lg-4 best-card">
        <div class="card h-100 employee-list-card">
          <div class="card-header py-2 bg-transparent border-bottom-0">
            <h6 class="mb-0 fw-bold">Karyawan Terbaik</h6>
          </div>
          <div class="card-body p-0">
            <div class="flex-grow-1">
              <div class="employee-item">
                <div class="d-flex align-items-center flex-fill">
                  <img
                    class="avatar rounded-circle img-thumbnail"
                    src="assets/images/lg/avatar2.jpg"
                    alt="profile" />
                  <div class="employee-info">
                    <h6 class="fw-bold mb-0 small-14">
                      Natalie Gibson
                    </h6>
                    <span class="text-muted">Ui/UX Designer</span>
                  </div>
                </div>
                <div class="employee-time">
                  <i class="icofont-clock-time"></i> 1.30
                </div>
              </div>

              <div class="employee-item">
                <div class="d-flex align-items-center flex-fill">
                  <img
                    class="avatar rounded-circle img-thumbnail"
                    src="assets/images/lg/avatar3.jpg"
                    alt="profile" />
                  <div class="employee-info">
                    <h6 class="fw-bold mb-0 small-14">Youn Bel</h6>
                    <span class="text-muted">Unity 3d</span>
                  </div>
                </div>
                <div class="employee-time">
                  <i class="icofont-clock-time"></i> 7.00
                </div>
              </div>

              <div class="employee-item">
                <div class="d-flex align-items-center flex-fill">
                  <img
                    class="avatar rounded-circle img-thumbnail"
                    src="assets/images/lg/avatar2.jpg"
                    alt="profile" />
                  <div class="employee-info">
                    <h6 class="fw-bold mb-0 small-14">Gibson Butler</h6>
                    <span class="text-muted">Networking</span>
                  </div>
                </div>
                <div class="employee-time">
                  <i class="icofont-clock-time"></i> 8.00
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Stat Cards -->
      <div class="col-6 col-md-3 col-lg-2">
        <div class="card h-100">
          <div class="card-header py-2 bg-transparent border-bottom-0">
            <h6 class="mb-0 fw-bold text-center">Karyawan</h6>
          </div>
          <div class="card-body stat-card">
            <img src="user.png" alt="User Icon" />
            <h4 class="fw-bold">120</h4>
            <span class="text-muted">Total</span>
          </div>
        </div>
      </div>

      <!-- divisi -->
      <div class="col-6 col-md-3 col-lg-2">
        <div class="card h-100">
          <div class="card-header py-2 bg-transparent border-bottom-0">
            <h6 class="mb-0 fw-bold text-center">Divisi</h6>
          </div>
          <div class="card-body stat-card">
            <img src="employment.png" alt="Divisi Icon" />
            <h4 class="fw-bold">8</h4>
            <span class="text-muted">Total</span>
          </div>
        </div>
      </div>

      <!-- Gender Chart -->
      <div class="col-12 col-md-6 col-lg-4">
        <div class="card h-100">
          <div class="card-header py-2 bg-transparent border-bottom-0">
            <h6 class="mb-0 fw-bold text-center">Gender Karyawan</h6>
          </div>
          <div class="card-body p-2">
            <div id="apex-MainCategories"></div>
          </div>
        </div>
      </div>

      <!-- Karyawan Bottom -->
      <div class="col-12 col-lg-4 worst-card">
        <div class="card h-100 employee-list-card">
          <div class="card-header py-2 bg-transparent border-bottom-0">
            <h6 class="mb-0 fw-bold">Perlu Perhatian</h6>
          </div>
          <div class="card-body p-0">
            <div class="flex-grow-1">
              <div class="employee-item">
                <div class="d-flex align-items-center flex-fill">
                  <img
                    class="avatar rounded-circle img-thumbnail"
                    src="assets/images/lg/avatar2.jpg"
                    alt="profile" />
                  <div class="employee-info">
                    <h6 class="fw-bold mb-0 small-14">
                      Natalie Gibson
                    </h6>
                    <span class="text-muted">Ui/UX Designer</span>
                  </div>
                </div>
                <div class="employee-time">
                  <i class="icofont-clock-time"></i> 1.30
                </div>
              </div>

              <div class="employee-item">
                <div class="d-flex align-items-center flex-fill">
                  <img
                    class="avatar rounded-circle img-thumbnail"
                    src="assets/images/lg/avatar3.jpg"
                    alt="profile" />
                  <div class="employee-info">
                    <h6 class="fw-bold mb-0 small-14">Youn Bel</h6>
                    <span class="text-muted">Unity 3d</span>
                  </div>
                </div>
                <div class="employee-time">
                  <i class="icofont-clock-time"></i> 7.00
                </div>
              </div>

              <div class="employee-item">
                <div class="d-flex align-items-center flex-fill">
                  <img
                    class="avatar rounded-circle img-thumbnail"
                    src="assets/images/lg/avatar2.jpg"
                    alt="profile" />
                  <div class="employee-info">
                    <h6 class="fw-bold mb-0 small-14">Gibson Butler</h6>
                    <span class="text-muted">Networking</span>
                  </div>
                </div>
                <div class="employee-time">
                  <i class="icofont-clock-time"></i> 8.00
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Distribusi Karyawan -->
      <div class="col-12">
        <div class="card">
          <div class="card-header py-2 bg-transparent border-bottom-0">
            <h6 class="mb-0 fw-bold">Distribusi Karyawan per Divisi</h6>
          </div>
          <div class="card-body p-2">
            <div id="hiringsources"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('script')
<script src="{{asset('assets/bundles/apexcharts.bundle.js')}}"></script>
@endsection