<!doctype html>
<html class="no-js" lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>@yield('title', 'Website HR')</title>
    <link rel="icon" href="favicon.ico" type="image/x-icon"> <!-- Favicon-->
    <!-- project css file  -->
    <link rel="stylesheet" href="{{ asset('assets/css/my-task.style.min.css') }}">
</head>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<body  data-mytask="theme-indigo">
<div id="mytask-layout">
    
    <!-- sidebar -->
    @php
    // Perbaiki logic untuk mendapatkan role
    $user = Auth::user();
    $isHead = false;
    $isHR = false;
    $isEmployee = false;
    
    if ($user && $user->employee && $user->employee->roles) {
        foreach ($user->employee->roles as $role) {
            if ($role->nama_jabatan === 'Kepala-divisi') {
                $isHead = true;
            } elseif ($role->nama_jabatan === 'HR') {
                $isHR = true;
            } elseif ($role->nama_jabatan === 'Karyawan') {
                $isEmployee = true;
            }
        }
    }
    
    // Tentukan role utama (prioritas: HR > Kepala-divisi > Karyawan)
    if ($isHR) {
        $mainRole = 'HR';
    } elseif ($isHead) {
        $mainRole = 'Kepala-divisi';
    } elseif ($isEmployee) {
        $mainRole = 'Karyawan';
    } else {
        $mainRole = '';
    }
    @endphp

    @if($mainRole === 'Kepala-divisi')
        @include('template.sidebar-penilai')
    @elseif($mainRole === 'HR')
        @include('template.sidebar-hr')
    @elseif($mainRole === 'Karyawan')
        @include('template.sidebar-karyawan')
    @else
        @include('template.sidebar-karyawan') <!-- Fallback default -->
    @endif
                
    <!-- main body area -->
    <div class="main px-lg-4 px-md-4">

        <!-- Body: Header -->
        <div class="header">
            <nav class="navbar py-4">
                <div class="container-xxl">
    
                    <!-- header rightbar icon -->
                    <div class="h-right d-flex align-items-center mr-5 mr-lg-0 order-1">
                        <div class="d-flex">
                        </div>
                        <div class="dropdown user-profile ml-2 ml-sm-3 d-flex align-items-center">
                            <div class="u-info me-2">
                                <p class="mb-0 text-end line-height-sm "><span class="font-weight-bold">{{ Auth::user()->Employee->nama }}</span></p>
                                <small>{{ $mainRole ?? 'Role tidak tersedia' }}</small>
                            </div>
                            <a class="nav-link dropdown-toggle pulse p-0" href="#" role="button" data-bs-toggle="dropdown" data-bs-display="static">
                                <img class="avatar lg rounded-circle img-thumbnail" 
                                     src="{{ Auth::user()->employee->foto ? asset('storage/' . Auth::user()->employee->foto) : asset('assets/images/profile_av.pngassets/images/xs/avatar2.jpg') }}" 
                                     alt="profile" 
                                     onerror="this.src='{{ asset('assets/images/profile_av.png') }}'">
                            </a>
                            <div class="dropdown-menu rounded-lg shadow border-0 dropdown-animation dropdown-menu-sm-start dropdown-menu-md-end" style=" width: max-content;">
                                <div class="card border-0">
                                    <div class="card-body pb-0">
                                        <div class="d-flex py-1">
                                            <img class="avatar rounded-circle" 
                                                 src="{{ Auth::user()->employee->foto ? asset('storage/' . Auth::user()->employee->foto) : asset('assets/images/profile_av.png') }}" 
                                                 alt="profile"
                                                 onerror="this.src='{{ asset('assets/images/profile_av.png') }}'">
                                            <div class="flex-fill ms-3">
                                                <p class="mb-0"><span class="font-weight-bold">{{ Auth::user()->Employee->nama }}</span></p>
                                                <small class="">{{Auth::user()->email}}</small>
                                            </div>
                                        </div>
                                        
                                        <div><hr class="dropdown-divider border-dark"></div>
                                    </div>
                                    <div class="list-group m-2 ">
                                        <a href="{{ route('profile.edit') }}" class="list-group-item list-group-item-action border-0 "><i class="icofont-contact-add fs-5 me-3"></i>Edit profil diri</a>
                                        <div><hr class="dropdown-divider border-dark"></div>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="list-group-item list-group-item-action border-0">
                                                <i class="icofont-logout fs-6 me-3"></i> Signout
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
    
                    <!-- menu toggler -->
                    <button class="navbar-toggler p-0 border-0 menu-toggle order-3" type="button" data-bs-toggle="collapse" data-bs-target="#mainHeader">
                        <span class="fa fa-bars"></span>
                    </button>
    
                    <!-- main menu Search-->
                    <div class="order-0 col-lg-4 col-md-4 col-sm-12 col-12 mb-3 mb-md-0 ">
                        <div class="input-group flex-nowrap input-group-lg">
                            <button type="button" class="input-group-text" id="addon-wrapping"><i class="fa fa-search"></i></button>
                            <input type="search" class="form-control" placeholder="Search" aria-label="search" aria-describedby="addon-wrapping">
                        </div>
                    </div>
                </div>
            </nav>
        </div>
    

        <!-- Main content-->
        <div class="body d-flex py-3">
            @yield('content')
        </div>  
    </div>
</div>

<!-- Jquery Core Js -->
<script src="{{asset('assets/bundles/libscripts.bundle.js')}}"></script>
<!-- Jquery Page Js -->
<script src="{{ asset('js/template.js') }}"></script>
<script src="{{ asset('js/page/hr.js') }}"></script>
@yield('script')

</body>
</html>