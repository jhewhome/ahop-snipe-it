@if(Gate::allows('view', \App\Models\Patient::class) || Gate::allows('view', \App\Models\Appointment::class) || Gate::allows('view', \App\Models\OpdVisit::class) || Gate::allows('view', \App\Models\LabOrder::class) || Gate::allows('view', \App\Models\BillingInvoice::class) || Gate::allows('ai_insights.view'))

    <li class="treeview{{ (request()->is('patients*', 'reception*', 'appointments*', 'opd-visits*', 'lab-orders*', 'billing-invoices*', 'clinical-analytics*') ? ' active' : '') }}" id="clinical-services-sidenav">

        <a href="#">

            <i class="fas fa-hospital-user fa-fw" aria-hidden="true"></i>

            <span>{{ trans('general.clinical_services') }}</span>

            <x-icon type="angle-left" class="pull-right fa-fw"/>

        </a>

        <ul class="treeview-menu">

            @if (Gate::allows('view', \App\Models\Patient::class) && (Gate::allows('create', \App\Models\OpdVisit::class) || Gate::allows('index', \App\Models\Appointment::class)))

                <li {{ (request()->is('reception*') ? ' class="active"' : '') }}>

                    <a href="{{ route('reception.check-in') }}">

                        <x-icon type="circle" class="text-grey fa-fw"/>

                        {{ trans('general.reception_check_in') }}

                    </a>

                </li>

            @endif

            @can('view', \App\Models\Patient::class)

                <li {{ (request()->is('patients*') ? ' class="active"' : '') }}>

                    <a href="{{ route('patients.index') }}">

                        <x-icon type="circle" class="text-grey fa-fw"/>

                        {{ trans('general.patients') }}

                    </a>

                </li>

            @endcan

            @can('view', \App\Models\Appointment::class)

                <li {{ (request()->is('appointments') && ! request()->is('appointments/calendar') ? ' class="active"' : '') }}>

                    <a href="{{ route('appointments.index') }}">

                        <x-icon type="circle" class="text-grey fa-fw"/>

                        {{ trans('general.appointments') }}

                    </a>

                </li>

                <li {{ (request()->is('appointments/calendar') ? ' class="active"' : '') }}>

                    <a href="{{ route('appointments.calendar') }}">

                        <x-icon type="circle" class="text-grey fa-fw"/>

                        {{ trans('admin/appointments/table.calendar') }}

                    </a>

                </li>

            @endcan

            @can('view', \App\Models\OpdVisit::class)

                <li {{ (request()->is('opd-visits/queue') ? ' class="active"' : '') }}>

                    <a href="{{ route('opd-visits.queue') }}">

                        <x-icon type="circle" class="text-grey fa-fw"/>

                        {{ trans('admin/opd_visits/table.queue_title') }}

                    </a>

                </li>

                <li {{ (request()->is('opd-visits*') && ! request()->is('opd-visits/queue') ? ' class="active"' : '') }}>

                    <a href="{{ route('opd-visits.index') }}">

                        <x-icon type="circle" class="text-grey fa-fw"/>

                        {{ trans('general.opd_visits') }}

                    </a>

                </li>

            @endcan

            @can('view', \App\Models\LabOrder::class)

                <li {{ (request()->is('lab-orders*') ? ' class="active"' : '') }}>

                    <a href="{{ route('lab-orders.index') }}">

                        <x-icon type="circle" class="text-grey fa-fw"/>

                        {{ trans('general.lab_orders') }}

                    </a>

                </li>

            @endcan

            @can('view', \App\Models\BillingInvoice::class)

                <li {{ (request()->is('billing-invoices*') ? ' class="active"' : '') }}>

                    <a href="{{ route('billing-invoices.index') }}">

                        <x-icon type="circle" class="text-grey fa-fw"/>

                        {{ trans('general.billing') }}

                    </a>

                </li>

            @endcan

            @can('ai_insights.view')

                <li {{ (request()->is('clinical-analytics*') ? ' class="active"' : '') }}>

                    <a href="{{ route('clinical-analytics.index') }}">

                        <x-icon type="circle" class="text-grey fa-fw"/>

                        {{ trans('general.clinical_analytics') }}

                    </a>

                </li>

            @endcan

        </ul>

    </li>

@endif

