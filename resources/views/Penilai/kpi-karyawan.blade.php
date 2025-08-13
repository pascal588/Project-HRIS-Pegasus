@extends('penilai.template')

@section('title', 'KPI Karyawan')

@section('content')
    <!-- CSS Tabel -->
    <link rel="stylesheet" href="{{asset('assets/plugin/datatables/responsive.dataTables.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/plugin/datatables/dataTables.bootstrap5.min.css')}}">


        <!-- Body: Body -->       
        <div class="body d-flex py-lg-3 py-md-2">
            <div class="container-xxl">
                <div class="row align-items-center">
                    <div class="border-0 mb-4">
                        <div class="card-header py-3 no-bg bg-transparent d-flex align-items-center px-0 justify-content-between border-bottom flex-wrap">
                            <h3 class="fw-bold mb-0">Nilai KPI</h3>
                        </div>
                        <div class="card-header py-3 no-bg bg-transparent d-flex align-items-center px-0 justify-content-between flex-wrap">
                            <h6 class="fw-bold mb-0">Id Karyawan : 019w09102</h6>
                            <h6 class="fw-bold mb-0">Nama : Agusyina</h6>
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
                                            <th>#</th>
                                            <th>Tahun</th> 
                                            <th>Bulan</th> 
                                            <th>Nilai KPI</th>   
                                            <th>Actions</th>  
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <span class="fw-bold">1</span>
                                            </td>
                                           <td>
                                              <span class="fw-bold ms-1">2023</span>
                                           </td>
                                           <td>
                                                <span class="fw-bold ms-1">Januari</span>
                                           </td>
                                           <td>
                                                80
                                           </td>
                                            <td>
                                                <div class="btn-group" role="group" aria-label="Basic outlined example">
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#showkpi"><i class="icofont-eye-alt text-success"></i></button>
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
        <div class="modal fade" id="showkpi" tabindex="-1"  aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-md modal-dialog-scrollable">
            <div class="modal-content">
              
        <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="nilaiDetailLabel">Detail Nilai KPI</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <!-- Tabel Detail Nilai -->
                <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                    <th>Kategori</th>
                    <th>Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                    <td>Disiplin</td>
                    <td>85</td>
                    </tr>
                    <tr>
                    <td>Kompetensi Umum</td>
                    <td>90</td>
                    </tr>
                    <tr>
                    <td>Kompetensi Teknikal</td>
                    <td>88</td>
                    </tr>
                </tbody>
                </table>

                <!-- Total & Grade -->
                <div class="mt-3 p-3 bg-light rounded">
                <h6 class="mb-1">Total Score: <strong>87.67</strong></h6>
                <h6 class="mb-0">Grade: <strong>A</strong></h6>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
                </div> 
            </div>  
@endsection 

@section('script')
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