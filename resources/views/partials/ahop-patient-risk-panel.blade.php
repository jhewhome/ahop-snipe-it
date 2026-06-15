<div class="ahop-ai-risk-panel ahop-ai-risk-{{ $risk['level'] }}" style="margin-bottom: 16px;">
    <h4 class="ahop-section-title" style="margin-top: 0;">
        {{ trans('admin/ai_insights/general.profile_risk_title') }}
        <span class="ahop-ai-risk ahop-ai-risk-{{ $risk['level'] }}">{{ $risk['level_label'] }}</span>
    </h4>
    <p style="margin-bottom: 8px;">
        <strong>{{ trans('admin/ai_insights/general.risk_score') }}:</strong> {{ $risk['score'] }}/100
        — {{ $risk['summary'] }}
    </p>
    @if (count($risk['factors']))
        <ul style="margin-bottom: 10px; font-size: 13px;">
            @foreach (array_slice($risk['factors'], 0, ($compact ?? false) ? 3 : 20) as $factor)
                <li>{{ $factor }}</li>
            @endforeach
        </ul>
    @endif
    <p class="text-muted" style="margin-bottom: 8px;">
        <small><i class="fas fa-info-circle" aria-hidden="true"></i> {{ trans('admin/ai_insights/general.disclaimer') }}</small>
    </p>
    @if ($showLink ?? true)
        <a href="{{ route('clinical-analytics.patient', $patient->id ?? $risk['patient_id']) }}" class="btn btn-xs btn-default">
            {{ trans('admin/ai_insights/general.view_full_analytics') }}
        </a>
    @endif
</div>
