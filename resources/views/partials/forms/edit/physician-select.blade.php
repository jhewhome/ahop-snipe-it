@php
    $fieldname = $fieldname ?? 'physician_id';
    $selectedId = (int) old($fieldname, (isset($item) && $item->{$fieldname}) ? $item->{$fieldname} : 0);
    $physicians = $physicians ?? collect();
@endphp

<div class="form-group{{ $errors->has($fieldname) ? ' has-error' : '' }}"{!! isset($style) ? ' style="'.e($style).'"' : '' !!}>
    <label for="{{ $fieldname }}" class="col-md-3 control-label">{{ $translated_name ?? trans('admin/opd_visits/table.physician') }}</label>
    <div class="col-md-8">
        <select name="{{ $fieldname }}" id="{{ $fieldname }}" class="form-control" aria-label="{{ $fieldname }}"{{ (isset($required) && $required === 'true') ? ' required' : '' }}>
            <option value="">{{ $placeholder ?? trans('admin/reception/table.physician_placeholder') }}</option>
            @foreach ($physicians as $physician)
                <option value="{{ $physician->id }}" {{ $selectedId === (int) $physician->id ? 'selected' : '' }}>
                    {{ $physician->display_name }}@if ($physician->username) ({{ $physician->username }})@endif
                </option>
            @endforeach
        </select>
    </div>

    @if (! empty($help_text))
        <div class="col-md-8 col-md-offset-3">
            <p class="help-block"><small>{{ $help_text }}</small></p>
        </div>
    @endif

    {!! $errors->first($fieldname, '<div class="col-md-8 col-md-offset-3"><span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span></div>') !!}
</div>
