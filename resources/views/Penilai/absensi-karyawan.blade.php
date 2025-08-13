@extends('penilai.template')

@section('title', 'Absensi Karyawan')

@section('content')

   <!-- CSS Tabel -->
    <link rel="stylesheet" href="{{ asset('assets/plugin/datatables/responsive.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugin/datatables/dataTables.bootstrap5.min.css') }}">

     <!-- Body: Body -->       
        <div class="body d-flex py-lg-3 py-md-2">
            <div class="container-xxl">
                <div class="row align-items-center">
                    <div class="border-0 mb-4">
                        <div class="card-header py-3 no-bg bg-transparent d-flex align-items-center px-0 justify-content-between border-bottom flex-wrap">
                            <h3 class="fw-bold mb-0">Absensi Karyawan</h3>
                            <div class="dropdown ms-auto me-3">
                                    <button class="btn btn-primary dropdown-toggle mt-1  w-sm-100" type="button" id="dropdownMenuButton2" data-bs-toggle="dropdown" aria-expanded="false">
                                        Bulan
                                    </button>
                                    <ul class="dropdown-menu  dropdown-menu-end" aria-labelledby="dropdownMenuButton2">
                                    <li><a class="dropdown-item" href="#">Semua</a></li>
                                    <li><a class="dropdown-item" href="#">Januari</a></li>
                                    </ul>
                            </div>
                            <div class="dropdown">
                                    <button class="btn btn-primary dropdown-toggle mt-1  w-sm-100" type="button" id="dropdownMenuButton2" data-bs-toggle="dropdown" aria-expanded="false">
                                    Tahun
                                    </button>
                                    <ul class="dropdown-menu  dropdown-menu-end" aria-labelledby="dropdownMenuButton2">
                                    <li><a class="dropdown-item" href="#">Semua</a></li>
                                    <li><a class="dropdown-item" href="#">2022</a></li>
                                    </ul>
                            </div>
                        </div>
                    </div>
                </div> <!-- Row end  -->
                <div class="row clearfix g-3">
                  <div class="col-sm-12">
                        <div class="card mb-3">
                            <div class="card-body">
                                <table id="myProjectTable" class="table table-hover align-middle mb-0" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Nama</th>
                                            <th>Divisi</th> 
                                            <th>Hadir</th> 
                                            <th>Izin</th>
                                            <th>Mangkir</th>
                                            <th>Terlambat</th>   
                                            <th>Actions</th>  
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <span class="fw-bold">mamaowd</span>
                                            </td>
                                           <td>
                                              <span class="fw-bold ms-1">ui</span>
                                           </td>
                                           <td>
                                                <span class="fw-bold ms-1">23</span>
                                           </td>
                                           <td>
                                                <span class="fw-bold ms-1">0</span>
                                           </td>
                                           <td>
                                                <span class="fw-bold ms-1">0</span>
                                            </td>
                                            <td>
                                                <span class="fw-bold ms-1">0</span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group" aria-label="Basic outlined example">
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#absenkaryawan"><i class="icofont-eye-alt"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                  </div>
                </div><!-- Row End -->
            </div>
        </div>

        <!-- modal KPI-->
        <div class="modal fade" id="absenkaryawan" tabindex="-1"  aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-md modal-dialog-scrollable">
            <div class="modal-content">
              <div class="modal-header">
        <h5 class="modal-title" id="absenModalLabel">Data Absensi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
                <div class="fw-bold m-2"><span>ID Karyawan : </span>12345</div>
                <div class="fw-bold mb-2 ms-2"><span>Nama Karyawan : </span>John Doe</div>
            <div class="modal-body p-0">
                <table class="table table-bordered table-striped mb-0 align-middle">
                    <thead class="table-primary text-center fw-bold">
                        <tr>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Jam Masuk</th>
                            <th>Jam Keluar</th>
                            <th>Lama Kerja</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        <tr class="table-hover">
                            <td>1</td>
                            <td><span class="badge bg-success rounded-pill px-3">Hadir</span></td>
                            <td>08:00</td>
                            <td>17:00</td>
                            <td>9 jam</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
    </div>
@endsection

@section('script')
    <!-- Plugin Js tabel-->
<script src="{{ asset('assets/bundles/dataTables.bundle.js') }}"></script>

    <script>
    // project data table
    $(document).ready(function() {
        $('#myProjectTable')
        .addClass( 'nowrap' )
        .dataTable( {
            responsive: true,
            columnDefs: [
                { targets: [-1, -3], className: 'dt-body-right' }
            ]
        });
        $('.deleterow').on('click',function(){
        var tablename = $(this).closest('table').DataTable();  
        tablename
                .row( $(this)
                .parents('tr') )
                .remove()
                .draw();

        } );
    });
</script>
@endsection