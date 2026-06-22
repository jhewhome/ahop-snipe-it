    @if (config('ahop.theme_enabled'))
    @if (config('ahop.theme_variant') === 'sleek' || config('ahop.modern_ui', true))
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @endif
    <link rel="stylesheet" href="{{ asset('css/ahop-theme.css') }}?v=90">
    @if (config('ahop.theme_variant') === 'sleek')
        <link rel="stylesheet" href="{{ asset('css/ahop-theme-sleek-login.css') }}?v=2">
        <link rel="stylesheet" href="{{ asset('css/ahop-theme-sleek-app.css') }}?v=7">
    @endif
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

        /* Mobile layout — must beat AdminLTE sidebar-mini.sidebar-collapse offsets */
        @media (max-width: 767px) {
            /* Off-canvas sidebar drawer */
            body.ahop-theme {
                --ahop-mobile-header-offset: 125px;
            }

            body.ahop-theme .main-sidebar {
                position: fixed !important;
                top: var(--ahop-mobile-header-offset);
                left: 0;
                height: calc(100vh - var(--ahop-mobile-header-offset));
                width: 230px;
                transform: translateX(-230px);
                transition: transform 0.2s ease-in-out;
                z-index: 10000;
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
            }

            body.ahop-theme.sidebar-open .main-sidebar {
                transform: translateX(0);
            }

            body.ahop-theme.sidebar-mini.sidebar-collapse .content-wrapper,
            body.ahop-theme.sidebar-mini.sidebar-collapse .main-footer,
            body.ahop-theme.sidebar-open .content-wrapper,
            body.ahop-theme.sidebar-open .main-footer,
            body.ahop-theme .content-wrapper,
            body.ahop-theme .main-footer {
                margin-left: 0 !important;
                transform: none !important;
                width: 100% !important;
                max-width: 100% !important;
            }

            body.ahop-theme.sidebar-mini.sidebar-collapse .main-header .navbar,
            body.ahop-theme .main-header .navbar {
                margin-left: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                float: none !important;
            }

            body.ahop-theme .main-header,
            body.ahop-theme .wrapper {
                width: 100% !important;
                max-width: 100vw !important;
            }

            body.ahop-theme .wrapper {
                overflow-x: hidden;
            }

            body.ahop-theme .main-header .navbar.navbar-static-top {
                flex-wrap: wrap;
                align-items: center;
                padding: 6px 8px 8px;
            }

            body.ahop-theme .main-header .sidebar-toggle,
            body.ahop-theme .main-header a.sidebar-toggle {
                margin-left: 0 !important;
                flex-shrink: 0;
            }

            body.ahop-theme .main-header .navbar-left {
                flex: 1 1 auto;
                min-width: 0;
                margin: 0 !important;
            }

            body.ahop-theme .main-header .navbar-custom-menu {
                flex: 1 1 100%;
                width: 100%;
                float: none !important;
            }

            body.ahop-theme .main-header .navbar-custom-menu > .navbar-nav {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                width: 100%;
                margin: 0;
                float: none !important;
            }

            body.ahop-theme .main-header .navbar-form,
            body.ahop-theme .main-header .navbar-custom-menu .navbar-form {
                display: none !important;
            }

            body.ahop-theme .main-header .ahop-header-tagline {
                display: none !important;
            }

            body.ahop-theme .main-header .ahop-header-title {
                font-size: 14px;
                max-width: 100%;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            body.ahop-theme .content-header .pagetitle,
            body.ahop-theme .content-header h1 {
                float: none !important;
                width: 100%;
                white-space: normal;
            }

            body.ahop-theme .sidebar-menu,
            body.ahop-theme .sidebar-menu.tree,
            body.ahop-theme ul.sidebar-menu[data-widget="tree"] {
                margin-top: 0 !important;
            }

            body.ahop-theme .sidebar-menu > li.firstnav,
            body.ahop-theme .firstnav {
                padding-top: 0 !important;
                margin-top: 0 !important;
            }

            body.ahop-theme.ahop-clinical-mode .main-header .navbar-custom-menu > .navbar-nav > li[aria-hidden="true"]:not(.dropdown) {
                display: none !important;
            }
        }
    </style>
@endif
