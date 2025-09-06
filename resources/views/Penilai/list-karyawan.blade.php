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
                        <h3 class="fw-bold flex-fill mb-0 mt-sm-0">Karyawan Divisi {{ $divisionName }}</h3>

                        <div class="d-flex align-items-center gap-2">
                            <!-- Form Pencarian -->
                            <form id="searchForm" class="d-flex">
                                <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Cari Karyawan..." value="{{ request('search') }}">
                                <button type="button" id="searchBtn" class="btn btn-sm btn-secondary ms-2">
                                    <i class="icofont-search-1"></i>
                                </button>
                            </form>

                            <!-- Dropdown Status -->
                            <div class="dropdown">
                                <button class="btn btn-primary dropdown-toggle mt-1 w-sm-100" type="button" id="dropdownMenuButton2" data-bs-toggle="dropdown" aria-expanded="false">
                                    Status
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton2">
                                    <li><a class="dropdown-item filter-status" href="#" data-status="all">Semua</a></li>
                                    <li><a class="dropdown-item filter-status" href="#" data-status="Aktif">Aktif</a></li>
                                    <li><a class="dropdown-item filter-status" href="#" data-status="Non-Aktif">Tidak Aktif</a></li>
                                    <li><a class="dropdown-item filter-status" href="#" data-status="Cuti">Cuti</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Header End -->

        <!-- Card List Karyawan -->
        <div id="employeeList" class="row g-3 row-cols-1 row-cols-lg-2 row-deck py-1 pb-4">
            @foreach($employees as $employee)
                @php
                    // Tentukan kelas badge berdasarkan status
                    $statusClass = match($employee->status) {
                        'Aktif' => 'light-success-bg',
                        'Non-Aktif' => 'light-danger-bg', 
                        'Cuti' => 'light-warning-bg',
                        default => 'light-info-bg'
                    };
                    
                    // Dapatkan daftar jabatan
                    $roles = $employee->roles->pluck('nama_jabatan')->implode(', ');
                    
                    // Tentukan path foto - PERBAIKAN DI SINI
                       $fotoPath = null;
    
                        if ($employee->foto) {
                            // Cek apakah foto sudah mengandung full path atau hanya filename
                            if (strpos($employee->foto, 'http') === 0) {
                                $fotoPath = $employee->foto;
                            } else if (strpos($employee->foto, 'storage/') === 0) {
                                $fotoPath = asset($employee->foto);
                            } else if (strpos($employee->foto, 'profile-photos/') === 0) {
                                $fotoPath = asset('storage/' . $employee->foto);
                            } else {
                                $fotoPath = asset('storage/profile-photos/' . $employee->foto);
                            }
                        } else {
                            $fotoPath = $employee->gender === 'Wanita' 
                                ? asset('assets/images/xs/avatar1.jpg') 
                                : asset('assets/images/xs/avatar2.jpg');
                        }
                @endphp
                
                <div class="col employee-card" data-status="{{ $employee->status }}" data-name="{{ strtolower($employee->nama) }}" data-roles="{{ strtolower($roles) }}">
                    <div class="card teacher-card">
                        <div class="card-body d-flex">
                            <!-- Profile Avatar -->
                            <div class="profile-av pe-xl-4 pe-md-2 pe-sm-4 pe-4 text-center w220">
                                <img src="{{ $fotoPath }}" alt="Avatar {{ $employee->nama }}" 
                                     class="avatar xl rounded-circle img-thumbnail shadow-sm"
                                     onerror="this.onerror=null; this.src='{{ $employee->gender === 'Wanita' ? asset('assets/images/xs/avatar1.jpg') : asset('assets/images/xs/avatar2.jpg') }}'">
                                <div class="about-info d-flex align-items-center mt-3 justify-content-center">
                                    <div class="star me-2">
                                        <i class="icofont-star text-warning fs-4"></i>
                                        <span>Nilai KPI</span>
                                        <span class="fw-bold">-</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Info Karyawan -->
                            <div class="teacher-info border-start ps-xl-4 ps-md-3 ps-sm-4 ps-4 w-100">
                                <h6 class="mb-0 mt-2 fw-bold fs-6">{{ $employee->nama }}</h6>
                                <span class="{{ $statusClass }} py-1 px-2 rounded-1 d-inline-block fw-bold small-11 mb-0 mt-1">{{ $employee->status }}</span>

                                <div class="video-setting-icon mt-3 pt-3 border-top">
                                    <div class="mb-1"><span class="fw-bold">No. Telp :</span> {{ $employee->no_telp }}</div>
                                    <div class="mb-1"><span class="fw-bold">Email :</span> {{ $employee->user->email ?? '-' }}</div>
                                    <div class="mb-1"><span class="fw-bold">Jabatan :</span> {{ $roles }}</div>
                                    <!-- Debug info - bisa diaktifkan jika masih ada masalah -->
                                    {{--
                                    <div class="mb-1 small text-muted">
                                        <span class="fw-bold">Foto :</span> {{ $employee->foto ?? 'Tidak ada' }}
                                        <br>
                                        <span class="fw-bold">Path :</span> {{ $fotoPath }}
                                        <br>
                                        <span class="fw-bold">File exists:</span> 
                                        {{ $employee->foto && Storage::exists('public/profile-photos/' . $employee->foto) ? 'Yes' : 'No' }}
                                    </div>
                                    --}}
                                </div>

                                <a href="https://wa.me/{{ $employee->no_telp }}?text=Halo {{ urlencode($employee->nama) }}" target="_blank" class="btn btn-dark btn-sm mt-2">
                                    <i class="icofont-brand-whatsapp me-2"></i>Hubungi Karyawan
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <!-- Card List Karyawan End -->

        <!-- No Results Message -->
        <div id="noResults" class="text-center py-4 d-none">
            <div class="alert alert-info">Tidak ada karyawan yang ditemukan dengan filter yang dipilih.</div>
        </div>

    </div>
</div>

<!-- JavaScript untuk Filter -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const employeeCards = document.querySelectorAll('.employee-card');
    const searchInput = document.getElementById('searchInput');
    const searchBtn = document.getElementById('searchBtn');
    const noResults = document.getElementById('noResults');
    let currentStatusFilter = 'all';

    // Fungsi untuk memfilter karyawan
    function filterEmployees() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        let hasVisibleItems = false;

        employeeCards.forEach(card => {
            const status = card.getAttribute('data-status');
            const name = card.getAttribute('data-name');
            const roles = card.getAttribute('data-roles');
            
            // Filter berdasarkan status
            const statusMatch = currentStatusFilter === 'all' || status === currentStatusFilter;
            
            // Filter berdasarkan pencarian
            const searchMatch = searchTerm === '' || 
                               name.includes(searchTerm) || 
                               roles.includes(searchTerm);
            
            // Tampilkan atau sembunyikan card
            if (statusMatch && searchMatch) {
                card.style.display = 'block';
                hasVisibleItems = true;
            } else {
                card.style.display = 'none';
            }
        });

        // Tampilkan pesan jika tidak ada hasil
        if (hasVisibleItems) {
            noResults.classList.add('d-none');
        } else {
            noResults.classList.remove('d-none');
        }
    }

    // Event listeners untuk pencarian
    searchBtn.addEventListener('click', filterEmployees);
    searchInput.addEventListener('keyup', filterEmployees);

    // Event listeners untuk filter status
    document.querySelectorAll('.filter-status').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            currentStatusFilter = this.getAttribute('data-status');
            document.getElementById('dropdownMenuButton2').textContent = 
                currentStatusFilter === 'all' ? 'Status' : this.textContent;
            filterEmployees();
        });
    });

    // Inisialisasi filter
    filterEmployees();
});
</script>
@endsection