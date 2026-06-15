@php
    $icon = $icon ?? 'fa-inbox';
    $title = $title ?? trans('general.no_results');
    $message = $message ?? null;
    $actionUrl = $actionUrl ?? null;
    $actionLabel = $actionLabel ?? null;
    $compact = $compact ?? false;
@endphp

<div class="ahop-empty-state{{ $compact ? ' ahop-empty-state--compact' : '' }}" role="status">
    <div class="ahop-empty-state__icon-wrap" aria-hidden="true">
        <i class="fas {{ $icon }}"></i>
    </div>
    <p class="ahop-empty-state__title">{{ $title }}</p>
    @if ($message)
        <p class="ahop-empty-state__message">{{ $message }}</p>
    @endif
    @if ($actionUrl && $actionLabel)
        <a href="{{ $actionUrl }}" class="btn btn-primary btn-sm ahop-empty-state__action">
            <i class="fas fa-plus" aria-hidden="true"></i> {{ $actionLabel }}
        </a>
    @endif
</div>
