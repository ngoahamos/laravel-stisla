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
                                     src="{{ asset('img/avatar/avatar-1.png') }}"
                                     class="rounded-circle mr-1" style="width: 100px; height: 100px">
                                <br/><br/>
                                <span class="text-dark">Name / </span> <span>Amos Ngoah</span> <br/>
                                <span class="text-dark">Username / </span> <span>github.com/ngoahamos</span> <br/>

                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">

                                {{html()->form('POST', route('dashboards.home'))->acceptsFiles()->open()}}

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
                                {{html()->form()->close()}}
                            </div>
                        </div>

                    </div>

                    <div class="col-12 col-md-6 col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h4>Change Password</h4>
                            </div>
                            <div class="card-body">
                                {{html()->form('POST', route('dashboards.home'))->open()}}

                                    <div class="form-group">
                                        {{html()->label('old_password','Old Password')}}
                                        {{html()->password('old_password')->attributes(['class' => 'form-control', 'required'])}}

                                    </div>

                                    <div class="form-group">
                                        {{html()->label('password','New Password')}}
                                        {{html()->password('password')->attributes(['class' => 'form-control', 'required'])}}
                                        @error('password')
                                            <span class="text-danger">{{$message}}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        {{html()->label('password_confirmation','Confirm Password')}}
                                        {{html()->password('password_confirmation')->attributes(['class' => 'form-control', 'required'])}}
                                    </div>

                                    <div class="form-group">
                                        <button class="btn btn-outline-primary form-submit">Change Password</button>
                                    </div>
                                {{html()->form()->close()}}
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
