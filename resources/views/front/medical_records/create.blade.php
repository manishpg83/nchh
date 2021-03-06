@extends('account.layouts.master')

@section('content')
<section class="section user-profile-container">
    <div class="section-header">
        <h1>{{$pageTitle}}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{route('medical_record.index')}}">Medical Record</a></div>
            <div class="breadcrumb-item">{{$pageTitle}}</div>
        </div>
    </div>
    <div class="section-body">
        <div class="row">
            <div class="col-12 col-sm-12 col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form id="medicalRecordForm" action="{{route('medical_record.store')}}" method="POST"
                            enctype="multipart/form-data">
                            @csrf

                            <div class="form-group row mb-0">

                                <div class="col-md-6 col-sm-12 mb-4">
                                    <label for="title">Title<span class="text-danger">*</span></label>
                                    <input type="text" name="title" class="form-control" value="">
                                    @error('title')
                                    <label id="title-error" class="error" for="title">{{ $message }}</label>
                                    @enderror
                                </div>

                                <div class="col-md-6 col-sm-12 mb-4">
                                    <label for="record_for">Record For<span class="text-danger">*</span></label>
                                    <input type="text" name="record_for" class="form-control"
                                        value="{{isset(Auth::user()->name) ? Auth::user()->name : ''}}">
                                    @error('record_for')
                                    <label id="record_for-error" class="error" for="record_for">{{ $message }}</label>
                                    @enderror
                                </div>

                                <div class="col-md-6 col-sm-12 mb-4">
                                    <label for="record_date">Date<span class="text-danger">*</span></label>
                                    <input type="date" name="record_date" class="form-control" data-date="yyyy-mm-dd"
                                        value="" max="{{today()->format('Y-m-d')}}" id="medical-date">
                                    @error('record_date')
                                    <label id="record_date-error" class="error" for="record_date">{{ $message }}</label>
                                    @enderror
                                </div>

                                <div class="col-md-6 col-sm-12 mb-4">
                                    <label for="email">Type of record<span class="text-danger">*</span></label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item text-center">
                                            <input type="radio" name="type" value="Report" class="selectgroup-input"
                                                checked>
                                            <span class="selectgroup-button selectgroup-button-icon">
                                                <i class="fas fa-file-medical"></i>
                                            </span>
                                            <strong>Report</strong>
                                        </label>
                                        <label class="selectgroup-item text-center">
                                            <input type="radio" name="type" value="Prescription"
                                                class="selectgroup-input">
                                            <span class="selectgroup-button selectgroup-button-icon">
                                                <i class="far fa-file"></i>
                                            </span>
                                            <strong>Prescription</strong>
                                        </label>
                                        <label class="selectgroup-item text-center">
                                            <input type="radio" name="type" value="Invoice" class="selectgroup-input">
                                            <span class="selectgroup-button selectgroup-button-icon">
                                                <i class="fas fa-money-bill"></i>
                                            </span>
                                            <strong>Invoice</strong>
                                        </label>
                                    </div>
                                    @error('record_date')
                                    <label id="record_date-error" class="error" for="record_date">{{ $message }}</label>
                                    @enderror
                                </div>

                            </div>

                            <div class="card-header pl-0">
                                <h4>Files</h4>
                            </div>

                            <div class="form-group row neucrad_fileuploader">
                                <div class="col-md-12 col-sm-12 mb-4">
                                    <div id="files" class="dropzone">
                                        <div class="dz-message">Drop image here or click to Upload</div>
                                    </div>
                                    <div class="showFileValidationError"></div>
                                </div>
                            </div>

                            <div class="form-group row mb-4">
                                <div class="col-sm-12 col-md-7">
                                    <button type="submit" class="btn btn-primary" id="btn_submit">Submit</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
@section('scripts')
<script type="text/javascript" src="{{ asset('account/js/page/medical_record.js')}}"></script>
<script type="text/javascript">
var medicalRecordForm = $('#medicalRecordForm');
</script>
@endsection