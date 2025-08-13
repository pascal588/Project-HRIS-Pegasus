@extends('penilai.template')

@section('title', 'Dashboard Penilai')

@section('content')
     <!-- Body: Body -->
        <div class="body d-flex py-lg-3 py-md-2">
            <div class="container-xxl">
                <div class="row clearfix">
                    <div class="col-md-12">
                        <div class="card border-0 mb-4 no-bg">
                            <div class="card-header py-3 px-0 d-sm-flex align-items-center  justify-content-between border-bottom">
                                <h3 class=" fw-bold flex-fill mb-0 mt-sm-0">Karyawan Divisi</h3>
                                <div class="dropdown">
                                    <button class="btn btn-primary dropdown-toggle mt-1  w-sm-100" type="button" id="dropdownMenuButton2" data-bs-toggle="dropdown" aria-expanded="false">
                                        Status
                                    </button>
                                    <ul class="dropdown-menu  dropdown-menu-end" aria-labelledby="dropdownMenuButton2">
                                    <li><a class="dropdown-item" href="#">All</a></li>
                                    <li><a class="dropdown-item" href="#">Aktif</a></li>
                                    <li><a class="dropdown-item" href="#">Tidak Aktif</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- Row End -->
                <div class="row g-3 row-cols-1 row-cols-sm-1 row-cols-md-1 row-cols-lg-2 row-cols-xl-2 row-cols-xxl-2 row-deck py-1 pb-4">
                    <div class="col">
                        <div class="card teacher-card">
                            <div class="card-body d-flex">
                                <div class="profile-av pe-xl-4 pe-md-2 pe-sm-4 pe-4 text-center w220">
                                    <img src="assets/images/lg/avatar3.jpg" alt="" class="avatar xl rounded-circle img-thumbnail shadow-sm">
                                    <div class="about-info d-flex align-items-center mt-3 justify-content-center">
                                        <div class="star me-2">
                                            <i class="icofont-star text-warning fs-4"></i>
                                            <span>Nilai KPI</span>
                                            <span class="">4.5</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="teacher-info border-start ps-xl-4 ps-md-3 ps-sm-4 ps-4 w-100">
                                    <h6  class="mb-0 mt-2  fw-bold d-block fs-6">Nama Karyawan</h6>
                                    <span class="light-info-bg py-1 px-2 rounded-1 d-inline-block fw-bold small-11 mb-0 mt-1">Status</span>
                                    <div class="video-setting-icon mt-3 pt-3 border-top">
                                        <div class=""><span class="fw-bold">No telp : </span>101902-2-</div>
                                        <div class="mt-1"><span class="fw-bold">Email : </span>luke.short@example.com</div>
                                        <div class="mt-1 mb-1"><span class="fw-bold">Divisi : </span>UI/UX Designer</div>
                                    </div>
                                    <a href="#" class="btn btn-dark btn-sm mt-1"><i class="icofont-brand-whatsapp me-2"></i>Hubungi karyawan</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>  
@endsection