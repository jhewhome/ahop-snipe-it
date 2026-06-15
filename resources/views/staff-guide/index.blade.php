@extends('layouts/default')

@section('title')
    {{ trans('ahop.staff_guide_title') }}
    @parent
@stop

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="box box-default ahop-panel">
            <div class="box-header with-border">
                <h3 class="box-title">{{ trans('ahop.staff_guide_title') }}</h3>
            </div>
            <div class="box-body">
                <p class="text-muted">{{ trans('ahop.staff_guide_intro') }}</p>

                <h4>{{ trans('ahop.staff_guide_policy_title') }}</h4>
                <ul>
                    <li>{{ trans('ahop.staff_guide_policy_1') }}</li>
                    <li>{{ trans('ahop.staff_guide_policy_2') }}</li>
                    <li>{{ trans('ahop.staff_guide_policy_3') }}</li>
                </ul>

                <h4>{{ trans('ahop.staff_guide_roles_title') }}</h4>
                <table class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th>{{ trans('ahop.staff_guide_role_col') }}</th>
                        <th>{{ trans('ahop.staff_guide_role_does') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td><strong>AHOP Reception</strong></td>
                        <td>{{ trans('ahop.staff_guide_role_reception') }}</td>
                    </tr>
                    <tr>
                        <td><strong>AHOP Clinic Staff</strong></td>
                        <td>{{ trans('ahop.staff_guide_role_clinic') }}</td>
                    </tr>
                    <tr>
                        <td><strong>AHOP Laboratory</strong></td>
                        <td>{{ trans('ahop.staff_guide_role_lab') }}</td>
                    </tr>
                    <tr>
                        <td><strong>AHOP Biomedical</strong></td>
                        <td>{{ trans('ahop.staff_guide_role_biomedical') }}</td>
                    </tr>
                    <tr>
                        <td><strong>AHOP Clinic Administrator</strong></td>
                        <td>{{ trans('ahop.staff_guide_role_admin') }}</td>
                    </tr>
                    </tbody>
                </table>

                <h4>{{ trans('ahop.staff_guide_workflow_title') }}</h4>
                <ol>
                    <li>{{ trans('ahop.staff_guide_workflow_1') }}</li>
                    <li>{{ trans('ahop.staff_guide_workflow_2') }}</li>
                    <li>{{ trans('ahop.staff_guide_workflow_3') }}</li>
                    <li>{{ trans('ahop.staff_guide_workflow_4') }}</li>
                    <li>{{ trans('ahop.staff_guide_workflow_5') }}</li>
                </ol>

                <h4>{{ trans('ahop.staff_guide_training_title') }}</h4>
                <ul>
                    <li>{{ trans('ahop.staff_guide_training_1') }}</li>
                    <li>{{ trans('ahop.staff_guide_training_2') }}</li>
                </ul>

                <h4>{{ trans('ahop.staff_guide_links_title') }}</h4>
                <div class="btn-group" role="group">
                    @if (Gate::allows('view', \App\Models\Patient::class) && (Gate::allows('create', \App\Models\OpdVisit::class) || Gate::allows('index', \App\Models\Appointment::class)))
                        <a href="{{ route('reception.check-in') }}" class="btn btn-primary">
                            <i class="fas fa-door-open" aria-hidden="true"></i> {{ trans('general.reception_check_in') }}
                        </a>
                    @endif
                    @can('view', \App\Models\Patient::class)
                        <a href="{{ route('patients.index') }}" class="btn btn-default">
                            <i class="fas fa-user-injured" aria-hidden="true"></i> {{ trans('general.patients') }}
                        </a>
                    @endcan
                    @can('view', \App\Models\OpdVisit::class)
                        <a href="{{ route('opd-visits.index') }}" class="btn btn-default">
                            <i class="fas fa-stethoscope" aria-hidden="true"></i> {{ trans('general.opd_visits') }}
                        </a>
                    @endcan
                    @can('view', \App\Models\LabOrder::class)
                        <a href="{{ route('lab-orders.index') }}" class="btn btn-default">
                            <i class="fas fa-flask" aria-hidden="true"></i> {{ trans('general.lab_orders') }}
                        </a>
                    @endcan
                    @can('view', \App\Models\BillingInvoice::class)
                        <a href="{{ route('billing-invoices.index') }}" class="btn btn-default">
                            <i class="fas fa-file-invoice-dollar" aria-hidden="true"></i> {{ trans('general.billing') }}
                        </a>
                    @endcan
                    @can('reports.view')
                        <a href="{{ route('reports.clinical.index') }}" class="btn btn-default">
                            <i class="fas fa-chart-line" aria-hidden="true"></i> {{ trans('admin/clinical_reports/general.title') }}
                        </a>
                    @endcan
                    @can('index', \App\Models\Asset::class)
                        <a href="{{ url('hardware') }}" class="btn btn-default">
                            <i class="fas fa-kit-medical" aria-hidden="true"></i> {{ trans('general.medical_equipment') }}
                        </a>
                    @endcan
                </div>

                <hr>

                <h4>{{ trans('ahop.staff_guide_reports_title') }}</h4>
                <ul>
                    <li>{{ trans('ahop.staff_guide_reports_1') }}</li>
                </ul>

                <h4>{{ trans('ahop.staff_guide_appointments_title') }}</h4>
                <ul>
                    <li>{{ trans('ahop.staff_guide_appointments_1') }}</li>
                    <li>{{ trans('ahop.staff_guide_appointments_2') }}</li>
                    <li>{{ trans('ahop.staff_guide_appointments_3') }}</li>
                </ul>

                <h4>{{ trans('ahop.staff_guide_supplies_title') }}</h4>
                <ul>
                    <li>{{ trans('ahop.staff_guide_supplies_1') }}</li>
                </ul>

                <h4>{{ trans('ahop.staff_guide_phase_b_title') }}</h4>
                <ul>
                    <li>{{ trans('ahop.staff_guide_phase_b_1') }}</li>
                    <li>{{ trans('ahop.staff_guide_phase_b_2') }}</li>
                    <li>{{ trans('ahop.staff_guide_phase_b_3') }}</li>
                </ul>

                <h4>{{ trans('ahop.staff_guide_phase_d_title') }}</h4>
                <ul>
                    <li>{{ trans('ahop.staff_guide_phase_d_1') }}</li>
                    <li>{{ trans('ahop.staff_guide_phase_d_2') }}</li>
                    <li>{{ trans('ahop.staff_guide_phase_d_3') }}</li>
                    <li>{{ trans('ahop.staff_guide_phase_d_4') }}</li>
                </ul>

                <h4>{{ trans('ahop.staff_guide_reception_title') }}</h4>
                <ul>
                    <li>{{ trans('ahop.staff_guide_reception_1') }}</li>
                    <li>{{ trans('ahop.staff_guide_reception_2') }}</li>
                    <li>{{ trans('ahop.staff_guide_reception_3') }}</li>
                    <li>{{ trans('ahop.staff_guide_reception_4') }}</li>
                </ul>

                <h4>{{ trans('ahop.staff_guide_med_cert_title') }}</h4>
                <ul>
                    <li>{{ trans('ahop.staff_guide_med_cert_1') }}</li>
                    <li>{{ trans('ahop.staff_guide_med_cert_2') }}</li>
                </ul>

                <h4>{{ trans('ahop.staff_guide_priority1_title') }}</h4>
                <ul>
                    <li>{{ trans('ahop.staff_guide_priority1_1') }}</li>
                    <li>{{ trans('ahop.staff_guide_priority1_2') }}</li>
                    <li>{{ trans('ahop.staff_guide_priority1_3') }}</li>
                    <li>{{ trans('ahop.staff_guide_priority1_4') }}</li>
                    <li>{{ trans('ahop.staff_guide_priority1_5') }}</li>
                </ul>

                <h4>{{ trans('ahop.staff_guide_roles_assign_title') }}</h4>
                <ol>
                    <li>{{ trans('ahop.staff_guide_roles_assign_1') }}</li>
                    <li>{{ trans('ahop.staff_guide_roles_assign_2') }}</li>
                    <li>{{ trans('ahop.staff_guide_roles_assign_3') }}</li>
                </ol>

                <h4>{{ trans('ahop.staff_guide_it_title') }}</h4>
                <p class="text-muted"><small>{{ trans('ahop.staff_guide_it_note') }}</small></p>
                <pre class="well" style="margin-top: 8px;">php artisan ahop:setup-priority1 --force
php artisan ahop:backup
php artisan ahop:backup-health
php artisan schedule:run</pre>
                <p class="text-muted"><small>{{ trans('ahop.staff_guide_it_windows') }}</small></p>
            </div>
        </div>
    </div>
</div>

@stop
