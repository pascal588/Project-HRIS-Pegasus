<!-- sidebar -->
    <div class="sidebar px-4 py-4 py-md-5 me-0">
        <div class="d-flex flex-column h-100">
            <a href="index.html" class="mb-0 brand-icon">
                <span class="logo-icon">
                    <svg  width="35" height="35" fill="currentColor" class="bi bi-clipboard-check" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M10.854 7.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 9.793l2.646-2.647a.5.5 0 0 1 .708 0z"/>
                        <path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1v-1z"/>
                        <path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5h3zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3z"/>
                    </svg>
                </span>
                <span class="logo-text">Hriss</span>
            </a>
            <!-- Menu: main ul -->

            <ul class="menu-list flex-grow-1 mt-3">
                <li class="collapsed">
                    <a class="m-link" href="{{ route('penilai.dashboard') }}">
                    <i class="icofont-home fs-5"></i> <span>Dashboard</span></a>
                </li>
                <li class="collapsed">
                    <a class="m-link"  href="{{ route('penilai.list-karyawan') }}">
                    <i class="icofont-users-alt-5"></i> <span>Karyawan</span></a>
                </li>
                <li class="collapsed">
                    <a class="m-link"  href="{{ route('penilai.kpi-karyawan') }}">
                   <i class="icofont-ui-calculator"></i> <span>KPI Karyawan</span></a>
                </li>
                <li class="collapsed">
                    <a class="m-link" data-bs-toggle="collapse" data-bs-target="#data-saya"><i
                            class="icofont-contrast"></i> <span>Data Saya</span> <span class="arrow icofont-dotted-down ms-auto text-end fs-5"></span></a>
                    <ul class="sub-menu collapse" id="data-saya">
                        <li><a class="ms-link" href="{{ route('penilai.absensi') }}"> <span>Absensi saya</span></a></li>
                        <li><a class="ms-link" href="{{ route('penilai.kpi-penilai') }}"> <span>KPI Saya</span></a></li>

                    </ul>
                </li>
                
            
            <!-- Menu: menu collepce btn -->
            <button type="button" class="btn btn-link sidebar-mini-btn text-light order-3 ms-2">
                <span class="ms-2"><i class="icofont-bubble-right"></i></span>
            </button>
        </div>
    </div>