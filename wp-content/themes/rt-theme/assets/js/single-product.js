(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var gallery = document.getElementById('sp-gallery');
        if (!gallery) return;

        var slides = Array.from(gallery.querySelectorAll('.sp-gallery__slide'));
        var dots   = Array.from(gallery.querySelectorAll('.sp-gallery__dot'));
        var current = 0;

        function goTo(index) {
            slides[current].classList.remove('is-active');
            dots[current] && dots[current].classList.remove('is-active');
            current = index;
            slides[current].classList.add('is-active');
            dots[current] && dots[current].classList.add('is-active');
        }

        dots.forEach(function (dot, i) {
            dot.addEventListener('click', function () { goTo(i); });
        });

        // Auto-advance
        if (slides.length > 1) {
            setInterval(function () {
                goTo((current + 1) % slides.length);
            }, 3500);
        }

        // Lightbox on click
        slides.forEach(function (slide) {
            slide.addEventListener('click', function (e) {
                e.preventDefault();
                var href = slide.getAttribute('href');
                if (!href) return;
                var overlay = document.createElement('div');
                overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.88);z-index:9999;display:flex;align-items:center;justify-content:center;cursor:zoom-out;';
                var img = document.createElement('img');
                img.src = href;
                img.style.cssText = 'max-width:90vw;max-height:90vh;object-fit:contain;';
                overlay.appendChild(img);
                overlay.addEventListener('click', function () { document.body.removeChild(overlay); });
                document.body.appendChild(overlay);
            });
        });
    });
})();
