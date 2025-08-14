@extends('admin.layout.master')
@section('content')

@push('styles')
<link rel="stylesheet" href="{{ asset('admin_assets/css/pages/posts.css') }}">
@endpush

<body>
    <div id="main-wrapper">
        <div class="page-wrapper">
            <!-- ============================================================== -->
            <!-- Container fluid  -->
            <!-- ============================================================== -->
            <div class="container-fluid">
                <!-- ============================================================== -->
                <!-- Bread crumb and right sidebar toggle -->
                <!-- ============================================================== -->
                <div class="row post_page_titles" style="margin-right: -50px;">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor post_page_title_text">{{ __('pages.property_details')}}</h4>
                    </div>
                    <div class="col-md-7 align-self-center text-end">
                        <div class="d-flex justify-content-end align-items-center">
                            <!-- <ol class="breadcrumb justify-content-end">
                                <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                                <li class="breadcrumb-item active">Property Detail</li>
                            </ol> -->
                            @if(Auth::user()->isSuperAdmin())
                            <a class="post_btn create_new_btn " href="{{route('posts.insert.show')}}">{{__('pages.create_new')}}</a>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <div class="card">
                            <div class="card-body">
                                <div id="carouselExampleIndicators2" class="carousel slide" data-bs-ride="carousel">
                                    <ol class="carousel-indicators">
                                        @foreach($post->gallaryImages as $key => $image)
                                        <li data-bs-target="#carouselExampleIndicators2" data-bs-slide-to="{{$key}}" class="{{$key == 0 ? 'active' : ''}}"></li>
                                        @endforeach
                                    </ol>
                                    <div class="carousel-inner" role="listbox">
                                        @foreach($post->gallaryImages as $key => $image)
                                        <div class="carousel-item {{$key == 0 ? 'active' : ''}}">
                                            <img class="post-detail-remaining-img" src="{{asset($image->name)}}" alt="Slide {{$key + 1}}">
                                        </div>
                                        @endforeach
                                    </div>
                                    <a class="carousel-control-prev" href="#carouselExampleIndicators2" role="button" data-bs-slide="prev" data-bs-target="#carouselExampleIndicators2">
                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                        <span class="sr-only">Previous</span>
                                    </a>
                                    <a class="carousel-control-next" href="#carouselExampleIndicators2" role="button" data-bs-slide="next" data-bs-target="#carouselExampleIndicators2">
                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                        <span class="sr-only">Next</span>
                                    </a>
                                </div>
                                <div class="p-t-20 p-b-20">
                                    <h4 class="card-title">{{$post->address}}</h4>
                                    <h5 class="m-b-0"><span class="text-muted"><i class="ti-map-alt text-danger m-r-10" aria-hidden="true"></i>{{$post->location}}</span></h5>
                                </div>
                                <hr class="m-0">
                                <pre class="text-dark p-t-20 pro-desc" style="white-space: pre-wrap; word-wrap: break-word; direction: rtl; text-align: right;">{!! nl2br(e($post->post_content)) !!}</pre>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6 col-md-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="post_page_title_text">{{ __('pages.essential_info')}}</h5>
                                        <div class="table-responsive border-top">
                                            <table class="table no-border">
                                                <tbody class="text-dark">
                                                    <tr>
                                                        <td>{{__('pages.price')}}</td>
                                                        <td>{{$post->price}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>{{__('pages.bedrooms')}}</td>
                                                        <td>{{$post->bedrooms ?? 0}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>{{__('pages.bathrooms')}}</td>
                                                        <td>{{$post->bathrooms ?? 0}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>{{__('pages.full_baths')}}</td>
                                                        <td>{{$post->full_baths ?? 0}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>{{__('pages.half_baths')}}</td>
                                                        <td>{{$post->half_baths ?? 0}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>{{__('pages.area')}}</td>
                                                        <td>{{$post->area ?? 0}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>{{__('pages.year_built')}}</td>
                                                        <td>{{$post->year_built ?? 0}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>{{__('pages.type')}}</td>
                                                        <td>{{$post->type ?? ''}}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-12 h-100">
                                <div class="card h-100">
                                    <div class="card-body h-100">
                                        <h5 class="post_page_title_text">{{ __('pages.room_dimensions')}}</h5>
                                        <div class="table-responsive p-t-10 border-top">
                                            <table class="table no-border">
                                                <tbody class="text-dark">
                                                    <tr>
                                                        <td>{{__('pages.dining_room')}}</td>
                                                        <td>{{$post->dining_room}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>{{__('pages.kitchen')}}</td>
                                                        <td>{{$post->kitchen}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>{{__('pages.living_room')}}</td>
                                                        <td>{{$post->living_room}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>{{__('pages.master_bedroom')}}</td>
                                                        <td>{{$post->master_bedroom}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>{{__('pages.bedroom_two')}}</< /td>
                                                        <td>{{$post->bedroom_two}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>{{__('pages.other_room')}}</td>
                                                        <td>{{$post->other_room}}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ============================================================== -->
            <!-- End Container fluid  -->
            <!-- ============================================================== -->
        </div>
        <!-- ============================================================== -->
        <!-- End Page wrapper  -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- footer -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- End footer -->
        <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- End Wrapper -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- All Jquery -->
    <!-- ============================================================== -->
    <script src="../assets/node_modules/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap tether Core JavaScript -->
    <script src="../assets/node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <!-- slimscrollbar scrollbar JavaScript -->
    <script src="dist/js/perfect-scrollbar.jquery.min.js"></script>
    <!--Wave Effects -->
    <script src="dist/js/waves.js"></script>
    <!--Menu sidebar -->
    <script src="dist/js/sidebarmenu.js"></script>
    <!--stickey kit -->
    <script src="../assets/node_modules/sticky-kit-master/dist/sticky-kit.min.js"></script>
    <script src="../assets/node_modules/sparkline/jquery.sparkline.min.js"></script>
    <!--Custom JavaScript -->
    <script src="dist/js/custom.min.js"></script>

</body>
@endsection