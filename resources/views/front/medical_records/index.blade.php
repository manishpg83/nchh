@extends('account.layouts.master')

@section('content')
<section class="section medical_record_container">
    <div class="section-header">
        <h1>{{$pageTitle}}</h1>
        <div class="section-header-breadcrumb">
        <div class="breadcrumb-item active"><a href="{{ url('/') }}">Home</a></div>
            <div class="breadcrumb-item">{{$pageTitle}}</div>
        </div>
    </div>
    <div class="section-body">
        <div class="row">
            <div class="col-md-12">
                <a href="{{route('medical_record.create')}}" class="btn btn-icon icon-left btn-primary mb-3 float-right"><i class="fas fa-plus"></i>Add</a>
            </div>
            <div class="col-12">
                <div class="activities">

                    @forelse($medicalRocord as $record)
                    <div class="activity">
                        <div class="activity-detail w-100">
                            <div class="row">
                                <div class="col-12 mb-2">
                                    <span class="text-job text-primary">{{$record->record_date}}</span>
                                    <span class="bullet"></span>
                                    <a class="text-job" href="#">{{$record->title}}</a>
                                    <div class="float-right dropdown dropleft">
                                        <a href="#" data-toggle="dropdown"><i class="fas fa-ellipsis-h"></i></a>
                                        <div class="dropdown-menu">
                                            <div class="dropdown-title">Options</div>
                                            <a href="{{route('medical_record.edit',$record->id)}}" class="dropdown-item has-icon"><i class="far fa-edit"></i> Edit</a>
                                            <div class="dropdown-divider"></div>
                                            <a href="javascript:;" class="dropdown-item has-icon text-danger" data-confirm="Wait, wait, wait...|Are you sure want to delete record?" data-confirm-text-yes="Yes, Delete" data-id="{{$record->id}}" onclick="deleteMedicalRecord(this,'{{$record->id}}')"><i class="fas fa-trash-alt"></i> Delete</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <p>Record for {{$record->record_for}}</p>
                                    <p class="text-success">Medical {{$record->type}}</p>
                                </div>
                                <div class="col-6 float-right">
                                    @if($record->files)
                                    <div class="gallery">
                                        @foreach($record->files as $file)
                                        <div class="gallery-item" data-image="{{$file->filename}}" data-title="{{$record->title}}"></div>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-lg-12 col-md-12">
                        <div class="card">
                            <h5 class="text-warning m-5 text-center">No Record Found.</h5>
                        </div>
                    </div>
                    @endforelse

                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal -->
<!-- <div class="modal fade" id="profileModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" mode="center"></div> -->
@endsection
@section('scripts')
<script src="{{ asset('account/js/page/medical_record.js')}}"></script>
<script type="text/javascript">
    var MedicalRecordContainer = $('.medical_record_container');
    var medicalRecordCreateUrl = "{{Route('medical_record.create')}}";
    var deleteMedicalRecordUrl = "{{Route('medical_record.destroy',':slug')}}";
</script>
@endsection