document.addEventListener('DOMContentLoaded', function () {
    var items = document.querySelectorAll('.rt-nav__item--mega');

    items.forEach(function (item) {
        var btn   = item.querySelector('.rt-nav__link--btn');
        var panel = item.querySelector('.rt-mega');
        if (!btn || !panel) return;

        function open() {
            item.classList.add('is-open');
            btn.setAttribute('aria-expanded', 'true');
        }

        function close() {
            item.classList.remove('is-open');
            btn.setAttribute('aria-expanded', 'false');
        }

        // Desktop — hover
        var hoverTimer;
        item.addEventListener('mouseenter', function () {
            clearTimeout(hoverTimer);
            open();
        });
        item.addEventListener('mouseleave', function () {
            hoverTimer = setTimeout(close, 120);
        });

        // Keyboard / touch — click toggle
        btn.addEventListener('click', function (e) {
            if (window.innerWidth < 1025) {
                e.preventDefault();
                item.classList.contains('is-open') ? close() : open();
            }
        });

        // Close on Escape
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') close();
        });
    });

    // Close when clicking outside
    document.addEventListener('click', function (e) {
        items.forEach(function (item) {
            if (!item.contains(e.target)) {
                item.classList.remove('is-open');
                var btn = item.querySelector('.rt-nav__link--btn');
                if (btn) btn.setAttribute('aria-expanded', 'false');
            }
        });
    });
});
