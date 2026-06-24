@extends('layouts.auth')

@section('htmlheader_title')
    Log in
@endsection

@section('content')
<body class="hold-transition login-page">
    <div id="app" v-cloak>
        <div class="login-box">
            <div class="login-logo">
                <a href="{{ url('/home') }}"><b>SiG</b>ReS</a>
            </div><!-- /.login-logo -->

        @if (count($errors) > 0)
            <div class="alert alert-danger">
                <strong>Whoops!</strong> {{ __('adminlte::message.someproblems') }}<br><br>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="login-box-body">
        <p class="login-box-msg"> {{ __('adminlte::message.siginsession') }} </p>

        <login-form name="{{ config('auth.providers.users.field','email') }}"
                    domain="{{ config('auth.defaults.domain','') }}"></login-form>

        {{-- @include('auth.partials.social_login') --}}

        <a href="{{ url('/password/reset') }}">{{ __('adminlte::message.forgotpassword') }}</a><br>
        <a href="{{ url('/register') }}" class="text-center">{{ __('adminlte::message.registermember') }}</a>

    </div>

    </div>
    </div>
    @include('layouts.partials.scripts_auth')
</body>

@endsection
