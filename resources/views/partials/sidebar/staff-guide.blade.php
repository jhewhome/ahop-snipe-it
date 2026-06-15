@if (config('ahop.theme_enabled') && config('ahop.priority1.staff_guide_enabled', true))
    <li {!! (request()->is('staff-guide*') ? ' class="active"' : '') !!}>
        <a href="{{ route('staff-guide.index') }}">
            <i class="fas fa-book-medical fa-fw" aria-hidden="true"></i>
            <span>{{ trans('ahop.staff_guide_nav') }}</span>
        </a>
    </li>
@endif
