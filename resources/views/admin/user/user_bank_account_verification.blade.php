@extends('admin.layouts.master')

@section('content')
<section class="section user-profile-container">
    <div class="section-header">
        <h1>{{$pageTitle}}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{route('admin.dashboard')}}">Dashboard</a></div>
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
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered nowrap" id="userBankAccountVerificationTable"
                                width="100%">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Name</th>
                                        <th>Bank Name</th>
                                        <th>Account Number</th>
                                        <th>IFSC Code</th>
                                        <th>Type</th>
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
    </div>
</section>


<!-- Modal -->
<div class="modal fade" id="dataTableModal" role="dialog"></div>

<div class="modal fade" tabindex="-1" role="dialog" id="rejectUserAccount" aria-hidden="true">       
    <div class="modal-dialog modal-md" role="document">         
        <div class="modal-content">           
            <div class="modal-header">             
                <h5 class="modal-title">Reject User Account</h5>             
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">               
                    <span aria-hidden="true">×</span>             
                </button>           
            </div>           
            <div class="modal-body">           
                <form id="rejectAccount" method="post" action="{{Route('admin.bank.account.verify')}}">@csrf
                    <input type="hidden" name="bank_account_id" id="bank_account_id">
                    <div class="form-group">
                        <label>Reason</label>
                        <div class="input-group">
                            <textarea name="rejection_reason" id="rejection_reason" class="form-control" placeholder="Reason"></textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" id="btn_submit">Submit</button>
                </form>
            </div>           
            <div class="modal-footer bg-whitesmoke">           
            </div>         
        </div>       
    </div>    
</div>
@endsection
@section('page_script')
<script type="text/javascript">
var userBankAccountVerificationTable;
var dataTableModal = $('#dataTableModal');
var rejectAccountForm = $('#rejectAccount');

var getBankAccountVerificationList = "{{Route('admin.bank.account.verification')}}";
var verifyBankAccountUrl = "{{Route('admin.bank.account.verify')}}";
</script>
<script type="text/javascript" src="{{asset('admin/js/datatable.js')}}"></script>
@endsection