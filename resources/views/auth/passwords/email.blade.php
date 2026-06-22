@extends('layouts/basic')

@php
    $loginSleek = config('ahop.theme_enabled') && config('ahop.theme_variant') === 'sleek';
@endphp

@section('content')

    @if ($snipeSettings->custom_forgot_pass_url)
        @if ($loginSleek)
            <div class="ahop-login-sleek-wrap">
                <div class="ahop-login-sleek-card">
                    @include('partials.ahop-auth-sleek-brand')
                    <p class="ahop-login-sleek-subtitle">{{ trans('auth/general.ldap_reset_password') }}</p>
                    <a href="{{ $snipeSettings->custom_forgot_pass_url }}" rel="noopener" class="ahop-login-sleek-submit" style="line-height:48px;text-align:center;text-decoration:none;display:block;">
                        {{ trans('auth/general.ldap_reset_password') }}
                    </a>
                    <div class="ahop-login-sleek-back">
                        <a href="{{ route('login') }}">{{ trans('auth/general.back_to_sign_in') }}</a>
                    </div>
                </div>
            </div>
        @else
            <div class="col-md-4 col-md-offset-4" style="margin-top: 20px;">
                <div class="box box-header text-center">
                    <h3 class="box-title">
                        <a href="{{ $snipeSettings->custom_forgot_pass_url }}" rel="noopener">
                            {{ trans('auth/general.ldap_reset_password') }}
                        </a>
                    </h3>
                </div>
            </div>
        @endif
    @else
        @if ($loginSleek)
            <form class="ahop-login-sleek-form" role="form" method="POST" action="{{ url('/password/email') }}">
                {!! csrf_field() !!}
                <div class="ahop-login-sleek-wrap">
                    <div class="ahop-login-sleek-card">
                        @include('partials.ahop-auth-sleek-brand')
                        <p class="ahop-login-sleek-subtitle">{{ trans('auth/general.forgot_password_subtitle') }}</p>

                        <div class="alert alert-info ahop-login-sleek-info">
                            <x-icon type="info-circle" />
                            {!! trans('auth/general.username_help_top') !!}
                        </div>

                        @include('partials.ahop-auth-notifications')

                        <div class="ahop-login-sleek-field form-group{{ $errors->has('username') ? ' has-error' : '' }}">
                            <label for="username">
                                <x-icon type="user" />
                                {{ trans('admin/users/table.username') }}
                            </label>
                            <input type="text" class="form-control" name="username" id="username" value="{{ old('username') }}" placeholder="{{ trans('admin/users/table.username') }}" aria-label="username" autofocus>
                            {!! $errors->first('username', '<span class="alert-msg"><i class="fas fa-times"></i> :message</span>') !!}
                        </div>

                        <div class="ahop-login-sleek-help">
                            <a href="#" id="show">
                                <x-icon type="caret-right" />
                                {{ trans('general.show_help') }}
                            </a>
                            <a href="#" id="hide" style="display:none">
                                <x-icon type="caret-up" />
                                {{ trans('general.hide_help') }}
                            </a>
                            <p class="help-block" id="help-text" style="display:none">
                                {!! trans('auth/general.username_help_bottom') !!}
                            </p>
                        </div>

                        <button type="submit" class="ahop-login-sleek-submit">
                            {{ trans('auth/general.email_reset_password') }}
                        </button>

                        <div class="ahop-login-sleek-back">
                            <a href="{{ route('login') }}">{{ trans('auth/general.back_to_sign_in') }}</a>
                        </div>
                    </div>
                </div>
            </form>
        @else
            <form class="form" role="form" method="POST" action="{{ url('/password/email') }}">
                {!! csrf_field() !!}
                <div class="container">
                    <div class="row">
                        <div class="col-md-4 col-md-offset-4">
                            <div class="box login-box" style="width: 100%">
                                <div class="box-header with-border">
                                    <h2 class="box-title"> {{ trans('auth/general.send_password_link') }}</h2>
                                </div>
                                <div class="login-box-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="alert alert-info">
                                                <x-icon type="info-circle" />
                                                {!! trans('auth/general.username_help_top') !!}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        @include('notifications')
                                        <div class="form-group{{ $errors->has('username') ? ' has-error' : '' }}">
                                            <div class="col-md-12">
                                                <label for="username"><x-icon type="user" /> {{ trans('admin/users/table.username') }} </label>
                                                <input type="text" class="form-control" name="username" value="{{ old('username') }}" placeholder="{{ trans('admin/users/table.username') }}" aria-label="username">
                                                {!! $errors->first('username', '<span class="alert-msg"><i class="fas fa-times"></i> :message</span>') !!}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <br>
                                            <a href="#" id="show">
                                                <x-icon type="caret-right" />
                                                {{ trans('general.show_help') }}
                                            </a>
                                            <a href="#" id="hide" style="display:none">
                                                <x-icon type="caret-up" />
                                                {{ trans('general.hide_help') }}
                                            </a>
                                            <p class="help-block" id="help-text" style="display:none">
                                                {!! trans('auth/general.username_help_bottom') !!}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="box-footer">
                                    <button type="submit" class="btn btn-lg btn-primary btn-block">
                                        {{ trans('auth/general.email_reset_password') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        @endif
    @endif

@stop

@push('js')
    <script nonce="{{ csrf_token() }}">
        $(document).ready(function () {
            $("#show").click(function(){
                $("#help-text").fadeIn(500);
                $("#show").hide();
                $("#hide").show();
            });

            $("#hide").click(function(){
                $("#help-text").fadeOut(300);
                $("#show").show();
                $("#hide").hide();
            });
        });
    </script>
@endpush
