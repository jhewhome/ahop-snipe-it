@if (config('ahop.theme_enabled'))
    @if (config('ahop.modern_ui', true))
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @endif
    <link rel="stylesheet" href="{{ asset('css/ahop-theme.css') }}?v=71">
    {{-- Load last: override Snipe global a:hover and nav-tabs-custom defaults --}}
    <style>
        body.ahop-theme .nav-tabs-custom.ahop-clinical-analytics-tabs > .nav-tabs > li > a,
        body.ahop-theme .nav-tabs-custom.ahop-clinical-analytics-tabs > .nav-tabs > li > a:link,
        body.ahop-theme .nav-tabs-custom.ahop-clinical-analytics-tabs > .nav-tabs > li > a:visited {
            color: #094a52 !important;
            background-color: #f8fafc !important;
        }

        body.ahop-theme .nav-tabs-custom.ahop-clinical-analytics-tabs > .nav-tabs > li > a:hover,
        body.ahop-theme .nav-tabs-custom.ahop-clinical-analytics-tabs > .nav-tabs > li > a:focus {
            color: #094a52 !important;
            background-color: #e8f7f5 !important;
        }

        body.ahop-theme .nav-tabs-custom.ahop-clinical-analytics-tabs > .nav-tabs > li.active > a,
        body.ahop-theme .nav-tabs-custom.ahop-clinical-analytics-tabs > .nav-tabs > li.active > a:link,
        body.ahop-theme .nav-tabs-custom.ahop-clinical-analytics-tabs > .nav-tabs > li.active > a:visited,
        body.ahop-theme .nav-tabs-custom.ahop-clinical-analytics-tabs > .nav-tabs > li.active > a:hover,
        body.ahop-theme .nav-tabs-custom.ahop-clinical-analytics-tabs > .nav-tabs > li.active > a:focus {
            color: #ffffff !important;
            background-color: #0d6e7a !important;
        }

        [data-theme="dark"] body.ahop-theme .nav-tabs-custom.ahop-clinical-analytics-tabs > .nav-tabs > li > a,
        [data-theme="dark"] body.ahop-theme .nav-tabs-custom.ahop-clinical-analytics-tabs > .nav-tabs > li > a:link,
        [data-theme="dark"] body.ahop-theme .nav-tabs-custom.ahop-clinical-analytics-tabs > .nav-tabs > li > a:visited {
            color: #e8f7f5 !important;
            background-color: #323131 !important;
        }

        [data-theme="dark"] body.ahop-theme .nav-tabs-custom.ahop-clinical-analytics-tabs > .nav-tabs > li > a:hover,
        [data-theme="dark"] body.ahop-theme .nav-tabs-custom.ahop-clinical-analytics-tabs > .nav-tabs > li > a:focus {
            color: #ffffff !important;
            background-color: rgba(46, 184, 166, 0.32) !important;
        }

        [data-theme="dark"] body.ahop-theme .nav-tabs-custom.ahop-clinical-analytics-tabs > .nav-tabs > li.active > a,
        [data-theme="dark"] body.ahop-theme .nav-tabs-custom.ahop-clinical-analytics-tabs > .nav-tabs > li.active > a:hover,
        [data-theme="dark"] body.ahop-theme .nav-tabs-custom.ahop-clinical-analytics-tabs > .nav-tabs > li.active > a:focus {
            color: #094a52 !important;
            background-color: #2eb8a6 !important;
        }
    </style>
@endif
