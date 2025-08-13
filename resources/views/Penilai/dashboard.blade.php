@extends('penilai.template')

@section('title', 'Dashboard Penilai')

@section('content')
{{-- <link rel="stylesheet" href="{{ asset('css/my-task.style.min.css') }}"> --}}
    <!-- Body: Body -->
        <div class="body d-flex py-3">
            <div class="container-xxl">
                <div class="row clearfix g-3">
                    <div class="col-xl-8 col-lg-12 col-md-12 flex-column">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <div class="card bg-primary text-white p-3">
                                <h2>Hai Karyawan</h2>
                                <h6>Score Kamu Yang terbaru B+</h6>
                                </div>
                            </div>
                        <div class="col-md-6">
                              <div class="card bg-primary">
                                    <div class="card-body row">
                                        <div class="col">
                                            <h6 class="mt-3 mb-3 text-white fw-bold">Total Karyawan yang harus anda tegur</h6>
                                            <h1 class="mb-0 fw-bold text-white mb-2"><i class="icofont-users-alt-2 me-3"></i>70</h1>
                                            <span class="text-white">Segera Tegur!</span>
                                        </div>
                                    </div>
                                </div>
                            </div>  
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header py-3 d-flex justify-content-between bg-transparent border-bottom-0">
                                        <h6 class="mb-0 fw-bold ">Statistik KPI anda</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="ac-line-transparent" id="apex-emplyoeeAnalytics"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header py-3 d-flex justify-content-between bg-transparent border-bottom-0">
                                        <h6 class="mb-0 fw-bold ">KPI rata rata Divisi</h6>
                                    </div>
                                    <div class="card-body">
                                        <div id="hiringsources"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header py-3 d-flex justify-content-between bg-transparent border-bottom-0">
                                        <h6 class="mb-0 fw-bold ">Absensi Karyawan Divisi Anda</h6>
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
                                            <div class="col-md-6 col-sm-6">
                                                <div class="card">
                                                    <div class="card-body ">
                                                            <i class="icofont-stopwatch fs-3"></i>
                                                        <h6 class="mt-3 mb-0 fw-bold small-14">Terlambat</h6>
                                                        <span class="text-muted">17</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-sm-6">
                                                <div class="card">
                                                    <div class="card-body ">
                                                            <i class="icofont-ban fs-3"></i>
                                                        <h6 class="mt-3 mb-0 fw-bold small-14">Mangkir</h6>
                                                        <span class="text-muted">06</span>
                                                    </div>
                                                </div>
                                            </div>
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
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header py-3 d-flex justify-content-between bg-transparent border-bottom-0">
                                        <h6 class="mb-0 fw-bold ">Total Kayawan</h6>
                                        <h4 class="mb-0 fw-bold ">423</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="mt-3" id="apex-MainCategories"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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
                                        <div class="d-flex align-items-center flex-fill">
                                            <span class="avatar lg light-success-bg rounded-circle text-center d-flex align-items-center justify-content-center"><i class="icofont-users-alt-2 fs-5"></i></span>
                                            <div class="d-flex flex-column ps-3  flex-fill">
                                                <h6 class="mt-3 mb-0 fw-bold">Jumlah Karyawan yang belum anda nilai</h6>
                                                <span class="text-muted fw-bold">29</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 col-lg-12 col-xl-12">
                                <div class="card">
                                <div class="card-header py-3 d-flex justify-content-between bg-transparent border-bottom-0">
                                    <h6 class="mb-0 fw-bold ">List Karyawan</h6>
                                </div>
                                <div class="card-body">
                                    <div class="flex-grow-1">
                                        <div class="py-2 d-flex align-items-center border-bottom flex-wrap">
                                            <div class="d-flex align-items-center flex-fill">
                                                <img class="avatar lg rounded-circle img-thumbnail" src="assets/images/lg/avatar2.jpg" alt="profile">
                                                <div class="d-flex flex-column ps-3">
                                                    <h6 class="fw-bold mb-0 small-14">Nama Karyawan</h6>
                                                    <span class="text-muted">ID Karyawan</span>
                                                </div>
                                            </div>
                                            <a class="btn btn-outline-warning" href="#" role="button">Nilai</a>
                                        </div>
                                    </div>
                                </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="card light-danger-bg">
                            <div class="card-header py-3 d-flex justify-content-between bg-transparent border-bottom-0">
                                <h6 class="mb-0 fw-bold ">Top Perfrormers</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3 align-items-center">
                                    <div class="col-md-12 col-lg-4 col-xl-4 col-xxl-2">
                                        <p>Karyawan <span class="fw-bold">Terbaik </span> bulan Agustus di Divisi Anda</p>
                                    </div>
                                    <div class="col-md-12 col-lg-12 col-xl-12 col-xxl-10">
                                        <div class="row g-3 row-cols-2 row-cols-sm-3 row-cols-md-3 row-cols-lg-3 row-cols-xl-3 row-cols-xxl-6 row-deck top-perfomer">
                                            <div class="col">
                                                <div class="card shadow">
                                                    <div class="card-body text-center d-flex flex-column justify-content-center">
                                                        <img class="avatar lg rounded-circle img-thumbnail mx-auto" src="assets/images/lg/avatar2.jpg" alt="profile">
                                                        <h6 class="fw-bold my-2 small-14">Luke Short</h6>
                                                        <span class="text-muted mb-2">Nilai KPI</span>
                                                        <h4 class="fw-bold text-primary fs-3">80</h4>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="card light-danger-bg">
                            <div class="card-header py-3 d-flex justify-content-between bg-transparent border-bottom-0">
                                <h6 class="mb-0 fw-bold ">Bottom Perfrormers</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3 align-items-center">
                                    <div class="col-md-12 col-lg-4 col-xl-4 col-xxl-2">
                                        <p>Karyawan <span class="fw-bold">terburuk </span> bulan Agustus di Divisi Anda</p>
                                    </div>
                                    <div class="col-md-12 col-lg-12 col-xl-12 col-xxl-10">
                                        <div class="row g-3 row-cols-2 row-cols-sm-3 row-cols-md-3 row-cols-lg-3 row-cols-xl-3 row-cols-xxl-6 row-deck top-perfomer">
                                            <div class="col">
                                                <div class="card shadow">
                                                    <div class="card-body text-center d-flex flex-column justify-content-center">
                                                        <img class="avatar lg rounded-circle img-thumbnail mx-auto" src="assets/images/lg/avatar2.jpg" alt="profile">
                                                        <h6 class="fw-bold my-2 small-14">Luke Short</h6>
                                                        <span class="text-muted mb-2">Nilai KPI</span>
                                                        <h4 class="fw-bold text-primary fs-3">60</h4>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- Row End -->
            </div>
        </div> 
@endsection

@section('script')
<!-- Plugin Js-->
<script src="{{asset('assets/bundles/apexcharts.bundle.js')}}"></script>
@endsection