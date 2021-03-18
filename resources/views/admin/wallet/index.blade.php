@extends('admin.layouts.master')

@section('content')
<section class="section user-profile-container">
    <div class="section-header">
        <h1>{{$pageTitle}}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{route('admin.dashboard.index')}}">Dashboard</a></div>
            <div class="breadcrumb-item">{{$pageTitle}}</div>
        </div>
    </div>
    <div class="section-body">
        <div class="row">
            <div class="col-12 col-sm-12 col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="form-group row mb-4">
                            <label class="col-form-label text-md-right col-12 col-md-9 col-lg-9">Users</label>
                            <div class="col-sm-12 col-md-3">
                                <select name='user_name' onchange="javascript:this.form.submit();" class="form-control select2">
                                    <option value="">All</option>
                                    @foreach ($user_wallet as $key => $u)
                                    <option value="{{$u->user_id}}">{{$u->user->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered nowrap" id="walletTable" width="100%">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Name</th>
                                        <th>Patient Name</th>
                                        <th>Date</th>
                                        <th>Amount</th>
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
<div class="modal fade" id="userModal" role="dialog"></div>
@endsection
@section('page_script')
<script type="text/javascript" src="{{asset('admin/js/wallet.js')}}"></script>
<script type="text/javascript">
    var walletTable;

    var getUserWalletList = "{{Route('admin.wallet.index')}}";
</script>
@endsection