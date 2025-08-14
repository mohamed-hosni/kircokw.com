@extends('admin.layout.master')
@section('content')
<div class="main-wrapper">
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-7 col-auto">
                        <h3 class="page-title">المتخلفين عن السداد</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item active">المتخلفين عن السداد</li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- /Page Header -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <!-- <form class="form row" action="{{ route('financial_transaction.filter') }}"
                                    method="get">
                                    <div class="form-group col-md-11 d-flex align-items-center">
                                        <input type="search" placeholder="{{ __('pages.search_by_name') }}" name="name"
                                            class="form-control d-block search_input w-25"
                                            value="{{request()->input('name')}}">
                                        <button class="btn btn-primary mx-2 btn-search">{{ __('pages.search') }}</button>
                                    </div>
                                </form> -->


                                <form class="form row" action="{{ route('unpaid.filter') }}"
                                    method="get">
                                    <div class="form-group col-md-11 d-flex align-items-center gap-1">
                                        <input type="search" placeholder="{{ __('pages.search_by_building_name') }}" name="building"
                                            class="form-control d-block search_input w-25"
                                            value="{{request()->input('building')}}">

                                        <button class="btn btn-primary btn-search">{{ __('pages.search')
                                            }}</button>
                                    </div>


                                    {{--

                                    <!--<a class="btn btn-sm bg-danger col-md-1 my-3"-->
                                    <!--    href="{{ route('financial_transaction.financial-pdf') }}">-->
                                    <!--    <i class="ti-print"></i> PDF-->
                                    <!--</a>-->
                                    --}}
                                </form>

                                <table id="exampleTable" class="table display table-hover table-center mb-0"
                                    filter="{{ route('unpaid.filter') }}">
                                    <thead>
                                        <tr>
                                            <th>اسم المستأجر</th>
                                            <th>اسم العقار</th>
                                            <th>اسم الوحدة</th>
                                            <th>المبلغ الإجمالي</th>
                                            <th> اخر 3 شهور</th>
                                            <th></th>
                                            <th></th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($apartments as $apartment)
                                        <tr class="record">
                                            <td>{{ $apartment->user_name }}</td>
                                            <td>{{ $apartment->building_name }}</td>
                                            <td>{{ $apartment->apartment_name }}</td>
                                            <td>{{ $apartment->tenant_price }} </td>
                                            <td data-sort="{{ $apartment->payment_created_at1 }}">{{ $apartment->payment_amount1 . ' (' . $apartment->payment_created_at1 . ')' }}</td>
                                            <td data-sort="{{ $apartment->payment_created_at2 }}">{{ $apartment->payment_amount2 . ' (' . $apartment->payment_created_at2 . ')' }}</td>
                                            <td data-sort="{{ $apartment->payment_created_at3 }}">{{ $apartment->payment_amount3 . ' (' . $apartment->payment_created_at3 . ')' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- /Page Wrapper -->

</div>
@endsection

@section('js')
<script>

    /*
    
     I've fixed the sorting issue with a simpler and more
  reliable approach:

  1. Wrapped code in $(document).ready() - Ensures DOM is
  fully loaded before processing
  2. Pre-process data before DataTable initialization - Adds
  data-order attributes to date columns
  3. Extract dates correctly - Converts dates from "Jun-2025
  (2025-07-22)" format to sortable "20250722" format
  4. Uses DataTables' built-in data-order feature - DataTables     
   automatically uses data-order attribute for sorting when        
  present

  The script now:
  - Extracts the date from parentheses (YYYY-MM-DD format)
  - Converts it to a sortable numeric format (YYYYMMDD)
  - Sets this as the data-order attribute on each date cell        
  - DataTables will use these values for sorting automatically     

  This approach is more reliable because it uses DataTables'       
  native sorting mechanism rather than custom sort functions.  
    
    */ 
    // انتظار تحميل الصفحة بالكامل قبل تنفيذ السكريبت
    $(document).ready(function() {
        // دالة لاستخراج التاريخ من النص بتنسيق "شهر-سنة (YYYY-MM-DD)"
        function extractDateValue(text) {
            // البحث عن التاريخ بين الأقواس بتنسيق YYYY-MM-DD
            var match = text.match(/\((\d{4}-\d{2}-\d{2})\)/);
            if (match && match[1]) {
                // تحويل التاريخ إلى رقم للفرز (YYYYMMDD)
                return match[1].replace(/-/g, '');
            }
            // إرجاع قيمة افتراضية منخفضة للسجلات الفارغة
            return '00000000';
        }
        
        // تحضير البيانات للفرز قبل إنشاء DataTable
        $('#exampleTable tbody tr').each(function() {
            // معالجة الأعمدة 4، 5، 6 (الشهور الثلاثة)
            $(this).find('td').eq(4).each(function() {
                var text = $(this).text();
                var sortValue = $(this).attr('data-sort') || extractDateValue(text);
                // إضافة data-order attribute للفرز
                $(this).attr('data-order', sortValue);
            });
            
            $(this).find('td').eq(5).each(function() {
                var text = $(this).text();
                var sortValue = $(this).attr('data-sort') || extractDateValue(text);
                // إضافة data-order attribute للفرز
                $(this).attr('data-order', sortValue);
            });
            
            $(this).find('td').eq(6).each(function() {
                var text = $(this).text();
                var sortValue = $(this).attr('data-sort') || extractDateValue(text);
                // إضافة data-order attribute للفرز
                $(this).attr('data-order', sortValue);
            });
        });
        
        // إنشاء جدول DataTable
        let table = new DataTable('#exampleTable', {
            order: [
                [3, 'desc'] // الفرز الافتراضي حسب المبلغ الإجمالي
            ],
            dom: 'Bfrtip',
            buttons: [],
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
    });


    $(document).on('click', '.btn-export', function(e) {
        const date = new Date();

        var selectedMonth = $('#transaction_month').val();
        var exportUrl = "{{ route('export-transactions') }}";
        // Append the selectedMonth to the exportUrl
        if (exportUrl.indexOf('?') !== -1) {
            exportUrl += '&transaction_month=' + selectedMonth;
        } else {
            exportUrl += '?transaction_month=' + selectedMonth;
        }
        $(this).attr('href', exportUrl + `&?v=${date}`);
    });
</script>

@endsection