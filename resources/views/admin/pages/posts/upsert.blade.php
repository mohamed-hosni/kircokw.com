@extends('admin.layout.master')
@section('content')

@push('styles')
<link rel="stylesheet" href="{{ asset('admin_assets/css/pages/posts.css') }}">
@endpush

<body>
    <div class="page-wrapper">
        <header id="header" class="post_page_titles">
            <h3 class="post_page_title_text">{{isset($post) ? __('pages.edit_property') : __('pages.add_property')}}</h3>
        </header>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body ">
                            <form class="post-add-form" method="POST" action="{{ isset($post) ? route('posts.edit') : route('posts.insert') }}" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="post_id" value="{{ $post->id ?? '' }}">
                                <div class="post-form-group">
                                    <label class="post_page_title_text" for="pname">{{__('pages.property_name')}}</label>
                                    <input name="title" type="text" class="post-input" id="pname" placeholder="{{__('pages.enter_name')}}" value="{{ $post->title ?? '' }}">
                                </div>
                                <div class="post-form-group">
                                    <label class="post_page_title_text" for="plocation">{{__('pages.property_location')}}</label>
                                    <input name="location" type="text" class="post-input" id="plocation" placeholder="{{__('pages.enter_location')}}" value="{{ $post->location ?? '' }}">
                                </div>
                                <div class="post-form-group">
                                    <label class="post_page_title_text" for="pdesc">{{__('pages.property_description')}}</label>
                                    <textarea name="post_content" class="post-input post-area" rows="5" id="pdesc" placeholder="{{__('pages.enter_description')}}">{{ $post->post_content ?? '' }}</textarea>
                                </div>
                                <div class="post-form-group" style="display: none;">
                                    <label class="post_page_title_text">{{__('pages.property_for')}}</label>
                                    <div class="col-md-9">
                                        <div class="post_page_radio_group">
                                            <div class="post_page_radio">
                                                <input type="radio" id="customRadio3" name="payment_type" value="1" class="form-check-input" {{ isset($post->payment_type) && $post->payment_type == 1 ? 'checked' : '' }}>
                                                <label class="post_page_title_text" for="customRadio3">{{__('pages.for_rent')}}</label>
                                            </div>
                                            <div class="post_page_radio">
                                                <input type="radio" id="customRadio4" name="payment_type" value="0" class="form-check-input" {{ isset($post->payment_type) && $post->payment_type == 0 ? 'checked' : '' }}>
                                                <label class="post_page_title_text" for="customRadio4">{{__('pages.for_sale')}}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="post-form-group">
                                    <label for="plocation">{{__('pages.price_rent')}}</label>
                                    <input name="price" type="text" class="post-input" id="plocation" placeholder="{{__('pages.enter_number')}}" value="{{ $post->price ?? '' }}">
                                </div>
                                <div class="post-form-group">
                                    <label for="paddress">{{__('pages.property_address')}}</label>
                                    <textarea name='address' class="post-input post-area" rows="3" id="paddress">{{ $post->address ?? '' }}</textarea>
                                </div>
                                <div class="post-form-group">
                                    <div class="row">
                                        <div class="col-sm-4 post-form-group">
                                            <label for="tch1">{{__('pages.bedrooms')}}</label>
                                            <div class="number-input-container">
                                                <button class="decrement" onclick="decrement(event)">-</button>
                                                <input class="sq-ft-input" name="bedrooms" type="number" id="bedroomInput" value="{{ $post->bedrooms ?? 0 }}" min="0">
                                                <button class="increment" onclick="increment(event)">+</button>
                                            </div>
                                        </div>
                                        <div class="col-sm-4 post-form-group">
                                            <label for="tch2">{{__('pages.garages')}}</label>
                                            <div class="number-input-container">
                                                <button class="decrement" onclick="decrement(event)">-</button>
                                                <input class="sq-ft-input" name="garages" type="number" id="garageInput" value="{{ $post->garages ?? 0 }}" min="0">
                                                <button class="increment" onclick="increment(event)">+</button>
                                            </div>
                                        </div>
                                        <div class="col-sm-4 post-form-group">
                                            <label for="tch3">{{__('pages.bathrooms')}}</label>
                                            <div class="number-input-container">
                                                <button class="decrement" onclick="decrement(event)">-</button>
                                                <input class="sq-foot-input" name="bathrooms" type="number" id="bathrooms" value="{{ $post->bathrooms ?? 0 }}" min="0">
                                                <button class="increment" onclick="increment(event)">+</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="post-form-group">
                                    <div class="row">
                                        <div class="col-sm-4 post-form-group">
                                            <label for="tch2">{{__('pages.half_baths')}}</label>
                                            <div class="number-input-container">
                                                <button class="decrement" onclick="decrement(event)">-</button>
                                                <input class="sq-ft-input" name="garages" type="number" id="garageInput" value="{{ $post->garages ?? 0 }}" min="0">
                                                <button class="increment" onclick="increment(event)">+</button>
                                            </div>
                                        </div>
                                        <div class="col-sm-4 post-form-group">
                                            <label for="tch3">{{__('pages.full_baths')}}</label>
                                            <div class="number-input-container">
                                                <button class="decrement" onclick="decrement(event)">-</button>
                                                <input class="sq-ft-input" name="bathrooms" type="number" id="bathrooms" value="{{ $post->bathrooms ?? 0 }}" min="0">
                                                <button class="increment" onclick="increment(event)">+</button>
                                            </div>
                                        </div>
                                        <div class="col-sm-4 post-form-group">
                                            <label for="psqft">{{__('pages.square_ft')}}</label>
                                            <input name="area" type="text" id="halfBathInput" class="sq-ft-input" value="{{ $post->area ?? '' }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-sm-4 post-form-group">
                                            <label for="pyear">{{__('pages.year_built')}}</label>
                                            <input name="year_built" type="text" id="halfBathInput" class="sq-ft-input" value="{{ $post->year_built ?? '' }}">
                                        </div>
                                        <div class="col-sm-4 post-form-group">
                                            <label for="type">{{__('pages.type')}}</label>
                                            <input name="type" type="text" id="halfBathInput" class="sq-ft-input" value="{{ $post->type ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                                <!-- Display existing images -->

                                <h3 class="card-title post_page_title_text mb-2">{{__('pages.dimensions')}}</h5>
                                <hr>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <label for="dining_room">{{__('pages.dining_room')}}</label>
                                            <input name="dining_room" type="text" id="halfBathInput" class="sq-ft-input" value="{{ $post->dining_room ?? '' }}" data-mask="99x99">
                                        </div>
                                        <div class="col-sm-4">
                                            <label for="kitchen">{{__('pages.kitchen')}}</label>
                                            <input name="kitchen" type="text" id="halfBathInput" class="sq-ft-input" value="{{ $post->kitchen ?? '' }}" data-mask="99x99">
                                        </div>
                                        <div class="col-sm-4">
                                            <label for="living_room">{{__('pages.living_room')}}</label>
                                            <input name="living_room" type="text" id="halfBathInput" class="sq-ft-input" value="{{ $post->living_room ?? '' }}" data-mask="99x99">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-sm-4 post-form-group">
                                            <label for="master_bedroom">{{__('pages.master_bedroom')}}</label>
                                            <input name="master_bedroom" type="text" id="halfBathInput" class="sq-ft-input" value="{{ $post->master_bedroom ?? '' }}" data-mask="99x99">
                                        </div>
                                        <div class="col-sm-4 post-form-group">
                                            <label for="bedroom_two">{{__('pages.bedroom_two')}}</label>
                                            <input name="bedroom_two" type="text" id="halfBathInput" class="sq-ft-input" value="{{ $post->bedroom_two ?? '' }}" data-mask="99x99">
                                        </div>
                                        <div class="col-sm-4 post-form-group">
                                            <label for="other_room">{{__('pages.other_room')}}</label>
                                            <input name="other_room" type="text" id="halfBathInput" class="sq-ft-input" value="{{ $post->other_room ?? '' }}" data-mask="99x99">
                                        </div>
                                    </div>
                                </div>

                                @if(isset($post->gallaryImages) && count($post->gallaryImages) > 0)
                                <div class="mb-3">
                                    <div class="row">
                                        @foreach($post->gallaryImages as $existingImage)
                                        <div class="col-md-3 text-center mb-3" style="position: relative;">
                                            <!-- Show the image thumbnail -->
                                            <img src="{{ asset($existingImage->name ?? $existingImage) }}"
                                                class="img-fluid img-thumbnail post-remaining-img"
                                                alt="Existing Image">

                                            <button type="button" data-image-id="{{ $existingImage->id ?? $loop->index }}"
                                                class="btn btn-danger btn-sm position-absolute remove-image-btn" style="top: 0; right: 0; padding: 2px 6px; border-radius: 50%;">&times;</button>


                                            <!-- Hidden field to keep track of this image if it’s not removed -->
                                            <input type="hidden" name="remaining_images[]" value="{{ $existingImage->id ?? $existingImage }}">
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif

                                <!-- Dropify input for uploading NEW images -->
                                <div class="post-form-group mb-3">
                                    <label for="images" class="form-label">{{ __('pages.upload_files') }}</label>
                                    <input id="input-file-now"
                                        type="file"
                                        name="images[]"
                                        class="dropify"
                                        multiple
                                        accept="image/*"
                                        data-show-multiple="true"
                                        data-max-file-size="2M"
                                        data-allowed-file-extensions="jpg jpeg png gif" />
                                </div>
                                <div class="d-flex flex-row justify-content-end w-100 gap-1">
                                    <button type="submit" class="post-btn post-submit-btn ">{{isset($post) ? __('pages.edit_property') : __('pages.add_property')}}</button>
                                    <a href="{{route('posts')}}" type="submit" class="post-btn post-cancel-btn ">{{__('pages.cancel')}}</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            document.addEventListener("click", function(event) {
                if (event.target.classList.contains("increment")) {
                    event.preventDefault();
                    let input = event.target.previousElementSibling;
                    if (input && input.type === "number") {
                        input.value = parseInt(input.value) + 1;
                    }
                }

                if (event.target.classList.contains("decrement")) {
                    event.preventDefault();

                    let input = event.target.nextElementSibling;
                    if (input && input.type === "number" && input.value > 0) {
                        input.value = parseInt(input.value) - 1;
                    }
                }
            });
        </script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


        <script>
            $("input[name='tch1']").TouchSpin();
            $("input[name='tch2']").TouchSpin();
            $("input[name='tch3']").TouchSpin();
            $("input[name='tch4']").TouchSpin();
            $("input[name='tch5']").TouchSpin();
            $('.dropify').dropify();
        </script>
        <script type="text/javascript">
            jQuery.browser = {};
            (function() {
                jQuery.browser.msie = false;
                jQuery.browser.version = 0;
                if (navigator.userAgent.match(/MSIE ([0-9]+)\./)) {
                    jQuery.browser.msie = true;
                    jQuery.browser.version = RegExp.$1;
                }
            })();
        </script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Handle remove image button
                const removeButtons = document.querySelectorAll('.remove-image-btn');
                removeButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const parentCol = this.closest('.col-md-3');
                        if (parentCol) {
                            // Remove that image block from the DOM
                            parentCol.remove();
                        }
                    });
                });

                // Initialize Dropify
                $('.dropify').dropify();
            });
        </script>

</body>
@endsection