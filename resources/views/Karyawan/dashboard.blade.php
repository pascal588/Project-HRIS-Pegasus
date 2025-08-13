@extends('karyawan.template')

@section('title', 'Dashboard Karyawan')

@section('content')
    <div class="body d-flex py-3">
            <div class="container-xxl">
                <div class="row clearfix g-3">

                    <div class="col-xl-8 col-lg-12 col-md-12 flex-column">
                        <div class="row g-3">
                        <!-- Hai Karyawan -->
                        <div class="col-md-12">
                            <div class="card bg-primary text-white p-3">
                            <h2>Hai Karyawan</h2>
                            <h6>Score Kamu Yang terbaru B+</h6>
                            </div>
                        </div>
                    <!-- Absensi -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header py-3 d-flex justify-content-between bg-transparent border-bottom-0">
                                        <h6 class="mb-0 fw-bold ">Histori Absensi</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-2 row-deck">
                                            <div class="col-md-6 col-sm-6">
                                                <div class="card">
                                                    <div class="card-body ">
                                                        <i class="icofont-checked fs-3"></i>
                                                        <h6 class="mt-3 mb-0 fw-bold small-14">Hadir</h6>
                                                        <span class="text-muted">400</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- <div class="col-md-6 col-sm-6">
                                                <div class="card">
                                                    <div class="card-body ">
                                                            <i class="icofont-stopwatch fs-3"></i>
                                                        <h6 class="mt-3 mb-0 fw-bold small-14">Terlambat</h6>
                                                        <span class="text-muted">17</span>
                                                    </div>
                                                </div>
                                            </div> -->
                                            <!-- <div class="col-md-6 col-sm-6">
                                                <div class="card">
                                                    <div class="card-body ">
                                                            <i class="icofont-ban fs-3"></i>
                                                        <h6 class="mt-3 mb-0 fw-bold small-14">Mangkir</h6>
                                                        <span class="text-muted">06</span>
                                                    </div>
                                                </div>
                                            </div> -->
                                            <div class="col-md-6 col-sm-6">
                                                <div class="card">
                                                    <div class="card-body ">
                                                        <i class="icofont-beach-bed fs-3"></i>
                                                        <h6 class="mt-3 mb-0 fw-bold small-14">Izin/Cuti</h6>
                                                        <span class="text-muted">14</span> 
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <!-- Top Karyawan -->
                            <div class="col-md-6">
                            <div class="card p-3 text-center">
                                <div class="d-flex justify-content-between">
                                <h6>Top Karyawan</h6>
                                <h6>Agustus</h6>
                                </div>
                                <div class="row mt-3">
                                <!-- Karyawan 1 -->
                                <div class="col text-center">
                                    <img class="avatar lg rounded-circle img-thumbnail mx-auto" src="assets/images/lg/avatar8.jpg" alt="profile">
                                    <h6 class="fw-bold my-2 small-14">Paul Rees</h6>
                                    <span class="text-muted mb-2">@Rees</span>
                                    <h4 class="fw-bold text-primary fs-5">77%</h4>
                                </div>
                                <!-- Karyawan 2 -->
                                <div class="col text-center">
                                    <img class="avatar lg rounded-circle img-thumbnail mx-auto" src="{{ asset('assets/images/lg/avatar9.jpg') }}" alt="profile">
                                    <h6 class="fw-bold my-2 small-14">John Doe</h6>
                                    <span class="text-muted mb-2">@John</span>
                                    <h4 class="fw-bold text-primary fs-5">75%</h4>
                                </div>
                                <!-- Karyawan 3 -->
                                <div class="col text-center">
                                    <img class="avatar lg rounded-circle img-thumbnail mx-auto" src="assets/images/lg/avatar10.jpg" alt="profile">
                                    <h6 class="fw-bold my-2 small-14">Jane Smith</h6>
                                    <span class="text-muted mb-2">@Jane</span>
                                    <h4 class="fw-bold text-primary fs-5">73%</h4>
                                </div>
                                </div>
                            </div>
                            </div>
                        </div>
                    </div>

                    <!-- Nilai KPI -->
                    <div class="col-xl-4 col-lg-12 col-md-12">
                        <div class="row g-3 row-deck">
                            <div class="col-md-6 col-lg-6 col-xl-12">
                                <div class="card bg-primary">
                                    <div class="card-body row">
                                        <div class="col">
                                            <span class="avatar lg bg-white rounded-circle text-center d-flex align-items-center justify-content-center"><i class="icofont-file-text fs-5"></i></span>
                                            <h6 class="mt-3 mb-0 text-white fw-bold">Nilai KPI terbaru</h6>
                                            <h1 class="mb-0 fw-bold text-white">70,9</h1>
                                            <a href="#"><span class="text-white">Periksa Nilai</span></a>
                                        </div>
                                        <div class="col">
                                            <img class="img-fluid" src="assets/images/interview.svg" alt="interview">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-6 col-xl-12  flex-column">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center flex-fill p-3">
                                            <span class="avatar lg light-success-bg rounded-circle text-center d-flex align-items-center justify-content-center"><i class="icofont-file-text fs-5"></i></span>
                                            <div class="d-flex flex-column ps-3  flex-fill">
                                                <h6 class="fw-bold mb-0 fs-4">80</h6>
                                                <span class="text-muted">Score KPI sebelumnya</span>
                                            </div>
                                            <i class="icofont-chart-line fs-3 text-muted"></i>
                                        </div>
                                    </div>
                                </div>                             
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header py-3 d-flex justify-content-between bg-transparent border-bottom-0">
                                <h6 class="mb-0 fw-bold ">Employees Info</h6>
                            </div>
                                <div class="card-body">
                                        <div class="ac-line-transparent" id="apex-emplyoeeAnalytics"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- Row End -->
            </div>
        </div>

@endsection
@section('script')
    <script src="{{ asset('assets/bundles/apexcharts.bundle.js') }}"></script>
@endsection