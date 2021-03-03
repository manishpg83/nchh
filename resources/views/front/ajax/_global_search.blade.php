@extends('layouts.app')

@section('content')

<section class="bg-grey pt-5 global_search_page">
    <div class="container-fluid">

        <div class="row">
            <div class="col-md-3 col-sm-12">
                <div class="sidebar-item mt-5">
                    <div class="make-me-sticky">
                        <div class="card">
                            <h5 class="card-header">Filter by</h5>
                            <div class="card-body p-0">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">
                                        <section class="filter-item-inner">
                                            <!-- <h6 class="filter-item-inner-heading minus">
                                                Search by keyword
                                            </h6> -->
                                            <div class="form-group has-search m-0">
                                                <i class="ion-ios-search"></i>
                                                <input type="text" class="form-control" id="txt_search" placeholder="Search..." value="{{isset($keyword) ? $keyword : ''}}">
                                            </div>
                                            <!-- <input type="search" class="search_by_keyword" placeholder="Search..."> -->
                                        </section>
                                    </li>
                                    <li class="list-group-item">
                                        <section class="filter-item-inner">
                                            <h6 class="filter-item-inner-heading minus">Consultant Fees</h6>
                                            <input type="text" class="consultant_fees_slider" data-slider-min="0" data-slider-max="5000" data-slider-step="50" data-slider-value="0" />
                                        </section>
                                    </li>
                                    <li class="list-group-item">
                                        <section class="filter-item-inner">
                                            <h6 class="filter-item-inner-heading minus">
                                                Consult As
                                            </h6>
                                            <ul class="filter-attribute-list ul-reset">
                                                <div class="filter-attribute-list-inner">
                                                    <li class="filter-attribute-item">
                                                        <input type="checkbox" class="common_selector consult_as" value="ONLINE">
                                                        <label for="online">Video Consultation</label>
                                                    </li>
                                                    <li class="filter-attribute-item">
                                                        <input type="checkbox" class="common_selector consult_as" value="INPERSON">
                                                        <label for="inperson">Appointemnt</label>
                                                    </li>
                                                    <li class="filter-attribute-item">
                                                        <input type="checkbox" class="common_selector consult_as" value="BOTH">
                                                        <label for="both">Both</label>
                                                    </li>
                                                </div>
                                            </ul>
                                        </section>
                                    </li>
                                    <li class="list-group-item">
                                        <section class="filter-item-inner">
                                            <h6 class="filter-item-inner-heading minus">
                                                Gender
                                            </h6>
                                            <ul class="filter-attribute-list ul-reset">
                                                <div class="filter-attribute-list-inner">
                                                    <li class="filter-attribute-item">
                                                        <input type="checkbox" class="common_selector gender" value="Male">
                                                        <label for="male">Male</label>
                                                    </li>
                                                    <li class="filter-attribute-item">
                                                        <input type="checkbox" class="common_selector gender" value="Female">
                                                        <label for="female">Female</label>
                                                    </li>
                                                    <li class="filter-attribute-item">
                                                        <input type="checkbox" class="common_selector gender" value="Other">
                                                        <label for="other">Other</label>
                                                    </li>
                                                </div>
                                            </ul>
                                        </section>
                                    </li>
                                    <!-- <li class="list-group-item">Vestibulum at eros</li> -->
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-9 mt-5">

                <div class="search-bar-section d-md-block d-none">
                    <div class="search-bar-info mb-3 p-3">
                        <div class="search-string-div pb-md-0 pb-3">
                            @if (Session::has('search_string'))
                            {!! Session::get('search_string') !!}
                            {{session()->forget('search_string')}}
                            @endif
                        </div>
                    </div>
                </div>

                <div class="content-section search_listing">
                    @include('front.ajax._load_result')
                </div>

                <div class="ajax-load text-center bg-white" style="display:none">
                    <p><img src="{{asset('images/loader/loader.gif')}}" width="50">Loading More Product</p>
                </div>
            </div>

        </div>
    </div>
</section>


@endsection
@section('page_script')
<script src="{{ asset('js/page/search.js')}}"></script>
<script type="text/javascript">
    var page = 1;
    var flag = 1;
    var max_page = "{{isset($last_page) ? $last_page : 0}}";
    var manageWishlistUrl = "{{Route('user.manage.wishlist')}}";
    var renderview = $('.search_listing');
    var outerdiv = $('.container-fluid');
    var keyword = "{{isset($keyword) ? $keyword : ''}}";
</script>
@endsection