@extends('admin.layout.master')
@section('content')
<div class="main-wrapper">
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                @if(Auth::user()->role_id == 3)
                <div class="row">
                    @forelse($tenants as $tenant)
                    <div class="col-xl-6 col-md-6 col-12">
                        <div class="container-tenant mb-4">
                            <div class="wrapper-tenant">
                                <!-- <div class="banner-image"> </div> -->
                                <h1>{{ $tenant->apartment_name . ' ' . $tenant->building_name }}</h1>
                                <p class="p_tenant">{{ $tenant->price . ' د.ك' }}</p>
                                <p class="p_tenant">{{ 'تاريخ بدء العقد: ' . $tenant->start_date }}</p>
                                <p class="p_tenant">{{ 'تاريخ انتهاء العقد: ' . $tenant->end_date }}</p>
                                <p class="p_tenant text-danger" style="height: 22px;">{{ $tenant->paid == 1 ? 'هذه الوحدة مدفوعة' : '' }}</p>
                                <div class="button-wrapper">
                                    @if($tenant->is_blocked)
                                    <div class="mb-5">لا يمكن الدفع لهذا الشهر برجاء التواصل مع المكتب</div>
                                    @else
                                    @if($tenant->picture_name)
                                    <button class="btn-tenant outline-tenant ms-4 mb-3" onClick="swipe({{$tenant->id}});">{{ __('pages.details') }}</button>
                                    <input type="hidden" class="img-building_{{$tenant->id}}" value="{{asset('/tenants/' . $tenant->id . '/'. $tenant->picture_name)}}" />
                                    @endif
                                    <a href="#" onclick="payment(this)" data-target="#payment" data-toggle="modal" data-id="{{Auth::user()->id}}" data-apartment_name="{{$tenant->apartment_name}}" data-tenant_id="{{$tenant->id}}" data-user_id="{{$tenant->user_id}}" data-apartment_id="{{$tenant->apartment_id}}" data-cost="{{$tenant->price}}" data-building_name="{{$tenant->building_name}}" data-start_date="{{$tenant->start_date}}" data-end_payment="{{$tenant->end_payment ?? null}}" class="btn-tenant fill-tenant text-nowrap">
                                        {{ $tenant->paid == 1 ? 'لدفع الأشهر القادمة' : __('pages.pay_now') }}
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <p>لا يوجد وحدات مستاجرة</p>
                    @endforelse
                </div>
                @elseif (Auth::user()->isSuperAdmin() || Auth::user()->role_id == 2)
                <div class="row row-cols-2">
                    @if (Auth::user()->isSuperAdmin() )
                    <div class="col-md-6 col-12">
                        <div class="container-tenant mb-4">
                            <div class="wrapper-tenant">
                                <h1> {{ __('pages.users') }} </h1>
                                <div class="button-wrapper">
                                    <a href="{{ route('user') }}" class="btn-tenant fill-tenant">{{ __('pages.users') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- button to download backup --}}
                    <!--<div class="col-md-6 col-12">-->
                    <!--    <div class="container-tenant mb-4">-->
                    <!--        <div class="wrapper-tenant">-->
                    <!--            <h1> {{ __('pages.backup') }} </h1>-->
                    <!--            <div class="button-wrapper">-->
                    <!--                <a href="{{ route('download-backup') }}" class="btn-tenant fill-tenant">{{ __('pages.backup') }}</a>  -->
                    <!--            </div>-->
                    <!--        </div>-->
                    <!--    </div>-->
                    <!--</div>-->
                    @endif
                    <div class="col-md-6 col-12">
                        <div class="container-tenant mb-4">
                            <div class="wrapper-tenant">
                                <h1>{{ __('pages.buildings') }}</h1>
                                <div class="button-wrapper">
                                    <a href="{{ route('building') }}" class="btn-tenant fill-tenant">{{ __('pages.buildings') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-12">
                        <div class="container-tenant mb-4">
                            <div class="wrapper-tenant">
                                <h1>{{ __('pages.fees') }}</h1>
                                <div class="button-wrapper">
                                    <a href="{{ route('maintenance') }}" class="btn-tenant fill-tenant">{{ __('pages.fees') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-12">
                        <div class="container-tenant mb-4">
                            <div class="wrapper-tenant">
                                <h1>{{ __('pages.revenue') }}</h1>
                                <div class="button-wrapper">
                                    <a href="{{ route('revenue') }}" class="btn-tenant fill-tenant">{{ __('pages.revenue') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-12">
                        <div class="container-tenant mb-4">
                            <div class="wrapper-tenant">
                                <h1>{{ __('pages.financial_transactions') }}</h1>
                                <div class="button-wrapper">
                                    <a href="{{ route('financial_transaction') }}" class="btn-tenant fill-tenant">{{ __('pages.financial_transactions') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-12">
                        <div class="container-tenant mb-4">
                            <div class="wrapper-tenant">
                                <h1>{{ __('pages.report') }}</h1>
                                <div class="button-wrapper">
                                    <a href="{{ route('building.report') }}" class="btn-tenant fill-tenant">التقارير</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    <div id="payment" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modelHeading">الدفع</h4>
                    <span class="button" data-dismiss="modal" aria-label="Close"> <i class="ti-close"></i> </span>
                </div>
                <div class="modal-body">
                    <form method="post" id="paymentForm" enctype="multipart/form-data" swalOnSuccess="{{ __('pages.sucessdata') }}" title="{{ __('pages.opps') }}" swalOnFail="{{ __('pages.wrongdata') }}">
                        @csrf
                        <input type="hidden" name="id" id="id">
                        <input type="hidden" name="tenant_id" id="tenant_id">
                        <input type="hidden" name="apartment_id" id="apartment_id">
                        <input type="hidden" name="user_id" id="user_id">
                        <input type="hidden" name="start_date" id="start_date">
                        <input type="hidden" name="end_payment" id="end_payment">
                        <input type="hidden" name="cost" id="cost">
                        <div class="form-group">
                            <label class="mb-2">اسم العقار</label>
                            <div class="col-md-12">
                                <input class="form-control text-start" id="building_name" type="text" disabled name="building_name">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="mb-2">اسم الوحدة</label>
                            <div class="col-md-12">
                                <input class="form-control text-start" id="apartment_name" type="text" disabled name="apartment_name">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="mb-2" for="flexSwitchCheckDefault">دفع شهور متتعدده</label>
                            <div class="col-md-12">
                                <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="mb-2">عدد الشهور المراد دفعها</label>
                            <div class="col-md-12 btn-payment">
                                <select class="form-control select-month" required  multiple name="quantity" route="{{ route('data') }}" id="quantity">
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-offset-2 col-sm-12 text-center">
                            <button type="submit" class="btn btn-primary" id="saveBtn" value="create">{{ __('pages.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    function swipe(id) {
        var url = $(".img-building_" + id).val();

        window.open(url, 'width=200px,height=200px,resizable=1');
    }

    $(document).ready(function() {
        let index_select = 0;
        let isCheckboxChecked = false; // Track the checkbox state

        function route() {
            return $(this).attr('route');
        }

        function placeholder() {
            return $(this).attr('placeholder');
        }

        function formatRepo(item) {
            return item.name;
        }

        function updateSelect2Options() {
            let maximumSelectionLength = isCheckboxChecked ? null : 1;

            $(".select-month").select2({
                dropdownParent: $("#payment")
                , maximumSelectionLength: maximumSelectionLength,

                ajax: {
                    url: route
                    , type: "post"
                    , dataType: 'json'
                    , delay: 250
                    , headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                    , data: function(term) {
                        return {
                            term: term
                            , tenant_id: $("#id").val()
                            , apartment_id: $("#apartment_id").val()
                        , };
                    }
                    // , beforeSend: function() {
                    //     $('.select-month').val(null).trigger('change');
                    //     index_select = 0;
                    // }
                    , processResults: function(response) {
                        if(index_select == 0){
                            for(let i = 0; i < response.length; i++){
                                if(response[i]['status'] == 'enabled'){
                                    index_select = i;
                                    break;
                                }
                            }
                        }

                        return {
                            results: $.map(response, function(item, index) {
                                console.log(item['status'], index, index_select, 'hi')
                                return {
                                    text: item['date'],
                                    disabled: item['status'] == 'enabled' ? index <= index_select ? "" : "disabled" : "disabled",
                                    id: item['date'],
                                }
                            })
                        }
                    }
                    , cache: false
                    , templateResult: formatRepo
                    , placeholder: placeholder
                , }
            , });
        }

        // Initialize Select2 options
        updateSelect2Options();

        // Checkbox change event
        $('#flexSwitchCheckDefault').on('change', function() {
            isCheckboxChecked = $(this).is(':checked');
            // reset the selection
            $('.select-month').val(null).trigger('change');
            index_select = 0;
            updateSelect2Options();
        });

        $('.select-month').on("select2:select", function(e) {
            index_select++;
            updateSelect2Options();
        });

        $('.select-month').on("select2:unselect", function(e) {
            index_select = 0;
            updateSelect2Options();
        });

    });


    $('#paymentForm').submit(function(e) {
        e.preventDefault();
        let end_payment = $('#end_payment').val();
        let selected_month = $('#quantity').val();

        let end_payment_date = new Date(end_payment);
        end_payment_date.setMonth(end_payment_date.getMonth() + 1);
        let end_payment_month = end_payment_date.toLocaleString('en-IN', {
            year: 'numeric'
            , month: 'short'
        }).replace(/ /g, '-');

        // if (selected_month.length > 0) {
        //     let last_selected_month = selected_month[0];
        //     if (end_payment && (last_selected_month != end_payment_month)) {
        //         alert(`رجاء إختيار أول شهر غير مدفوع لإتمام عملية الدفع .. لقد إخترت شهر ${selected_month[0]} بدلا من ${end_payment_month}`);
        //         return;
        //     }
        // }
        alert('يرجي الإنتظار حتي يتم الإنتهاء من عملية الدفع و من ثم يعاد تسجيل الدخول للتاكد من الدفع و الحصول علي وصل الدفع');

        $.ajax({
            url: "{{route('payment.rent')}}"
            , headers: {
                'X-CSRF-TOKEN': '{{csrf_token()}}'
            }
            , type: "POST"
            , async: false
            , contentType: "application/json; charset=utf-8"
            , data: JSON.stringify({
                quantity: $('#quantity').val()
                , user_id: $('#user_id').val()
                , apartment_id: $('#apartment_id').val()
                , id: $('#id').val()
                , tenant_id: $('#tenant_id').val()
                , cost: $('#cost').val()
                , start_date: $('#start_date').val()
            , })
            , success: function(data) {
                window.location.href = data;
            }
        });
    });

    function payment(el) {
        $('select-month').select2('data', null);
        $('.select-montht').html(null);
        var link = $(el);
        var modal = $("#payment");
        var id = link.data('id');
        var tenant_id = link.data('tenant_id');
        var cost = link.data('cost');
        var building_name = link.data('building_name');
        var apartment_name = link.data('apartment_name');
        var apartment_id = link.data('apartment_id');
        var user_id = link.data('user_id');
        var start_date = link.data('start_date');
        var end_payment = link.data('end_payment');

        modal.find('#id').val(id);
        modal.find('#tenant_id').val(tenant_id);
        modal.find('#cost').val(cost);
        modal.find('#user_id').val(user_id);
        modal.find('#start_date').val(start_date);
        modal.find('#apartment_id').val(apartment_id);
        modal.find('#building_name').val(building_name);
        modal.find('#apartment_name').val(apartment_name);
        modal.find('#end_payment').val(end_payment);


        if ($('.select-month').length == 0) {
            let index_select = 0;
            let isCheckboxChecked = false;
            let maximumSelectionLength = isCheckboxChecked ? null : 1;

            function route() {
                return $(this).attr('route');
            }

            function placeholder() {
                return $(this).attr('placeholder');
            }

            function formatRepo(item) {
                return item.name;
            }

            $('.btn-payment').children().remove();
            $('.btn-payment').append(`
                    <select class="form-control select-month" multiple name="quantity" route="{{ route('data') }}" id="quantity">
                    </select>
                `);

            // $('.select-month').select2({
            //     dropdownParent: $("#payment"),
            //     maximumSelectionLength: maximumSelectionLength,

            //     ajax: {
            //         url: route,
            //         type: "post",
            //         dataType: 'json',
            //         delay: 250,
            //         headers: {
            //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            //         },
            //         data: function (term) {
            //             return {
            //                 term: term,
            //                 tenancy_id: $("#tenant_id").val()
            //             };
            //         },
            //         processResults: function (response) {
            //             if(index_select == 0){
            //                 for(let i = 0; i < response.length; i++){
            //                     if(response[i]['status'] == 'enabled'){
            //                         index_select = i;
            //                         break;
            //                     }
            //                 }
            //             }

            //             if(response[0]['status'] == 'enabled'){
            //                 const date = new Date(response[0]['date']);
            //                 const date_now = new Date();
            //                 const options = {
            //                     year: 'numeric',
            //                     month: 'long',
            //                 };

            //                 if(date_now.toLocaleString('en-IN', options) >= date.toLocaleString('en-IN', options)){
            //                     const options = {
            //                         year: 'numeric',
            //                         month: 'long',
            //                         day: 'numeric',
            //                     };

            //                     const d = new Date();
            //                     let day = d.toLocaleString('en-IN', options);

            //                     if(d.getDate() >= 21){
            //                         $('.btn-payment').children().remove();
            //                         $('.btn-payment').append(`
            //                             <div>لا يمكن الدفع لهذا الشهر برجاء التواصل مع المكتب </div>
            //                         `);
            //                     }
            //                 }
            //             }

            //             return {
            //                 results: $.map(response, function(item, index) {
            //                     return {
            //                         text: item['date'],
            //                         disabled: item['status'] == 'enabled' ? index <= index_select ? "" : "disabled" : "disabled",
            //                         id: item['date'],
            //                     }
            //                 })
            //             }
            //         },
            //         cache: true,
            //         templateResult: formatRepo,
            //         placeholder: placeholder,
            //     },
            // });
        }
    }

</script>
@endsection

