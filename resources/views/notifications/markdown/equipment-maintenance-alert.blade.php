@component('mail::message')
# {{ trans('ahop.equipment_alert_heading') }}

{{ trans('ahop.equipment_alert_intro') }}

@if (count($alerts['pending_repair']))
## {{ trans('ahop.equipment_alert_pending_title') }}

@component('mail::table')
| {{ trans('ahop.equipment_alert_asset') }} | {{ trans('ahop.equipment_alert_status') }} | {{ trans('ahop.equipment_alert_location') }} |
|:--|:--|:--|
@foreach ($alerts['pending_repair'] as $asset)
| [{{ $asset['asset_tag'] }} — {{ $asset['name'] }}]({{ $asset['url'] }}) | {{ $asset['status'] }} | {{ $asset['location'] ?? '—' }} |
@endforeach
@endcomponent
@endif

@if (count($alerts['maintenance']))
## {{ trans('ahop.equipment_alert_maintenance_title') }}

@component('mail::table')
| {{ trans('ahop.equipment_alert_asset') }} | {{ trans('ahop.equipment_alert_priority') }} | {{ trans('ahop.equipment_alert_due') }} |
|:--|:--|:--|
@foreach ($alerts['maintenance'] as $item)
| [{{ $item['asset_tag'] }} — {{ $item['name'] }}]({{ $item['url'] }}) | {{ $item['urgency_label'] }} ({{ $item['priority_score'] }}) | {{ $item['due_label'] }} |
@endforeach
@endcomponent
@endif

@component('mail::button', ['url' => $analyticsUrl])
{{ trans('ahop.equipment_alert_view_analytics') }}
@endcomponent

{{ trans('ahop.equipment_alert_footer') }}
@endcomponent
