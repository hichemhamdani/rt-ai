document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.rt-carousel').forEach(function (carousel) {
        var track    = carousel.querySelector('.rt-carousel__track');
        var slides   = carousel.querySelectorAll('.rt-carousel__slide');
        var prevBtn  = carousel.querySelector('.rt-carousel__prev');
        var nextBtn  = carousel.querySelector('.rt-carousel__next');
        var dotsWrap = carousel.querySelector('.rt-carousel__dots');

        if (!track || slides.length === 0) return;

        var current  = 0;
        var autoplay = carousel.dataset.autoplay === 'true';
        var visible  = parseInt(carousel.dataset.visible || '1', 10);
        var total    = slides.length;
        var timer    = null;

        // Build dots
        var dots = [];
        if (dotsWrap) {
            for (var i = 0; i < total; i++) {
                var dot = document.createElement('button');
                dot.className = 'rt-carousel__dot' + (i === 0 ? ' is-active' : '');
                dot.setAttribute('aria-label', 'Slide ' + (i + 1));
                dotsWrap.appendChild(dot);
                dots.push(dot);
            }
        }

        function goTo(index) {
            current = (index + total) % total;
            if (visible === 1) {
                var slideWidth = slides[0].getBoundingClientRect().width;
                track.style.transform = 'translateX(-' + (current * slideWidth) + 'px)';
            } else {
                // multi-visible: scroll by one
                var slideEl = slides[0];
                var gap = parseFloat(getComputedStyle(track).gap) || 0;
                var w = slideEl.getBoundingClientRect().width + gap;
                track.style.transform = 'translateX(-' + (current * w) + 'px)';
            }
            dots.forEach(function (d, i) {
                d.classList.toggle('is-active', i === current);
            });
        }

        track.style.display = 'flex';
        track.style.transition = 'transform 0.45s ease';
        track.style.willChange = 'transform';

        if (prevBtn) prevBtn.addEventListener('click', function () { goTo(current - 1); resetTimer(); });
        if (nextBtn) nextBtn.addEventListener('click', function () { goTo(current + 1); resetTimer(); });

        dots.forEach(function (dot, i) {
            dot.addEventListener('click', function () { goTo(i); resetTimer(); });
        });

        function startTimer() {
            if (!autoplay) return;
            timer = setInterval(function () { goTo(current + 1); }, 4000);
        }

        function resetTimer() {
            clearInterval(timer);
            startTimer();
        }

        startTimer();

        // Touch support
        var startX = 0;
        track.addEventListener('touchstart', function (e) { startX = e.touches[0].clientX; }, { passive: true });
        track.addEventListener('touchend', function (e) {
            var diff = startX - e.changedTouches[0].clientX;
            if (Math.abs(diff) > 40) goTo(diff > 0 ? current + 1 : current - 1);
        }, { passive: true });
    });

});
