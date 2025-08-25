@extends('template.template')

@section('title', 'Dashboard Penilai')

@section('content')
<!-- Body: Body -->
<div class="body d-flex py-lg-3 py-md-2">
    <div class="container-xxl">

        <!-- Header -->
        <div class="row clearfix">
            <div class="col-md-12">
                <div class="card border-0 mb-4 no-bg">
                    <div class="card-header py-3 px-0 d-sm-flex align-items-center justify-content-between border-bottom">
                        <h3 class="fw-bold flex-fill mb-0 mt-sm-0">Karyawan Divisi</h3>

                        <div class="d-flex align-items-center gap-2">
                            <!-- Form Pencarian -->
                            <form action="" method="GET" class="d-flex">
                                <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari Karyawan...">
                                <button type="submit" class="btn btn-sm btn-secondary ms-2">
                                    <i class="icofont-search-1"></i>
                                </button>
                            </form>

                            <!-- Dropdown Status -->
                            <div class="dropdown">
                                <button class="btn btn-primary dropdown-toggle mt-1 w-sm-100" type="button" id="dropdownMenuButton2" data-bs-toggle="dropdown" aria-expanded="false">
                                    Status
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton2">
                                    <li><a class="dropdown-item" href="#">Semua</a></li>
                                    <li><a class="dropdown-item" href="#">Aktif</a></li>
                                    <li><a class="dropdown-item" href="#">Tidak Aktif</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Header End -->

        <!-- Card List Karyawan -->
        <div class="row g-3 row-cols-1 row-cols-lg-2 row-deck py-1 pb-4">
            <div class="col">
                <div class="card teacher-card">
                    <div class="card-body d-flex">
                        <!-- Profile Avatar -->
                        <div class="profile-av pe-xl-4 pe-md-2 pe-sm-4 pe-4 text-center w220">
                            <img src="{{ asset('assets/images/xs/avatar2.jpg') }}" alt="Avatar Karyawan" class="avatar xl rounded-circle img-thumbnail shadow-sm">
                            <div class="about-info d-flex align-items-center mt-3 justify-content-center">
                                <div class="star me-2">
                                    <i class="icofont-star text-warning fs-4"></i>
                                    <span>Nilai KPI</span>
                                    <span class="fw-bold">4.5</span>
                                </div>
                            </div>
                        </div>

                        <!-- Info Karyawan -->
                        <div class="teacher-info border-start ps-xl-4 ps-md-3 ps-sm-4 ps-4 w-100">
                            <h6 class="mb-0 mt-2 fw-bold fs-6">Nama Karyawan</h6>
                            <span class="light-info-bg py-1 px-2 rounded-1 d-inline-block fw-bold small-11 mb-0 mt-1">Aktif</span>

                            <div class="video-setting-icon mt-3 pt-3 border-top">
                                <div class="mb-1"><span class="fw-bold">No. Telp :</span> 0812-3456-7890</div>
                                <div class="mb-1"><span class="fw-bold">Email :</span> luke.short@example.com</div>
                                <div class="mb-1"><span class="fw-bold">Divisi :</span> UI/UX Designer</div>
                            </div>

                            <a href="#" class="btn btn-dark btn-sm mt-2">
                                <i class="icofont-brand-whatsapp me-2"></i>Hubungi Karyawan
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Card List Karyawan End -->

    </div>
</div>
@endsection
