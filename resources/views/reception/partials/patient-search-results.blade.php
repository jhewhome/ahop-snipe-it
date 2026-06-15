@if ($searchResults->count())
    <div class="table-responsive ahop-reception-search-table-wrap">
        <table class="table table-bordered ahop-reception-patient-table">
            <thead>
            <tr>
                <th>{{ trans('admin/patients/table.patient_number') }}</th>
                <th>{{ trans('admin/patients/table.full_name') }}</th>
                <th>{{ trans('admin/patients/table.contact_number') }}</th>
                <th class="text-right">{{ trans('table.actions') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($searchResults as $result)
                <tr>
                    <td>{{ $result->patient_number }}</td>
                    <td>{{ $result->full_name }}</td>
                    <td>{{ $result->contact_number ?: '—' }}</td>
                    <td class="text-right">
                        <a href="{{ route('reception.check-in', ['patient_id' => $result->id]) }}" class="btn btn-sm btn-primary">
                            {{ trans('admin/reception/table.select_patient') }}
                        </a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@elseif (! empty($search))
    <p class="text-muted ahop-reception-search-empty">{{ trans('admin/reception/table.no_results') }}</p>
@endif
