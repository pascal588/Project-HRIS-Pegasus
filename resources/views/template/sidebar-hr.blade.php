<!-- sidebar -->
<div class="sidebar px-4 py-4 py-md-5 me-0">
  <div class="d-flex flex-column h-100">
    <a href="index.html" class="mb-0 brand-icon">
      <span class="logo-icon">
        <svg
          width="35"
          height="35"
          fill="currentColor"
          class="bi bi-clipboard-check"
          viewBox="0 0 16 16">
          <path
            fill-rule="evenodd"
            d="M10.854 7.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 9.793l2.646-2.647a.5.5 0 0 1 .708 0z" />
          <path
            d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1v-1z" />
          <path
            d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5h3z" />
        </svg>
      </span>
      <span class="logo-text">Hriss</span>
    </a>

    <!-- Menu: main ul -->
    <ul class="menu-list flex-grow-1 mt-3">
      <li class="collapsed">
        <a class="m-link {{ request()->routeIs('hr.dashboard') ? 'active' : '' }}" href="{{ route('hr.dashboard')}}">
          <i class="icofont-home fs-5"></i> <span>Dashboard</span>
        </a>
      </li>
      <li class="collapsed">
        <a class="m-link {{ request()->routeIs('hr.penilaian') ? 'active' : '' }}" href="{{ route('hr.penilaian')}}">
          <i class="icofont-users-alt-5"></i>
          <span>Penilaian Karyawan</span>
        </a>
      </li>
      <li class="collapsed">
        <a
          class="m-link {{ request()->routeIs('hr.absensi','hr.kpi-karyawan') ? '' : 'collapsed' }}"
          data-bs-toggle="collapse"
          data-bs-target="#history-components"
          href="#">
          <i class="icofont-history"></i> <span>History</span>
          <span
            class="arrow icofont-dotted-down ms-auto text-end fs-5"></span>
        </a>
        <!-- Menu: Sub menu ul -->
        <ul
        class="sub-menu collapse {{ request()->routeIs('hr.absensi','hr.kpi-karyawan') ? 'show' : '' }}"
          id="history-components"
          style="transition: all 0.3s ease">
          <li>
            <a class="ms-link {{ request()->routeIs('hr.absensi') ? 'active' : '' }}" href="{{ route('hr.absensi')}}"><span>Laporan Hasil Absensi</span></a>
          </li>
          <li>
            <a class="ms-link {{ request()->routeIs('hr.kpi-karyawan') ? 'active' : '' }}" href="{{route ('hr.kpi-karyawan')}}"><span>Laporan Hasil KPI</span></a>
          </li>
        </ul>
      </li>

      <li class="collapsed">
        <a
          class="m-link {{ request()->routeIs('hr.karyawan','hr.divisi', 'hr.kpi') ? '' : 'collapsed' }}"
          data-bs-toggle="collapse"
          data-bs-target="#masterdata-components"
          href="#">
          <i class="icofont-ui-calculator"></i> <span>Master Data</span>
          <span
            class="arrow icofont-dotted-down ms-auto text-end fs-5"></span>
        </a>
        <!-- Menu: Sub menu ul -->
        <ul
          class="sub-menu collapse {{ request()->routeIs('hr.karyawan','hr.divisi', 'hr.kpi') ? 'show' : '' }}"
          id="masterdata-components"
          style="transition: all 0.3s ease">
          <li>
            <a class="ms-link {{ request()->routeIs('hr.karyawan') ? 'active' : '' }}" href="{{route('hr.karyawan')}}"><span>Karyawan</span></a>
          </li>
          <li>
            <a class="ms-link {{ request()->routeIs('hr.divisi') ? 'active' : '' }}" href="{{route('hr.divisi')}}"><span>Divisi</span></a>
          </li>
          <li>
            <a class="ms-link {{ request()->routeIs('hr.kpi') ? 'active' : '' }}" href="{{route('hr.kpi')}}">
              <span>Penilaian KPI</span></a>
          </li>
        </ul>
      </li>
    </ul>

    <!-- Menu: menu collapse btn -->
    <button
      type="button"
      class="btn btn-link sidebar-mini-btn text-light">
      <span class="ms-2"><i class="icofont-bubble-right"></i></span>
    </button>
  </div>
</div>