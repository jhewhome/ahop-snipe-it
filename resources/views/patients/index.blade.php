@extends('layouts/default')

@section('title')
    {{ trans('general.patients') }}
    @parent
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h2 class="box-title">{{ trans('general.patients') }}</h2>
                    <div class="box-tools pull-right">
                        @can('create', \App\Models\Patient::class)
                            <a href="{{ route('patients.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus" aria-hidden="true"></i> {{ trans('admin/patients/table.create') }}
                            </a>
                        @endcan
                    </div>
                </div>

                <div class="box-body">
                    <form method="get" action="{{ route('patients.index') }}" class="form-inline" style="margin-bottom: 15px;">
                        <div class="form-group">
                            <input type="text" name="search" class="form-control" placeholder="{{ trans('general.search') }}"
                                   value="{{ request('search') }}">
                        </div>
                        <button type="submit" class="btn btn-default">{{ trans('general.search') }}</button>
                        @if (request('search'))
                            <a href="{{ route('patients.index') }}" class="btn btn-link">{{ trans('general.clear_selection') }}</a>
                        @endif
                    </form>

                    <div class="table-responsive">
                        <table class="table table-striped snipe-table">
                            <thead>
                            <tr>
                                <th>{{ trans('admin/patients/table.bhc_id') }}</th>
                                <th>{{ trans('admin/patients/table.full_name') }}</th>
                                <th>{{ trans('admin/patients/table.sex') }}</th>
                                <th>{{ trans('admin/patients/table.birthdate') }}</th>
                                <th>{{ trans('admin/patients/table.contact_number') }}</th>
                                <th class="text-right">{{ trans('table.actions') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse ($patients as $patient)
                                <tr>
                                    <td>
                                        <a href="{{ route('patients.show', $patient) }}">{{ $patient->bhc_id }}</a>
                                    </td>
                                    <td>{{ $patient->full_name }}</td>
                                    <td>{{ $patient->sex }}</td>
                                    <td>{{ $patient->birthdate ? $patient->birthdate->format('Y-m-d') : '' }}</td>
                                    <td>{{ $patient->contact_number }}</td>
                                    <td class="text-right">
                                        @can('view', $patient)
                                            <a href="{{ route('patients.show', $patient) }}" class="btn btn-sm btn-default" title="{{ trans('general.view') }}">
                                                <i class="fas fa-eye" aria-hidden="true"></i>
                                            </a>
                                        @endcan
                                        @can('update', $patient)
                                            <a href="{{ route('patients.edit', $patient) }}" class="btn btn-sm btn-warning" title="{{ trans('general.edit') }}">
                                                <i class="fas fa-pencil" aria-hidden="true"></i>
                                            </a>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">{{ trans('general.no_results') }}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $patients->links() }}
                </div>
            </div>
        </div>
    </div>
@stop
