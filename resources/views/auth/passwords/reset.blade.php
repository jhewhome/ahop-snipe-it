@extends('layouts/basic')

@php
    $loginSleek = config('ahop.theme_enabled') && config('ahop.theme_variant') === 'sleek';
@endphp

@section('content')

@if ($loginSleek)
    <form class="ahop-login-sleek-form" role="form" method="POST" action="{{ url('/password/reset') }}">
        {!! csrf_field() !!}
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="ahop-login-sleek-wrap">
            <div class="ahop-login-sleek-card">
                @include('partials.ahop-auth-sleek-brand')
                <p class="ahop-login-sleek-subtitle">{{ trans('auth/general.reset_password_subtitle') }}</p>

                @include('notifications')

                <div class="ahop-login-sleek-field form-group{{ $errors->has('username') ? ' has-error' : '' }}">
                    <label for="username">
                        <x-icon type="user" />
                        {{ trans('admin/users/table.username') }}
                    </label>
                    <input type="text" class="form-control" name="username" id="username" value="{{ old('username', $username) }}" aria-label="username" autofocus>
                    {!! $errors->first('username', '<span class="alert-msg"><i class="fas fa-times"></i> :message</span>') !!}
                </div>

                <div class="ahop-login-sleek-field form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                    <label for="password">
                        <x-icon type="password" />
                        {{ trans('admin/users/table.password') }}
                    </label>
                    <input type="password" class="form-control" name="password" id="password" aria-label="password">
                    {!! $errors->first('password', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                </div>

                <div class="ahop-login-sleek-field form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
                    <label for="password_confirmation">
                        <x-icon type="password" />
                        {{ trans('admin/users/table.password_confirm') }}
                    </label>
                    <input type="password" class="form-control" name="password_confirmation" id="password_confirmation" aria-label="password_confirmation">
                    {!! $errors->first('password_confirmation', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                </div>

                <button type="submit" class="ahop-login-sleek-submit">
                    {{ trans('auth/general.reset_password') }}
                </button>

                <div class="ahop-login-sleek-back">
                    <a href="{{ route('login') }}">{{ trans('auth/general.back_to_sign_in') }}</a>
                </div>
            </div>
        </div>
    </form>
@else
    <form class="form-horizontal" role="form" method="POST" action="{{ url('/password/reset') }}">
        {!! csrf_field() !!}

        <div class="container">
            <div class="row">

                <div class="col-md-6 col-md-offset-3">

                    <div class="box login-box" style="width: 100%">
                        <div class="box-header with-border">
                            <h2 class="box-title"> {{ trans('auth/general.reset_password') }}</h2>
                        </div>


                        <div class="login-box-body">
                            <div class="row">

                                @include('notifications')

                                <input type="hidden" name="token" value="{{ $token }}">

                                <div class="form-group{{ $errors->has('username') ? ' has-error' : '' }}">
                                    <label class="col-md-4 control-label"><x-icon type="user" /> {{ trans('admin/users/table.username') }}</label>

                                    <div class="col-md-6">
                                        <input type="text" class="form-control" name="username" value="{{ old('username', $username) }}">
                                        {!! $errors->first('username', '<span class="alert-msg"><i class="fas fa-times"></i> :message</span>') !!}
                                    </div>
                                </div>

                                <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                                    <label class="col-md-4 control-label" for="password">
                                        <x-icon type="password" />
                                        {{ trans('admin/users/table.password') }}
                                    </label>

                                    <div class="col-md-6">
                                        <input type="password" class="form-control" name="password" aria-label="password">
                                        {!! $errors->first('password', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                                    </div>
                                </div>

                                <div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
                                    <label class="col-md-4 control-label" for="password_confirmation">
                                        <x-icon type="password" />
                                        {{ trans('admin/users/table.password_confirm') }}</label>
                                    <div class="col-md-6">
                                        <input type="password" class="form-control" name="password_confirmation" aria-label="password_confirmation">
                                        {!! $errors->first('password_confirmation', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-lg btn-primary btn-block">
                                {{ trans('auth/general.reset_password') }}
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </form>
@endif

@stop
