@extends('penilai.template')

@section('title', 'KPI Karyawan')

@section('content')
   <link rel="stylesheet" href="{{ asset('assets/plugin/datatables/responsive.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugin/datatables/dataTables.bootstrap5.min.css') }}">

    <!-- Body: Body -->       
        <div class="body d-flex">
            <div class="container-xxl">
                <div class="row align-items-center">
                    <div class="border-0 mb-2">
                        <div class="card-header py-3 no-bg bg-transparent d-flex align-items-center px-0 justify-content-between border-bottom flex-wrap">
                            <h3 class="fw-bold mb-0">Nilai KPI</h3>
                        </div>
                    </div>
                </div>
                <div class="container mt-1">
                <!-- Ringkasan KPI -->
                <h5 class="fw-bold">penilaian KPI terakhir</h5>
                <div class="row mb-3 mt-3">
                    <div class="col-md-3">
                    <div class="card shadow-sm text-center p-3">
                        <h6 class="mb-1">Total Score</h6>
                        <h3 class="fw-bold text-primary">87</h3>
                        <small class="text-muted">dari 100</small>
                    </div>
                    </div>
                    <div class="col-md-3">
                    <div class="card shadow-sm text-center p-3">
                        <h6 class="mb-1">Grade</h6>
                        <h3 class="fw-bold text-success">A</h3>
                        <small class="text-muted">Sangat Baik</small>
                    </div>
                    </div>
                    <div class="col-md-3">
                    <div class="card shadow-sm text-center p-3">
                        <h6 class="mb-1">Ranking Divisi</h6>
                        <h3 class="fw-bold text-warning">3</h3>
                        <small class="text-muted">dari 15 orang</small>
                    </div>
                    </div>
                    <div class="col-md-3">
                    <div class="card shadow-sm text-center p-3">
                        <h6 class="mb-1">Perubahan</h6>
                        <h3 class="fw-bold text-info">+5%</h3>
                        <small class="text-muted">dibanding bulan lalu</small>
                    </div>
                    </div>
                </div>

                 
                <!-- Tabel KPI Detail -->
                <div class="card shadow-sm mb-3">
    <div class="card-header bg-transparent">
        <h6 class="mb-0 fw-bold">Detail Score KPI</h6>
    </div>
    <div class="card-body">
        <div class="row clearfix g-3">
            <div class="col-sm-12">
                <div class="card mb-3">
                    <div class="card-body">
                        <table id="myProjectTable" class="table table-hover align-middle mb-0" style="width:100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Aspek</th>
                                    <th>Nilai</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="fw-bold">1</span></td>
                                    <td><span class="fw-bold ms-1">Disiplin</span></td>
                                    <td><span class="fw-bold ms-1">90</span></td>
                                </tr>
                                <tr>
                                    <td><span class="fw-bold">2</span></td>
                                    <td><span class="fw-bold ms-1">Kompetensi Umum</span></td>
                                    <td><span class="fw-bold ms-1">85</span></td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="table-light">
                                    <td colspan="2" class="fw-bold text-end">Rata-rata</td>
                                    <td><span class="fw-bold text-primary">87.5</span></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

                </div><!-- Row End -->
            </div>
        </div>

                <!-- Grafik Perkembangan -->
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-transparent">
                    <h6 class="mb-0 fw-bold">Grafik Perkembangan KPI</h6>
                    </div>
                    <div class="card-body">
                    <div id="chartKPI" style="min-height:300px;"></div>
                    </div>
                </div>
                </div>
            </div>
        </div>


@endsection
@section('script')
<script src="{{ asset('assets/bundles/dataTables.bundle.js') }}"></script>
<script src="{{asset('assets/bundles/apexcharts.bundle.js')}}"></script>

   {{-- <script>
    $(document).ready(function() {
        $('#myProjectTable')
        .addClass( 'nowrap' )
        $('#myProjectTable').DataTable({
            responsive: true,
            columnDefs: [
                { targets: -1, orderable: false, searchable: false, className: 'dt-body-right all' }
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
</script> --}}
    <script>
    var options = {
        chart: {
        type: 'line',
        height: 300
        },
        series: [{
        name: 'Score KPI',
        data: [78, 82, 85, 90, 87, 92] // contoh data tiap bulan
        }],
        xaxis: {
        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun']
        }
    };

    var chart = new ApexCharts(document.querySelector("#chartKPI"), options);
    chart.render();
    </script>
@endsection