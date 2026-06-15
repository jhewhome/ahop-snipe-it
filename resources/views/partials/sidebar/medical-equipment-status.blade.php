@if (config('ahop.show_equipment_status_nav'))
    @php
        $status_navs = \App\Models\Statuslabel::where('show_in_nav', '=', 1)
            ->withCount('assets as asset_count')
            ->orderBy('name')
            ->get();
    @endphp
    @if (count($status_navs) > 0)
        @foreach ($status_navs as $status_nav)
            <li{!! (request()->is('statuslabels/'.$status_nav->id) ? ' class="active"' : '') !!}>
                <a href="{{ route('statuslabels.show', ['statuslabel' => $status_nav->id]) }}">
                    <i class="fas fa-circle text-grey fa-fw"
                       aria-hidden="true"{!! ($status_nav->color != '' ? ' style="color: '.e($status_nav->color).'"' : '') !!}></i>
                    {{ $status_nav->name }}
                    <span class="badge badge-secondary">{{ $status_nav->asset_count }}</span>
                </a>
            </li>
        @endforeach
    @endif
@endif
