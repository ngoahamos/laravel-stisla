@extends('layouts.app')

@section('title', 'Profile')

@push('style')
    <!-- CSS Libraries -->
@endpush

@section('main')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Profile</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="{{route('dashboards.home')}}">Dashboard</a></div>
                    <div class="breadcrumb-item">Profile</div>
                </div>
            </div>

            <div class="section-body">


                <div class="row">
                    <div class="col-12 col-md-6 col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h4>Details</h4>
                            </div>
                            <div class="card-body">
                                <img alt="image"
                                     src="{{ Auth::check() ? Auth::user()->raw_picture != null ? auth::user()->avatar : asset('img/avatar/avatar-1.png')  :  asset('img/avatar/avatar-1.png') }}"
                                     class="rounded-circle mr-1" style="width: 100px; height: 100px">
                                <br/><br/>
                                <span class="text-dark">Name / </span> <span>{{$user->name}}</span> <br/>
                                <span class="text-dark">Username / </span> <span>{{$user->username}}</span> <br/>
                                <span class="text-dark">Role / </span> <span>{{$user->role_name}}</span> <br/>
                                <span class="text-dark">ID / </span> <span>{{$user->idType ? $user->idType->name . " | " . $user->id_number  : "$user->id_number"}}</span> <br/>
                                <span class="text-dark">Company / </span> <span>{{$user->company ? $user->company->name : ''}}</span> <br/>
                                <span class="text-dark">Branch / </span> <span>{{$user->branch ? $user->company->branch : ''}}</span> <br/>

                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                               {{Form::open(['url' => route('dashboards.change-my-dp'), 'method' => 'post', 'files' => true])}}
                                <div class="custom-file">
                                    <input type="file"
                                           class="custom-file-input"
                                           id="customFile" name="image" accept="images/*" required>
                                    <label class="custom-file-label"
                                           for="customFile">Choose file</label>
                                </div>
                                <div class="form-group mt-2">
                                    <button class="btn btn-outline-primary form-submit">Change Profile</button>
                                </div>
                                {{Form::close()}}
                            </div>
                        </div>

                    </div>

                    <div class="col-12 col-md-6 col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h4>Change Password</h4>
                            </div>
                            <div class="card-body">
                                {{Form::open(['url' => route('dashboards.change-my-password'), 'method' => 'post'])}}
                                    <div class="form-group">
                                        {{Form::label('old_password', 'Old Password')}}
                                        {{Form::password('old_password', ['class' => 'form-control', 'required'])}}
                                    </div>

                                    <div class="form-group">
                                        {{Form::label('password', 'New Password')}}
                                        {{Form::password('password', ['class' => 'form-control', 'required'])}}
                                        @error('password')
                                            <span class="text-danger">{{$message}}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        {{Form::label('password_confirmation', 'Confirm Password')}}
                                        {{Form::password('password_confirmation', ['class' => 'form-control', 'required'])}}
                                    </div>

                                    <div class="form-group">
                                        <button class="btn btn-outline-primary form-submit">Change Password</button>
                                    </div>
                                {{Form::close()}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <!-- JS Libraies -->

    <!-- Page Specific JS File -->
@endpush
