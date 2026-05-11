document.addEventListener('DOMContentLoaded', function () {
    var header   = document.getElementById('rt-header');
    var topbar   = document.getElementById('rt-topbar');
    var mainnav  = document.getElementById('rt-mainnav');
    var burger   = document.getElementById('rt-burger');
    var nav      = document.getElementById('rt-nav');
    var scrolled = false;

    function onScroll() {
        var y         = window.scrollY || window.pageYOffset;
        var threshold = topbar ? topbar.offsetHeight : 40;

        if (y > threshold && !scrolled) {
            scrolled = true;
            mainnav && mainnav.classList.add('is-scrolled');
        } else if (y <= threshold && scrolled) {
            scrolled = false;
            mainnav && mainnav.classList.remove('is-scrolled');
        }
    }

    window.addEventListener('scroll', onScroll, { passive: true });

    // Burger toggle
    if (burger && nav) {
        burger.addEventListener('click', function () {
            var open = burger.classList.toggle('is-open');
            nav.classList.toggle('is-mobile-open', open);
            burger.setAttribute('aria-expanded', String(open));
        });
    }
});
