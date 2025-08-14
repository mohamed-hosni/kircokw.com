@extends('admin.layout.master')
@section('content')

@push('styles')
<link rel="stylesheet" href="{{ asset('admin_assets/css/pages/posts.css') }}">
@endpush

<body>
    <div class="page-wrapper">
        <header id="header" class="post_page_titles">
            <h3 class="post_page_title_text">{{__('pages.add_property')}}</h3>
            @if(Auth::user()->isSuperAdmin())
            <a class="post_btn create_new_btn" href="{{route('posts.insert.show')}}">{{__('pages.create_new')}}</a>
            @endif
        </header>
        <div class="post-row">
            @foreach($posts as $key => $post)
            <div class="col-lg-4 col-md-6">

                <div class="post-card">
                    <a href="{{route('posts.details', ['post_id'=> $post['id']])}}" id="carouselPostImages{{$key}}" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000" data-bs-wrap="true">
                        <div class="carousel-inner">
                            @if(count($post['images']) > 0)
                            @foreach($post['images'] as $index => $image)
                            <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                                <img src="{{ asset($image) }}" class="post-card-img-top" alt="Property Image">
                            </div>
                            @endforeach
                            @else
                            <div class="carousel-item active">
                                <img src="{{ asset('admin_assets/images/placeholder-image.jpg') }}" class="post-card-img-top" alt="Property Image">
                            </div>
                            @endif
                        </div>

                        <!-- Side Navigation buttons -->
                        <!-- <button class="carousel-nav-btn prev" onclick="moveSlide('carouselPostImages{{$key}}', 'prev')">
                            <i class="ti-angle-left"></i>
                        </button>

                        <button class="carousel-nav-btn next" onclick="moveSlide('carouselPostImages{{$key}}', 'next')">
                            <i class="ti-angle-right"></i>
                        </button> -->
                    </a>


                    <div class="card-img-overlay" style="pointer-events: none;">
                        <span class="badge bg-danger rounded-pill">للايجار</span>
                    </div>
                    <div class="card-body bg-light">
                        <h4 class="card-title">{{ $post['location'] }}</h4>
                        <h4 class="text-primary">{{ $post['price'] }} د.ك</h4>
                    </div>
                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center justify-content-between w-100">
                            <div class="d-flex align-items-center">
                                <span><img src="{{ asset('icons/pro-bath.png')}}"></span>
                                <span class="p-10 text-muted">{{ __('pages.bathrooms')}}</span>
                            </div>
                            <span class="badge rounded-pill number-pill">{{ $post['bathrooms']  ?? 0 }}</span>
                        </div>
                        <div class="d-flex no-block align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <span><img src="{{ asset('icons/pro-bed.png')}}"></span>
                                <span class="p-10 text-muted">{{ __('pages.bedrooms')}}</span>
                            </div>
                            <span class="badge rounded-pill number-pill">{{ $post['bedrooms']  ?? 0  }}</span>
                        </div>
                        <div class="d-flex no-block align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <span><img src="{{ asset('icons/pro-garage.png')}}"></span>
                                <span class="p-10 text-muted">{{ __('pages.garages')}}</span>
                            </div>
                            <span class="badge rounded-pill number-pill">{{ $post['garages'] ?? 0 }}</span>
                        </div>
                        <div class="d-flex no-block align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <span><i class="ti-ruler-alt"></i></span>
                                <span class="p-10 text-muted">{{ __('pages.area')}}</span>
                            </div>
                            <span class="badge rounded-pill number-pill">{{ $post['area']  ?? 0 }}</span>
                        </div>
                    </div>
                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center justify-content-between">
                            <div>
                                <h5 class="card-title m-b-0">{{$post['user_name']}}</h5>
                                <small class="text-muted">{{$post['title']}}</small>
                            </div>

                            @if(Auth::user()->isSuperAdmin())
                            <div class="d-flex justify-self-end gap-2">
                                <a data-bs-toggle="modal" href="#" class="btn btn-sm bg-danger-light btn_delete-post" route="{{ route('posts.delete',['post_id' => $post['id']])}}">
                                    <i class="ti-trash"></i> {{ __('pages.delete') }}
                                </a>
                                <a
                                    href="{{ route('posts.edit.show', ['post_id' => $post['id'] ]) }}"
                                    class="btn btn-sm bg-success-light">
                                    <i class="ti-pencil"></i> {{ __('pages.edit') }}
                                </a>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            @endforeach

        </div>

        <div id="edit_post" class="modal fade">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modelHeading">تعديل المقال</h4>
                        <span class="button" data-dismiss="modal" aria-label="Close"><i class="ti-close"></i></span>
                    </div>
                    <div class="modal-body">
                        <form method="post" enctype="multipart/form-data" action="{{ route('posts.edit') }}" class="ajax-form" swalOnSuccess="{{ __('pages.sucessdata') }}" title="{{ __('pages.opps') }}" swalOnFail="{{ __('pages.wrongdata') }}" redirect="{{ route('posts') }}">
                            @csrf
                            <input type="hidden" name="post_id" id="post_id">
                            <div class="form-group">
                                <label for="name" class="col-md-12 col-sm-12 mb-2 control-label">{{ __('pages.whats_on_your_mind')}}</label>
                                <div class="col-sm-12">
                                    <textarea id="post_content" name="post_content" value="" class="form-control" rows="4" placeholder="{{ __('pages.whats_on_your_mind')}}"></textarea>
                                </div>
                            </div>

                            <div class="form-group">
                                <button type="button" class="btn btn-light" onclick="document.getElementById('imageInputModal').click();">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M21 19V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2ZM8.414 13.586l2.5-2.5a1 1 0 0 1 1.414 0l3.5 3.5 2.5-2.5A1 1 0 0 1 20 12v5H6v-1.586l2.414-2.414ZM8 8a2 2 0 1 1 4 0 2 2 0 0 1-4 0Z"></path>
                                    </svg>
                                </button>
                                <input type="file" name="images[]" id="imageInputModal" class="d-none" onchange="previewImages(event, 'image')" multiple>
                                <div id="image" class="d-flex flex-wrap mt-2"></div>
                            </div>
                            <div class="col-sm-offset-2 col-sm-12 text-center">
                                <button type="submit" class="btn btn-primary" id="saveBtn" value="create">
                                    {{ __('pages.save') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>`
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        function moveSlide(carouselId, direction) {
            const carousel = document.getElementById(carouselId);
            const bsCarousel = bootstrap.Carousel.getInstance(carousel);

            if (direction === 'prev') {
                bsCarousel.prev();
            } else {
                bsCarousel.next();
            }
        }

        function toggleAutoplay(carouselId) {
            const carousel = document.getElementById(carouselId);
            const bsCarousel = bootstrap.Carousel.getInstance(carousel);
            const key = carouselId.replace('carouselPostImages', '');
            const icon = document.getElementById('playPauseIcon' + key);

            if (carousel.dataset.bsInterval) {
                // Pause the carousel
                bsCarousel.pause();
                carousel.dataset.bsInterval = '';
                icon.classList.remove('fa-pause');
                icon.classList.add('fa-play');
            } else {
                // Resume the carousel
                carousel.dataset.bsInterval = '3000';
                bsCarousel.cycle();
                icon.classList.remove('fa-play');
                icon.classList.add('fa-pause');
            }
        }

        // Initialize all carousels
        document.addEventListener('DOMContentLoaded', function() {
            const carousels = document.querySelectorAll('.carousel');
            carousels.forEach(carousel => {
                new bootstrap.Carousel(carousel, {
                    interval: 3000,
                    wrap: true
                });
            });
        });
    </script>

    <script>
        let allSelectedFiles = [];

        function previewImages(event, id) {
            const newFiles = Array.from(event.target.files);
            const previewContainer = document.getElementById(id);

            allSelectedFiles = allSelectedFiles.concat(newFiles);

            const dataTransfer = new DataTransfer();
            allSelectedFiles.forEach(file => {
                dataTransfer.items.add(file);
            });

            event.target.files = dataTransfer.files;
            previewContainer.innerHTML = '';

            allSelectedFiles.forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    let imageWrapper = document.createElement("div");
                    imageWrapper.classList.add("position-relative", "mr-2", "mb-2");
                    imageWrapper.style.display = "inline-block";

                    let img = document.createElement("img");
                    img.src = e.target.result;
                    img.style.width = '50px';
                    img.style.height = '50px';
                    img.style.objectFit = 'cover';

                    let removeBtn = document.createElement("button");
                    removeBtn.innerHTML = "&times;";
                    removeBtn.type = "button";
                    removeBtn.classList.add("btn", "btn-danger", "btn-sm", "position-absolute");
                    removeBtn.style.top = "0";
                    removeBtn.style.right = "0";
                    removeBtn.style.padding = "2px 6px";
                    removeBtn.style.borderRadius = "50%";
                    removeBtn.onclick = function() {
                        imageWrapper.remove();
                    };

                    imageWrapper.appendChild(img);
                    imageWrapper.appendChild(removeBtn);
                    previewContainer.appendChild(imageWrapper);
                };
                reader.readAsDataURL(file);
            });
        }

        function edit_post(el) {
            var link = $(el);
            var modal = $("#edit_post");
            var post_id = link.data('id');
            var post_content = link.data('post_content');
            var image = link.data('image')
            modal.find('#post_id').val(post_id);
            modal.find('#post_content').val(post_content);
            $("#image").children().remove();

            if (image) {
                image.split(',').forEach((img, index) => {
                    $("#image").append(`
                        <div class="position-relative mr-2 mb-2" style="display: inline-block;">
                            <img src="${img}" style="width: 50px; height: 50px; object-fit: cover;">
                            <button type="button" class="btn btn-danger btn-sm position-absolute" style="top: 0; right: 0; padding: 2px 6px; border-radius: 50%;" onclick="deleteFunction(event)">&times;</button>
                            <input type="hidden" id="image_${index}" name="old_images[]" value="${'post_images' + img.split('/post_images')[1]}">
                        </div>
                    `);
                });
            }
        }

        function deleteFunction(event) {
            event.target.parentElement.remove();
        }
    </script>
    <script>
        $('.btn_delete-post').on('click', function() {
            Swal.fire({
                title: '{{ __("pages.slow_down") }}',
                text: "{{ __('pages.the_deleted_data_cant_be_restored') }}",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '{{ __("pages.confirm") }}',
                cancelButtonText: '{{ __("pages.cancel") }}'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        icon: 'success',
                        title: '{{ __("pages.your_file_has_been_deleted") }}',
                    });

                    $($(this).siblings().eq(0)).remove();
                    $(this).remove();
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        url: $(this).attr('route'),
                        method: 'POST',
                        success: function() {
                            window.location.reload();
                        }
                    });
                }
            });
        });
    </script>
</body>
@endsection