<div class="ahop-login-sleek-brand">
    @if (($snipeSettings) && ($snipeSettings->logo != ''))
        <img src="{{ Storage::disk('public')->url(e($snipeSettings->logo)) }}" alt="{{ $snipeSettings->site_name }}">
    @else
        <div class="ahop-login-sleek-icon"><i class="fas fa-heart-pulse" aria-hidden="true"></i></div>
    @endif
    <h1>{{ ($snipeSettings && $snipeSettings->site_name) ? $snipeSettings->site_name : config('ahop.default_site_name') }}</h1>
</div>
