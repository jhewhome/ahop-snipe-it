@extends('layouts/edit-form', [
    'createText' => trans('admin/billing_invoices/table.create'),
    'updateText' => trans('admin/billing_invoices/table.update'),
    'formAction' => (isset($item->id)) ? route('billing-invoices.update', ['billing_invoice' => $item->id]) : route('billing-invoices.store'),
    'boxClasses' => 'ahop-panel',
    'index_route' => 'billing-invoices.index',
])

@section('inputFields')

    <input type="hidden" name="opd_visit_id" value="{{ old('opd_visit_id', $item->opd_visit_id) }}">
    <input type="hidden" name="appointment_id" value="{{ old('appointment_id', $item->appointment_id) }}">

    @if ($item->opd_visit_id || $item->appointment_id)
        <div class="form-group">
            <div class="col-md-8 col-md-offset-3">
                <div class="alert alert-info" style="margin-bottom: 0;">
                    @if ($item->opd_visit_id)
                        <i class="fas fa-stethoscope" aria-hidden="true"></i>
                        {{ trans('admin/billing_invoices/table.opd_visit') }} #{{ $item->opd_visit_id }}
                    @endif
                    @if ($item->appointment_id)
                        @if ($item->opd_visit_id) &nbsp;·&nbsp; @endif
                        <i class="fas fa-calendar-check" aria-hidden="true"></i>
                        {{ trans('admin/billing_invoices/table.appointment') }} #{{ $item->appointment_id }}
                    @endif
                </div>
            </div>
        </div>
    @endif

    <div class="form-group{{ $errors->has('invoice_number') ? ' has-error' : '' }}">
        <label for="invoice_number" class="col-md-3 control-label">{{ trans('admin/billing_invoices/table.invoice_number') }}</label>
        <div class="col-md-8">
            <input class="form-control" type="text" name="invoice_number" id="invoice_number" value="{{ old('invoice_number', $item->invoice_number) }}" required />
            {!! $errors->first('invoice_number', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>

    <div class="form-group{{ $errors->has('patient_id') ? ' has-error' : '' }}">
        <label for="patient_id" class="col-md-3 control-label">{{ trans('admin/billing_invoices/table.patient') }}</label>
        <div class="col-md-8">
            <select name="patient_id" id="patient_id" class="form-control select2" required style="width: 100%;">
                <option value="">{{ trans('general.select') }}...</option>
                @foreach ($patients as $patient)
                    <option value="{{ $patient->id }}" {{ (int) old('patient_id', $item->patient_id) === $patient->id ? 'selected' : '' }}>
                        {{ $patient->patient_number }} — {{ $patient->full_name }}
                    </option>
                @endforeach
            </select>
            {!! $errors->first('patient_id', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>

    <div class="form-group{{ $errors->has('issued_at') ? ' has-error' : '' }}">
        <label for="issued_at" class="col-md-3 control-label">{{ trans('admin/billing_invoices/table.issued_at') }}</label>
        <div class="col-md-8">
            <input class="form-control" type="datetime-local" name="issued_at" id="issued_at"
                   value="{{ old('issued_at', optional($item->issued_at)->format('Y-m-d\TH:i')) }}" />
            {!! $errors->first('issued_at', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>

    @if ($item->exists)
        <div class="form-group{{ $errors->has('status') ? ' has-error' : '' }}">
            <label for="status" class="col-md-3 control-label">{{ trans('admin/billing_invoices/table.status') }}</label>
            <div class="col-md-8">
                <select name="status" id="status" class="form-control" required>
                    @foreach (\App\Models\BillingInvoice::statusOptions() as $value => $label)
                        <option value="{{ $value }}" {{ old('status', $item->status) === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                {!! $errors->first('status', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
            </div>
        </div>
    @else
        <div class="form-group">
            <label class="col-md-3 control-label">{{ trans('admin/billing_invoices/table.status') }}</label>
            <div class="col-md-8">
                <div class="checkbox ahop-checkbox">
                    <label for="issue_now">
                        <input type="checkbox" name="issue_now" id="issue_now" value="1" {{ old('issue_now', true) ? 'checked' : '' }}>
                        {{ trans('admin/billing_invoices/table.issue_now') }}
                    </label>
                </div>
            </div>
        </div>
    @endif

    <div class="form-group{{ $errors->has('notes') ? ' has-error' : '' }}">
        <label for="notes" class="col-md-3 control-label">{{ trans('admin/billing_invoices/table.notes') }}</label>
        <div class="col-md-8">
            <textarea class="form-control" name="notes" id="notes" rows="3">{{ old('notes', $item->notes) }}</textarea>
            {!! $errors->first('notes', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>

@stop
