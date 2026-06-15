<script nonce="{{ csrf_token() }}">
(function () {
    if (!document.body.classList.contains('ahop-phase-c')) {
        return;
    }

    var backdrop = document.createElement('div');
    backdrop.className = 'ahop-sidebar-backdrop';
    backdrop.setAttribute('aria-hidden', 'true');
    document.body.appendChild(backdrop);

    function syncBackdrop() {
        var mobile = window.matchMedia('(max-width: 767px)').matches;
        var open = document.body.classList.contains('sidebar-open');
        backdrop.classList.toggle('is-visible', mobile && open);
    }

    backdrop.addEventListener('click', function () {
        document.body.classList.remove('sidebar-open');
        syncBackdrop();
    });

    document.addEventListener('click', function (event) {
        if (event.target.closest('[data-toggle="push-menu"]')) {
            window.setTimeout(syncBackdrop, 50);
        }
    });

    window.addEventListener('resize', syncBackdrop);

    var observer = new MutationObserver(syncBackdrop);
    observer.observe(document.body, { attributes: true, attributeFilter: ['class'] });

    syncBackdrop();
})();
</script>
