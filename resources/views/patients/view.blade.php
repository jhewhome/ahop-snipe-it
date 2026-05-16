@extends('layouts/default')

@section('title')
    {{ $patient->display_name }}
    @parent
@stop

@section('content')
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h2 class="box-title">{{ $patient->full_name }}</h2>
                    <div class="box-tools pull-right">
                        @can('update', $patient)
                            <a href="{{ route('patients.edit', $patient) }}" class="btn btn-sm btn-warning">
                                <i class="fas fa-pencil" aria-hidden="true"></i> {{ trans('general.edit') }}
                            </a>
                        @endcan
                        @can('delete', $patient)
                            <form method="post" action="{{ route('patients.destroy', $patient) }}" style="display:inline;" onsubmit="return confirm('{{ trans('admin/patients/message.delete.confirm') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash" aria-hidden="true"></i> {{ trans('general.delete') }}
                                </button>
                            </form>
                        @endcan
                    </div>
                </div>
                <div class="box-body">
                    <table class="table table-striped">
                        <tbody>
                        <tr>
                            <th style="width: 35%;">{{ trans('admin/patients/table.bhc_id') }}</th>
                            <td>{{ $patient->bhc_id }}</td>
                        </tr>
                        <tr>
                            <th>{{ trans('admin/patients/table.full_name') }}</th>
                            <td>{{ $patient->full_name }}</td>
                        </tr>
                        <tr>
                            <th>{{ trans('admin/patients/table.sex') }}</th>
                            <td>{{ $patient->sex === 'M' ? 'Male' : 'Female' }}</td>
                        </tr>
                        <tr>
                            <th>{{ trans('admin/patients/table.birthdate') }}</th>
                            <td>{{ $patient->birthdate ? $patient->birthdate->format('Y-m-d') : '' }}</td>
                        </tr>
                        <tr>
                            <th>{{ trans('admin/patients/table.contact_number') }}</th>
                            <td>{{ $patient->contact_number ?: '—' }}</td>
                        </tr>
                        @if ($patient->notes)
                            <tr>
                                <th>{{ trans('admin/patients/table.notes') }}</th>
                                <td>{{ $patient->notes }}</td>
                            </tr>
                        @endif
                        <tr>
                            <th>{{ trans('general.created_at') }}</th>
                            <td>{{ $patient->created_at }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="box-footer text-right">
                    <a href="{{ route('patients.index') }}" class="btn btn-default">{{ trans('general.back') }}</a>
                </div>
            </div>
        </div>
    </div>
@stop
