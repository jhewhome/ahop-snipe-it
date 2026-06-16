<button type="button" class="ahop-back-to-top" id="ahopBackToTop" aria-label="{{ trans('general.back_to_top') }}" title="{{ trans('general.back_to_top') }}" aria-hidden="true" tabindex="-1">
    <i class="fas fa-chevron-up ahop-back-to-top__icon" aria-hidden="true"></i>
    <span class="ahop-back-to-top__label">{{ trans('general.back_to_top') }}</span>
</button>

<script nonce="{{ csrf_token() }}">
(function () {
    var btn = document.getElementById('ahopBackToTop');
    if (!btn) {
        return;
    }

    var threshold = 320;
    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function toggleVisibility() {
        var show = window.scrollY > threshold;
        btn.classList.toggle('is-visible', show);
        btn.setAttribute('aria-hidden', show ? 'false' : 'true');
        btn.tabIndex = show ? 0 : -1;
    }

    btn.addEventListener('click', function () {
        window.scrollTo({
            top: 0,
            behavior: reduceMotion ? 'auto' : 'smooth'
        });
    });

    window.addEventListener('scroll', toggleVisibility, { passive: true });
    toggleVisibility();
})();
</script>
