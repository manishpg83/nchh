@extends('account.layouts.master')

@section('content')
<section class="section user-profile-container">
    <div class="section-header">
        <h1>{{$pageTitle}}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ url('/') }}">Home</a></div>
            <div class="breadcrumb-item active"><a href="{{ route('account.agent.refferal.users') }}">Refferal Users</a></div>
            <div class="breadcrumb-item">{{$pageTitle}}</div>
        </div>
    </div>
    <div class="section-body">
        <div class="row">
            <div class="col-12 col-sm-12 col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <table class="table">
                            <!-- <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">First</th>
                                    <th scope="col">Last</th>
                                    <th scope="col">Handle</th>
                                </tr>
                            </thead> -->
                            <tbody>
                                <tr scope="row">
                                    <td><b>Profile Picture</b></td>
                                    <td><img src="{{$user->profile_picture}}" width="10%" class="rounded-circle"></td>
                                </tr>
                                <tr scope="row">
                                    <td><b>Name</b></td>
                                    <td>{{$user->name}}</td>
                                </tr>
                                <tr>
                                    <td><b>Email</b></td>
                                    <td>{{$user->email ? $user->email : 'Not mentioned'}}</td>
                                </tr>
                                <tr>
                                    <td><b>Contact No</b></td>
                                    <td>{{$user->phone}}</td>
                                </tr>
                                <tr>
                                    <td><b>Gender</b></td>
                                    <td>{{$user->gender ? $user->gender : 'Not mentioned'}}</td>
                                </tr>
                                <tr>
                                    <td><b>DOB</b></td>
                                    <td>{{$user->dob ? $user->dob : 'Not mentioned'}}</td>
                                </tr>
                                <tr>
                                    <td><b>TimeZone</b></td>
                                    <td>{{$user->timezone ? $user->timezone : 'Not mentioned'}}</td>
                                </tr>
                                <tr>
                                    <td><b>Address</b></td>
                                    <td>{{$user->address ? $user->address : 'Not mentioned'}}</td>
                                </tr>
                                
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection