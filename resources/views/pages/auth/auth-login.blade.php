@extends('layouts.auth')

@section('title', 'Login')

@push('style')

@endpush

@section('main')
    <div class="card card-success">
        <div class="card-header">
            <h4 class="text-success">Login</h4>
        </div>

        <div class="card-body">
         {{Form::open(['url' => route('attempt'), 'action' => 'post'])}}
                <div class="form-group">
                    <label for="username">Username</label>
                    <input id="username"
                        type="text"
                        class="form-control"
                        name="username"
                        tabindex="1"
                        required
                        autofocus>
                    <div class="invalid-feedback">
                        Please fill in your username
                    </div>
                </div>

                <div class="form-group">
                    <div class="d-block">
                        <label for="password"
                            class="control-label">Password</label>
                    </div>
                    <input id="password"
                        type="password"
                        class="form-control"
                        name="password"
                        tabindex="2"
                        required>
                    <div class="invalid-feedback">
                        please fill in your password
                    </div>
                </div>

                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox"
                            name="remember"
                            class="custom-control-input"
                            tabindex="3"
                            id="remember-me">
                        <label class="custom-control-label"
                            for="remember-me">Remember Me</label>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit"
                            id="form-submit"
                        class="btn btn-success btn-lg btn-block"
                        tabindex="4">
                        Login
                    </button>
                </div>
          {{Form::close()}}


        </div>
    </div>
    <div class="text-muted mt-5 text-center">
{{--        Don't have an account? <a href="auth-register.html">Create One</a>--}}
    </div>
@endsection

@push('scripts')
    <!-- JS Libraies -->

    <!-- Page Specific JS File -->
@endpush
