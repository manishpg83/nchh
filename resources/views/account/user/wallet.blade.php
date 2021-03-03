@extends('account.layouts.master')

@section('content')
<section class="section user-profile-container">
    <div class="section-header">
        <h1>{{$pageTitle}}</h1>
        <div class="section-header-breadcrumb">
        <div class="breadcrumb-item active"><a href="{{ url('/') }}">Home</a></div>
            <div class="breadcrumb-item">{{$pageTitle}}</div>
        </div>
    </div>
    <div class="section-body">
        <!-- <h2 class="section-title">Great Progress!</h2>
        <p class="section-lead">
        Your profile is just few steps away from going live.
        </p>
        -->
        <div class="row">
            <div class="col-12 col-sm-12 col-lg-12">
                <div class="card">
                    <div>
                        <span class="btn btn-icon icon-left float-left mt-3 ml-3"><strong><h4>Total Balance : <span class="text-success">₹ <span id="total_balance">{{$total_balance}}</span></span></h4></strong> </span>
                        <a href="#" class="btn btn-icon icon-left btn-primary float-right mt-3 mr-4" onclick="withdrawBalance()">Withdraw</a>
                        <span class="btn btn-icon icon-left float-right mt-3 mr-4"><strong><h6>Withdrawable Balance : <span class="text-success">₹ <span id="withdrawable_balance">{{$withdrawable_balance}}</span></span></h6></strong> </span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered nowrap" id="walletTable" width="100%">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Name</th>
                                        <th>Date</th>
                                        <th>Price</th>
                                        <th>Status</th>
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
    </div>
</section>

<!-- Modal -->
<div class="modal fade" id="walletModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" mode="center" data-backdrop="static" data-keyboard="false"></div>
@endsection
@section('scripts')
<script src="{{ asset('account/js/page/wallet.js')}}"></script>
<script type="text/javascript">
    var walletTable;

    //url
    var getWalletList = "{{Route('account.user.wallet')}}"; 
    var withdrawBalanceUrl = "{{Route('account.user.wallet.balance.withdraw')}}"; 
</script>
@endsection