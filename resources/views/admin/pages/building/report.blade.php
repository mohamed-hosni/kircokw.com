@extends('admin.layout.copy')
@section('content')
<div class="main-wrapper">
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-7 col-auto">
                        <h3 class="page-title">{{ __('pages.report') }}</h3>
                    </div>
                    <div class="col-sm-5 col">
                        @if(Auth::user()->isSuperAdmin())
                            <a href="{{ route('building.report-upsert') }}"
                                class="btn btn-primary float-end mt-2"><i class="ti-plus"></i>
                                {{__('pages.add_report') }}</a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <form class="form" action="{{ route('building.report-filter') }}" method="get">
                                    <div class="form-group d-flex align-items-center">
                                        <input type="search" placeholder="{{ __('pages.search_by_name') }}" name="name"
                                            class="form-control d-block search_input w-50"
                                            value="{{request()->input('name')}}">
                                        <button class="btn btn-primary mx-2 btn-search">{{ __('pages.search')
                                            }}</button>
                                    </div>
                                </form>

                                <table id="example3" class=" display table table-hover table-center mb-0"
                                    filter="{{ route('building.report-filter') }}"
                                    redirect="{{ route('building.report') }}">
                                    <thead>
                                        <tr>
                                            <th>{{ __('pages.building_name') }}</th>
                                            <th>تاريخ التقرير</th>
                                            <th>{{ __('pages.report_month') }}</th>
                                            <th>{{ __('pages.report') }}</th>
                                            @if(Auth::user()->isSuperAdmin())
                                                <th>{{ __('pages.actions') }}</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- list all reports for the building --}}
                                        @foreach($buildings as $building)
                                            @foreach($building->report as $report)
                                                @if($report)
                                                    <tr class='record'>
                                                        <td>{{ $building->name }}</td>
                                                        <td>{{ $report->created_at->format('Y-m-d H:i:s') }}</td>
                                                        <td>{{ $report->report_month ?? $report->created_at->format('Y-m-d H:i:s') }}</td>
                                                        <td>
                                                            @if ($report)
                                                            <a href="{{ asset('/reports/'.$building->id.'/'. $report->name) }}" target="_blank" class="btn btn-primary btn">
                                                                <i class="ti ti-eye"></i>
                                                            </a>
                                                            @endif
                                                        </td>
                                                        @if(Auth::user()->isSuperAdmin())
                                                            <td class="text-end">
                                                                <div class="actions">
                                                                    <a href="#" onclick="edit_report(this)"
                                                                        data-target="#edit_report"
                                                                        data-toggle="modal"
                                                                        data-id="{{$building->id}}"
                                                                        data-report_id="{{$report->id}}"
                                                                        class="btn btn-sm bg-success-light">
                                                                        <i class="ti-pencil"></i> {{ __('pages.edit') }}
                                                                    </a>
                                                                    <a data-bs-toggle="modal" href="#"
                                                                        class="btn btn-sm bg-danger-light btn_delete"
                                                                        route="{{ route('building.report-delete', ['building_id' => $building->id, 'report_id' => $report->id]) }}">
                                                                        <i class="ti-trash"></i> {{ __('pages.delete') }}
                                                                    </a>
                                                                </div>
                                                            </td>
                                                        @endif
                                                    </tr>
                                                @endif
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>


                                <nav aria-label="Page navigation example" class="mt-2">
                                    <ul class="pagination">
                                        @for($i = 1; $i <= $buildings->lastPage(); $i++)
                                            <li class="page-item">
                                                <a class="page-link" href="?page={{$i}}">{{$i}}</a>
                                            </li>
                                            @endfor
                                    </ul>
                                </nav>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="edit_report" class="modal fade">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="modelHeading">
                                تعديل التقرير
                            </h4>
                            <span class="button" data-dismiss="modal" aria-label="Close"><i class="ti-close"></i></span>
                        </div>
                        <div class="modal-body">
                            <form method="post" enctype="multipart/form-data" action="{{ route('building.report-update-data') }}" class="ajax-form" swalOnSuccess="{{ __('pages.sucessdata') }}" title="{{ __('pages.opps') }}" swalOnFail="{{ __('pages.wrongdata') }}" redirect="{{ route('building.report') }}">
                                @csrf
                                <input type="hidden" name="id" id="building_id">
                                <input type="hidden" name="report_id" id="report_id">
                                <div class="form-group">
                                    <label for="name" class="col-md-12 col-sm-12 mb-2 control-label">{{ __('pages.report_month') }}</label>
                                    <div class="col-sm-12">
                                        <input type="date" class="form-control" id="report_month" name="report_month" value="" required>
                                    </div>
                                </div>
                                <div class="col-sm-offset-2 col-sm-12 text-center">
                                    <button type="submit" class="btn btn-primary" id="saveBtn" value="create">
                                        {{ __('pages.save') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
        </div>
    </div>


</div>
@endsection


@section('js')
<script>
    let table = new DataTable('#example3', {
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excel',
                exportOptions: {
                    columns: [ 0, 1, 2, 3]
                }
            },
        ],
        order: [[1, 'desc']],
        searching: false,
        responsive: true,
        paging: false,
        info: false,
        language: {
            "sProcessing": "جارٍ التحميل...",
            "sLengthMenu": "أظهر _MENU_ مدخلات",
            "sZeroRecords": "لم يعثر على أية سجلات",
            "sInfo": "إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل",
            "sInfoEmpty": "يعرض 0 إلى 0 من أصل 0 سجل",
            "sInfoFiltered": "(منتقاة من مجموع _MAX_ مُدخل)",
            "sInfoPostFix": "",
            "sSearch": "ابحث:",
            "sUrl": "",
            "oPaginate": {
                "sFirst": "الأول",
                "sPrevious": "السابق",
                "sNext": "التالي",
                "sLast": "الأخير"
            }
        }
    });

    $(".sorting").css({ 'text-align': 'right' });
    $("#example_filter").css({ 'margin-bottom': "20px" });
    $(".buttons-excel").css({ 'display': "none" });

    $('.btn_delete').on('click', function(){
        $(this).closest('.record').remove();
    })


    function edit_report(el) {
        var link = $(el);
        var modal = $("#edit_report");
        var id = link.data('id');
        var report_id = link.data('report_id');
        console.log(id);
        console.log(report_id);

        modal.find('#building_id').val(id);
        modal.find('#report_id').val(report_id);
    }
</script>

@endsection
