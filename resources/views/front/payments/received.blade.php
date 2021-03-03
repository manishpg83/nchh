@extends('account.layouts.master')

@section('content')
<section class="section user-profile-container">
    <div class="section-header">
        <h1>{{$pageTitle}}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="#">Dashboard</a></div>
            <div class="breadcrumb-item">{{$pageTitle}}</div>
        </div>
    </div>
    <h2 class="section-title">Overview</h2>
    <p class="section-lead">
        {{$content}}
    </p>
    <div class="section-body">
        <div class="row">
            <div class="col-12 col-sm-12 col-lg-12">
                <div class="card">
                    <div class="col-sm-12">
                        @if(session()->get('success'))
                        <div class="alert alert-success">
                            {{ session()->get('success') }}
                        </div>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered nowrap" id="paymentTable" width="100%">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Patient</th>
                                        <th>Invoice ID</th>
                                        <th>Receipt ID</th>
                                        <th>Order ID</th>
                                        <th>Payment ID</th>
                                        <th>Payment Date</th>
                                        <th>Amount</th>
                                        <th>Discount</th>
                                        <th>Refund</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" mode="center"></div>
@endsection
@section('scripts')
<script src="{{ asset('account/js/page/payment.js')}}"></script>
<script type="text/javascript">
    var paymentTable;
    var paymentModel = $('#paymentModal');
    var receivedPaymentList = "{{Route('payment.received')}}";
    var viewPaymentDetailUrl = "{{Route('payment.show',':slug')}}";
</script>
@endsection