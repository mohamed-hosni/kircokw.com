@extends('admin.layout.master')
@section('content')
<div class="main-wrapper">
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="content container-fluid">
                <!-- Page Header -->
                <div class="page-header">
                    <div class="row">
                        <div class="col-sm-12">
                            <h3 class="page-title">{{ __('pages.add_report') }}</h3>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="javascript:(0);">{{ __('pages.report') }}</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- /Page Header -->
                <div class="row">
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-body custom-edit-service">
                                <!-- Add Blog -->
                                <form method="post" enctype="multipart/form-data"
                                    action="{{ route('building.report-modify') }}" class="ajax-form"
                                    swalOnSuccess="{{ __('pages.sucessdata') }}" title="{{ __('pages.opps') }}"
                                    swalOnFail="{{ __('pages.wrongdata') }}" redirect="{{ route('building.report') }}">
                                    @csrf
                                    <div class="service-fields mb-3">
                                        <div class="form-group">
                                            <div class="row">
                                                <div class="col-md-6 mt-3">
                                                    <label class="mb-2">{{ __('pages.building') }}</label>
                                                    <select class="form-control select2 d-flex" id="building_id"
                                                        placeholder="{{ __('pages.building') }}"
                                                        route="{{route('buildings')}}" name="building_id">
                                                        @if($building->building_id)
                                                        <option class="form-control" selected
                                                            value="{{$building->building->id}}">{{
                                                            $building->building->name}}</option>
                                                        @endif
                                                    </select>
                                                </div>
                                                <div class="col-md-6 mt-3">
                                                    <label class="mb-2">
                                                        {{ __('pages.report_month') }}
                                                    </label>
                                                    <input type="date" class="form-control" id="report_month" name="report_month" value="" required>
                                                    <p class="error error_contract"></p>
                                                </div>

                                                <div class="col-md-12 mt-3">
                                                    <div class="form-group">
                                                        <label class="mb-2">
                                                            التقرير
                                                        </label>
                                                        <input type="file" class="dropify"
                                                            data-default-file="@if($building->id){{ asset('/reports/'.$building->id.'/'.$building->report->name) }}@endif"
                                                            name="report" />
                                                        <p class="error error_contract"></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="submit-section">
                                            <button class="btn btn-primary submit-btn" type="submit" name="form_submit"
                                                placeholder="submit">{{ __('pages.submit') }}</button>
                                        </div>
                                </form>
                                <!-- /Add Blog -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('js')
<script>
    $('.dropify').dropify();

    $(document).ready(function(){
        function route(){
            return $(this).attr('route');
        }

        function placeholder(){
            return $(this).attr('placeholder');
        }

        $("#compound_id").select2({
            ajax: {
                url: route,
                type: "post",
                dataType: 'json',
                delay: 250,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: function (term) {
                    return {
                        term: term,
                        user_id: $("#user_id").val()
                    };
                },
                processResults: function (response) {
                    return {
                        results: $.map(response, function(item) {
                            return {
                                text: item.name ,
                                id: item.id,
                            }
                        })
                    }
                },
                cache: true,
                templateResult: formatRepo,
                placeholder: placeholder,
            },
        });

        $("#building_id").select2({
            ajax: {
                url: route,
                type: "post",
                dataType: 'json',
                delay: 250,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: function (term) {
                    return {
                        // term: term,
                        // compound_id: $("#compound_id").val()
                    };
                },
                processResults: function (response) {
                    return {
                        results: $.map(response, function(item) {
                            return {
                                text: item.name ,
                                id: item.id,
                            }
                        })
                    }
                },
                cache: true,
                templateResult: formatRepo,
                placeholder: placeholder,
            },
        });

        $("#apartment_id").select2({
            ajax: {
                url: route,
                type: "post",
                dataType: 'json',
                delay: 250,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: function (term) {
                    return {
                        term: term,
                        building_id: $("#building_id").val()
                    };
                },
                processResults: function (response) {
                    return {
                        results: $.map(response, function(item) {
                            return {
                                text: item.name ,
                                id: item.id,
                            }
                        })
                    }
                },
                cache: true,
                templateResult: formatRepo,
                placeholder: placeholder,
            },
        });

        function formatRepo (item) {
            return item.name;
        }
    });
</script>
@endsection
