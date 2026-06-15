@if (config('ahop.theme_enabled'))
    <div class="ahop-login-brand">
        @if (($snipeSettings) && ($snipeSettings->logo != ''))
            <a href="{{ config('app.url') }}">
                <img src="{{ Storage::disk('public')->url(e($snipeSettings->logo)) }}" alt="{{ $snipeSettings->site_name }}" style="max-height: 72px;">
            </a>
        @else
            <div class="ahop-login-icon"><i class="fas fa-heart-pulse" aria-hidden="true"></i></div>
        @endif
        <h1>{{ ($snipeSettings && $snipeSettings->site_name) ? $snipeSettings->site_name : config('ahop.default_site_name') }}</h1>
        <p>{{ config('ahop.tagline') }}</p>
    </div>
@endif
