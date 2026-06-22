@extends('layouts/basic')

@php
    $loginSleek = config('ahop.theme_enabled') && config('ahop.theme_variant') === 'sleek';
@endphp

@section('content')

@if ($loginSleek)
    <form role="form" action="{{ url('/login') }}" method="POST" autocomplete="{{ (config('auth.login_autocomplete') === true) ? 'on' : 'off' }}" class="ahop-login-sleek-form">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="text" name="prevent_autofill" id="prevent_autofill" value="" style="display:none;" aria-hidden="true">
        <input type="password" name="password_fake" id="password_fake" value="" style="display:none;" aria-hidden="true">

        <div class="ahop-login-sleek-wrap">
            <div class="ahop-login-sleek-card">
                @include('partials.ahop-auth-sleek-brand')

                <p class="ahop-login-sleek-subtitle">{{ trans('auth/general.login_subtitle') }}</p>

                @if ($snipeSettings->login_note)
                    <div class="alert alert-info">
                        {!! Helper::parseEscapedMarkedown($snipeSettings->login_note) !!}
                    </div>
                @endif

                @include('notifications')

                @if (($snipeSettings->google_login == '1') && ($snipeSettings->google_client_id != '') && ($snipeSettings->google_client_secret != ''))
                    <a href="{{ route('google.redirect') }}" class="ahop-login-sleek-google">
                        <i class="fa-brands fa-google" aria-hidden="true"></i>
                        {{ trans('auth/general.google_login') }}
                    </a>
                    <div class="ahop-login-sleek-divider">{{ strtoupper(trans('general.or')) }}</div>
                @endif

                @if (!config('app.require_saml'))
                    <fieldset name="login" aria-label="login">
                        <div class="ahop-login-sleek-field form-group{{ $errors->has('username') ? ' has-error' : '' }}">
                            <label for="username">
                                <x-icon type="user" />
                                {{ trans('admin/users/table.username') }}
                            </label>
                            <input class="form-control" placeholder="{{ trans('admin/users/table.username') }}" name="username" type="text" id="username" autocomplete="{{ (config('auth.login_autocomplete') === true) ? 'on' : 'off' }}" autofocus>
                            {!! $errors->first('username', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                        </div>

                        <div class="ahop-login-sleek-field form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                            <label for="password">
                                <x-icon type="password" />
                                {{ trans('admin/users/table.password') }}
                            </label>
                            <input class="form-control" placeholder="{{ trans('admin/users/table.password') }}" name="password" type="password" id="password" autocomplete="{{ (config('auth.login_autocomplete') === true) ? 'on' : 'off' }}">
                            {!! $errors->first('password', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                        </div>

                        <div class="ahop-login-sleek-row">
                            <label class="ahop-login-sleek-remember" for="remember">
                                <input name="remember" type="checkbox" value="1" id="remember">
                                {{ trans('auth/general.remember_me') }}
                            </label>

                            @if ($snipeSettings->custom_forgot_pass_url)
                                <a class="ahop-login-sleek-forgot" href="{{ $snipeSettings->custom_forgot_pass_url }}" rel="noopener">{{ trans('auth/general.forgot_password') }}</a>
                            @elseif (!config('app.require_saml'))
                                <a class="ahop-login-sleek-forgot" href="{{ route('password.request') }}">{{ trans('auth/general.forgot_password') }}</a>
                            @endif
                        </div>
                    </fieldset>

                    <button class="ahop-login-sleek-submit" type="submit" id="submit">
                        {{ trans('auth/general.sign_in') }}
                    </button>
                @endif

                @if (config('app.require_saml'))
                    <a class="ahop-login-sleek-submit" href="{{ route('saml.login') }}" style="line-height:48px;text-align:center;text-decoration:none;display:block;">
                        {{ trans('auth/general.saml_login') }}
                    </a>
                @endif

                @if (!config('app.require_saml') && $snipeSettings->saml_enabled)
                    <div class="ahop-login-sleek-saml">
                        <a href="{{ route('saml.login') }}">{{ trans('auth/general.saml_login') }}</a>
                    </div>
                @endif
            </div>
        </div>
    </form>
@else
    <form role="form" action="{{ url('/login') }}" method="POST" autocomplete="{{ (config('auth.login_autocomplete') === true) ? 'on' : 'off'  }}">
        <input type="hidden" name="_token" value="{{ csrf_token() }}" />

        <!-- this is a hack to prevent Chrome from trying to autocomplete fields -->
        <input type="text" name="prevent_autofill" id="prevent_autofill" value="" style="display:none;" aria-hidden="true">
        <input type="password" name="password_fake" id="password_fake" value="" style="display:none;" aria-hidden="true">

        <div class="container">
            <div class="row">

                <div class="col-md-4 col-md-offset-4">

                    @if (($snipeSettings->google_login=='1') && ($snipeSettings->google_client_id!='') && ($snipeSettings->google_client_secret!=''))

                        <br><br>
                        <a href="{{ route('google.redirect')  }}" class="btn btn-block btn-social btn-google btn-lg">
                            <i class="fa-brands fa-google"></i>
                            {{ trans('auth/general.google_login') }}
                        </a>

                        <div class="separator">{{ strtoupper(trans('general.or')) }}</div>
                    @endif


                    <div class="box login-box">
                        <div class="box-header with-border">
                            <h1 class="box-title"> {{ trans('auth/general.login_prompt')  }}</h1>
                        </div>


                        <div class="login-box-body">
                            <div class="row">

                                @if ($snipeSettings->login_note)
                                    <div class="col-md-12">
                                        <div class="alert alert-info">
                                            {!!  Helper::parseEscapedMarkedown($snipeSettings->login_note)  !!}
                                        </div>
                                    </div>
                                @endif

                                <!-- Notifications -->
                                @include('notifications')

                                @if (!config('app.require_saml'))
                                <div class="col-md-12">
                                    <!-- CSRF Token -->


                                    <fieldset name="login" aria-label="login">

                                        <div class="form-group{{ $errors->has('username') ? ' has-error' : '' }}">
                                            <label for="username">
                                                <x-icon type="user" />
                                                {{ trans('admin/users/table.username')  }}
                                            </label>
                                            <input class="form-control" placeholder="{{ trans('admin/users/table.username')  }}" name="username" type="text" id="username" autocomplete="{{ (config('auth.login_autocomplete') === true) ? 'on' : 'off'  }}" autofocus>
                                            {!! $errors->first('username', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                                        </div>
                                        <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                                            <label for="password">
                                                <x-icon type="password" />
                                                {{ trans('admin/users/table.password')  }}
                                            </label>
                                            <input class="form-control" placeholder="{{ trans('admin/users/table.password')  }}" name="password" type="password" id="password" autocomplete="{{ (config('auth.login_autocomplete') === true) ? 'on' : 'off'  }}">
                                            {!! $errors->first('password', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                                        </div>
                                        <div class="form-group">
                                            <label class="form-control">
                                                <input name="remember" type="checkbox" value="1" id="remember"> {{ trans('auth/general.remember_me')  }}
                                            </label>
                                        </div>
                                    </fieldset>
                                </div> <!-- end col-md-12 -->
                                @endif
                            </div> <!-- end row -->

                            @if (!config('app.require_saml') && $snipeSettings->saml_enabled)
                            <div class="row">
                                <div class="text-right col-md-12">
                                    <a href="{{ route('saml.login')  }}">{{ trans('auth/general.saml_login')  }}</a>
                                </div>
                            </div>
                            @endif
                        </div>
                        <div class="box-footer">
                            @if (config('app.require_saml'))
                                <a class="btn btn-primary btn-block" href="{{ route('saml.login')  }}">{{ trans('auth/general.saml_login')  }}</a>
                            @else
                                <button class="btn btn-primary btn-block" type="submit" id="submit">
                                    {{ trans('auth/general.login')  }}
                                </button>
                            @endif

                            @if ($snipeSettings->custom_forgot_pass_url)
                                <div class="col-md-12 text-right" style="padding-top: 15px;">
                                    <a href="{{ $snipeSettings->custom_forgot_pass_url  }}" rel="noopener">{{ trans('auth/general.forgot_password')  }}</a>
                                </div>
                            @elseif (!config('app.require_saml'))
                                <div class="col-md-12 text-right" style="padding-top: 15px;">
                                    <a href="{{ route('password.request')  }}">{{ trans('auth/general.forgot_password')  }}</a>
                                </div>
                            @endif

                        </div>

                    </div> <!-- end login box -->


                </div> <!-- col-md-4 -->

            </div> <!-- end row -->
        </div> <!-- end container -->
    </form>
@endif

@stop
